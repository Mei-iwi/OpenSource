<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DepartmentManagerTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function createDepartment(array $attributes = []): Department
    {
        $id = $this->seq++;

        return Department::create(array_merge([
            'code' => sprintf('PB-MGR-%04d', $id),
            'name' => "Phòng ban {$id}",
            'description' => "Mô tả phòng ban {$id}",
            'manager_id' => null,
        ], $attributes));
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

    protected function createEmployee(?User $user = null, ?Department $dept = null, string $status = 'active'): Employee
    {
        $user ??= $this->createUser('employee');
        $dept ??= $this->createDepartment();
        $id = $this->seq++;

        return Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'employee_code' => sprintf('EMP-MGR-%04d', $id),
            'position' => 'Chuyên viên',
            'hire_date' => '2025-01-01',
            'date_of_birth' => '1995-05-15',
            'employment_status' => $status,
        ]);
    }

    public function test_admin_and_hr_can_create_department_with_manager(): void
    {
        $hr = $this->createUser('hr');
        $employee = $this->createEmployee();

        $response = $this->actingAs($hr)->post(route('hr.departments.store'), [
            'code' => 'PB-NEW-MGR',
            'name' => 'Phòng Công nghệ và Đổi mới',
            'description' => 'Phòng ban công nghệ',
            'manager_id' => $employee->id,
        ]);

        $response->assertRedirect(route('hr.departments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'code' => 'PB-NEW-MGR',
            'manager_id' => $employee->id,
        ]);

        $createdDept = Department::where('code', 'PB-NEW-MGR')->first();
        $this->assertNotNull($createdDept->manager);
        $this->assertEquals($employee->id, $createdDept->manager->id);
    }

    public function test_admin_and_hr_can_update_department_manager(): void
    {
        $hr = $this->createUser('hr');
        $initialManager = $this->createEmployee();
        $dept = $this->createDepartment(['manager_id' => $initialManager->id]);

        $newManager = $this->createEmployee();

        $response = $this->actingAs($hr)->put(route('hr.departments.update', $dept), [
            'code' => $dept->code,
            'name' => 'Tên mới phòng ban',
            'description' => $dept->description,
            'manager_id' => $newManager->id,
        ]);

        $response->assertRedirect(route('hr.departments.index'));
        $response->assertSessionHas('success');

        $dept->refresh();
        $this->assertEquals($newManager->id, $dept->manager_id);
    }

    public function test_department_can_be_created_and_updated_with_null_manager(): void
    {
        $hr = $this->createUser('hr');

        // Create with null manager
        $response = $this->actingAs($hr)->post(route('hr.departments.store'), [
            'code' => 'PB-NO-MGR',
            'name' => 'Phòng Chưa Có Trưởng Phòng',
            'manager_id' => '',
        ]);

        $response->assertRedirect(route('hr.departments.index'));
        $this->assertDatabaseHas('departments', [
            'code' => 'PB-NO-MGR',
            'manager_id' => null,
        ]);

        // Update existing manager to null
        $dept = Department::where('code', 'PB-NO-MGR')->first();
        $mgr = $this->createEmployee();
        $dept->update(['manager_id' => $mgr->id]);

        $updateResponse = $this->actingAs($hr)->put(route('hr.departments.update', $dept), [
            'code' => $dept->code,
            'name' => $dept->name,
            'manager_id' => '',
        ]);

        $updateResponse->assertRedirect(route('hr.departments.index'));
        $dept->refresh();
        $this->assertNull($dept->manager_id);
    }

    public function test_invalid_or_inactive_employee_cannot_be_assigned_as_manager(): void
    {
        $hr = $this->createUser('hr');

        // Non-existing employee ID
        $response1 = $this->actingAs($hr)->post(route('hr.departments.store'), [
            'code' => 'PB-INV-1',
            'name' => 'Phòng Lỗi ID',
            'manager_id' => 999999,
        ]);
        $response1->assertSessionHasErrors('manager_id');

        // Inactive employee
        $inactiveEmployee = $this->createEmployee(null, null, 'inactive');
        $response2 = $this->actingAs($hr)->post(route('hr.departments.store'), [
            'code' => 'PB-INV-2',
            'name' => 'Phòng Inactive',
            'manager_id' => $inactiveEmployee->id,
        ]);
        $response2->assertSessionHasErrors('manager_id');
    }

    public function test_employee_is_forbidden_from_department_crud(): void
    {
        $employeeUser = $this->createUser('employee');
        $dept = $this->createDepartment();

        $this->actingAs($employeeUser)->get(route('hr.departments.index'))->assertForbidden();
        $this->actingAs($employeeUser)->get(route('hr.departments.create'))->assertForbidden();
        $this->actingAs($employeeUser)->post(route('hr.departments.store'), ['name' => 'Dept', 'code' => 'PB'])->assertForbidden();
        $this->actingAs($employeeUser)->get(route('hr.departments.show', $dept))->assertForbidden();
        $this->actingAs($employeeUser)->get(route('hr.departments.edit', $dept))->assertForbidden();
        $this->actingAs($employeeUser)->put(route('hr.departments.update', $dept), ['name' => 'Dept', 'code' => 'PB'])->assertForbidden();
        $this->actingAs($employeeUser)->delete(route('hr.departments.destroy', $dept))->assertForbidden();
    }

    public function test_deleting_employee_who_is_manager_sets_department_manager_to_null(): void
    {
        $managerEmployee = $this->createEmployee();
        $dept = $this->createDepartment(['manager_id' => $managerEmployee->id]);

        $this->assertEquals($managerEmployee->id, $dept->fresh()->manager_id);

        // Delete the employee
        $managerEmployee->delete();

        $this->assertNull($dept->fresh()->manager_id);
    }

    public function test_employee_transferring_department_clears_old_department_manager(): void
    {
        $dept1 = $this->createDepartment();
        $dept2 = $this->createDepartment();

        $manager = $this->createEmployee(null, $dept1);
        $dept1->update(['manager_id' => $manager->id]);

        $this->assertEquals($manager->id, $dept1->fresh()->manager_id);

        // Transfer manager to Dept 2
        $manager->update(['department_id' => $dept2->id]);

        // Dept 1's manager_id is automatically cleared
        $this->assertNull($dept1->fresh()->manager_id);
    }

    public function test_department_views_render_manager_details_and_form_options(): void
    {
        $hr = $this->createUser('hr');
        $manager = $this->createEmployee();
        $dept = $this->createDepartment(['manager_id' => $manager->id]);

        // Index page renders manager
        $indexResponse = $this->actingAs($hr)->get(route('hr.departments.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee($manager->user->name);
        $indexResponse->assertSee($manager->employee_code);

        // Show page renders manager
        $showResponse = $this->actingAs($hr)->get(route('hr.departments.show', $dept));
        $showResponse->assertOk();
        $showResponse->assertSee($manager->user->name);
        $showResponse->assertSee($manager->employee_code);

        // Create page renders manager select option
        $createResponse = $this->actingAs($hr)->get(route('hr.departments.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Trưởng phòng');
        $createResponse->assertSee($manager->user->name);

        // Edit page renders selected manager
        $editResponse = $this->actingAs($hr)->get(route('hr.departments.edit', $dept));
        $editResponse->assertOk();
        $editResponse->assertSee('selected');
        $editResponse->assertSee($manager->user->name);
    }
}
