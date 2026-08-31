<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuntimeSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_authenticated_get_pages_render_without_500(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $hr = User::factory()->create(['role' => 'hr']);
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $employeeUser->id, 'department_id' => $department->id]);
        $attendance = Attendance::factory()->create(['employee_id' => $employee->id, 'work_date' => '2026-08-31']);

        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();

        foreach (['/admin/dashboard', '/admin/users', '/admin/users/create', '/admin/users/'.$admin->id, '/admin/users/'.$admin->id.'/edit'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
        foreach (['/hr/dashboard', '/hr/departments', '/hr/departments/create', '/hr/departments/'.$department->id, '/hr/departments/'.$department->id.'/edit', '/hr/employees', '/hr/employees/create', '/hr/employees/'.$employee->id, '/hr/employees/'.$employee->id.'/edit', '/hr/attendances', '/hr/attendances/create', '/hr/attendances/'.$attendance->id.'/edit', '/hr/reports', '/hr/reports/print', '/hr/reports/export.csv'] as $url) {
            $this->actingAs($hr)->get($url)->assertOk();
        }
        foreach (['/employee/dashboard', '/employee/profile', '/employee/profile/edit', '/employee/attendances'] as $url) {
            $this->actingAs($employeeUser)->get($url)->assertOk();
        }
    }

    public function test_removed_preview_routes_are_not_application_routes(): void
    {
        $this->get('/preview/admin')->assertNotFound();
        $this->get('/preview/hr')->assertNotFound();
        $this->get('/preview/employee')->assertNotFound();
    }
}
