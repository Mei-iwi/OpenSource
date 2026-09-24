<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceDeleteTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('filesystems.attendance_proof_disk', 'persistent_uploads'));
    }

    protected function createDepartment(): Department
    {
        return Department::create([
            'code' => 'PB-DEL-'.$this->seq,
            'name' => 'Phòng ban '.$this->seq++,
            'description' => 'Mô tả phòng ban',
        ]);
    }

    protected function createUser(string $role = 'employee', string $status = 'active'): User
    {
        $id = $this->seq++;

        return User::create([
            'name' => "User {$role} {$id}",
            'email' => "user{$id}_{$role}@example.com",
            'password' => Hash::make('Password123!'),
            'role' => $role,
            'account_status' => $status,
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

    protected function createAttendance(Employee $employee, array $attributes = []): Attendance
    {
        return Attendance::create(array_merge([
            'employee_id' => $employee->id,
            'work_date' => '2026-09-23',
            'check_in' => '08:00',
            'check_out' => '17:00',
            'status' => 'present',
            'note' => 'Bản ghi chấm công mẫu',
        ], $attributes));
    }

    public function test_authorized_hr_can_delete_attendance_and_writes_audit_log(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();
        $attendance = $this->createAttendance($employee, [
            'work_date' => '2026-09-20',
        ]);

        Log::spy();

        $response = $this->actingAs($hr)->delete(route('hr.attendances.destroy', $attendance));

        $response->assertRedirect(route('hr.attendances.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);

        Log::shouldHaveReceived('info')->withArgs(function ($message, $context) use ($attendance, $hr) {
            return $message === 'Attendance record deleted'
                && ($context['attendance_id'] ?? null) === $attendance->id
                && ($context['deleted_by'] ?? null) === $hr->id
                && ($context['deleted_by_role'] ?? null) === 'hr';
        })->once();
    }

    public function test_authorized_admin_can_delete_attendance(): void
    {
        $admin = $this->createUser('admin');
        $employee = $this->createEmployee();
        $attendance = $this->createAttendance($employee);

        $response = $this->actingAs($admin)->delete(route('hr.attendances.destroy', $attendance));

        $response->assertRedirect(route('hr.attendances.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);
    }

    public function test_regular_employee_cannot_delete_attendance(): void
    {
        $employeeUser = $this->createUser('employee');
        $employee = $this->createEmployee($employeeUser);
        $attendance = $this->createAttendance($employee);

        $response = $this->actingAs($employeeUser)->delete(route('hr.attendances.destroy', $attendance));

        $response->assertForbidden();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
        ]);
    }

    public function test_employee_cannot_delete_other_employee_attendance(): void
    {
        $emp1User = $this->createUser('employee');
        $emp1 = $this->createEmployee($emp1User);

        $emp2User = $this->createUser('employee');
        $emp2 = $this->createEmployee($emp2User);

        $attendance2 = $this->createAttendance($emp2);

        $response = $this->actingAs($emp1User)->delete(route('hr.attendances.destroy', $attendance2));

        $response->assertForbidden();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance2->id,
        ]);
    }

    public function test_unauthenticated_guest_is_redirected_to_login(): void
    {
        $employee = $this->createEmployee();
        $attendance = $this->createAttendance($employee);

        $response = $this->delete(route('hr.attendances.destroy', $attendance));

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
        ]);
    }

    public function test_locked_hr_user_is_forbidden_from_deleting_attendance(): void
    {
        $lockedHr = $this->createUser('hr', 'locked');
        $employee = $this->createEmployee();
        $attendance = $this->createAttendance($employee);

        $response = $this->actingAs($lockedHr)->delete(route('hr.attendances.destroy', $attendance));

        $response->assertForbidden();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
        ]);
    }

    public function test_delete_non_existing_attendance_returns_404(): void
    {
        $hr = $this->createUser('hr');

        $response = $this->actingAs($hr)->delete('/hr/attendances/9999999');

        $response->assertNotFound();
    }

    public function test_proof_files_are_cleaned_up_from_storage_when_attendance_is_deleted(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();
        $disk = config('filesystems.attendance_proof_disk', 'persistent_uploads');

        $inPath = 'attendance_proofs/check_in_photo_test.jpg';
        $outPath = 'attendance_proofs/check_out_photo_test.jpg';

        Storage::disk($disk)->put($inPath, 'fake-in-image-content');
        Storage::disk($disk)->put($outPath, 'fake-out-image-content');

        Storage::disk($disk)->assertExists($inPath);
        Storage::disk($disk)->assertExists($outPath);

        $attendance = $this->createAttendance($employee, [
            'check_in_photo_path' => $inPath,
            'check_out_photo_path' => $outPath,
        ]);

        $response = $this->actingAs($hr)->delete(route('hr.attendances.destroy', $attendance));

        $response->assertRedirect(route('hr.attendances.index'));
        $this->assertDatabaseMissing('attendances', ['id' => $attendance->id]);

        Storage::disk($disk)->assertMissing($inPath);
        Storage::disk($disk)->assertMissing($outPath);
    }

    public function test_proof_cleanup_handles_partially_missing_files_gracefully(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();
        $disk = config('filesystems.attendance_proof_disk', 'persistent_uploads');

        $inPath = 'attendance_proofs/in_only.jpg';
        Storage::disk($disk)->put($inPath, 'in-content');

        $attendance = $this->createAttendance($employee, [
            'check_in_photo_path' => $inPath,
            'check_out_photo_path' => 'attendance_proofs/file_does_not_exist.jpg',
        ]);

        $response = $this->actingAs($hr)->delete(route('hr.attendances.destroy', $attendance));

        $response->assertRedirect(route('hr.attendances.index'));
        $this->assertDatabaseMissing('attendances', ['id' => $attendance->id]);

        Storage::disk($disk)->assertMissing($inPath);
    }

    public function test_hr_attendance_index_and_edit_views_display_delete_action_for_authorized_users(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();
        $attendance = $this->createAttendance($employee);

        // Index page has delete form with DELETE method and CSRF
        $indexResponse = $this->actingAs($hr)->get(route('hr.attendances.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee(route('hr.attendances.destroy', $attendance));
        $indexResponse->assertSee('value="DELETE"', false);
        $indexResponse->assertSee('onsubmit="return confirm(', false);

        // Edit page has delete form with confirmation
        $editResponse = $this->actingAs($hr)->get(route('hr.attendances.edit', $attendance));
        $editResponse->assertOk();
        $editResponse->assertSee(route('hr.attendances.destroy', $attendance));
        $editResponse->assertSee('Xóa bản ghi');
        $editResponse->assertSee('value="DELETE"', false);
    }

    public function test_employee_cannot_access_hr_attendance_routes(): void
    {
        $employeeUser = $this->createUser('employee');
        $employee = $this->createEmployee($employeeUser);
        $attendance = $this->createAttendance($employee);

        $this->actingAs($employeeUser)->get(route('hr.attendances.index'))->assertForbidden();
        $this->actingAs($employeeUser)->get(route('hr.attendances.edit', $attendance))->assertForbidden();
    }
}
