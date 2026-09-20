<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceLateDetectionTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('filesystems.attendance_proof_disk'));
        // Standard test configuration: 08:00 start, 15m grace, Asia/Ho_Chi_Minh
        config([
            'attendance.work_start_time' => '08:00',
            'attendance.grace_period_minutes' => 15,
            'attendance.timezone' => 'Asia/Ho_Chi_Minh',
            'attendance.allow_weekend_checkin' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    protected function createDepartment(): Department
    {
        return Department::create([
            'code' => 'PB-LATE-'.$this->seq,
            'name' => 'Phòng ban '.$this->seq++,
            'description' => 'Mô tả',
        ]);
    }

    protected function createEmployee(array $userAttrs = [], array $empAttrs = []): array
    {
        $id = $this->seq++;
        $user = User::create(array_merge([
            'name' => "Nhân viên {$id}",
            'email' => "nv{$id}@example.com",
            'password' => Hash::make('Password123!'),
            'role' => 'employee',
            'account_status' => 'active',
        ], $userAttrs));

        $dept = $this->createDepartment();

        $employee = Employee::create(array_merge([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'employee_code' => sprintf('EMP-%04d', $id),
            'position' => 'Chuyên viên',
            'hire_date' => '2025-01-01',
            'date_of_birth' => '1995-05-15',
            'employment_status' => 'active',
        ], $empAttrs));

        return [$user, $employee];
    }

    public function test_check_in_before_start_time_records_status_present(): void
    {
        [$user, $employee] = $this->createEmployee();
        $timezone = config('attendance.timezone');

        // Check-in at 07:45 on Thursday (work start is 08:00)
        Carbon::setTestNow(Carbon::parse('2026-10-15 07:45:00', $timezone));

        $response = $this->actingAs($user)->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'method' => 'camera',
        ]);

        $response->assertRedirect('/me/attendance');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'work_date' => '2026-10-15',
            'check_in' => '07:45:00',
            'status' => 'present',
        ]);
    }

    public function test_check_in_exactly_at_start_time_records_status_present(): void
    {
        [$user, $employee] = $this->createEmployee();
        $timezone = config('attendance.timezone');

        // Check-in at exactly 08:00:00
        Carbon::setTestNow(Carbon::parse('2026-10-15 08:00:00', $timezone));

        $response = $this->actingAs($user)->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'method' => 'camera',
        ]);

        $response->assertRedirect('/me/attendance');

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'work_date' => '2026-10-15',
            'check_in' => '08:00:00',
            'status' => 'present',
        ]);
    }

    public function test_check_in_within_grace_period_records_status_present(): void
    {
        [$user, $employee] = $this->createEmployee();
        $timezone = config('attendance.timezone');

        // Check-in at 08:10:00 (within 15-min grace period)
        Carbon::setTestNow(Carbon::parse('2026-10-15 08:10:00', $timezone));

        $response = $this->actingAs($user)->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'method' => 'camera',
        ]);

        $response->assertRedirect('/me/attendance');

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'work_date' => '2026-10-15',
            'check_in' => '08:10:00',
            'status' => 'present',
        ]);
    }

    public function test_check_in_exactly_at_grace_boundary_records_status_present(): void
    {
        [$user, $employee] = $this->createEmployee();
        $timezone = config('attendance.timezone');

        // Check-in at exactly 08:15:00 (exact boundary)
        Carbon::setTestNow(Carbon::parse('2026-10-15 08:15:00', $timezone));

        $response = $this->actingAs($user)->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'method' => 'camera',
        ]);

        $response->assertRedirect('/me/attendance');

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'work_date' => '2026-10-15',
            'check_in' => '08:15:00',
            'status' => 'present',
        ]);
    }

    public function test_check_in_after_grace_boundary_records_status_late(): void
    {
        [$user, $employee] = $this->createEmployee();
        $timezone = config('attendance.timezone');

        // Check-in at 08:15:01 (1 second past grace period)
        Carbon::setTestNow(Carbon::parse('2026-10-15 08:15:01', $timezone));

        $response = $this->actingAs($user)->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'method' => 'camera',
        ]);

        $response->assertRedirect('/me/attendance');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'work_date' => '2026-10-15',
            'check_in' => '08:15:01',
            'status' => 'late',
        ]);
    }

    public function test_check_in_well_after_grace_period_records_status_late(): void
    {
        [$user, $employee] = $this->createEmployee();
        $timezone = config('attendance.timezone');

        // Check-in at 08:45:00
        Carbon::setTestNow(Carbon::parse('2026-10-15 08:45:00', $timezone));

        $response = $this->actingAs($user)->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'method' => 'upload',
        ]);

        $response->assertRedirect('/me/attendance');

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'work_date' => '2026-10-15',
            'check_in' => '08:45:00',
            'status' => 'late',
        ]);
    }

    public function test_timezone_conversion_correctly_evaluates_late_status(): void
    {
        [$user, $employee] = $this->createEmployee();

        // Configured timezone is Asia/Ho_Chi_Minh (UTC+7)
        // 01:10:00 UTC = 08:10:00 Asia/Ho_Chi_Minh -> present (within grace)
        config(['attendance.timezone' => 'Asia/Ho_Chi_Minh']);
        Carbon::setTestNow(Carbon::parse('2026-10-15 01:10:00', 'UTC'));

        $this->actingAs($user)->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'method' => 'camera',
        ]);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'work_date' => '2026-10-15',
            'check_in' => '08:10:00',
            'status' => 'present',
        ]);
    }

    public function test_duplicate_check_in_on_same_day_is_rejected(): void
    {
        [$user, $employee] = $this->createEmployee();
        $timezone = config('attendance.timezone');
        Carbon::setTestNow(Carbon::parse('2026-10-15 08:05:00', $timezone));

        // First check-in
        $this->actingAs($user)->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('proof1.jpg'),
            'method' => 'camera',
        ])->assertRedirect('/me/attendance');

        // Second check-in on the same day
        $response = $this->actingAs($user)->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('proof2.jpg'),
            'method' => 'camera',
        ]);

        $response->assertSessionHasErrors(['photo']);
        $this->assertEquals(1, Attendance::where('employee_id', $employee->id)->whereDate('work_date', '2026-10-15')->count());
    }

    public function test_inactive_employee_cannot_check_in(): void
    {
        [$user, $employee] = $this->createEmployee([], ['employment_status' => 'inactive']);
        $timezone = config('attendance.timezone');
        Carbon::setTestNow(Carbon::parse('2026-10-15 08:00:00', $timezone));

        // Submitting check-in is forbidden
        $response = $this->actingAs($user)->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'method' => 'camera',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('attendances', ['employee_id' => $employee->id]);

        // Viewing self attendance page is also forbidden
        $this->actingAs($user)->get('/me/attendance')->assertForbidden();
    }

    public function test_weekend_check_in_rejected_when_configured(): void
    {
        config(['attendance.allow_weekend_checkin' => false]);

        [$user, $employee] = $this->createEmployee();
        $timezone = config('attendance.timezone');

        // Saturday 2026-10-17
        Carbon::setTestNow(Carbon::parse('2026-10-17 08:00:00', $timezone));

        $response = $this->actingAs($user)->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'method' => 'camera',
        ]);

        $response->assertSessionHasErrors(['photo']);
        $this->assertDatabaseMissing('attendances', [
            'employee_id' => $employee->id,
            'work_date' => '2026-10-17',
        ]);
    }

    public function test_authorization_unauthenticated_and_users_without_profile(): void
    {
        // Unauthenticated
        $this->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'method' => 'camera',
        ])->assertRedirect('/login');

        // User without employee profile
        $userWithoutProfile = User::create([
            'name' => 'Không hồ sơ',
            'email' => 'noprofile@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'employee',
            'account_status' => 'active',
        ]);

        $this->actingAs($userWithoutProfile)
            ->post('/me/attendance/check-in', [
                'photo' => UploadedFile::fake()->image('proof.jpg'),
                'method' => 'camera',
            ])
            ->assertForbidden();
    }

    public function test_existing_self_attendance_workflow_regression(): void
    {
        [$user, $employee] = $this->createEmployee();
        $timezone = config('attendance.timezone');

        // Check in at 08:20 (Late)
        Carbon::setTestNow(Carbon::parse('2026-10-15 08:20:00', $timezone));
        $this->actingAs($user)->post('/me/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('checkin.jpg'),
            'method' => 'camera',
        ])->assertRedirect('/me/attendance');

        $attendance = Attendance::where('employee_id', $employee->id)->whereDate('work_date', '2026-10-15')->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('late', $attendance->status);
        $this->assertNotNull($attendance->check_in_photo_path);

        // Check out at 17:30
        Carbon::setTestNow(Carbon::parse('2026-10-15 17:30:00', $timezone));
        $this->actingAs($user)->post('/me/attendance/check-out', [
            'photo' => UploadedFile::fake()->image('checkout.jpg'),
            'method' => 'camera',
        ])->assertRedirect('/me/attendance');

        $attendance->refresh();
        $this->assertEquals('17:30:00', $attendance->check_out);
        $this->assertEquals('late', $attendance->status); // Status preserved, not overwritten by checkout
        $this->assertNotNull($attendance->check_out_photo_path);
    }
}
