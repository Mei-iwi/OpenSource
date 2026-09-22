<?php

namespace Tests\Feature;

use App\Models\ChatChannel;
use App\Models\ChatChannelMember;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalChatDepartmentValidationTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function createUserWithEmployee(Department $department, string $role = 'employee'): User
    {
        $id = $this->seq++;

        $user = User::create([
            'name' => "Dept User {$id}",
            'email' => "dept_user{$id}@example.com",
            'password' => Hash::make('Password123!'),
            'role' => $role,
            'account_status' => 'active',
        ]);

        Employee::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'employee_code' => 'EMP'.str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'phone' => '0901234'.str_pad((string) $id, 3, '0', STR_PAD_LEFT),
            'position' => 'Chuyên viên',
            'employment_status' => 'active',
            'hire_date' => now()->toDateString(),
        ]);

        return $user;
    }

    protected function createAdmin(): User
    {
        $id = $this->seq++;

        return User::create([
            'name' => "Admin {$id}",
            'email' => "admin_dept{$id}@example.com",
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'account_status' => 'active',
        ]);
    }

    public function test_create_department_channel_rejects_employee_from_another_department(): void
    {
        $admin = $this->createAdmin();

        $deptIT = Department::create(['name' => 'Phòng Công Nghệ', 'code' => 'IT_'.uniqid()]);
        $deptHR = Department::create(['name' => 'Phòng Nhân Sự', 'code' => 'HR_'.uniqid()]);

        $userIT = $this->createUserWithEmployee($deptIT);
        $userHR = $this->createUserWithEmployee($deptHR);

        // Cố tình thêm userHR vào kênh thuộc phòng ban deptIT
        $response = $this->actingAs($admin)->post(route('chat.channels.store'), [
            'name' => 'Kênh IT Nội Bộ',
            'slug' => 'it-noi-bo',
            'type' => 'department',
            'department_id' => $deptIT->id,
            'members' => [$userIT->id, $userHR->id],
        ]);

        $response->assertSessionHasErrors('members');
        $this->assertDatabaseMissing('chat_channels', [
            'slug' => 'it-noi-bo',
        ]);
    }

    public function test_create_department_channel_succeeds_with_matching_department_employees(): void
    {
        $admin = $this->createAdmin();

        $deptIT = Department::create(['name' => 'Phòng Công Nghệ', 'code' => 'IT_'.uniqid()]);
        $userIT1 = $this->createUserWithEmployee($deptIT);
        $userIT2 = $this->createUserWithEmployee($deptIT);

        $response = $this->actingAs($admin)->post(route('chat.channels.store'), [
            'name' => 'Kênh IT Hợp Lệ',
            'slug' => 'it-hop-le',
            'type' => 'department',
            'department_id' => $deptIT->id,
            'members' => [$userIT1->id, $userIT2->id],
        ]);

        $response->assertRedirect(route('chat.channels.show', 'it-hop-le'));
        $this->assertDatabaseHas('chat_channels', [
            'slug' => 'it-hop-le',
            'department_id' => $deptIT->id,
        ]);
    }

    public function test_add_member_to_department_channel_rejects_wrong_department(): void
    {
        $admin = $this->createAdmin();

        $deptIT = Department::create(['name' => 'Phòng Công Nghệ', 'code' => 'IT_'.uniqid()]);
        $deptSales = Department::create(['name' => 'Phòng Kinh Doanh', 'code' => 'SALE_'.uniqid()]);

        $channel = ChatChannel::create([
            'name' => 'Kênh IT Đã Có',
            'slug' => 'it-da-co',
            'type' => 'department',
            'department_id' => $deptIT->id,
        ]);

        $userSales = $this->createUserWithEmployee($deptSales);

        $response = $this->actingAs($admin)->post(route('chat.channels.members.store', $channel->slug), [
            'user_ids' => [$userSales->id],
        ]);

        $response->assertSessionHasErrors('user_ids');
        $this->assertDatabaseMissing('chat_channel_members', [
            'channel_id' => $channel->id,
            'user_id' => $userSales->id,
        ]);
    }

    public function test_members_page_filters_available_users_by_channel_department(): void
    {
        $admin = $this->createAdmin();

        $deptIT = Department::create(['name' => 'Phòng Công Nghệ', 'code' => 'IT_'.uniqid()]);
        $deptSales = Department::create(['name' => 'Phòng Kinh Doanh', 'code' => 'SALE_'.uniqid()]);

        $channel = ChatChannel::create([
            'name' => 'Kênh IT Xem Thành Viên',
            'slug' => 'it-xem-tv',
            'type' => 'department',
            'department_id' => $deptIT->id,
        ]);

        ChatChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $admin->id,
            'joined_at' => now(),
        ]);

        $userIT = $this->createUserWithEmployee($deptIT);
        $userSales = $this->createUserWithEmployee($deptSales);

        $response = $this->actingAs($admin)->get(route('chat.channels.members.index', $channel->slug));

        $response->assertOk();
        $response->assertViewHas('availableUsers', function ($users) use ($userIT, $userSales) {
            return $users->contains('id', $userIT->id) && ! $users->contains('id', $userSales->id);
        });
    }
}
