<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    private function employee(): Employee { return Employee::factory()->create(['user_id' => User::factory()->create(['role' => 'employee'])->id]); }
    private function hr(): User { return User::factory()->create(['role' => 'hr']); }

    public function test_employee_can_create_leave_request(): void
    {
        $employee = $this->employee();
        $this->actingAs($employee->user)->post('/employee/leave-requests', ['leave_type' => 'annual', 'start_date' => '2026-10-01', 'end_date' => '2026-10-03', 'reason' => 'Nghỉ phép cá nhân'])->assertRedirect('/employee/leave-requests');
        $this->assertDatabaseHas('leave_requests', ['employee_id' => $employee->id, 'status' => 'pending', 'leave_type' => 'annual']);
    }

    public function test_leave_dates_and_type_are_validated(): void
    {
        $employee = $this->employee();
        $this->actingAs($employee->user)->post('/employee/leave-requests', ['leave_type' => 'invalid', 'start_date' => '2026-10-03', 'end_date' => '2026-10-01', 'reason' => 'Test'])->assertSessionHasErrors(['leave_type', 'start_date', 'end_date']);
    }

    public function test_overlapping_pending_or_approved_request_is_rejected(): void
    {
        $employee = $this->employee();
        LeaveRequest::factory()->create(['employee_id' => $employee->id, 'start_date' => '2026-10-02', 'end_date' => '2026-10-04', 'status' => 'approved']);
        $this->actingAs($employee->user)->post('/employee/leave-requests', ['leave_type' => 'sick', 'start_date' => '2026-10-04', 'end_date' => '2026-10-05', 'reason' => 'Bị ốm'])->assertSessionHasErrors('start_date');
    }

    public function test_employee_owns_list_detail_and_can_cancel_pending(): void
    {
        $employee = $this->employee();
        $request = LeaveRequest::factory()->create(['employee_id' => $employee->id, 'status' => 'pending']);
        $other = LeaveRequest::factory()->create(['status' => 'pending', 'start_date' => '2026-11-06', 'end_date' => '2026-11-08']);
        $this->actingAs($employee->user)->get('/employee/leave-requests')->assertOk()->assertSee($request->start_date->format('d/m/Y'))->assertDontSee($other->start_date->format('d/m/Y'));
        $this->actingAs($employee->user)->patch('/employee/leave-requests/'.$request->id.'/cancel')->assertRedirect('/employee/leave-requests');
        $this->assertDatabaseHas('leave_requests', ['id' => $request->id, 'status' => 'cancelled']);
        $this->actingAs($employee->user)->get('/employee/leave-requests/'.$other->id)->assertForbidden();
    }

    public function test_employee_cannot_cancel_approved_request(): void
    {
        $employee = $this->employee();
        $request = LeaveRequest::factory()->create(['employee_id' => $employee->id, 'status' => 'approved']);
        $this->actingAs($employee->user)->patch('/employee/leave-requests/'.$request->id.'/cancel')->assertStatus(422);
    }

    public function test_hr_can_list_and_approve_leave_request(): void
    {
        $request = LeaveRequest::factory()->create(['status' => 'pending']);
        $this->actingAs($this->hr())->get('/hr/leave-requests')->assertOk()->assertSee($request->start_date->format('d/m/Y'));
        $this->actingAs($this->hr())->patch('/hr/leave-requests/'.$request->id.'/review', ['status' => 'approved', 'review_note' => 'Đã duyệt'])->assertRedirect('/hr/leave-requests/'.$request->id);
        $this->assertDatabaseHas('leave_requests', ['id' => $request->id, 'status' => 'approved', 'review_note' => 'Đã duyệt']);
    }

    public function test_hr_can_reject_pending_request_and_cannot_reopen_it(): void
    {
        $request = LeaveRequest::factory()->create(['status' => 'pending']);
        $hr = $this->hr();
        $this->actingAs($hr)->patch('/hr/leave-requests/'.$request->id.'/review', ['status' => 'rejected'])->assertRedirect();
        $this->actingAs($hr)->patch('/hr/leave-requests/'.$request->id.'/review', ['status' => 'approved'])->assertRedirect()->assertSessionHas('error');
    }

    public function test_employee_cannot_access_hr_leave_management(): void
    {
        $employee = $this->employee();
        $this->actingAs($employee->user)->get('/hr/leave-requests')->assertForbidden();
        $request = LeaveRequest::factory()->create(['status' => 'pending']);
        $this->actingAs($employee->user)->patch('/hr/leave-requests/'.$request->id.'/review', ['status' => 'approved'])->assertForbidden();
    }

    public function test_empty_leave_lists_render(): void
    {
        $this->actingAs($this->employee()->user)->get('/employee/leave-requests')->assertOk()->assertSee('Chưa có đơn xin nghỉ.');
        $this->actingAs($this->hr())->get('/hr/leave-requests')->assertOk()->assertSee('Chưa có đơn nghỉ phù hợp.');
    }

    public function test_dashboard_displays_pending_leave_count_for_admin_and_hr(): void
    {
        LeaveRequest::factory(2)->create(['status' => 'pending']);
        $this->actingAs(User::factory()->create(['role' => 'hr']))->get('/hr/dashboard')->assertOk()->assertSee('2 đơn chờ duyệt');
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin/dashboard')->assertOk()->assertSee('2 đơn gần nhất cần xem xét');
    }
}
