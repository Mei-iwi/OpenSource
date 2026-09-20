<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveAttendanceSyncService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeaveAttendanceSyncTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function createDepartment(): Department
    {
        return Department::create([
            'code' => 'PB-TEST-'.$this->seq,
            'name' => 'Phòng ban Test '.$this->seq++,
            'description' => 'Mô tả phòng ban',
        ]);
    }

    protected function createUser(string $role = 'employee'): User
    {
        $id = $this->seq++;

        return User::create([
            'name' => "User {$role} {$id}",
            'email' => "user{$id}_{$role}@example.com",
            'password' => Hash::make('Password123!'),
            'role' => $role,
            'account_status' => 'active',
        ]);
    }

    protected function createEmployee(?User $user = null): Employee
    {
        $user ??= $this->createUser('employee');
        $dept = $this->createDepartment();
        $id = $this->seq++;

        return Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'employee_code' => sprintf('EMP-%04d', $id),
            'position' => 'Chuyên viên',
            'hire_date' => '2025-01-01',
            'date_of_birth' => '1995-05-15',
            'employment_status' => 'active',
        ]);
    }

    protected function createLeaveRequest(Employee $employee, array $attributes = []): LeaveRequest
    {
        return LeaveRequest::create(array_merge([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-01',
            'reason' => 'Nghỉ phép cá nhân',
            'status' => 'pending',
        ], $attributes));
    }

    public function test_approving_single_day_leave_creates_attendance_with_status_leave(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();
        $leave = $this->createLeaveRequest($employee, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
        ]);

        $response = $this->actingAs($hr)->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'approved',
            'review_note' => 'Đã duyệt nghỉ 1 ngày',
        ]);

        $response->assertRedirect("/hr/leave-requests/{$leave->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'approved',
            'reviewed_by' => $hr->id,
            'review_note' => 'Đã duyệt nghỉ 1 ngày',
        ]);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'work_date' => '2026-10-05',
            'status' => 'leave',
        ]);
    }

    public function test_approving_multi_day_leave_creates_attendance_for_all_days(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();
        $leave = $this->createLeaveRequest($employee, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-08',
        ]);

        $response = $this->actingAs($hr)->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'approved',
            'review_note' => 'Duyệt 4 ngày',
        ]);

        $response->assertRedirect("/hr/leave-requests/{$leave->id}");

        $dates = ['2026-10-05', '2026-10-06', '2026-10-07', '2026-10-08'];
        foreach ($dates as $date) {
            $this->assertDatabaseHas('attendances', [
                'employee_id' => $employee->id,
                'work_date' => $date,
                'status' => 'leave',
            ]);
        }

        $this->assertSame(4, Attendance::where('employee_id', $employee->id)->where('status', 'leave')->count());
    }

    public function test_rejected_leave_does_not_create_any_attendance(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();
        $leave = $this->createLeaveRequest($employee, [
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-12',
        ]);

        $response = $this->actingAs($hr)->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'rejected',
            'review_note' => 'Không đủ nhân sự',
        ]);

        $response->assertRedirect("/hr/leave-requests/{$leave->id}");

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'rejected',
            'reviewed_by' => $hr->id,
            'review_note' => 'Không đủ nhân sự',
        ]);

        $this->assertSame(0, Attendance::where('employee_id', $employee->id)->count());
    }

    public function test_duplicate_attendance_does_not_create_duplicate_record(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();

        // Đã có sẵn bản ghi leave trước đó
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => '2026-10-05',
            'status' => 'leave',
            'note' => 'Bản ghi ban đầu',
        ]);

        $leave = $this->createLeaveRequest($employee, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
        ]);

        $this->actingAs($hr)->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'approved',
        ])->assertRedirect();

        // Vẫn chỉ có duy nhất 1 bản ghi chấm công cho ngày này
        $this->assertSame(1, Attendance::where('employee_id', $employee->id)->where('work_date', '2026-10-05')->count());
    }

    public function test_existing_work_attendance_with_check_in_is_preserved(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();

        // Nhân viên đã thực tế check-in vào làm việc
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => '2026-10-05',
            'check_in' => '08:00:00',
            'check_out' => '17:00:00',
            'status' => 'present',
            'note' => 'Đã đi làm thực tế',
        ]);

        $leave = $this->createLeaveRequest($employee, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
        ]);

        $this->actingAs($hr)->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'approved',
        ])->assertRedirect();

        // Bản ghi không bị ghi đè thành leave làm mất giờ làm việc thực tế
        $attendance = Attendance::where('employee_id', $employee->id)->where('work_date', '2026-10-05')->first();
        $this->assertSame('present', $attendance->status);
        $this->assertSame('08:00:00', $attendance->check_in);
    }

    public function test_existing_absent_attendance_is_converted_to_leave(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();

        // Bản ghi ban đầu bị đánh vắng mặt do chưa có đơn duyệt
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => '2026-10-05',
            'status' => 'absent',
            'note' => 'Chưa có phép',
        ]);

        $leave = $this->createLeaveRequest($employee, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
        ]);

        $this->actingAs($hr)->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'approved',
        ])->assertRedirect();

        // Bản ghi được cập nhật thành leave hợp lệ
        $attendance = Attendance::where('employee_id', $employee->id)->where('work_date', '2026-10-05')->first();
        $this->assertSame('leave', $attendance->status);
        $this->assertStringContainsString('Nghỉ phép', $attendance->note);
    }

    public function test_transaction_rollback_when_sync_fails(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();
        $leave = $this->createLeaveRequest($employee, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
        ]);

        // Giả lập mock service ném Exception để kiểm tra transaction rollback
        $mockService = $this->mock(LeaveAttendanceSyncService::class);
        $mockService->shouldReceive('sync')->andThrow(new Exception('Database connection lost during sync'));

        $response = $this->actingAs($hr)->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'approved',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Đơn nghỉ phép PHẢI giữ nguyên trạng thái pending, không bị dở dang
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'pending',
            'reviewed_by' => null,
        ]);

        // Không có bản ghi attendance nào bị tạo rác
        $this->assertSame(0, Attendance::where('employee_id', $employee->id)->count());
    }

    public function test_unauthorized_user_cannot_review_or_approve_leave(): void
    {
        $employeeUser = $this->createUser('employee');
        $employee = $this->createEmployee();
        $leave = $this->createLeaveRequest($employee);

        // Employee thông thường không có quyền truy cập route review
        $this->actingAs($employeeUser)->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'approved',
        ])->assertForbidden();

        // Khách chưa đăng nhập bị chuyển hướng về login
        $this->post('/logout');
        $this->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'approved',
        ])->assertRedirect('/login');

        // Trạng thái đơn vẫn là pending
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_re_review_already_processed_leave(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();
        $leave = $this->createLeaveRequest($employee, [
            'status' => 'approved',
            'reviewed_by' => $hr->id,
            'reviewed_at' => now(),
        ]);

        // Gửi request duyệt lại lần nữa
        $response = $this->actingAs($hr)->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'rejected',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Vẫn giữ nguyên trạng thái approved ban đầu
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_also_approve_leave_request(): void
    {
        $admin = $this->createUser('admin');
        $employee = $this->createEmployee();
        $leave = $this->createLeaveRequest($employee, [
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-15',
        ]);

        $response = $this->actingAs($admin)->patch("/hr/leave-requests/{$leave->id}/review", [
            'status' => 'approved',
            'review_note' => 'Admin phê duyệt',
        ]);

        $response->assertRedirect("/hr/leave-requests/{$leave->id}");
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'work_date' => '2026-10-15',
            'status' => 'leave',
        ]);
    }
}
