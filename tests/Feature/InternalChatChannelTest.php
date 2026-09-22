<?php

namespace Tests\Feature;

use App\Models\ChatChannel;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\ChatSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalChatChannelTest extends TestCase
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

    protected function createDepartment(): Department
    {
        $id = $this->seq++;

        return Department::create([
            'code' => 'PB-CHAT-'.$id,
            'name' => 'Phòng Chat '.$id,
            'description' => 'Mô tả phòng ban',
        ]);
    }

    public function test_admin_can_create_department_chat_channel(): void
    {
        $admin = $this->createUser('admin');
        $dept = $this->createDepartment();

        $response = $this->actingAs($admin)->post(route('chat.channels.store'), [
            'name' => 'Kế toán tài chính',
            'slug' => 'ke-toan-tai-chinh',
            'description' => 'Kênh trao đổi kế toán',
            'type' => 'department',
            'department_id' => $dept->id,
        ]);

        $response->assertRedirect(route('chat.channels.show', 'ke-toan-tai-chinh'));
        $this->assertDatabaseHas('chat_channels', [
            'slug' => 'ke-toan-tai-chinh',
            'name' => 'Kế toán tài chính',
            'type' => 'department',
            'department_id' => $dept->id,
            'created_by' => $admin->id,
        ]);
    }

    public function test_hr_can_create_group_chat_channel(): void
    {
        $hr = $this->createUser('hr');

        $response = $this->actingAs($hr)->post(route('chat.channels.store'), [
            'name' => 'Dự án Alpha',
            'slug' => 'du-an-alpha',
            'description' => 'Thảo luận dự án Alpha',
            'type' => 'group',
        ]);

        $response->assertRedirect(route('chat.channels.show', 'du-an-alpha'));
        $this->assertDatabaseHas('chat_channels', [
            'slug' => 'du-an-alpha',
            'name' => 'Dự án Alpha',
            'type' => 'group',
            'created_by' => $hr->id,
        ]);
    }

    public function test_employee_cannot_create_channel(): void
    {
        $employee = $this->createUser('employee');

        $response = $this->actingAs($employee)->post(route('chat.channels.store'), [
            'name' => 'Kênh trái phép',
            'slug' => 'kenh-trai-phep',
            'type' => 'group',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('chat_channels', [
            'slug' => 'kenh-trai-phep',
        ]);
    }

    public function test_cannot_create_channel_with_duplicate_slug(): void
    {
        $admin = $this->createUser('admin');
        ChatChannel::create([
            'name' => 'Kênh Đã Có',
            'slug' => 'kenh-da-co',
            'type' => 'group',
        ]);

        $response = $this->actingAs($admin)->post(route('chat.channels.store'), [
            'name' => 'Kênh Mới Trùng Slug',
            'slug' => 'kenh-da-co',
            'type' => 'group',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_company_channel_cannot_be_deleted(): void
    {
        $admin = $this->createUser('admin');
        $companyChannel = ChatChannel::create([
            'name' => 'Toàn công ty',
            'slug' => 'cong-ty',
            'type' => 'company',
            'is_default' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('chat.channels.destroy', $companyChannel->slug));

        $response->assertForbidden();
        $this->assertDatabaseHas('chat_channels', [
            'id' => $companyChannel->id,
            'slug' => 'cong-ty',
        ]);
    }

    public function test_admin_can_update_and_delete_custom_channel(): void
    {
        $admin = $this->createUser('admin');
        $channel = ChatChannel::create([
            'name' => 'Kênh Ban Đầu',
            'slug' => 'kenh-ban-dau',
            'type' => 'group',
            'created_by' => $admin->id,
        ]);

        // Cập nhật
        $updateResp = $this->actingAs($admin)->patch(route('chat.channels.update', $channel->slug), [
            'name' => 'Kênh Đã Đổi Tên',
            'description' => 'Mô tả mới',
        ]);
        $updateResp->assertRedirect(route('chat.channels.show', $channel->slug));
        $this->assertDatabaseHas('chat_channels', [
            'id' => $channel->id,
            'name' => 'Kênh Đã Đổi Tên',
            'description' => 'Mô tả mới',
        ]);

        // Xóa
        $deleteResp = $this->actingAs($admin)->delete(route('chat.channels.destroy', $channel->slug));
        $deleteResp->assertRedirect(route('chat.index'));
        $this->assertDatabaseMissing('chat_channels', [
            'id' => $channel->id,
        ]);
    }

    public function test_chat_seeder_creates_default_company_channel_without_duplicate(): void
    {
        $admin = $this->createUser('admin');

        $this->seed(ChatSeeder::class);
        $this->assertDatabaseHas('chat_channels', [
            'slug' => 'cong-ty',
            'is_default' => true,
            'type' => 'company',
        ]);

        // Chạy lại seeder lần nữa để kiểm tra không bị trùng lặp
        $this->seed(ChatSeeder::class);
        $this->assertEquals(1, ChatChannel::where('slug', 'cong-ty')->count());
    }
}
