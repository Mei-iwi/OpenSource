<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JobPositionCrudTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function createDepartment(): Department
    {
        return Department::create([
            'code' => 'PB-POS-'.$this->seq,
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

    protected function createEmployee(?User $user = null, ?string $position = null): Employee
    {
        $user ??= $this->createUser('employee');
        $dept = $this->createDepartment();
        $id = $this->seq++;

        return Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'employee_code' => sprintf('EMP-POS-%04d', $id),
            'position' => $position ?? 'Lập trình viên Backend',
            'hire_date' => '2025-01-01',
            'date_of_birth' => '1995-05-15',
            'employment_status' => 'active',
        ]);
    }

    public function test_hr_can_view_job_positions_index_with_search(): void
    {
        $hr = $this->createUser('hr');

        $pos1 = JobPosition::firstOrCreate(['name' => 'Kỹ sư Điện toán đám mây']);
        $pos2 = JobPosition::firstOrCreate(['name' => 'Chuyên viên Kiểm thử QA']);

        $hrResponse = $this->actingAs($hr)->get(route('hr.job-positions.index'));
        $hrResponse->assertOk();
        $hrResponse->assertSee($pos1->name);
        $hrResponse->assertSee($pos2->name);

        // Search filter
        $searchResponse = $this->actingAs($hr)->get(route('hr.job-positions.index', ['search' => 'Điện toán']));
        $searchResponse->assertOk();
        $searchResponse->assertSee($pos1->name);
        $searchResponse->assertDontSee($pos2->name);
    }

    public function test_admin_can_view_job_positions_index(): void
    {
        $admin = $this->createUser('admin');
        $pos = JobPosition::firstOrCreate(['name' => 'Kiến trúc sư Giải pháp']);

        $adminResponse = $this->actingAs($admin)->get(route('hr.job-positions.index'));
        $adminResponse->assertOk();
        $adminResponse->assertSee($pos->name);
    }

    public function test_admin_and_hr_can_create_job_position(): void
    {
        $hr = $this->createUser('hr');

        $response = $this->actingAs($hr)->post(route('hr.job-positions.store'), [
            'name' => 'Kỹ sư An toàn thông tin',
        ]);

        $response->assertRedirect(route('hr.job-positions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('job_positions', [
            'name' => 'Kỹ sư An toàn thông tin',
        ]);
    }

    public function test_duplicate_position_name_is_rejected(): void
    {
        $hr = $this->createUser('hr');
        JobPosition::firstOrCreate(['name' => 'Kỹ sư Trí tuệ nhân tạo']);

        $response = $this->actingAs($hr)->post(route('hr.job-positions.store'), [
            'name' => 'Kỹ sư Trí tuệ nhân tạo',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_and_hr_can_update_job_position_and_syncs_existing_employees(): void
    {
        $hr = $this->createUser('hr');
        $position = JobPosition::firstOrCreate(['name' => 'Chuyên viên SEO']);
        $employee = $this->createEmployee(null, 'Chuyên viên SEO');

        $this->assertEquals('Chuyên viên SEO', $employee->position);

        $response = $this->actingAs($hr)->put(route('hr.job-positions.update', $position), [
            'name' => 'Trưởng nhóm SEO',
        ]);

        $response->assertRedirect(route('hr.job-positions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('job_positions', ['id' => $position->id, 'name' => 'Trưởng nhóm SEO']);
        $this->assertDatabaseMissing('job_positions', ['name' => 'Chuyên viên SEO']);

        $employee->refresh();
        $this->assertEquals('Trưởng nhóm SEO', $employee->position);
    }

    public function test_update_allows_keeping_same_name(): void
    {
        $hr = $this->createUser('hr');
        $position = JobPosition::firstOrCreate(['name' => 'Chuyên viên Tuyển dụng']);

        $response = $this->actingAs($hr)->put(route('hr.job-positions.update', $position), [
            'name' => 'Chuyên viên Tuyển dụng',
        ]);

        $response->assertRedirect(route('hr.job-positions.index'));
        $response->assertSessionHas('success');
    }

    public function test_admin_and_hr_can_delete_unused_job_position(): void
    {
        $hr = $this->createUser('hr');
        $position = JobPosition::firstOrCreate(['name' => 'Vị trí thử nghiệm chưa ai dùng']);

        $response = $this->actingAs($hr)->delete(route('hr.job-positions.destroy', $position));

        $response->assertRedirect(route('hr.job-positions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('job_positions', [
            'id' => $position->id,
        ]);
    }

    public function test_cannot_delete_job_position_currently_assigned_to_employees(): void
    {
        $hr = $this->createUser('hr');
        $position = JobPosition::firstOrCreate(['name' => 'Kỹ sư Dữ liệu']);
        $this->createEmployee(null, 'Kỹ sư Dữ liệu');

        $response = $this->actingAs($hr)->delete(route('hr.job-positions.destroy', $position));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('job_positions', [
            'id' => $position->id,
            'name' => 'Kỹ sư Dữ liệu',
        ]);
    }

    public function test_employee_is_forbidden_from_job_position_crud(): void
    {
        $employeeUser = $this->createUser('employee');
        $position = JobPosition::firstOrCreate(['name' => 'Nhân viên Bảo vệ']);

        $this->actingAs($employeeUser)->get(route('hr.job-positions.index'))->assertForbidden();
        $this->actingAs($employeeUser)->get(route('hr.job-positions.create'))->assertForbidden();
        $this->actingAs($employeeUser)->post(route('hr.job-positions.store'), ['name' => 'New Pos'])->assertForbidden();
        $this->actingAs($employeeUser)->get(route('hr.job-positions.show', $position))->assertForbidden();
        $this->actingAs($employeeUser)->get(route('hr.job-positions.edit', $position))->assertForbidden();
        $this->actingAs($employeeUser)->put(route('hr.job-positions.update', $position), ['name' => 'Update Pos'])->assertForbidden();
        $this->actingAs($employeeUser)->delete(route('hr.job-positions.destroy', $position))->assertForbidden();
    }

    public function test_unauthenticated_guest_is_redirected_to_login(): void
    {
        $position = JobPosition::firstOrCreate(['name' => 'Chuyên viên Đào tạo']);

        $this->get(route('hr.job-positions.index'))->assertRedirect(route('login'));
        $this->delete(route('hr.job-positions.destroy', $position))->assertRedirect(route('login'));
    }

    public function test_employee_create_and_edit_forms_load_all_active_job_positions(): void
    {
        $hr = $this->createUser('hr');
        $customPosition = JobPosition::firstOrCreate(['name' => 'Giám đốc Điều hành']);
        $employee = $this->createEmployee();

        // Create form contains position
        $createResponse = $this->actingAs($hr)->get(route('hr.employees.create'));
        $createResponse->assertOk();
        $createResponse->assertSee($customPosition->name);

        // Edit form contains position
        $editResponse = $this->actingAs($hr)->get(route('hr.employees.edit', $employee));
        $editResponse->assertOk();
        $editResponse->assertSee($customPosition->name);
    }

    public function test_store_employee_validates_position_exists_in_job_positions(): void
    {
        $hr = $this->createUser('hr');
        $dept = $this->createDepartment();
        $pos = JobPosition::firstOrCreate(['name' => 'Chuyên viên Pháp chế']);

        // Valid position passes validation
        $validData = [
            'name' => 'Nguyễn Văn Test',
            'email' => 'validpos@example.com',
            'role' => 'employee',
            'department_id' => $dept->id,
            'position' => $pos->name,
            'date_of_birth' => '1996-01-01',
            'hire_date' => '2025-01-01',
            'employment_status' => 'active',
        ];

        $successResponse = $this->actingAs($hr)->post(route('hr.employees.store'), $validData);
        $successResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('employees', ['position' => $pos->name]);

        // Invalid position fails validation
        $invalidData = array_merge($validData, [
            'email' => 'invalidpos@example.com',
            'position' => 'Chức vụ hoàn toàn không tồn tại trên hệ thống',
        ]);

        $failResponse = $this->actingAs($hr)->post(route('hr.employees.store'), $invalidData);
        $failResponse->assertSessionHasErrors('position');
    }
}
