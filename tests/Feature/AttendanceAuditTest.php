<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_admin_hr_and_employee_accounts_have_employee_profiles(): void
    {
        $this->seed();
        foreach (['admin@example.com', 'hr@example.com', 'hr2@example.com', 'employee1@example.com'] as $email) {
            $this->assertNotNull(User::where('email', $email)->firstOrFail()->employee);
        }
        $this->assertSame(User::whereIn('role', ['admin', 'hr', 'employee'])->count(), Employee::count());
    }

    public function test_user_without_employee_profile_is_not_eligible_for_attendance_subject(): void
    {
        $user = User::factory()->create(['role' => 'hr']);
        $this->assertNull($user->employee);
        $this->assertFalse(Employee::where('user_id', $user->id)->exists());
    }

    public function test_attendance_proof_columns_exist_and_are_nullable(): void
    {
        foreach (['check_in_photo_path', 'check_out_photo_path', 'check_in_method', 'check_out_method'] as $column) {
            $this->assertTrue(Schema::hasColumn('attendances', $column));
        }
        $attendance = Attendance::factory()->create();
        $this->assertNull($attendance->check_in_photo_path);
        $this->assertNull($attendance->check_out_photo_path);
    }

    public function test_attendance_subject_remains_employee_and_date_is_unique(): void
    {
        $employee = Employee::factory()->create();
        $attendance = Attendance::factory()->create(['employee_id' => $employee->id, 'work_date' => '2026-10-10']);
        $this->assertTrue($attendance->employee->is($employee));
        $this->assertTrue(Schema::hasTable('attendances'));
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        Attendance::factory()->create(['employee_id' => $employee->id, 'work_date' => '2026-10-10']);
    }
}
