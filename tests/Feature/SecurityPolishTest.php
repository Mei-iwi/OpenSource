<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityPolishTest extends TestCase
{
    use RefreshDatabase;

    private function hr(): User { return User::factory()->create(['role' => 'hr']); }
    private function employee(): Employee { return Employee::factory()->create(['user_id' => User::factory()->create(['role' => 'employee'])->id]); }

    public function test_authorized_code_availability_returns_json_and_supports_edit_exclusion(): void
    {
        $employee = $this->employee();
        $response = $this->actingAs($this->hr())->getJson('/hr/employees/check-code?employee_code='.$employee->employee_code);
        $response->assertOk()->assertJson(['available' => false]);
        $this->actingAs($this->hr())->getJson('/hr/employees/check-code?employee_code='.$employee->employee_code.'&employee_id='.$employee->id)->assertOk()->assertJson(['available' => true]);
        $this->actingAs($this->hr())->getJson('/hr/employees/check-code?employee_code=EMP-NOT-FOUND')->assertOk()->assertJson(['available' => true]);
        $this->actingAs(User::factory()->create(['role' => 'employee']))->getJson('/hr/employees/check-code?employee_code=EMP-NOT-FOUND')->assertForbidden();
    }

    public function test_hr_can_upload_valid_avatar_and_invalid_uploads_are_rejected(): void
    {
        Storage::fake('persistent_uploads');
        $department = Department::factory()->create();
        $payload = ['name' => 'Avatar User', 'email' => 'avatar@example.test', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'department_id' => $department->id, 'employee_code' => 'EMP-AVATAR', 'hire_date' => '2025-01-01', 'employment_status' => 'active', 'avatar' => UploadedFile::fake()->image('avatar.jpg')];
        $this->actingAs($this->hr())->post('/hr/employees', $payload)->assertRedirect('/hr/employees');
        $employee = Employee::where('employee_code', 'EMP-AVATAR')->firstOrFail();
        Storage::disk('persistent_uploads')->assertExists($employee->avatar_path);
        $invalid = [...$payload, 'email' => 'invalid@example.test', 'employee_code' => 'EMP-BAD', 'avatar' => UploadedFile::fake()->create('script.php', 10, 'application/x-php')];
        $this->actingAs($this->hr())->post('/hr/employees', $invalid)->assertSessionHasErrors('avatar');
        $oversized = [...$payload, 'email' => 'large@example.test', 'employee_code' => 'EMP-LARGE', 'avatar' => UploadedFile::fake()->image('large.jpg')->size(2049)];
        $this->actingAs($this->hr())->post('/hr/employees', $oversized)->assertSessionHasErrors('avatar');
    }

    public function test_employee_can_update_own_avatar_but_cannot_manage_other_employee(): void
    {
        Storage::fake('persistent_uploads');
        $employee = $this->employee();
        $this->actingAs($employee->user)->put('/employee/profile', ['phone' => '0901234567', 'address' => 'Updated', 'date_of_birth' => '1995-02-02', 'avatar' => UploadedFile::fake()->image('self.png')])->assertRedirect('/employee/profile');
        $employee->refresh();
        Storage::disk('persistent_uploads')->assertExists($employee->avatar_path);
        $other = $this->employee();
        $this->actingAs($employee->user)->get('/hr/employees/'.$other->id.'/edit')->assertForbidden();
    }
}
