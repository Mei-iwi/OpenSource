<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => 'Password123!',
            'role' => $role,
            'account_status' => 'active',
        ]);
    }

    private function attendance(): Attendance
    {
        $user = $this->user('employee', 'owner@example.test');
        $department = Department::create(['code' => 'OPS', 'name' => 'Operations']);
        $employee = Employee::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'employee_code' => 'EMP-0001',
            'hire_date' => '2025-01-01',
            'employment_status' => 'active',
        ]);

        return Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => '2026-10-15',
            'check_in' => '08:00:00',
            'status' => 'present',
        ]);
    }

    public function test_hr_cannot_edit_or_delete_attendance(): void
    {
        $attendance = $this->attendance();
        $hr = $this->user('hr', 'hr@example.test');

        $this->assertFalse(Route::has('hr.attendances.edit'));
        $this->assertFalse(Route::has('hr.attendances.update'));
        $this->assertFalse(Route::has('hr.attendances.destroy'));
        $this->assertFalse($hr->can('update', $attendance));
        $this->assertFalse($hr->can('delete', $attendance));
        $this->actingAs($hr)->put("/hr/attendances/{$attendance->id}", ['status' => 'absent'])->assertNotFound();
        $this->actingAs($hr)->delete("/hr/attendances/{$attendance->id}")->assertNotFound();
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id, 'status' => 'present']);
    }

    public function test_admin_cannot_edit_or_delete_attendance(): void
    {
        $attendance = $this->attendance();
        $admin = $this->user('admin', 'admin@example.test');

        $this->assertFalse($admin->can('update', $attendance));
        $this->assertFalse($admin->can('delete', $attendance));
        $this->actingAs($admin)->put("/hr/attendances/{$attendance->id}", ['status' => 'absent'])->assertNotFound();
        $this->actingAs($admin)->delete("/hr/attendances/{$attendance->id}")->assertNotFound();
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id, 'status' => 'present']);
    }

    public function test_hr_index_is_read_only_and_proof_remains_authorized(): void
    {
        Storage::fake(config('filesystems.attendance_proof_disk'));
        $attendance = $this->attendance();
        $attendance->update(['check_in_photo_path' => 'attendance-proofs/proof.jpg']);
        Storage::disk(config('filesystems.attendance_proof_disk'))->put('attendance-proofs/proof.jpg', 'proof');
        $hr = $this->user('hr', 'hr@example.test');

        $response = $this->actingAs($hr)->get(route('hr.attendances.index'));
        $response->assertOk()->assertDontSee("/hr/attendances/{$attendance->id}/edit");
        $this->actingAs($hr)->get(route('attendance.proof', [$attendance, 'check-in']))->assertOk();
    }

    public function test_employee_cannot_access_hr_attendance_index(): void
    {
        $this->attendance();
        $employee = User::where('email', 'owner@example.test')->firstOrFail();
        $this->actingAs($employee)->get(route('hr.attendances.index'))->assertForbidden();
    }
}
