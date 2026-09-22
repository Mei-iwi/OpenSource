<?php

namespace Tests\Feature;

use App\Models\ChatChannel;
use App\Models\ChatChannelMember;
use App\Models\ChatMessage;
use App\Models\ChatMessageAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InternalChatRichMessageTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function createUser(string $role = 'employee', array $attrs = []): User
    {
        $id = $this->seq++;

        return User::create(array_merge([
            'name' => "User {$role} {$id}",
            'email' => "rich_user{$id}_{$role}@example.com",
            'password' => Hash::make('Password123!'),
            'role' => $role,
            'account_status' => 'active',
        ], $attrs));
    }

    protected function createChannel(User ...$members): ChatChannel
    {
        $channel = ChatChannel::create([
            'name' => 'Kênh Rich Text '.$this->seq++,
            'slug' => 'rich-text-'.$this->seq,
            'type' => 'group',
        ]);

        foreach ($members as $user) {
            ChatChannelMember::create([
                'channel_id' => $channel->id,
                'user_id' => $user->id,
                'joined_at' => now(),
            ]);
        }

        return $channel;
    }

    public function test_rich_text_formatting_renders_properly(): void
    {
        $user = $this->createUser('employee');
        $channel = $this->createChannel($user);

        $rawText = "Đây là **in đậm** và *in nghiêng* cùng ~~gạch ngang~~\n> Trích dẫn quan trọng\n- Mục 1\n- Mục 2\nXem tại https://example.com/tailieu";

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => $rawText,
        ]);

        $html = $message->formatted_html;

        $this->assertStringContainsString('<strong>in đậm</strong>', $html);
        $this->assertStringContainsString('<em>in nghiêng</em>', $html);
        $this->assertStringContainsString('<del>gạch ngang</del>', $html);
        $this->assertStringContainsString('<blockquote', $html);
        $this->assertStringContainsString('Trích dẫn quan trọng', $html);
        $this->assertStringContainsString('<li class="ml-4 list-disc">Mục 1</li>', $html);
        $this->assertStringContainsString('<li class="ml-4 list-disc">Mục 2</li>', $html);
        $this->assertStringContainsString('href="https://example.com/tailieu"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
    }

    public function test_strict_xss_prevention_in_rich_text(): void
    {
        $user = $this->createUser('employee');
        $channel = $this->createChannel($user);

        $malicious = "<script>alert('XSS')</script> **an toàn** <img src=x onerror=alert(1)> <a href=\"javascript:alert(2)\">click</a>";

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => $malicious,
        ]);

        $html = $message->formatted_html;

        // Raw tags must NOT be present
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringNotContainsString('href="javascript:', $html);

        // Entities must be escaped
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('&lt;img', $html);

        // Formatting applied cleanly
        $this->assertStringContainsString('<strong>an toàn</strong>', $html);
    }

    public function test_user_can_send_message_with_attachments(): void
    {
        Storage::fake('local');

        $user = $this->createUser('employee');
        $channel = $this->createChannel($user);

        $imageFile = UploadedFile::fake()->image('bang-ke-luong.png', 200, 200);
        $pdfFile = UploadedFile::fake()->create('bao-cao.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->post(route('chat.channels.messages.store', $channel->slug), [
            'message' => 'Gửi kèm bảng kê và báo cáo',
            'attachments' => [$imageFile, $pdfFile],
        ], ['Accept' => 'application/json']);

        $response->assertCreated();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(2, 'message.attachments');

        $this->assertDatabaseHas('chat_message_attachments', [
            'file_name' => 'bang-ke-luong.png',
            'is_image' => 1,
        ]);

        $this->assertDatabaseHas('chat_message_attachments', [
            'file_name' => 'bao-cao.pdf',
            'is_image' => 0,
        ]);
    }

    public function test_user_can_send_attachment_without_text(): void
    {
        Storage::fake('local');

        $user = $this->createUser('employee');
        $channel = $this->createChannel($user);

        $imageFile = UploadedFile::fake()->image('anh-chup.jpg', 100, 100);

        $response = $this->actingAs($user)->post(route('chat.channels.messages.store', $channel->slug), [
            'message' => '',
            'attachments' => [$imageFile],
        ], ['Accept' => 'application/json']);

        $response->assertCreated();
        $response->assertJsonPath('success', true);
    }

    public function test_channel_member_can_download_attachment_with_security_headers(): void
    {
        Storage::fake('local');

        $user = $this->createUser('employee');
        $channel = $this->createChannel($user);

        $storedPath = 'chat_attachments/2026/09/test-file.pdf';
        Storage::disk('local')->put($storedPath, 'Fake PDF Content');

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => 'Tệp mẫu',
        ]);

        $attachment = ChatMessageAttachment::create([
            'message_id' => $message->id,
            'file_path' => $storedPath,
            'file_name' => 'test-file.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'is_image' => false,
        ]);

        $response = $this->actingAs($user)->get(route('chat.attachments.show', $attachment->id));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertEquals('Fake PDF Content', $response->streamedContent());
    }

    public function test_non_member_cannot_download_attachment_idor_protection(): void
    {
        Storage::fake('local');

        $member = $this->createUser('employee');
        $nonMember = $this->createUser('employee');
        $channel = $this->createChannel($member);

        $storedPath = 'chat_attachments/2026/09/secret-doc.pdf';
        Storage::disk('local')->put($storedPath, 'Secret Content');

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $member->id,
            'message' => 'Tài liệu mật nội bộ',
        ]);

        $attachment = ChatMessageAttachment::create([
            'message_id' => $message->id,
            'file_path' => $storedPath,
            'file_name' => 'secret-doc.pdf',
            'file_size' => 2048,
            'mime_type' => 'application/pdf',
            'is_image' => false,
        ]);

        $this->flushSession();
        $response = $this->actingAs($nonMember)->get(route('chat.attachments.show', $attachment->id));

        $response->assertForbidden();
    }
}
