<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\ContractExpiringNotification;
use App\Notifications\LeaveRequestApprovedNotification;
use App\Notifications\LeaveRequestRejectedNotification;
use App\Notifications\LeaveRequestSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function setUp(): void
    {
        parent::setUp();
        JobPosition::create(['name' => 'Chuyên viên', 'code' => 'CV-01', 'is_active' => true]);
        JobPosition::create(['name' => 'Nhân sự', 'code' => 'HR-01', 'is_active' => true]);
    }

    protected function createDepartment(): Department
    {
        return Department::create([
            'code' => 'PB-NOTIF-'.$this->seq,
            'name' => 'Phòng ban '.$this->seq++,
            'description' => 'Mô tả phòng ban',
        ]);
    }

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

    protected function createEmployee(?User $user = null, array $attrs = []): Employee
    {
        $user ??= $this->createUser('employee');
        $dept = $this->createDepartment();
        $id = $this->seq++;

        return Employee::create(array_merge([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'employee_code' => sprintf('EMP-%04d', $id),
            'position' => 'Chuyên viên',
            'hire_date' => '2024-01-01',
            'contract_end_date' => '2027-01-01',
            'date_of_birth' => '1995-05-15',
            'employment_status' => 'active',
        ], $attrs));
    }

    protected function createHrUser(): User
    {
        $user = $this->createUser('hr');
        $dept = $this->createDepartment();
        $id = $this->seq++;

        Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'employee_code' => sprintf('HR-%04d', $id),
            'position' => 'Nhân sự',
            'hire_date' => '2024-01-01',
            'contract_end_date' => '2027-01-01',
            'date_of_birth' => '1990-01-01',
            'employment_status' => 'active',
        ]);

        return $user;
    }

    public function test_submitting_leave_request_notifies_hr_and_admin_and_not_other_employees(): void
    {
        $admin = $this->createUser('admin');
        $hr = $this->createHrUser();
        $otherEmployee = $this->createEmployee();

        $employee = $this->createEmployee();

        $response = $this->actingAs($employee->user)->post(route('employee.leave-requests.store'), [
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
            'reason' => 'Nghỉ việc cá nhân',
        ]);

        $response->assertRedirect(route('employee.leave-requests.index'));

        // Kiểm tra HR & Admin nhận thông báo
        $this->assertEquals(1, $admin->unreadNotifications()->count());
        $this->assertEquals(1, $hr->unreadNotifications()->count());

        $notification = $hr->unreadNotifications()->first();
        $this->assertEquals(LeaveRequestSubmittedNotification::class, $notification->type);
        $this->assertEquals('leave_submitted', $notification->data['type']);
        $this->assertStringContainsString($employee->user->name, $notification->data['message']);

        // Nhân viên khác không nhận được thông báo này
        $this->assertEquals(0, $otherEmployee->user->unreadNotifications()->count());
    }

    public function test_approving_leave_request_notifies_only_owner_employee(): void
    {
        $hr = $this->createHrUser();
        $otherEmployee = $this->createEmployee();
        $employee = $this->createEmployee();

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-11',
            'reason' => 'Nghỉ phép',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hr)->patch(route('hr.leave-requests.review', $leave), [
            'status' => 'approved',
            'review_note' => 'Đã duyệt hồ sơ.',
        ]);

        $response->assertRedirect(route('hr.leave-requests.show', $leave));

        // Nhân viên nhận thông báo phê duyệt
        $this->assertEquals(1, $employee->user->unreadNotifications()->count());
        $notification = $employee->user->unreadNotifications()->first();
        $this->assertEquals(LeaveRequestApprovedNotification::class, $notification->type);
        $this->assertEquals('leave_approved', $notification->data['type']);
        $this->assertEquals('Đơn xin nghỉ đã được duyệt', $notification->data['title']);

        // Người khác không nhận thông báo
        $this->assertEquals(0, $otherEmployee->user->unreadNotifications()->count());
    }

    public function test_rejecting_leave_request_notifies_only_owner_employee_with_reason(): void
    {
        $hr = $this->createHrUser();
        $employee = $this->createEmployee();

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-15',
            'end_date' => '2026-11-16',
            'reason' => 'Nghỉ phép',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hr)->patch(route('hr.leave-requests.review', $leave), [
            'status' => 'rejected',
            'review_note' => 'Không đủ nhân sự trực ca.',
        ]);

        $response->assertRedirect(route('hr.leave-requests.show', $leave));

        // Nhân viên nhận thông báo từ chối
        $this->assertEquals(1, $employee->user->unreadNotifications()->count());
        $notification = $employee->user->unreadNotifications()->first();
        $this->assertEquals(LeaveRequestRejectedNotification::class, $notification->type);
        $this->assertEquals('leave_rejected', $notification->data['type']);
        $this->assertStringContainsString('Không đủ nhân sự trực ca', $notification->data['message']);
    }

    public function test_contract_expiring_command_notifies_hr_and_admin(): void
    {
        $admin = $this->createUser('admin');
        $hr = $this->createHrUser();

        // 1. Nhân viên sắp hết hạn hợp đồng trong 15 ngày
        $expiringEmp = $this->createEmployee(null, [
            'contract_end_date' => Carbon::today()->addDays(15)->toDateString(),
            'employment_status' => 'active',
        ]);

        // 2. Nhân viên còn lâu mới hết hạn (60 ngày)
        $notExpiringEmp = $this->createEmployee(null, [
            'contract_end_date' => Carbon::today()->addDays(60)->toDateString(),
            'employment_status' => 'active',
        ]);

        // 3. Nhân viên đã nghỉ việc (inactive)
        $inactiveEmp = $this->createEmployee(null, [
            'contract_end_date' => Carbon::today()->addDays(5)->toDateString(),
            'employment_status' => 'inactive',
        ]);

        $this->artisan('notify:expiring-contracts --days=30')
            ->expectsOutputToContain('Đã kiểm tra 1 nhân viên')
            ->assertSuccessful();

        $this->assertEquals(1, $admin->unreadNotifications()->count());
        $this->assertEquals(1, $hr->unreadNotifications()->count());

        $notification = $hr->unreadNotifications()->first();
        $this->assertEquals(ContractExpiringNotification::class, $notification->type);
        $this->assertEquals('contract_expiring', $notification->data['type']);
        $this->assertEquals($expiringEmp->id, $notification->data['employee_id']);

        // Chạy lại lần thứ 2: Không gửi trùng lặp do đã gửi trong 7 ngày
        $this->artisan('notify:expiring-contracts --days=30')
            ->expectsOutputToContain('Đã gửi 0 thông báo.')
            ->assertSuccessful();

        $this->assertEquals(1, $hr->unreadNotifications()->count());
    }

    public function test_user_can_view_their_own_notifications_in_index(): void
    {
        $employee = $this->createEmployee();
        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-11',
            'reason' => 'Nghỉ việc riêng',
            'status' => 'approved',
        ]);

        $employee->user->notify(new LeaveRequestApprovedNotification($leave));

        $response = $this->actingAs($employee->user)->get(route('notifications.index'));
        $response->assertOk();
        $response->assertSee('Thông báo của tôi');
        $response->assertSee('Đơn xin nghỉ đã được duyệt');
    }

    public function test_user_cannot_access_or_mark_as_read_another_users_notification(): void
    {
        $emp1 = $this->createEmployee();
        $emp2 = $this->createEmployee();

        $leave = LeaveRequest::create([
            'employee_id' => $emp1->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-11',
            'reason' => 'Nghỉ việc',
            'status' => 'approved',
        ]);

        $emp1->user->notify(new LeaveRequestApprovedNotification($leave));
        $notification = $emp1->user->unreadNotifications()->first();

        // emp2 cố ý gửi request mark as read thông báo của emp1 -> 403 Forbidden
        $response = $this->actingAs($emp2->user)->patch(route('notifications.read', $notification->id));
        $response->assertForbidden();

        // Notification của emp1 vẫn chưa đọc
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_their_own_notification_as_read(): void
    {
        $employee = $this->createEmployee();
        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-11',
            'reason' => 'Nghỉ việc',
            'status' => 'approved',
        ]);

        $employee->user->notify(new LeaveRequestApprovedNotification($leave));
        $notification = $employee->user->unreadNotifications()->first();

        $response = $this->actingAs($employee->user)->patch(route('notifications.read', $notification->id));
        $response->assertRedirect(route('employee.leave-requests.show', $leave));

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertEquals(0, $employee->user->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $employee = $this->createEmployee();
        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-11',
            'reason' => 'Nghỉ',
            'status' => 'approved',
        ]);

        $employee->user->notify(new LeaveRequestApprovedNotification($leave));
        $employee->user->notify(new LeaveRequestRejectedNotification($leave));

        $this->assertEquals(2, $employee->user->unreadNotifications()->count());

        $response = $this->actingAs($employee->user)->post(route('notifications.mark-all-read'));
        $response->assertSessionHas('success');

        $this->assertEquals(0, $employee->user->unreadNotifications()->count());
    }

    public function test_unread_count_api_endpoint(): void
    {
        $employee = $this->createEmployee();
        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-11',
            'reason' => 'Nghỉ',
            'status' => 'approved',
        ]);

        $employee->user->notify(new LeaveRequestApprovedNotification($leave));

        $response = $this->actingAs($employee->user)->getJson(route('notifications.unread-count'));
        $response->assertOk();
        $response->assertJson(['count' => 1]);
    }

    public function test_bell_ui_renders_unread_badge_and_notification_dropdown_items(): void
    {
        $employee = $this->createEmployee();
        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-11',
            'reason' => 'Nghỉ',
            'status' => 'approved',
        ]);

        $employee->user->notify(new LeaveRequestApprovedNotification($leave));

        $response = $this->actingAs($employee->user)->get(route('employee.dashboard'));
        $response->assertOk();
        $response->assertSee('title="Thông báo"', false);
        $response->assertSee('Đơn xin nghỉ đã được duyệt');
    }
}
