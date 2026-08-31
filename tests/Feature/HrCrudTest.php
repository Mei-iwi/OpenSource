<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function hr(): User
    {
        return User::factory()->create(['role' => 'hr']);
    }

    private function department(): Department
    {
        return Department::factory()->create(['code' => 'PB-'.fake()->unique()->numberBetween(10, 99)]);
    }

    public function test_admin_and_hr_can_crud_departments(): void
    {
        $admin = $this->admin();
        $response = $this->actingAs($admin)->post('/hr/departments', ['code' => 'PB-NEW', 'name' => 'Phòng mới']);
        $response->assertRedirect('/hr/departments');
        $department = Department::where('code', 'PB-NEW')->firstOrFail();

        $this->actingAs($this->hr())->put("/hr/departments/{$department->id}", ['code' => 'PB-UPD', 'name' => 'Phòng cập nhật'])->assertRedirect('/hr/departments');
        $this->assertDatabaseHas('departments', ['id' => $department->id, 'code' => 'PB-UPD']);
        $this->actingAs($admin)->delete("/hr/departments/{$department->id}")->assertRedirect('/hr/departments');
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_employee_role_is_blocked_from_hr_crud(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $this->actingAs($employee)->get('/hr/departments')->assertForbidden();
        $this->actingAs($employee)->get('/hr/employees')->assertForbidden();
    }

    public function test_duplicate_department_code_is_rejected_and_department_with_employee_cannot_delete(): void
    {
        $admin = $this->admin();
        $department = $this->department();
        $this->actingAs($admin)->post('/hr/departments', ['code' => $department->code, 'name' => 'Trùng mã'])->assertSessionHasErrors('code');

        Employee::factory()->create(['department_id' => $department->id]);
        $this->actingAs($admin)->delete("/hr/departments/{$department->id}")->assertRedirect();
        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_admin_and_hr_can_create_employee_with_user_in_transaction(): void
    {
        $department = $this->department();
        $this->actingAs($this->hr())->post('/hr/employees', [
            'name' => 'Nhân viên mới', 'email' => 'employee-new@example.com', 'password' => 'Password123!', 'password_confirmation' => 'Password123!',
            'department_id' => $department->id, 'employee_code' => 'EMP-NEW', 'phone' => '0900000000', 'address' => 'Hà Nội',
            'position' => 'Chuyên viên', 'hire_date' => '2025-01-01', 'employment_status' => 'active',
        ])->assertRedirect('/hr/employees');

        $user = User::where('email', 'employee-new@example.com')->firstOrFail();
        $this->assertSame('employee', $user->role);
        $this->assertDatabaseHas('employees', ['user_id' => $user->id, 'employee_code' => 'EMP-NEW']);
    }

    public function test_duplicate_employee_code_and_email_are_rejected(): void
    {
        $admin = $this->admin();
        $department = $this->department();
        $existing = Employee::factory()->create(['department_id' => $department->id]);
        $payload = ['name' => 'Trùng', 'email' => $existing->user->email, 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'department_id' => $department->id, 'employee_code' => $existing->employee_code, 'hire_date' => '2025-01-01', 'employment_status' => 'active'];
        $this->actingAs($admin)->post('/hr/employees', $payload)->assertSessionHasErrors(['email', 'employee_code']);
    }

    public function test_employee_search_filter_and_pagination_keep_query_string(): void
    {
        $admin = $this->admin();
        $department = $this->department();
        Employee::factory()->create(['department_id' => $department->id, 'employee_code' => 'EMP-SEARCH', 'employment_status' => 'active']);
        Employee::factory()->create(['department_id' => $department->id, 'employee_code' => 'EMP-OTHER', 'employment_status' => 'inactive']);
        foreach (range(1, 11) as $index) {
            Employee::factory()->create([
                'department_id' => $department->id,
                'employee_code' => 'EMP-SEARCH-'.$index,
                'employment_status' => 'active',
            ]);
        }

        $response = $this->actingAs($admin)->get('/hr/employees?search=EMP-SEARCH&department_id='.$department->id.'&employment_status=active');
        $response->assertOk()->assertSee('EMP-SEARCH')->assertDontSee('EMP-OTHER')->assertSee('search=EMP-SEARCH', false);
    }

    public function test_admin_can_access_hr_module(): void
    {
        $this->actingAs($this->admin())->get('/hr/departments')->assertOk();
        $this->actingAs($this->admin())->get('/hr/employees')->assertOk();
    }
}
