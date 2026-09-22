<?php

namespace Tests\Feature;

use App\Models\ChatChannel;
use App\Models\ChatChannelMember;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalChatAuthorizationIdorTest extends TestCase
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

    public function test_user_in_channel_a_cannot_access_channel_b_via_url_or_idor(): void
    {
        $userA = $this->createUser('employee');
        $userB = $this->createUser('employee');

        // Kênh A (User A là thành viên)
        $channelA = ChatChannel::create([
            'name' => 'Kênh Kế Toán',
            'slug' => 'ke-toan',
            'type' => 'group',
        ]);
        ChatChannelMember::create(['channel_id' => $channelA->id, 'user_id' => $userA->id]);

        // Kênh B (User B là thành viên)
        $channelB = ChatChannel::create([
            'name' => 'Kênh Nhân Sự',
            'slug' => 'nhan-su',
            'type' => 'group',
        ]);
        ChatChannelMember::create(['channel_id' => $channelB->id, 'user_id' => $userB->id]);

        // User B cố truy cập trực tiếp vào Kênh A qua URL
        $response = $this->actingAs($userB)->get(route('chat.channels.show', $channelA->slug));
        $response->assertForbidden();

        // User A cố truy cập trực tiếp vào Kênh B qua URL
        $this->flushSession();
        $response2 = $this->actingAs($userA)->get(route('chat.channels.show', $channelB->slug));
        $response2->assertForbidden();
    }

    public function test_user_cannot_fetch_messages_from_unauthorized_channel_via_api(): void
    {
        $userA = $this->createUser('employee');
        $userB = $this->createUser('employee');

        $channelA = ChatChannel::create([
            'name' => 'Kênh Bí Mật A',
            'slug' => 'bi-mat-a',
            'type' => 'group',
        ]);
        ChatChannelMember::create(['channel_id' => $channelA->id, 'user_id' => $userA->id]);

        ChatMessage::create([
            'channel_id' => $channelA->id,
            'user_id' => $userA->id,
            'message' => 'Nội dung tuyệt mật phòng ban A',
        ]);

        // User B gọi API lấy tin nhắn của Kênh A
        $response = $this->actingAs($userB)->getJson(route('chat.channels.messages.index', $channelA->slug));

        $response->assertForbidden();
        $response->assertJsonMissing([
            'message' => 'Nội dung tuyệt mật phòng ban A',
        ]);
    }

    public function test_user_cannot_send_messages_to_unauthorized_channel_via_api(): void
    {
        $userA = $this->createUser('employee');
        $userB = $this->createUser('employee');

        $channelA = ChatChannel::create([
            'name' => 'Kênh Bí Mật A',
            'slug' => 'bi-mat-a',
            'type' => 'group',
        ]);
        ChatChannelMember::create(['channel_id' => $channelA->id, 'user_id' => $userA->id]);

        // User B cố POST tin nhắn vào Kênh A
        $response = $this->actingAs($userB)->postJson(route('chat.channels.messages.store', $channelA->slug), [
            'message' => 'Spam từ người ngoài',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('chat_messages', [
            'channel_id' => $channelA->id,
            'user_id' => $userB->id,
        ]);
    }

    public function test_user_cannot_mark_unauthorized_channel_as_read(): void
    {
        $userA = $this->createUser('employee');
        $userB = $this->createUser('employee');

        $channelA = ChatChannel::create([
            'name' => 'Kênh A',
            'slug' => 'kenh-a',
            'type' => 'group',
        ]);
        ChatChannelMember::create(['channel_id' => $channelA->id, 'user_id' => $userA->id]);

        $response = $this->actingAs($userB)->postJson(route('chat.channels.read', $channelA->slug));
        $response->assertForbidden();
    }
}
