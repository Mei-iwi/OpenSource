<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeaveConflictDetectionTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    private function createDepartment(string $name = 'Phòng CNTT'): Department
    {
        return Department::create([
            'code' => 'PB-'.($this->seq++),
            'name' => $name,
            'description' => 'Mô tả phòng ban',
        ]);
    }

    private function createUser(string $role = 'employee', array $attrs = []): User
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

    private function createEmployee(?Department $dept = null, ?User $user = null, array $attrs = []): Employee
    {
        $user ??= $this->createUser('employee');
        $dept ??= $this->createDepartment();
        $id = $this->seq++;

        return Employee::create(array_merge([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'employee_code' => sprintf('EMP-%04d', $id),
            'position' => 'Chuyên viên',
            'hire_date' => '2025-01-01',
            'date_of_birth' => '1995-05-15',
            'employment_status' => 'active',
        ], $attrs));
    }

    private function createHrUser(): User
    {
        $user = $this->createUser('hr', ['name' => 'HR Manager']);
        $dept = $this->createDepartment('Phòng Nhân sự');
        $this->createEmployee($dept, $user, ['employee_code' => 'HR-0001']);

        return $user;
    }

    private function createAdminUser(): User
    {
        return $this->createUser('admin', ['name' => 'System Admin']);
    }

    private function createLeaveRequest(Employee $employee, array $attrs = []): LeaveRequest
    {
        return LeaveRequest::create(array_merge([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-15',
            'reason' => 'Nghỉ phép',
            'status' => 'pending',
        ], $attrs));
    }

    public function test_hr_sees_normal_staffing_analysis_when_under_thirty_percent(): void
    {
        $hr = $this->createHrUser();
        $dept = $this->createDepartment('Phòng Kế toán');

        // Tạo 10 nhân viên
        $employees = [];
        for ($i = 0; $i < 10; $i++) {
            $employees[] = $this->createEmployee($dept);
        }

        // Đơn của Emp 0 (1/10 = 10% -> normal)
        $leave = $this->createLeaveRequest($employees[0], [
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-15',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hr)->get("/hr/leave-requests/{$leave->id}");

        $response->assertOk();
        $response->assertSee('Phân tích nhân sự');
        $response->assertSee('Mức nhân sự của Phòng Kế toán vẫn đảm bảo trong thời gian nghỉ này.');
        $response->assertSee('15/10/2026');
        $response->assertSee('1/10');
        $response->assertSee('10%');
        $response->assertSee('Đảm bảo nhân sự');
    }

    public function test_hr_sees_warning_staffing_analysis_when_between_thirty_and_fifty_percent(): void
    {
        $hr = $this->createHrUser();
        $dept = $this->createDepartment('Phòng CNTT');

        // Tạo 8 nhân viên
        $employees = [];
        for ($i = 0; $i < 8; $i++) {
            $employees[] = $this->createEmployee($dept);
        }

        // 2 người đã approved nghỉ ngày 14/10/2026
        $this->createLeaveRequest($employees[1], [
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-14',
            'status' => 'approved',
        ]);
        $this->createLeaveRequest($employees[2], [
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-14',
            'status' => 'approved',
        ]);

        // Đơn hiện tại: Emp 0 xin nghỉ 14/10 (projected = 3/8 = 37.5% -> warning)
        $leave = $this->createLeaveRequest($employees[0], [
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-14',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hr)->get("/hr/leave-requests/{$leave->id}");

        $response->assertOk();
        $response->assertSee('Phân tích nhân sự');
        $response->assertSee('Lưu ý về nhân sự');
        $response->assertSee('Nếu duyệt đơn này: Ngày');
        $response->assertSee('14/10/2026');
        $response->assertSee('3/8');
        $response->assertSee('37.5%');
    }

    public function test_hr_sees_danger_staffing_analysis_when_fifty_percent_or_above(): void
    {
        $hr = $this->createHrUser();
        $dept = $this->createDepartment('Phòng CNTT');

        // Tạo 8 nhân viên
        $employees = [];
        for ($i = 0; $i < 8; $i++) {
            $employees[] = $this->createEmployee($dept);
        }

        // 4 người đã approved nghỉ ngày 14/10/2026
        for ($i = 1; $i <= 4; $i++) {
            $this->createLeaveRequest($employees[$i], [
                'start_date' => '2026-10-14',
                'end_date' => '2026-10-14',
                'status' => 'approved',
            ]);
        }

        // Đơn hiện tại: Emp 0 xin nghỉ 14/10 (projected = 5/8 = 62.5% -> danger)
        $leave = $this->createLeaveRequest($employees[0], [
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-14',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hr)->get("/hr/leave-requests/{$leave->id}");

        $response->assertOk();
        $response->assertSee('Phân tích nhân sự');
        $response->assertSee('Nguy cơ thiếu nhân sự');
        $response->assertSee('5/8');
        $response->assertSee('62.5%');
        $response->assertSee('Việc duyệt đơn có thể ảnh hưởng đến khả năng bố trí nhân sự của phòng ban.');
    }

    public function test_multi_day_leave_displays_detail_table(): void
    {
        $hr = $this->createHrUser();
        $dept = $this->createDepartment('Phòng CNTT');

        // Tạo 8 nhân viên
        $employees = [];
        for ($i = 0; $i < 8; $i++) {
            $employees[] = $this->createEmployee($dept);
        }

        // 12/10 -> 15/10
        $this->createLeaveRequest($employees[1], [
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-15',
            'status' => 'approved',
        ]);

        $leave = $this->createLeaveRequest($employees[0], [
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-15',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hr)->get("/hr/leave-requests/{$leave->id}");

        $response->assertOk();
        $response->assertSee('Bảng chi tiết từng ngày nghỉ');
        $response->assertSee('Đã nghỉ');
        $response->assertSee('Nếu duyệt');
        $response->assertSee('Tỷ lệ');
        $response->assertSee('Đánh giá');
        $response->assertSee('12/10/2026');
        $response->assertSee('13/10/2026');
        $response->assertSee('14/10/2026');
        $response->assertSee('15/10/2026');
    }

    public function test_admin_can_view_leave_request_and_see_staffing_analysis(): void
    {
        $admin = $this->createAdminUser();
        $dept = $this->createDepartment('Phòng Vận Hành');
        $emp = $this->createEmployee($dept);

        $leave = $this->createLeaveRequest($emp, [
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-15',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get("/hr/leave-requests/{$leave->id}");

        $response->assertOk();
        $response->assertSee('Phân tích nhân sự');
    }

    public function test_employee_cannot_view_hr_leave_request_detail(): void
    {
        $dept = $this->createDepartment();
        $emp1 = $this->createEmployee($dept);
        $emp2 = $this->createEmployee($dept);

        $leave = $this->createLeaveRequest($emp1);

        $response = $this->actingAs($emp2->user)->get("/hr/leave-requests/{$leave->id}");

        $response->assertForbidden();
    }

    public function test_employee_viewing_own_leave_request_does_not_see_hr_conflict_card(): void
    {
        $dept = $this->createDepartment();
        $emp = $this->createEmployee($dept);

        $leave = $this->createLeaveRequest($emp);

        $response = $this->actingAs($emp->user)->get("/employee/leave-requests/{$leave->id}");

        $response->assertOk();
        // Employee view does not include internal HR analysis
        $response->assertDontSee('Phân tích nhân sự');
    }

    public function test_hr_can_still_approve_leave_request_even_with_high_conflict(): void
    {
        $hr = $this->createHrUser();
        $dept = $this->createDepartment('Phòng CNTT');

        // Phòng 2 người, 1 người xin nghỉ (1/2 = 50% -> danger)
        $emp1 = $this->createEmployee($dept);
        $emp2 = $this->createEmployee($dept);

        $leave = $this->createLeaveRequest($emp1, [
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-15',
            'status' => 'pending',
        ]);

        // HR vẫn duyệt được bình thường (không bị chặn)
        $response = $this->actingAs($hr)->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'approved',
            'review_note' => 'Đã duyệt mặc dù tỷ lệ 50%',
        ]);

        $response->assertRedirect("/hr/leave-requests/{$leave->id}");
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'approved',
            'review_note' => 'Đã duyệt mặc dù tỷ lệ 50%',
            'reviewed_by' => $hr->id,
        ]);
    }

    public function test_hr_can_reject_leave_request_with_reason(): void
    {
        $hr = $this->createHrUser();
        $dept = $this->createDepartment('Phòng CNTT');

        $emp = $this->createEmployee($dept);
        $leave = $this->createLeaveRequest($emp, [
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-15',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hr)->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'rejected',
            'review_note' => 'Không đủ nhân sự trực vận hành.',
        ]);

        $response->assertRedirect("/hr/leave-requests/{$leave->id}");
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'rejected',
            'review_note' => 'Không đủ nhân sự trực vận hành.',
            'reviewed_by' => $hr->id,
        ]);
    }
}
