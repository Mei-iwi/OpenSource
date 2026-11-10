<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('attendance.timezone', 'Asia/Ho_Chi_Minh');
        config()->set('attendance.face_api_url', 'http://face-service.test');
        config()->set('attendance.face_api_key', 'test-only-key');
        Storage::fake(config('filesystems.attendance_proof_disk'));

        $this->user = User::create([
            'name' => 'Employee', 'email' => 'employee@example.test', 'password' => 'Password123!',
            'role' => 'employee', 'account_status' => 'active',
        ]);
        $department = Department::create(['code' => 'OPS', 'name' => 'Operations']);
        $this->employee = Employee::create([
            'user_id' => $this->user->id, 'department_id' => $department->id,
            'employee_code' => 'EMP-0001', 'hire_date' => '2025-01-01', 'employment_status' => 'active',
        ]);
        $this->employee->forceFill(['face_reference_path' => 'face-references/reference.jpg'])->save();
        Storage::disk(config('filesystems.attendance_proof_disk'))->put('face-references/reference.jpg', 'reference-image');
        Carbon::setTestNow(Carbon::parse('2026-10-15 08:00:00', 'Asia/Ho_Chi_Minh'));
        $this->faceResponse(0.95);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function faceResponse(float $similarity): void
    {
        Http::swap(new Factory);
        Http::fake(['face-service.test/*' => Http::response([
            'result' => [[
                'source_image_face' => ['box' => ['probability' => 0.99]],
                'face_matches' => [[
                    'box' => ['probability' => 0.99],
                    'similarity' => $similarity,
                ]],
            ]],
        ])]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'photo' => UploadedFile::fake()->image('camera.jpg'),
            'method' => 'camera',
            'latitude' => 10.80728,
            'longitude' => 106.62863,
            'accuracy' => 10,
        ], $overrides);
    }

    public function test_location_and_accuracy_are_required_and_enforced(): void
    {
        $this->actingAs($this->user)->post(route('me.attendance.check-in'), [
            'photo' => UploadedFile::fake()->image('camera.jpg'), 'method' => 'camera',
        ])->assertSessionHasErrors(['latitude', 'longitude', 'accuracy']);
        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload(['latitude' => 10.82000]))
            ->assertSessionHasErrors('location');
        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload(['accuracy' => 500]))
            ->assertSessionHasErrors('location');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_checkin_accepts_a_location_within_100_meters_and_rejects_one_beyond_it(): void
    {
        $this->assertSame(100, config('attendance.site_radius_meters'));

        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload(['latitude' => 10.80828]))
            ->assertSessionHasErrors('location');
        $this->assertDatabaseCount('attendances', 0);

        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload(['latitude' => 10.80808]))
            ->assertRedirect(route('me.attendance.index'));
        $this->assertDatabaseCount('attendances', 1);
        $this->assertLessThanOrEqual(100, Attendance::firstOrFail()->check_in_distance_m);
    }

    public function test_upload_method_and_missing_enrollment_are_rejected(): void
    {
        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload(['method' => 'upload']))
            ->assertSessionHasErrors('method');
        $this->employee->forceFill(['face_reference_path' => null])->save();
        $this->user->unsetRelation('employee');
        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload())
            ->assertSessionHasErrors('photo');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_face_mismatch_or_unavailable_service_cannot_create_attendance(): void
    {
        $this->faceResponse(0.2);
        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload())
            ->assertSessionHasErrors('photo');
        config()->set('attendance.face_api_key', null);
        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload())
            ->assertSessionHasErrors('photo');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_face_verification_rejects_multiple_faces(): void
    {
        Http::swap(new Factory);
        Http::fake(['face-service.test/*' => Http::response(['result' => [
            ['source_image_face' => ['box' => ['probability' => 0.99]], 'face_matches' => [['box' => ['probability' => 0.99], 'similarity' => 0.95]]],
            ['source_image_face' => ['box' => ['probability' => 0.99]], 'face_matches' => [['box' => ['probability' => 0.99], 'similarity' => 0.95]]],
        ]])]);

        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload())
            ->assertSessionHasErrors('photo');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_server_records_actual_time_and_checkout_requires_checkin_and_same_face(): void
    {
        $this->actingAs($this->user)->post(route('me.attendance.check-out'), $this->payload())
            ->assertSessionHasErrors('photo');
        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload([
            'check_in' => '03:00:00', 'work_date' => '2020-01-01',
        ]))->assertRedirect(route('me.attendance.index'));
        $attendance = Attendance::firstOrFail();
        $this->assertSame('08:00:00', $attendance->check_in);
        $this->assertSame('2026-10-15', $attendance->work_date->toDateString());
        $this->assertSame('2026-10-15 01:00:00', $attendance->checked_in_at->format('Y-m-d H:i:s'));

        $this->faceResponse(0.2);
        $this->actingAs($this->user)->post(route('me.attendance.check-out'), $this->payload())
            ->assertSessionHasErrors('photo');
        $this->assertNull($attendance->fresh()->check_out);

        $this->faceResponse(0.95);
        Carbon::setTestNow(Carbon::parse('2026-10-15 17:00:00', 'Asia/Ho_Chi_Minh'));
        $this->actingAs($this->user)->post(route('me.attendance.check-out'), $this->payload())
            ->assertRedirect(route('me.attendance.index'));
        $this->assertSame('17:00:00', $attendance->fresh()->check_out);
        $this->assertSame('2026-10-15 10:00:00', $attendance->fresh()->checked_out_at->format('Y-m-d H:i:s'));
    }

    public function test_previous_open_shift_must_be_checked_out_before_new_checkin(): void
    {
        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload())->assertRedirect();
        Carbon::setTestNow(Carbon::parse('2026-10-16 08:00:00', 'Asia/Ho_Chi_Minh'));
        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload())
            ->assertSessionHasErrors('photo');
        $this->assertDatabaseCount('attendances', 1);

        $this->actingAs($this->user)->post(route('me.attendance.check-out'), $this->payload())->assertRedirect();
        $previous = Attendance::whereDate('work_date', '2026-10-15')->firstOrFail();
        $this->assertSame('08:00:00', $previous->check_out);
        $this->assertSame('2026-10-16 01:00:00', $previous->checked_out_at->format('Y-m-d H:i:s'));

        $this->actingAs($this->user)->post(route('me.attendance.check-in'), $this->payload())->assertRedirect();
        $this->assertDatabaseHas('attendances', ['employee_id' => $this->employee->id, 'work_date' => '2026-10-16']);
    }

    public function test_employee_cannot_register_face_reference(): void
    {
        $this->actingAs($this->user)->post(route('hr.employees.face-reference.store', $this->employee), [
            'face_reference' => UploadedFile::fake()->image('face.jpg'),
        ])->assertForbidden();
    }

    public function test_hr_can_register_face_reference(): void
    {
        $hr = User::create([
            'name' => 'HR', 'email' => 'hr@example.test', 'password' => 'Password123!',
            'role' => 'hr', 'account_status' => 'active',
        ]);
        $oldPath = $this->employee->face_reference_path;
        $this->actingAs($hr)->post(route('hr.employees.face-reference.store', $this->employee), [
            'face_reference' => UploadedFile::fake()->image('face.jpg'),
        ])->assertRedirect();
        $this->assertNotSame($oldPath, $this->employee->fresh()->face_reference_path);
        Storage::disk(config('filesystems.attendance_proof_disk'))->assertMissing($oldPath);
        Storage::disk(config('filesystems.attendance_proof_disk'))->assertExists($this->employee->fresh()->face_reference_path);
    }

    public function test_hr_cannot_replace_admin_face_reference(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'Password123!',
            'role' => 'admin', 'account_status' => 'active',
        ]);
        $adminEmployee = Employee::create([
            'user_id' => $admin->id,
            'department_id' => $this->employee->department_id,
            'employee_code' => 'ADM-0001', 'hire_date' => '2025-01-01', 'employment_status' => 'active',
        ]);
        $hr = User::create([
            'name' => 'HR', 'email' => 'hr@example.test', 'password' => 'Password123!',
            'role' => 'hr', 'account_status' => 'active',
        ]);

        $this->actingAs($hr)->post(route('hr.employees.face-reference.store', $adminEmployee), [
            'face_reference' => UploadedFile::fake()->image('face.jpg'),
        ])->assertForbidden();
        $this->assertNull($adminEmployee->fresh()->face_reference_path);
    }
}
