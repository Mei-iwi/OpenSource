<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceProofTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_admin_and_hr_can_view_private_proof(): void
    {
        $attendance = $this->attendanceWithProof();
        $path = $attendance->check_in_photo_path;

        foreach (['admin', 'hr'] as $role) {
            $manager = User::factory()->create(['role' => $role]);
            $this->actingAs($manager)->get(route('attendance.proof', [$attendance, 'check-in']))
                ->assertOk()
                ->assertHeader('Content-Disposition', 'inline; filename="'.basename($path).'"');
        }
    }

    public function test_employee_can_view_own_proof_but_not_another_employees_proof(): void
    {
        [$owner, $attendance] = $this->employeeAttendance();
        [, $otherAttendance] = $this->employeeAttendance();

        $this->actingAs($owner)->get(route('attendance.proof', [$attendance, 'check-in']))->assertOk();
        $this->actingAs($owner)->get(route('attendance.proof', [$otherAttendance, 'check-in']))->assertForbidden();
    }

    public function test_missing_proof_returns_not_found_and_manual_attendance_renders(): void
    {
        $attendance = Attendance::factory()->create(['check_in_photo_path' => null, 'check_out_photo_path' => null, 'check_in_method' => null, 'check_out_method' => null]);
        $manager = User::factory()->create(['role' => 'hr']);

        $this->actingAs($manager)->get(route('attendance.proof', [$attendance, 'check-in']))->assertNotFound();
        $this->actingAs($manager)->get(route('hr.attendances.index'))->assertOk()->assertSee('Không có ảnh');
    }

    public function test_invalid_proof_type_is_not_found(): void
    {
        $attendance = $this->attendanceWithProof();
        $manager = User::factory()->create(['role' => 'admin']);

        $this->actingAs($manager)->get(route('attendance.proof', [$attendance, 'side']))->assertNotFound();
    }

    /** @return array{0: User, 1: Attendance} */
    private function employeeAttendance(): array
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = Employee::factory()->create(['user_id' => $user->id]);
        $attendance = $this->attendanceWithProof($employee);

        return [$user, $attendance];
    }

    private function attendanceWithProof(?Employee $employee = null): Attendance
    {
        $employee ??= Employee::factory()->create();
        Storage::disk('local')->put('attendance-proofs/test/check-in.jpg', 'proof');

        return Attendance::factory()->create([
            'employee_id' => $employee->id,
            'work_date' => fake()->unique()->date(),
            'check_in_photo_path' => 'attendance-proofs/test/check-in.jpg',
            'check_in_method' => 'upload',
        ]);
    }
}
