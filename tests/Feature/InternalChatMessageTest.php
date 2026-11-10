<?php

namespace Tests\Feature;

use App\Models\ChatChannel;
use App\Models\ChatChannelMember;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalChatMessageTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function createUser(string $role = 'employee', array $attrs = []): User
    {
        $id = $this->seq++;

        return User::create(array_merge([
            'name' => "User {$role} {$id}",
            'email' => "user{$id}_{$role}@example.com",
            'password' => Hash::make('Password123!'),
            'role' => $role,
            'account_status' => 'active',
        ], $attrs));
    }

    protected function createChannelWithMember(User $user): ChatChannel
    {
        $channel = ChatChannel::create([
            'name' => 'Kênh Thảo Luận '.$this->seq++,
            'slug' => 'kenh-thao-luan-'.$this->seq,
            'type' => 'group',
        ]);

        ChatChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'joined_at' => now(),
        ]);

        return $channel;
    }

    public function test_channel_member_can_send_message(): void
    {
        $user = $this->createUser('employee');
        $channel = $this->createChannelWithMember($user);

        $response = $this->actingAs($user)->post(route('chat.channels.messages.store', $channel->slug), [
            'message' => 'Xin chào mọi người trong phòng ban!',
        ]);

        $response->assertRedirect();
        $message = ChatMessage::where('channel_id', $channel->id)->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Xin chào mọi người trong phòng ban!', $message->message);
        $stored = DB::table('chat_messages')->where('id', $message->id)->value('message');
        $this->assertStringStartsWith('enc:v1:', $stored);
        $this->assertStringNotContainsString('Xin chào', $stored);
    }

    public function test_existing_plaintext_messages_are_encrypted_without_losing_readability(): void
    {
        $user = $this->createUser();
        $channel = $this->createChannelWithMember($user);
        $id = DB::table('chat_messages')->insertGetId([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => 'Legacy private message',
        ]);

        $migration = require database_path('migrations/2026_11_10_000000_encrypt_existing_chat_messages.php');
        $migration->up();
        $encrypted = DB::table('chat_messages')->where('id', $id)->value('message');
        $this->assertStringStartsWith('enc:v1:', $encrypted);
        $this->assertSame('Legacy private message', ChatMessage::findOrFail($id)->message);

        $migration->up();
        $this->assertSame($encrypted, DB::table('chat_messages')->where('id', $id)->value('message'));
    }

    public function test_non_member_cannot_send_message_to_channel(): void
    {
        $member = $this->createUser('employee');
        $outsider = $this->createUser('employee');
        $channel = $this->createChannelWithMember($member);

        $response = $this->actingAs($outsider)->post(route('chat.channels.messages.store', $channel->slug), [
            'message' => 'Tin nhắn từ người ngoài kênh',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('chat_messages', [
            'message' => 'Tin nhắn từ người ngoài kênh',
        ]);
    }

    public function test_empty_or_whitespace_message_is_rejected(): void
    {
        $user = $this->createUser('employee');
        $channel = $this->createChannelWithMember($user);

        // Tin nhắn rỗng
        $resp1 = $this->actingAs($user)->post(route('chat.channels.messages.store', $channel->slug), [
            'message' => '',
        ]);
        $resp1->assertSessionHasErrors('message');

        // Tin nhắn chỉ có dấu cách
        $resp2 = $this->actingAs($user)->post(route('chat.channels.messages.store', $channel->slug), [
            'message' => '     ',
        ]);
        $resp2->assertSessionHasErrors('message');
    }

    public function test_message_length_limit_is_enforced(): void
    {
        $user = $this->createUser('employee');
        $channel = $this->createChannelWithMember($user);

        $tooLongMessage = str_repeat('A', 3001);

        $response = $this->actingAs($user)->post(route('chat.channels.messages.store', $channel->slug), [
            'message' => $tooLongMessage,
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_xss_payload_in_message_is_safely_handled(): void
    {
        $user = $this->createUser('employee');
        $channel = $this->createChannelWithMember($user);

        $xssPayload = '<script>alert("XSS-ATTACK")</script>';

        $response = $this->actingAs($user)->postJson(route('chat.channels.messages.store', $channel->slug), [
            'message' => $xssPayload,
        ]);

        $response->assertCreated();
        // Kiểm tra trong JSON phản hồi, message đã được escape
        $response->assertJson([
            'success' => true,
            'message' => [
                'message' => e($xssPayload),
            ],
        ]);

        // Kiểm tra API messages index cũng escape
        $indexResp = $this->actingAs($user)->getJson(route('chat.channels.messages.index', $channel->slug));
        $indexResp->assertOk();
        $this->assertStringNotContainsString('<script>alert("XSS-ATTACK")</script>', $indexResp->json('messages.0.message'));
        $this->assertEquals(e($xssPayload), $indexResp->json('messages.0.message'));
    }

    public function test_user_can_edit_their_own_message(): void
    {
        $user = $this->createUser('employee');
        $channel = $this->createChannelWithMember($user);

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => 'Nội dung ban đầu',
        ]);

        $response = $this->actingAs($user)->patch(route('chat.messages.update', $message->id), [
            'message' => 'Nội dung đã được chỉnh sửa',
        ]);

        $response->assertRedirect();
        $message->refresh();
        $this->assertEquals('Nội dung đã được chỉnh sửa', $message->message);
        $this->assertNotNull($message->edited_at);
    }

    public function test_user_cannot_edit_another_user_message(): void
    {
        $user1 = $this->createUser('employee');
        $user2 = $this->createUser('employee');
        $channel = $this->createChannelWithMember($user1);
        ChatChannelMember::create(['channel_id' => $channel->id, 'user_id' => $user2->id]);

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $user1->id,
            'message' => 'Tin nhắn của User 1',
        ]);

        // User 2 cố sửa tin nhắn của User 1
        $response = $this->actingAs($user2)->patch(route('chat.messages.update', $message->id), [
            'message' => 'User 2 cố sửa',
        ]);

        $response->assertForbidden();
        $this->assertEquals('Tin nhắn của User 1', $message->fresh()->message);
    }

    public function test_user_can_delete_their_own_message(): void
    {
        $user = $this->createUser('employee');
        $channel = $this->createChannelWithMember($user);

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => 'Tin nhắn sắp xóa',
        ]);

        $response = $this->actingAs($user)->delete(route('chat.messages.destroy', $message->id));
        $response->assertRedirect();

        $this->assertSoftDeleted('chat_messages', ['id' => $message->id]);
    }

    public function test_admin_can_delete_any_message_for_moderation(): void
    {
        $admin = $this->createUser('admin');
        $user = $this->createUser('employee');
        $channel = $this->createChannelWithMember($user);

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => 'Tin nhắn vi phạm',
        ]);

        $response = $this->actingAs($admin)->delete(route('chat.messages.destroy', $message->id));
        $response->assertRedirect();

        $this->assertSoftDeleted('chat_messages', ['id' => $message->id]);
    }
}
