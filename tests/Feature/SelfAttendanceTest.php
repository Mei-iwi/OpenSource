<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SelfAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-01 08:00:00');
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_employee_can_check_in_with_uploaded_image(): void
    {
        [$user, $employee] = $this->employeeUser('employee');

        $this->actingAs($user)->post(route('me.attendance.check-in'), [
            'photo' => UploadedFile::fake()->image('check-in.jpg'),
            'method' => 'upload',
            'employee_id' => Employee::factory()->create()->id,
        ])->assertRedirect(route('me.attendance.index'));

        $attendance = Attendance::where('employee_id', $employee->id)->firstOrFail();
        $this->assertSame('present', $attendance->status);
        $this->assertSame('upload', $attendance->check_in_method);
        $this->assertStringStartsWith('attendance-proofs/employee-'.$employee->id.'/2026/09/check-in-', $attendance->check_in_photo_path);
        Storage::disk('local')->assertExists($attendance->check_in_photo_path);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_employee_can_check_out_after_check_in(): void
    {
        [$user, $employee] = $this->employeeUser('employee');
        Attendance::create(['employee_id' => $employee->id, 'work_date' => '2026-09-01', 'check_in' => '08:00:00', 'status' => 'present']);

        Carbon::setTestNow('2026-09-01 17:00:00');
        $this->actingAs($user)->post(route('me.attendance.check-out'), [
            'photo' => UploadedFile::fake()->image('check-out.png'),
            'method' => 'upload',
        ])->assertRedirect(route('me.attendance.index'));

        $attendance = Attendance::firstOrFail();
        $this->assertSame('17:00:00', $attendance->check_out);
        $this->assertNotNull($attendance->check_out_photo_path);
        Storage::disk('local')->assertExists($attendance->check_out_photo_path);
    }

    public function test_hr_and_admin_with_employee_profiles_can_check_in(): void
    {
        foreach (['hr', 'admin'] as $role) {
            [$user, $employee] = $this->employeeUser($role);
            $this->actingAs($user)->post(route('me.attendance.check-in'), [
                'photo' => UploadedFile::fake()->image($role.'.webp'),
                'method' => 'camera',
            ])->assertRedirect(route('me.attendance.index'));
            $this->assertDatabaseHas('attendances', ['employee_id' => $employee->id, 'check_in_method' => 'camera']);
        }
    }

    public function test_user_without_employee_profile_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'employee']);

        $this->actingAs($user)->get(route('me.attendance.index'))->assertForbidden();
        $this->actingAs($user)->post(route('me.attendance.check-in'), [
            'photo' => UploadedFile::fake()->image('photo.jpg'),
            'method' => 'upload',
        ])->assertForbidden();
    }

    public function test_duplicate_check_in_and_check_out_are_rejected(): void
    {
        [$user, $employee] = $this->employeeUser('employee');
        Attendance::create(['employee_id' => $employee->id, 'work_date' => '2026-09-01', 'check_in' => '08:00:00', 'check_out' => '17:00:00', 'status' => 'present']);

        $this->actingAs($user)->post(route('me.attendance.check-in'), ['photo' => UploadedFile::fake()->image('again.jpg'), 'method' => 'upload'])->assertSessionHasErrors('photo');
        $this->actingAs($user)->post(route('me.attendance.check-out'), ['photo' => UploadedFile::fake()->image('again.jpg'), 'method' => 'upload'])->assertSessionHasErrors('photo');
    }

    public function test_check_out_before_check_in_is_rejected(): void
    {
        [$user] = $this->employeeUser('employee');

        $this->actingAs($user)->post(route('me.attendance.check-out'), [
            'photo' => UploadedFile::fake()->image('early.jpg'),
            'method' => 'upload',
        ])->assertSessionHasErrors('photo');
    }

    public function test_invalid_and_oversized_images_are_rejected(): void
    {
        [$user] = $this->employeeUser('employee');
        $payload = fn ($photo) => ['photo' => $photo, 'method' => 'upload'];

        $this->actingAs($user)->post(route('me.attendance.check-in'), $payload(UploadedFile::fake()->create('proof.php', 20, 'application/x-php')))->assertSessionHasErrors('photo');
        $this->actingAs($user)->post(route('me.attendance.check-in'), $payload(UploadedFile::fake()->create('large.jpg', 3073, 'image/jpeg')))->assertSessionHasErrors('photo');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_self_attendance_page_is_available_and_ownership_is_server_side(): void
    {
        [$user, $employee] = $this->employeeUser('employee');
        $other = Employee::factory()->create();
        Attendance::create(['employee_id' => $other->id, 'work_date' => '2026-09-01', 'check_in' => '08:00:00', 'status' => 'present']);

        $this->actingAs($user)->get(route('me.attendance.index'))->assertOk()->assertSee('Chấm công của tôi')->assertDontSee($other->employee_code);
        $this->actingAs($user)->post(route('me.attendance.check-in'), ['photo' => UploadedFile::fake()->image('own.jpg'), 'method' => 'upload', 'employee_id' => $other->id])->assertRedirect();
        $this->assertDatabaseHas('attendances', ['employee_id' => $employee->id, 'work_date' => '2026-09-01']);
    }

    /** @return array{0: User, 1: Employee} */
    private function employeeUser(string $role): array
    {
        $user = User::factory()->create(['role' => $role]);
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        return [$user, $employee];
    }
}
