<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AttendanceSelfServiceTest extends TestCase
{
    use RefreshDatabase;

    private function hr(): User { return User::factory()->create(['role' => 'hr']); }
    private function employee(): Employee { return Employee::factory()->create(['user_id' => User::factory()->create(['role' => 'employee'])->id]); }

    public function test_hr_can_create_and_update_attendance(): void
    {
        $employee = $this->employee();
        $payload = ['employee_id' => $employee->id, 'work_date' => '2026-08-20', 'check_in' => '08:05', 'check_out' => '17:00', 'status' => 'late', 'note' => 'Traffic'];
        $this->actingAs($this->hr())->post('/hr/attendances', $payload)->assertRedirect('/hr/attendances');
        $attendance = Attendance::firstOrFail();
        $this->actingAs($this->hr())->put('/hr/attendances/'.$attendance->id, ['employee_id' => $employee->id, 'work_date' => '2026-08-20', 'check_in' => '08:00', 'check_out' => '17:30', 'status' => 'present', 'note' => 'Updated'])->assertRedirect('/hr/attendances');
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id, 'status' => 'present', 'check_out' => '17:30:00']);
    }

    public function test_duplicate_date_and_invalid_checkout_are_rejected(): void
    {
        $employee = $this->employee();
        Attendance::factory()->create(['employee_id' => $employee->id, 'work_date' => '2026-08-21']);
        $base = ['employee_id' => $employee->id, 'work_date' => '2026-08-21', 'check_in' => '08:00', 'check_out' => '17:00', 'status' => 'present'];
        $this->actingAs($this->hr())->post('/hr/attendances', $base)->assertSessionHasErrors('employee_id');
        $this->actingAs($this->hr())->post('/hr/attendances', [...$base, 'work_date' => '2026-08-22', 'check_out' => '07:00'])->assertSessionHasErrors('check_out');
    }

    public function test_employee_can_only_see_own_attendance_history(): void
    {
        $employee = $this->employee();
        $other = $this->employee();
        Attendance::factory()->create(['employee_id' => $employee->id, 'work_date' => '2026-08-23']);
        Attendance::factory()->create(['employee_id' => $other->id, 'work_date' => '2026-08-24']);
        $response = $this->actingAs($employee->user)->get('/employee/attendances?employee_id='.$other->id);
        $response->assertOk()->assertSee('23/08/2026')->assertDontSee('24/08/2026');
        $this->actingAs($employee->user)->get('/hr/attendances')->assertForbidden();
    }

    public function test_employee_can_update_only_safe_profile_fields(): void
    {
        $employee = $this->employee();
        $original = $employee->only(['employee_code', 'department_id', 'position', 'hire_date', 'employment_status']);
        $this->actingAs($employee->user)->put('/employee/profile', ['phone' => '0900000000', 'address' => 'New address', 'date_of_birth' => '1995-01-02', 'employee_code' => 'HACKED', 'department_id' => Department::factory()->create()->id, 'position' => 'Hacker', 'employment_status' => 'inactive', 'role' => 'admin'])->assertRedirect('/employee/profile');
        $employee->refresh();
        $this->assertSame('0900000000', $employee->phone);
        $this->assertSame('New address', $employee->address);
        $this->assertEquals($original, $employee->only(['employee_code', 'department_id', 'position', 'hire_date', 'employment_status']));
    }

    public function test_employee_policy_enforces_ownership(): void
    {
        $employee = $this->employee();
        $other = $this->employee();
        $this->assertTrue(Gate::forUser($employee->user)->allows('view', $employee));
        $this->assertTrue(Gate::forUser($employee->user)->allows('update', $employee));
        $this->assertFalse(Gate::forUser($employee->user)->allows('view', $other));
        $this->assertFalse(Gate::forUser($employee->user)->allows('update', $other));
    }
}
