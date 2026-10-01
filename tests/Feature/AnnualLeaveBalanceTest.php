<?php

namespace Tests\Feature;

use App\Models\AnnualLeaveBalance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AnnualLeaveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AnnualLeaveBalanceTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function createDepartment(): Department
    {
        return Department::create([
            'code' => 'PB-AL-'.$this->seq,
            'name' => 'Phòng ban AL '.$this->seq++,
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
            'employee_code' => sprintf('EMP-AL-%04d', $id),
            'position' => 'Nhân viên',
            'hire_date' => '2025-01-01',
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
            'employee_code' => sprintf('EMP-HR-%04d', $id),
            'position' => 'Chuyên viên Nhân sự',
            'hire_date' => '2024-01-01',
            'date_of_birth' => '1990-01-01',
            'employment_status' => 'active',
        ]);

        return $user;
    }

    public function test_initial_balance_created_with_default_days_if_no_record_exists(): void
    {
        $employee = $this->createEmployee(null, ['hire_date' => '2024-01-01']);
        $service = app(AnnualLeaveService::class);

        $summary = $service->getBalanceSummary($employee, 2026);

        $this->assertEquals(2026, $summary['year']);
        $this->assertEquals(12.0, $summary['total_days']);
        $this->assertEquals(0.0, $summary['carried_over_days']);
        $this->assertEquals(12.0, $summary['entitlement']);
        $this->assertEquals(0.0, $summary['used_days']);
        $this->assertEquals(0.0, $summary['pending_days']);
        $this->assertEquals(12.0, $summary['available_days']);

        $this->assertDatabaseHas('annual_leave_balances', [
            'employee_id' => $employee->id,
            'year' => 2026,
            'total_days' => 12.0,
        ]);
    }

    public function test_first_year_hire_date_prorates_annual_leave_quota(): void
    {
        // Nhân viên vào làm tháng 7/2026 => 6 tháng còn lại (7, 8, 9, 10, 11, 12)
        // (12 / 12) * 6 = 6 ngày
        $employee = $this->createEmployee(null, ['hire_date' => '2026-07-01']);
        $service = app(AnnualLeaveService::class);

        $summary = $service->getBalanceSummary($employee, 2026);

        $this->assertEquals(6.0, $summary['total_days']);
        $this->assertEquals(6.0, $summary['available_days']);
    }

    public function test_single_day_and_multi_day_leave_calculations(): void
    {
        $service = app(AnnualLeaveService::class);

        // 1 day
        $this->assertEquals(1, $service->calculateDays('2026-05-10', '2026-05-10'));

        // 5 days inclusive (10, 11, 12, 13, 14)
        $this->assertEquals(5, $service->calculateDays('2026-05-10', '2026-05-14'));
    }

    public function test_carried_over_days_increase_entitlement(): void
    {
        $employee = $this->createEmployee();
        AnnualLeaveBalance::create([
            'employee_id' => $employee->id,
            'year' => 2026,
            'total_days' => 12.0,
            'carried_over_days' => 3.5,
            'note' => 'Chuyển phép từ 2025',
        ]);

        $service = app(AnnualLeaveService::class);
        $summary = $service->getBalanceSummary($employee, 2026);

        $this->assertEquals(15.5, $summary['entitlement']);
        $this->assertEquals(15.5, $summary['available_days']);
    }

    public function test_pending_requests_hold_quota_and_prevent_overbooking(): void
    {
        $employee = $this->createEmployee(null, ['hire_date' => '2025-01-01']);

        // Tạo 1 đơn pending 4 ngày
        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-04',
            'reason' => 'Nghỉ cá nhân',
            'status' => 'pending',
        ]);

        $service = app(AnnualLeaveService::class);
        $summary = $service->getBalanceSummary($employee, 2026);

        // Tổng: 12, Chờ: 4 => Khả dụng: 8
        $this->assertEquals(4.0, $summary['pending_days']);
        $this->assertEquals(8.0, $summary['available_days']);

        // Gửi đơn 9 ngày => vượt quá 8 ngày khả dụng => lỗi validation
        $response = $this->actingAs($employee->user)->post(route('employee.leave-requests.store'), [
            'leave_type' => 'annual',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-09', // 9 days
            'reason' => 'Đi du lịch dài ngày',
        ]);

        $response->assertSessionHasErrors('start_date');

        // Gửi đơn 8 ngày => hợp lệ
        $validResponse = $this->actingAs($employee->user)->post(route('employee.leave-requests.store'), [
            'leave_type' => 'annual',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-08', // 8 days
            'reason' => 'Đi du lịch vừa đủ ngày',
        ]);

        $validResponse->assertRedirect(route('employee.leave-requests.index'));
        $validResponse->assertSessionHas('success');

        // Bây giờ cả 12 ngày đều đang pending, khả dụng còn 0
        $summaryAfter = $service->getBalanceSummary($employee, 2026);
        $this->assertEquals(12.0, $summaryAfter['pending_days']);
        $this->assertEquals(0.0, $summaryAfter['available_days']);
    }

    public function test_approved_requests_deduct_quota(): void
    {
        $employee = $this->createEmployee(null, ['hire_date' => '2025-01-01']);
        $hr = $this->createHrUser();

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-03', // 3 days
            'reason' => 'Nghỉ phép',
            'status' => 'pending',
        ]);

        $service = app(AnnualLeaveService::class);
        $summaryBefore = $service->getBalanceSummary($employee, 2026);
        $this->assertEquals(3.0, $summaryBefore['pending_days']);
        $this->assertEquals(0.0, $summaryBefore['used_days']);
        $this->assertEquals(9.0, $summaryBefore['available_days']);

        // HR duyệt đơn
        $response = $this->actingAs($hr)->patch(route('hr.leave-requests.review', $leave), [
            'status' => 'approved',
            'review_note' => 'Đồng ý duyệt',
        ]);

        $response->assertRedirect(route('hr.leave-requests.show', $leave));

        $summaryAfter = $service->getBalanceSummary($employee, 2026);
        $this->assertEquals(0.0, $summaryAfter['pending_days']);
        $this->assertEquals(3.0, $summaryAfter['used_days']);
        $this->assertEquals(9.0, $summaryAfter['remaining_days']);
        $this->assertEquals(9.0, $summaryAfter['available_days']);
    }

    public function test_rejected_requests_release_pending_quota(): void
    {
        $employee = $this->createEmployee(null, ['hire_date' => '2025-01-01']);
        $hr = $this->createHrUser();

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05', // 5 days
            'reason' => 'Nghỉ hè',
            'status' => 'pending',
        ]);

        $service = app(AnnualLeaveService::class);
        $this->assertEquals(7.0, $service->getBalanceSummary($employee, 2026)['available_days']);

        // HR từ chối đơn
        $response = $this->actingAs($hr)->patch(route('hr.leave-requests.review', $leave), [
            'status' => 'rejected',
            'review_note' => 'Trùng lịch dự án gấp',
        ]);

        $response->assertRedirect(route('hr.leave-requests.show', $leave));

        $summaryAfter = $service->getBalanceSummary($employee, 2026);
        $this->assertEquals(0.0, $summaryAfter['pending_days']);
        $this->assertEquals(0.0, $summaryAfter['used_days']);
        $this->assertEquals(12.0, $summaryAfter['available_days']);
    }

    public function test_cancelled_requests_release_pending_quota(): void
    {
        $employee = $this->createEmployee(null, ['hire_date' => '2025-01-01']);

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-04', // 4 days
            'reason' => 'Việc gia đình',
            'status' => 'pending',
        ]);

        $service = app(AnnualLeaveService::class);
        $this->assertEquals(8.0, $service->getBalanceSummary($employee, 2026)['available_days']);

        // Employee tự hủy đơn
        $response = $this->actingAs($employee->user)->patch(route('employee.leave-requests.cancel', $leave));
        $response->assertRedirect(route('employee.leave-requests.index'));

        $this->assertEquals('cancelled', $leave->fresh()->status);
        $summaryAfter = $service->getBalanceSummary($employee, 2026);
        $this->assertEquals(0.0, $summaryAfter['pending_days']);
        $this->assertEquals(12.0, $summaryAfter['available_days']);
    }

    public function test_request_spanning_across_years_is_rejected(): void
    {
        $employee = $this->createEmployee();

        // Nghỉ từ 30/12/2026 đến 02/01/2027 (vắt ngang 2 năm)
        $response = $this->actingAs($employee->user)->post(route('employee.leave-requests.store'), [
            'leave_type' => 'annual',
            'start_date' => '2026-12-30',
            'end_date' => '2027-01-02',
            'reason' => 'Nghỉ Tết dương lịch dài',
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_non_annual_leave_types_are_exempt_from_annual_leave_quota(): void
    {
        $employee = $this->createEmployee();

        // Gửi đơn 20 ngày nghỉ không lương (unpaid) => không bị chặn bởi quota 12 ngày phép năm
        $response = $this->actingAs($employee->user)->post(route('employee.leave-requests.store'), [
            'leave_type' => 'unpaid',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-20',
            'reason' => 'Việc cá nhân dài hạn',
        ]);

        $response->assertRedirect(route('employee.leave-requests.index'));
        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $employee->id,
            'leave_type' => 'unpaid',
            'status' => 'pending',
        ]);

        // Phép năm vẫn nguyên vẹn 12 ngày khả dụng
        $summary = app(AnnualLeaveService::class)->getBalanceSummary($employee, 2026);
        $this->assertEquals(12.0, $summary['available_days']);
        $this->assertEquals(0.0, $summary['pending_days']);
    }

    public function test_employee_views_render_annual_leave_balance_properly(): void
    {
        $employee = $this->createEmployee();

        // 1. Employee index view shows balance cards
        $responseIndex = $this->actingAs($employee->user)->get(route('employee.leave-requests.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('Tổng phép năm 2026');
        $responseIndex->assertSee('Khả dụng còn lại');

        // 2. Employee create view shows quota banner
        $responseCreate = $this->actingAs($employee->user)->get(route('employee.leave-requests.create'));
        $responseCreate->assertOk();
        $responseCreate->assertSee('Quỹ nghỉ phép năm 2026 của bạn');

        // 3. Employee creates an annual leave request
        $this->actingAs($employee->user)->post(route('employee.leave-requests.store'), [
            'leave_type' => 'annual',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-02',
            'reason' => 'Nghỉ việc riêng',
        ]);

        $leave = LeaveRequest::where('employee_id', $employee->id)->latest()->first();

        // 4. Employee show view shows balance context
        $responseEmpShow = $this->actingAs($employee->user)->get(route('employee.leave-requests.show', $leave));
        $responseEmpShow->assertOk();
        $responseEmpShow->assertSee('Thông tin quỹ phép năm 2026:');
    }

    public function test_hr_view_renders_annual_leave_balance_properly(): void
    {
        $employee = $this->createEmployee();
        $hr = $this->createHrUser();

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-02',
            'reason' => 'Nghỉ việc riêng',
            'status' => 'pending',
        ]);

        $responseHrShow = $this->actingAs($hr)->get(route('hr.leave-requests.show', $leave));
        $responseHrShow->assertOk();
        $responseHrShow->assertSee('Quỹ phép năm 2026 của nhân viên');
        $responseHrShow->assertSee('Trong hạn mức');
    }

    public function test_hr_cannot_approve_request_if_it_exceeds_entitlement(): void
    {
        $employee = $this->createEmployee();
        $hr = $this->createHrUser();

        // Giả sử quota năm 2026 chỉ được cấp 2 ngày
        AnnualLeaveBalance::create([
            'employee_id' => $employee->id,
            'year' => 2026,
            'total_days' => 2.0,
            'carried_over_days' => 0.0,
        ]);

        // Đơn xin 3 ngày (tạo trước hoặc balance bị điều chỉnh giảm)
        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03', // 3 days
            'reason' => 'Việc đột xuất',
            'status' => 'pending',
        ]);

        // HR cố duyệt đơn này
        $response = $this->actingAs($hr)->patch(route('hr.leave-requests.review', $leave), [
            'status' => 'approved',
            'review_note' => 'Cố chấp duyệt',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('pending', $leave->fresh()->status);
    }
}
