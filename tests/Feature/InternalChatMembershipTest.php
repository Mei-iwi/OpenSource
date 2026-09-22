<?php

namespace Tests\Feature;

use App\Models\ChatChannel;
use App\Models\ChatChannelMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalChatMembershipTest extends TestCase
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

    public function test_admin_can_add_members_to_channel(): void
    {
        $admin = $this->createUser('admin');
        $user1 = $this->createUser('employee');
        $user2 = $this->createUser('employee');

        $channel = ChatChannel::create([
            'name' => 'Kênh Kế Toán',
            'slug' => 'ke-toan',
            'type' => 'group',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('chat.channels.members.store', $channel->slug), [
            'user_ids' => [$user1->id, $user2->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('chat_channel_members', [
            'channel_id' => $channel->id,
            'user_id' => $user1->id,
        ]);
        $this->assertDatabaseHas('chat_channel_members', [
            'channel_id' => $channel->id,
            'user_id' => $user2->id,
        ]);
    }

    public function test_duplicate_member_in_channel_is_prevented_and_idempotent(): void
    {
        $admin = $this->createUser('admin');
        $user = $this->createUser('employee');

        $channel = ChatChannel::create([
            'name' => 'Kênh Nhóm',
            'slug' => 'kenh-nhom',
            'type' => 'group',
            'created_by' => $admin->id,
        ]);

        // Thêm lần 1
        ChatChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'joined_at' => now(),
        ]);

        // Thêm lần 2 qua action
        $this->actingAs($admin)->post(route('chat.channels.members.store', $channel->slug), [
            'user_ids' => [$user->id],
        ]);

        // Không tạo 2 bản ghi cho cùng 1 user trong kênh
        $this->assertEquals(1, ChatChannelMember::where('channel_id', $channel->id)->where('user_id', $user->id)->count());
    }

    public function test_admin_can_remove_member_and_user_immediately_loses_access(): void
    {
        $admin = $this->createUser('admin');
        $user = $this->createUser('employee');

        $channel = ChatChannel::create([
            'name' => 'Kênh Bảo Mật',
            'slug' => 'kenh-bao-mat',
            'type' => 'group',
            'created_by' => $admin->id,
        ]);

        ChatChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'joined_at' => now(),
        ]);

        // Trước khi xóa: user truy cập bình thường
        $this->actingAs($user)->get(route('chat.channels.show', $channel->slug))->assertOk();

        // Admin xóa thành viên
        $this->flushSession();
        $response = $this->actingAs($admin)->delete(route('chat.channels.members.destroy', [
            'channel' => $channel->slug,
            'user' => $user->id,
        ]));
        $response->assertRedirect();

        $this->assertDatabaseMissing('chat_channel_members', [
            'channel_id' => $channel->id,
            'user_id' => $user->id,
        ]);

        // Sau khi bị xóa: user bị chặn ngay lập tức với mã lỗi 403
        $this->flushSession();
        $this->actingAs($user)->get(route('chat.channels.show', $channel->slug))->assertForbidden();
    }

    public function test_regular_employee_cannot_add_or_remove_members(): void
    {
        $emp1 = $this->createUser('employee');
        $emp2 = $this->createUser('employee');
        $channel = ChatChannel::create([
            'name' => 'Kênh Dự Án',
            'slug' => 'kenh-du-an',
            'type' => 'group',
        ]);
        ChatChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $emp1->id,
        ]);

        // Employee 1 cố thêm Employee 2
        $this->actingAs($emp1)->post(route('chat.channels.members.store', $channel->slug), [
            'user_ids' => [$emp2->id],
        ])->assertForbidden();

        // Employee 1 cố xóa chính mình hoặc người khác qua route quản lý
        $this->actingAs($emp1)->delete(route('chat.channels.members.destroy', [
            'channel' => $channel->slug,
            'user' => $emp1->id,
        ]))->assertForbidden();
    }
}
