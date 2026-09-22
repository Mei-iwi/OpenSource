<?php

namespace Tests\Feature;

use App\Models\ChatChannel;
use App\Models\ChatChannelMember;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalChatUnreadTest extends TestCase
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

    public function test_new_message_from_other_member_increases_unread_count(): void
    {
        $userA = $this->createUser('employee');
        $userB = $this->createUser('employee');

        $channel = ChatChannel::create([
            'name' => 'Kênh Trao Đổi',
            'slug' => 'trao-doi',
            'type' => 'group',
        ]);

        $memberA = ChatChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $userA->id,
            'last_read_at' => now()->subMinutes(10),
        ]);
        ChatChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $userB->id,
            'last_read_at' => now(),
        ]);

        // User B gửi 2 tin nhắn mới
        ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $userB->id,
            'message' => 'Tin nhắn 1',
            'created_at' => now()->subMinutes(5),
        ]);
        ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $userB->id,
            'message' => 'Tin nhắn 2',
            'created_at' => now()->subMinutes(2),
        ]);

        // Kiểm tra unread count cho User A
        $this->assertEquals(2, $channel->unreadCountFor($userA->id));

        // Kiểm tra unread count cho User B (chính người gửi) -> 0
        $this->assertEquals(0, $channel->unreadCountFor($userB->id));
    }

    public function test_visiting_channel_marks_messages_as_read(): void
    {
        $userA = $this->createUser('employee');
        $userB = $this->createUser('employee');

        $channel = ChatChannel::create([
            'name' => 'Kênh Dự Án',
            'slug' => 'du-an',
            'type' => 'group',
        ]);

        $memberA = ChatChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $userA->id,
            'last_read_at' => now()->subHour(),
        ]);
        ChatChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $userB->id,
        ]);

        ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $userB->id,
            'message' => 'Thông báo mới',
            'created_at' => now()->subMinutes(10),
        ]);

        // Trước khi xem: unread = 1
        $this->assertEquals(1, $channel->unreadCountFor($userA->id));

        // User A mở kênh
        $response = $this->actingAs($userA)->get(route('chat.channels.show', $channel->slug));
        $response->assertOk();

        // Sau khi mở: unread trở về 0
        $memberA->refresh();
        $this->assertEquals(0, $channel->unreadCountFor($userA->id));
    }

    public function test_unread_summary_api_returns_accurate_unread_counts(): void
    {
        $userA = $this->createUser('employee');
        $userB = $this->createUser('employee');

        $channel1 = ChatChannel::create([
            'name' => 'Kênh 1',
            'slug' => 'kenh-1',
            'type' => 'group',
        ]);
        $channel2 = ChatChannel::create([
            'name' => 'Kênh 2',
            'slug' => 'kenh-2',
            'type' => 'group',
        ]);

        ChatChannelMember::create([
            'channel_id' => $channel1->id,
            'user_id' => $userA->id,
            'last_read_at' => now()->subMinutes(10),
        ]);
        ChatChannelMember::create([
            'channel_id' => $channel2->id,
            'user_id' => $userA->id,
            'last_read_at' => now()->subMinutes(10),
        ]);

        // Channel 1 có 3 tin nhắn
        for ($i = 1; $i <= 3; $i++) {
            ChatMessage::create([
                'channel_id' => $channel1->id,
                'user_id' => $userB->id,
                'message' => "Msg {$i}",
                'created_at' => now()->subMinutes(2),
            ]);
        }

        // Channel 2 có 1 tin nhắn
        ChatMessage::create([
            'channel_id' => $channel2->id,
            'user_id' => $userB->id,
            'message' => 'Single msg',
            'created_at' => now()->subMinutes(2),
        ]);

        $response = $this->actingAs($userA)->getJson(route('chat.unread-summary'));

        $response->assertOk();
        $response->assertJson([
            'unread_counts' => [
                $channel1->id => 3,
                $channel2->id => 1,
            ],
            'total_unread' => 4,
        ]);
    }
}
