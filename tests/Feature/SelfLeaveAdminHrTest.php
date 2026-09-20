<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SelfLeaveAdminHrTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function createDepartment(): Department
    {
        return Department::create([
            'code' => 'PB-SELF-'.$this->seq,
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

    protected function createEmployee(User $user, string $prefix = 'EMP'): Employee
    {
        $dept = $this->createDepartment();
        $id = $this->seq++;

        return Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'employee_code' => sprintf('%s-%04d', $prefix, $id),
            'position' => 'Chuyên viên',
            'hire_date' => '2025-01-01',
            'date_of_birth' => '1995-05-15',
            'employment_status' => 'active',
        ]);
    }

    public function test_regular_employee_can_create_self_leave_request_without_regression(): void
    {
        $user = $this->createUser('employee');
        $employee = $this->createEmployee($user);

        $response = $this->actingAs($user)->post('/employee/leave-requests', [
            'leave_type' => 'annual',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'reason' => 'Nghỉ phép cá nhân',
        ]);

        $response->assertRedirect('/employee/leave-requests');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-01 00:00:00',
            'end_date' => '2026-11-03 00:00:00',
            'status' => 'pending',
            'reason' => 'Nghỉ phép cá nhân',
        ]);
    }

    public function test_hr_user_with_employee_profile_can_create_self_leave_request(): void
    {
        $hrUser = $this->createUser('hr');
        $hrEmployee = $this->createEmployee($hrUser, 'HR');

        $response = $this->actingAs($hrUser)->post('/employee/leave-requests', [
            'leave_type' => 'sick',
            'start_date' => '2026-11-05',
            'end_date' => '2026-11-06',
            'reason' => 'Nghỉ ốm theo chỉ định bác sĩ',
        ]);

        $response->assertRedirect('/employee/leave-requests');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $hrEmployee->id,
            'leave_type' => 'sick',
            'start_date' => '2026-11-05 00:00:00',
            'end_date' => '2026-11-06 00:00:00',
            'status' => 'pending',
            'reason' => 'Nghỉ ốm theo chỉ định bác sĩ',
        ]);

        $leave = LeaveRequest::where('employee_id', $hrEmployee->id)->first();
        $this->assertNotNull($leave);

        $showResponse = $this->actingAs($hrUser)->get("/employee/leave-requests/{$leave->id}");
        $showResponse->assertOk();
        $showResponse->assertSee('Nghỉ ốm theo chỉ định bác sĩ');
    }

    public function test_admin_user_with_employee_profile_can_create_self_leave_request(): void
    {
        $adminUser = $this->createUser('admin');
        $adminEmployee = $this->createEmployee($adminUser, 'ADM');

        $response = $this->actingAs($adminUser)->post('/employee/leave-requests', [
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
            'reason' => 'Nghỉ phép việc gia đình',
        ]);

        $response->assertRedirect('/employee/leave-requests');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $adminEmployee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-10 00:00:00',
            'end_date' => '2026-11-12 00:00:00',
            'status' => 'pending',
            'reason' => 'Nghỉ phép việc gia đình',
        ]);

        $leave = LeaveRequest::where('employee_id', $adminEmployee->id)->first();
        $this->assertNotNull($leave);

        $showResponse = $this->actingAs($adminUser)->get("/employee/leave-requests/{$leave->id}");
        $showResponse->assertOk();
        $showResponse->assertSee('Nghỉ phép việc gia đình');
    }

    public function test_hr_cannot_create_leave_for_another_employee_via_payload_manipulation(): void
    {
        $hrUser = $this->createUser('hr');
        $hrEmployee = $this->createEmployee($hrUser, 'HR');

        $otherUser = $this->createUser('employee');
        $otherEmployee = $this->createEmployee($otherUser);

        // Attacker injects employee_id of another employee in POST data
        $response = $this->actingAs($hrUser)->post('/employee/leave-requests', [
            'employee_id' => $otherEmployee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-15',
            'end_date' => '2026-11-16',
            'reason' => 'Cố gắng tạo đơn hộ nhân viên khác',
        ]);

        $response->assertRedirect('/employee/leave-requests');

        // The request MUST be created for $hrEmployee, NOT $otherEmployee
        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $hrEmployee->id,
            'reason' => 'Cố gắng tạo đơn hộ nhân viên khác',
        ]);

        $this->assertDatabaseMissing('leave_requests', [
            'employee_id' => $otherEmployee->id,
            'reason' => 'Cố gắng tạo đơn hộ nhân viên khác',
        ]);
    }

    public function test_admin_cannot_create_leave_for_another_employee_via_payload_manipulation(): void
    {
        $adminUser = $this->createUser('admin');
        $adminEmployee = $this->createEmployee($adminUser, 'ADM');

        $otherUser = $this->createUser('employee');
        $otherEmployee = $this->createEmployee($otherUser);

        // Attacker injects employee_id of another employee in POST data
        $response = $this->actingAs($adminUser)->post('/employee/leave-requests', [
            'employee_id' => $otherEmployee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-18',
            'end_date' => '2026-11-19',
            'reason' => 'Admin cố gắng tạo đơn hộ',
        ]);

        $response->assertRedirect('/employee/leave-requests');

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $adminEmployee->id,
            'reason' => 'Admin cố gắng tạo đơn hộ',
        ]);

        $this->assertDatabaseMissing('leave_requests', [
            'employee_id' => $otherEmployee->id,
            'reason' => 'Admin cố gắng tạo đơn hộ',
        ]);
    }

    public function test_user_without_employee_profile_is_handled_safely(): void
    {
        $adminWithoutProfile = $this->createUser('admin');

        // Visiting index works and shows warning banner
        $indexResponse = $this->actingAs($adminWithoutProfile)->get('/employee/leave-requests');
        $indexResponse->assertOk();
        $indexResponse->assertSee('Tài khoản chưa liên kết hồ sơ nhân viên');
        $indexResponse->assertDontSee('Gửi đơn xin nghỉ');

        // Visiting create redirects with error
        $createResponse = $this->actingAs($adminWithoutProfile)->get('/employee/leave-requests/create');
        $createResponse->assertRedirect('/employee/leave-requests');
        $createResponse->assertSessionHas('error');

        // Submitting store receives 403 Forbidden from FormRequest authorize
        $storeResponse = $this->actingAs($adminWithoutProfile)->post('/employee/leave-requests', [
            'leave_type' => 'annual',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-22',
            'reason' => 'Tạo khi không có profile',
        ]);

        $storeResponse->assertForbidden();
        $this->assertDatabaseMissing('leave_requests', [
            'reason' => 'Tạo khi không có profile',
        ]);
    }

    public function test_hr_cannot_view_or_cancel_another_employee_leave_via_self_service(): void
    {
        $empUser = $this->createUser('employee');
        $employee = $this->createEmployee($empUser);

        $otherLeave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-25',
            'end_date' => '2026-11-26',
            'reason' => 'Đơn của nhân viên',
            'status' => 'pending',
        ]);

        $hrUser = $this->createUser('hr');
        $this->createEmployee($hrUser, 'HR');

        $this->actingAs($hrUser)->get("/employee/leave-requests/{$otherLeave->id}")
            ->assertForbidden();

        $this->actingAs($hrUser)->patch("/employee/leave-requests/{$otherLeave->id}/cancel")
            ->assertForbidden();

        $this->assertEquals('pending', $otherLeave->fresh()->status);
    }

    public function test_admin_cannot_view_or_cancel_another_employee_leave_via_self_service(): void
    {
        $empUser = $this->createUser('employee');
        $employee = $this->createEmployee($empUser);

        $otherLeave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-25',
            'end_date' => '2026-11-26',
            'reason' => 'Đơn của nhân viên',
            'status' => 'pending',
        ]);

        $adminUser = $this->createUser('admin');
        $this->createEmployee($adminUser, 'ADM');

        $this->actingAs($adminUser)->get("/employee/leave-requests/{$otherLeave->id}")
            ->assertForbidden();

        $this->actingAs($adminUser)->patch("/employee/leave-requests/{$otherLeave->id}/cancel")
            ->assertForbidden();

        $this->assertEquals('pending', $otherLeave->fresh()->status);
    }

    public function test_leave_request_date_and_type_validation(): void
    {
        $hrUser = $this->createUser('hr');
        $this->createEmployee($hrUser, 'HR');

        // Missing required fields
        $response = $this->actingAs($hrUser)->post('/employee/leave-requests', []);
        $response->assertSessionHasErrors(['leave_type', 'start_date', 'end_date', 'reason']);

        // Invalid leave type
        $responseInvalidType = $this->actingAs($hrUser)->post('/employee/leave-requests', [
            'leave_type' => 'invalid_type',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-02',
            'reason' => 'Lý do hợp lệ',
        ]);
        $responseInvalidType->assertSessionHasErrors(['leave_type']);

        // Start date after end date
        $responseInvalidDates = $this->actingAs($hrUser)->post('/employee/leave-requests', [
            'leave_type' => 'annual',
            'start_date' => '2026-11-05',
            'end_date' => '2026-11-02',
            'reason' => 'Lý do hợp lệ',
        ]);
        $responseInvalidDates->assertSessionHasErrors(['start_date', 'end_date']);
    }

    public function test_overlap_validation_regression_applies_to_hr(): void
    {
        $hrUser = $this->createUser('hr');
        $hrEmployee = $this->createEmployee($hrUser, 'HR');

        LeaveRequest::create([
            'employee_id' => $hrEmployee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-15',
            'reason' => 'Đơn ban đầu',
            'status' => 'approved',
        ]);

        $responseHr = $this->actingAs($hrUser)->post('/employee/leave-requests', [
            'leave_type' => 'annual',
            'start_date' => '2026-11-12',
            'end_date' => '2026-11-13',
            'reason' => 'Đơn trùng lịch',
        ]);

        $responseHr->assertSessionHasErrors(['start_date']);
    }

    public function test_overlap_validation_regression_applies_to_admin(): void
    {
        $adminUser = $this->createUser('admin');
        $adminEmployee = $this->createEmployee($adminUser, 'ADM');

        LeaveRequest::create([
            'employee_id' => $adminEmployee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-05',
            'reason' => 'Đơn ban đầu của admin',
            'status' => 'pending',
        ]);

        $responseAdmin = $this->actingAs($adminUser)->post('/employee/leave-requests', [
            'leave_type' => 'annual',
            'start_date' => '2026-12-04',
            'end_date' => '2026-12-08',
            'reason' => 'Đơn trùng lịch admin',
        ]);

        $responseAdmin->assertSessionHasErrors(['start_date']);
    }
}
