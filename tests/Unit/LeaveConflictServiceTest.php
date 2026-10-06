<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveConflictService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeaveConflictServiceTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;
    private LeaveConflictService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LeaveConflictService();
    }

    private function createDepartment(string $name = 'Phòng CNTT'): Department
    {
        return Department::create([
            'code' => 'PB-'.($this->seq++),
            'name' => $name,
            'description' => 'Mô tả phòng ban',
        ]);
    }

    private function createEmployee(Department $department, array $attrs = []): Employee
    {
        $id = $this->seq++;
        $user = User::create([
            'name' => 'Nhân viên '.$id,
            'email' => 'user_'.$id.'@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'employee',
            'account_status' => 'active',
        ]);

        return Employee::create(array_merge([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'employee_code' => sprintf('EMP-%04d', $id),
            'position' => 'Chuyên viên',
            'hire_date' => '2025-01-01',
            'date_of_birth' => '1995-05-15',
            'employment_status' => 'active',
        ], $attrs));
    }

    private function createLeaveRequest(Employee $employee, array $attrs = []): LeaveRequest
    {
        return LeaveRequest::create(array_merge([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-15',
            'reason' => 'Nghỉ phép thường niên',
            'status' => 'pending',
        ], $attrs));
    }

    /**
     * 1. Phòng 10 người, 2 người approved nghỉ:
     *    đơn hiện tại tạo projected = 3/10 = 30%.
     */
    public function test_ten_employees_two_approved_creates_thirty_percent_projected_warning(): void
    {
        $dept = $this->createDepartment('Phòng CNTT');

        // Tạo 10 nhân viên active
        $employees = [];
        for ($i = 0; $i < 10; $i++) {
            $employees[] = $this->createEmployee($dept);
        }

        // 2 nhân viên khác có đơn nghỉ approved vào ngày 2026-10-20
        $this->createLeaveRequest($employees[1], [
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-20',
            'status' => 'approved',
        ]);
        $this->createLeaveRequest($employees[2], [
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-20',
            'status' => 'approved',
        ]);

        // Đơn hiện tại của nhân viên 0
        $currentLeave = $this->createLeaveRequest($employees[0], [
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-20',
            'status' => 'pending',
        ]);

        $result = $this->service->analyze($currentLeave);

        $this->assertTrue($result['has_department']);
        $this->assertSame(10, $result['total_active_employees']);
        $this->assertSame(30.0, $result['highest_percentage']);
        $this->assertSame('warning', $result['highest_level']);
        $this->assertTrue($result['has_conflict']);

        $this->assertCount(1, $result['days']);
        $day = $result['days'][0];
        $this->assertSame('2026-10-20', $day['date']);
        $this->assertSame(2, $day['approved_leave_count']);
        $this->assertSame(3, $day['projected_leave_count']);
        $this->assertSame(10, $day['total_employees']);
        $this->assertSame(30.0, $day['percentage']);
        $this->assertSame('warning', $day['level']);
    }

    /**
     * 2. Đơn nghỉ nhiều ngày tính đúng từng ngày.
     */
    public function test_multi_day_leave_calculates_each_day_accurately(): void
    {
        $dept = $this->createDepartment('Phòng Kỹ thuật');

        // Tạo 8 nhân viên active
        $employees = [];
        for ($i = 0; $i < 8; $i++) {
            $employees[] = $this->createEmployee($dept);
        }

        // 12/10: 1 người approved -> projected = 2/8 (25%) -> normal
        // 13/10: 2 người approved -> projected = 3/8 (37.5%) -> warning
        // 14/10: 4 người approved -> projected = 5/8 (62.5%) -> danger
        // 15/10: 1 người approved -> projected = 2/8 (25%) -> normal

        // Emp 1 nghỉ 12/10 đến 15/10 (cả 4 ngày)
        $this->createLeaveRequest($employees[1], [
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-15',
            'status' => 'approved',
        ]);
        // Emp 2 nghỉ 13/10 đến 14/10
        $this->createLeaveRequest($employees[2], [
            'start_date' => '2026-10-13',
            'end_date' => '2026-10-14',
            'status' => 'approved',
        ]);
        // Emp 3 và Emp 4 chỉ nghỉ ngày 14/10
        $this->createLeaveRequest($employees[3], [
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-14',
            'status' => 'approved',
        ]);
        $this->createLeaveRequest($employees[4], [
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-14',
            'status' => 'approved',
        ]);

        // Đơn hiện tại: 12/10 -> 15/10 của Emp 0
        $currentLeave = $this->createLeaveRequest($employees[0], [
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-15',
            'status' => 'pending',
        ]);

        $result = $this->service->analyze($currentLeave);

        $this->assertCount(4, $result['days']);
        $this->assertSame(62.5, $result['highest_percentage']);
        $this->assertSame('danger', $result['highest_level']);
        $this->assertTrue($result['has_conflict']);
        $this->assertSame('2026-10-14', $result['highest_day']['date']);

        $daysMap = collect($result['days'])->keyBy('date');

        // 12/10
        $this->assertSame(1, $daysMap['2026-10-12']['approved_leave_count']);
        $this->assertSame(2, $daysMap['2026-10-12']['projected_leave_count']);
        $this->assertSame(25.0, $daysMap['2026-10-12']['percentage']);
        $this->assertSame('normal', $daysMap['2026-10-12']['level']);

        // 13/10
        $this->assertSame(2, $daysMap['2026-10-13']['approved_leave_count']);
        $this->assertSame(3, $daysMap['2026-10-13']['projected_leave_count']);
        $this->assertSame(37.5, $daysMap['2026-10-13']['percentage']);
        $this->assertSame('warning', $daysMap['2026-10-13']['level']);

        // 14/10
        $this->assertSame(4, $daysMap['2026-10-14']['approved_leave_count']);
        $this->assertSame(5, $daysMap['2026-10-14']['projected_leave_count']);
        $this->assertSame(62.5, $daysMap['2026-10-14']['percentage']);
        $this->assertSame('danger', $daysMap['2026-10-14']['level']);

        // 15/10
        $this->assertSame(1, $daysMap['2026-10-15']['approved_leave_count']);
        $this->assertSame(2, $daysMap['2026-10-15']['projected_leave_count']);
        $this->assertSame(25.0, $daysMap['2026-10-15']['percentage']);
        $this->assertSame('normal', $daysMap['2026-10-15']['level']);
    }

    /**
     * 3. Đơn rejected không được tính.
     */
    public function test_rejected_leaves_are_not_counted(): void
    {
        $dept = $this->createDepartment();
        $emp1 = $this->createEmployee($dept);
        $emp2 = $this->createEmployee($dept);
        $emp3 = $this->createEmployee($dept);

        $this->createLeaveRequest($emp2, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
            'status' => 'rejected',
        ]);

        $current = $this->createLeaveRequest($emp1, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
            'status' => 'pending',
        ]);

        $result = $this->service->analyze($current);

        $this->assertSame(0, $result['days'][0]['approved_leave_count']);
        $this->assertSame(1, $result['days'][0]['projected_leave_count']);
    }

    /**
     * 4. Đơn pending khác không được tính.
     */
    public function test_pending_leaves_from_other_employees_are_not_counted(): void
    {
        $dept = $this->createDepartment();
        $emp1 = $this->createEmployee($dept);
        $emp2 = $this->createEmployee($dept);
        $emp3 = $this->createEmployee($dept);

        $this->createLeaveRequest($emp2, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
            'status' => 'pending',
        ]);

        $current = $this->createLeaveRequest($emp1, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
            'status' => 'pending',
        ]);

        $result = $this->service->analyze($current);

        $this->assertSame(0, $result['days'][0]['approved_leave_count']);
        $this->assertSame(1, $result['days'][0]['projected_leave_count']);
    }

    /**
     * 5. Nhân viên inactive không tính vào tổng active employee.
     */
    public function test_inactive_employees_are_excluded_from_total_and_leaves(): void
    {
        $dept = $this->createDepartment();
        $emp1 = $this->createEmployee($dept, ['employment_status' => 'active']);
        $emp2 = $this->createEmployee($dept, ['employment_status' => 'active']);
        $inactiveEmp = $this->createEmployee($dept, ['employment_status' => 'inactive']);

        // Inactive emp có đơn approved (ví dụ nghỉ trước khi inactive)
        $this->createLeaveRequest($inactiveEmp, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
            'status' => 'approved',
        ]);

        $current = $this->createLeaveRequest($emp1, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
            'status' => 'pending',
        ]);

        $result = $this->service->analyze($current);

        // Tổng nhân viên active chỉ là 2 (không tính inactive)
        $this->assertSame(2, $result['total_active_employees']);
        // Đơn của inactive employee không được tính vào approved
        $this->assertSame(0, $result['days'][0]['approved_leave_count']);
        $this->assertSame(1, $result['days'][0]['projected_leave_count']);
        $this->assertSame(50.0, $result['days'][0]['percentage']);
    }

    /**
     * 6. Nhân viên phòng khác không ảnh hưởng.
     */
    public function test_employees_of_other_departments_are_ignored(): void
    {
        $deptA = $this->createDepartment('Phòng A');
        $deptB = $this->createDepartment('Phòng B');

        $empA1 = $this->createEmployee($deptA);
        $empA2 = $this->createEmployee($deptA);

        $empB1 = $this->createEmployee($deptB);
        $this->createLeaveRequest($empB1, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
            'status' => 'approved',
        ]);

        $current = $this->createLeaveRequest($empA1, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
            'status' => 'pending',
        ]);

        $result = $this->service->analyze($current);

        $this->assertSame(2, $result['total_active_employees']);
        $this->assertSame(0, $result['days'][0]['approved_leave_count']);
        $this->assertSame(1, $result['days'][0]['projected_leave_count']);
        $this->assertSame(50.0, $result['days'][0]['percentage']);
    }

    /**
     * 7. Không count duplicate employee nhiều lần trong cùng một ngày.
     */
    public function test_does_not_count_duplicate_employee_multiple_times_on_same_day(): void
    {
        $dept = $this->createDepartment();
        $emp1 = $this->createEmployee($dept);
        $emp2 = $this->createEmployee($dept);
        $emp3 = $this->createEmployee($dept);

        // Emp 2 có 2 đơn approved giao nhau ngày 2026-10-05
        $this->createLeaveRequest($emp2, [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-05',
            'status' => 'approved',
        ]);
        $this->createLeaveRequest($emp2, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-10',
            'status' => 'approved',
        ]);

        $current = $this->createLeaveRequest($emp1, [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-05',
            'status' => 'pending',
        ]);

        $result = $this->service->analyze($current);

        // Emp 2 chỉ được tính là 1 người nghỉ
        $this->assertSame(1, $result['days'][0]['approved_leave_count']);
        $this->assertSame(2, $result['days'][0]['projected_leave_count']);
    }

    /**
     * Edge case: Nhân viên chưa có phòng ban.
     */
    public function test_employee_without_department_returns_safe_fallback(): void
    {
        $id = $this->seq++;
        $user = User::create([
            'name' => 'Nhân viên Không PB '.$id,
            'email' => 'nopb_'.$id.'@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'employee',
            'account_status' => 'active',
        ]);

        // Employee có department_id null
        // Do migration employees table foreign key department_id có restrictOnDelete và not null,
        // hãy test trường hợp employee model relation trả về null hoặc department_id = 0
        $dept = $this->createDepartment();
        $emp = $this->createEmployee($dept);
        $emp->department_id = null; // Unset in memory
        
        $leave = new LeaveRequest([
            'employee_id' => $emp->id,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-10',
            'status' => 'pending',
        ]);
        $leave->setRelation('employee', $emp);

        $result = $this->service->analyze($leave);

        $this->assertFalse($result['has_department']);
        $this->assertStringContainsString('chưa được phân bổ phòng ban', $result['message']);
        $this->assertFalse($result['has_conflict']);
    }

    /**
     * Edge case: Phòng ban chỉ có 1 người.
     */
    public function test_department_with_single_employee_results_in_one_hundred_percent(): void
    {
        $dept = $this->createDepartment();
        $emp1 = $this->createEmployee($dept);

        $leave = $this->createLeaveRequest($emp1, [
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-10',
            'status' => 'pending',
        ]);

        $result = $this->service->analyze($leave);

        $this->assertSame(1, $result['total_active_employees']);
        $this->assertSame(0, $result['days'][0]['approved_leave_count']);
        $this->assertSame(1, $result['days'][0]['projected_leave_count']);
        $this->assertSame(100.0, $result['days'][0]['percentage']);
        $this->assertSame('danger', $result['highest_level']);
        $this->assertTrue($result['has_conflict']);
    }

    /**
     * Edge case: Không có đơn nghỉ approved nào khác.
     */
    public function test_no_other_approved_leaves_projected_count_is_one(): void
    {
        $dept = $this->createDepartment();
        for ($i = 0; $i < 5; $i++) {
            $employees[] = $this->createEmployee($dept);
        }

        $leave = $this->createLeaveRequest($employees[0], [
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-10',
            'status' => 'pending',
        ]);

        $result = $this->service->analyze($leave);

        $this->assertSame(0, $result['days'][0]['approved_leave_count']);
        $this->assertSame(1, $result['days'][0]['projected_leave_count']);
        $this->assertSame(20.0, $result['days'][0]['percentage']);
        $this->assertSame('normal', $result['highest_level']);
        $this->assertFalse($result['has_conflict']);
    }
}
