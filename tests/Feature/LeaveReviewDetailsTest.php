<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeaveReviewDetailsTest extends TestCase
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
            'hire_date' => '2025-01-01',
            'date_of_birth' => '1995-05-15',
            'employment_status' => 'active',
        ], $attrs));
    }

    protected function createHrEmployee(User $hrUser): Employee
    {
        $dept = $this->createDepartment();
        $id = $this->seq++;

        return Employee::create([
            'user_id' => $hrUser->id,
            'department_id' => $dept->id,
            'employee_code' => sprintf('HR-%04d', $id),
            'position' => 'Chuyên viên Nhân sự',
            'hire_date' => '2024-01-01',
            'date_of_birth' => '1990-01-01',
            'employment_status' => 'active',
        ]);
    }

    protected function createLeaveRequest(Employee $employee, array $attributes = []): LeaveRequest
    {
        return LeaveRequest::create(array_merge([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-02',
            'reason' => 'Nghỉ phép thường niên',
            'status' => 'pending',
        ], $attributes));
    }

    public function test_employee_can_view_review_details_of_their_own_approved_request(): void
    {
        $hrUser = $this->createUser('hr', ['name' => 'Trần Thị HR']);
        $this->createHrEmployee($hrUser);

        $empUser = $this->createUser('employee', ['name' => 'Nguyễn Văn Nhân Viên']);
        $employee = $this->createEmployee($empUser);

        $leave = $this->createLeaveRequest($employee, [
            'status' => 'approved',
            'reviewed_by' => $hrUser->id,
            'reviewed_at' => now()->subHours(2),
            'review_note' => 'Đã duyệt nghỉ theo chính sách công ty.',
        ]);

        $response = $this->actingAs($empUser)->get("/employee/leave-requests/{$leave->id}");

        $response->assertOk();
        $response->assertSee('Thông tin phê duyệt');
        $response->assertSee('Trần Thị HR');
        $response->assertSee('Nhân sự');
        $response->assertSee('Đã duyệt nghỉ theo chính sách công ty.');
        $response->assertSee('Đã duyệt thành công');
    }

    public function test_employee_can_view_review_details_of_their_own_rejected_request_with_reason(): void
    {
        $hrUser = $this->createUser('hr', ['name' => 'Lê Quản Trị']);
        $this->createHrEmployee($hrUser);

        $empUser = $this->createUser('employee', ['name' => 'Phạm Văn B']);
        $employee = $this->createEmployee($empUser);

        $leave = $this->createLeaveRequest($employee, [
            'status' => 'rejected',
            'reviewed_by' => $hrUser->id,
            'reviewed_at' => now()->subHour(),
            'review_note' => 'Dự án đang trong giai đoạn chạy nước rút bàn giao.',
        ]);

        $response = $this->actingAs($empUser)->get("/employee/leave-requests/{$leave->id}");

        $response->assertOk();
        $response->assertSee('Thông tin từ chối đơn');
        $response->assertSee('Lý do từ chối');
        $response->assertSee('Lê Quản Trị');
        $response->assertSee('Dự án đang trong giai đoạn chạy nước rút bàn giao.');
        $response->assertSee('Đã bị từ chối');
    }

    public function test_employee_cannot_view_leave_request_of_another_employee(): void
    {
        $emp1User = $this->createUser('employee');
        $employee1 = $this->createEmployee($emp1User);

        $emp2User = $this->createUser('employee');
        $employee2 = $this->createEmployee($emp2User);

        $leave = $this->createLeaveRequest($employee1);

        $response = $this->actingAs($emp2User)->get("/employee/leave-requests/{$leave->id}");

        $response->assertForbidden();
    }

    public function test_hr_can_view_leave_request_with_reviewer_details(): void
    {
        $hrReviewer = $this->createUser('hr', ['name' => 'HR Phụ Trách']);
        $this->createHrEmployee($hrReviewer);

        $hrViewer = $this->createUser('hr');
        $this->createHrEmployee($hrViewer);

        $empUser = $this->createUser('employee', ['name' => 'Nhân Viên C']);
        $employee = $this->createEmployee($empUser);

        $leave = $this->createLeaveRequest($employee, [
            'status' => 'approved',
            'reviewed_by' => $hrReviewer->id,
            'reviewed_at' => now()->subDay(),
            'review_note' => 'HR Phụ Trách đã xem xét và duyệt.',
        ]);

        $response = $this->actingAs($hrViewer)->get("/hr/leave-requests/{$leave->id}");

        $response->assertOk();
        $response->assertSee('Thông tin phê duyệt');
        $response->assertSee('HR Phụ Trách');
        $response->assertSee('HR Phụ Trách đã xem xét và duyệt.');
        $response->assertSee('Nhân Viên C');
    }

    public function test_admin_can_view_leave_request_with_reviewer_details(): void
    {
        $admin = $this->createUser('admin', ['name' => 'Tổng Giám Đốc']);
        $empUser = $this->createUser('employee');
        $employee = $this->createEmployee($empUser);

        $leave = $this->createLeaveRequest($employee, [
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'review_note' => 'Ban Giám Đốc đặc cách duyệt.',
        ]);

        $response = $this->actingAs($admin)->get("/hr/leave-requests/{$leave->id}");

        $response->assertOk();
        $response->assertSee('Thông tin phê duyệt');
        $response->assertSee('Tổng Giám Đốc');
        $response->assertSee('Quản trị viên');
        $response->assertSee('Ban Giám Đốc đặc cách duyệt.');
    }

    public function test_pending_request_does_not_display_reviewer_or_notes_for_employee(): void
    {
        $empUser = $this->createUser('employee');
        $employee = $this->createEmployee($empUser);

        $leave = $this->createLeaveRequest($employee, [
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ]);

        $response = $this->actingAs($empUser)->get("/employee/leave-requests/{$leave->id}");
        $response->assertOk();
        $response->assertDontSee('Thông tin phê duyệt');
        $response->assertDontSee('Thông tin từ chối đơn');
        $response->assertSee('Đang chờ');
        $response->assertSee('Hủy đơn');
    }

    public function test_pending_request_displays_review_form_for_hr(): void
    {
        $empUser = $this->createUser('employee');
        $employee = $this->createEmployee($empUser);

        $leave = $this->createLeaveRequest($employee, [
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ]);

        $hrUser = $this->createUser('hr');
        $this->createHrEmployee($hrUser);

        $response = $this->actingAs($hrUser)->get("/hr/leave-requests/{$leave->id}");
        $response->assertOk();
        $response->assertDontSee('Thông tin phê duyệt');
        $response->assertDontSee('Thông tin từ chối đơn');
        $response->assertSee('Duyệt đơn');
        $response->assertSee('Từ chối');
    }

    public function test_review_note_html_and_xss_is_safely_escaped_for_employee(): void
    {
        $hrUser = $this->createUser('hr', ['name' => 'HR Security']);
        $this->createHrEmployee($hrUser);

        $empUser = $this->createUser('employee');
        $employee = $this->createEmployee($empUser);

        $xssPayload = '<script>alert("xss")</script><b class="bold-tag">bold text</b>';

        $leave = $this->createLeaveRequest($employee, [
            'status' => 'rejected',
            'reviewed_by' => $hrUser->id,
            'reviewed_at' => now(),
            'review_note' => $xssPayload,
        ]);

        $empResponse = $this->actingAs($empUser)->get("/employee/leave-requests/{$leave->id}");
        $empResponse->assertOk();
        $empResponse->assertDontSee($xssPayload, false);
        $empResponse->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;&lt;b class=&quot;bold-tag&quot;&gt;bold text&lt;/b&gt;', false);
    }

    public function test_review_note_html_and_xss_is_safely_escaped_for_hr(): void
    {
        $hrUser = $this->createUser('hr', ['name' => 'HR Security']);
        $this->createHrEmployee($hrUser);

        $empUser = $this->createUser('employee');
        $employee = $this->createEmployee($empUser);

        $xssPayload = '<script>alert("xss")</script><b class="bold-tag">bold text</b>';

        $leave = $this->createLeaveRequest($employee, [
            'status' => 'rejected',
            'reviewed_by' => $hrUser->id,
            'reviewed_at' => now(),
            'review_note' => $xssPayload,
        ]);

        $hrResponse = $this->actingAs($hrUser)->get("/hr/leave-requests/{$leave->id}");
        $hrResponse->assertOk();
        $hrResponse->assertDontSee($xssPayload, false);
        $hrResponse->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;&lt;b class=&quot;bold-tag&quot;&gt;bold text&lt;/b&gt;', false);
    }

    public function test_employee_cannot_access_hr_leave_request_detail(): void
    {
        $empUser = $this->createUser('employee');
        $employee = $this->createEmployee($empUser);
        $leave = $this->createLeaveRequest($employee);

        $response = $this->actingAs($empUser)->get("/hr/leave-requests/{$leave->id}");

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_leave_requests(): void
    {
        $empUser = $this->createUser('employee');
        $employee = $this->createEmployee($empUser);
        $leave = $this->createLeaveRequest($employee);

        $this->get("/employee/leave-requests/{$leave->id}")->assertRedirect('/login');
        $this->get("/hr/leave-requests/{$leave->id}")->assertRedirect('/login');
    }
}
