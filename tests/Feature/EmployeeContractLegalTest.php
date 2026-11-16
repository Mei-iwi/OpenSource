<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\User;
use App\Notifications\ContractExpiringNotification;
use Database\Seeders\DemoContractSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmployeeContractLegalTest extends TestCase
{
    use RefreshDatabase;

    private Department $department;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->department = Department::create(['code' => 'HR-CONTRACT', 'name' => 'Nhân sự']);
        JobPosition::firstOrCreate(['name' => 'Nhân viên']);
        $this->employee = $this->makeEmployee('employee', 'EMP-C-1');
    }

    private function makeEmployee(string $role, string $code): Employee
    {
        $user = User::create([
            'name' => $code,
            'email' => strtolower($code).'@example.com',
            'password' => 'Password123!',
            'role' => $role,
            'account_status' => 'active',
        ]);

        return Employee::create([
            'user_id' => $user->id,
            'department_id' => $this->department->id,
            'employee_code' => $code,
            'position' => 'Nhân viên',
            'hire_date' => '2025-01-01',
            'employment_status' => 'active',
        ]);
    }

    private function contractData(array $changes = []): array
    {
        return array_merge([
            'contract_type' => 'fixed-term',
            'contract_start_date' => '2026-01-01',
            'contract_end_date' => '2026-12-31',
        ], $changes);
    }

    public function test_existing_employees_have_nullable_contract_columns(): void
    {
        foreach (['contract_type', 'contract_start_date', 'contract_end_date', 'identity_number', 'tax_code', 'social_insurance_number'] as $column) {
            $this->assertTrue(Schema::hasColumn('employees', $column));
            $this->assertNull(DB::table('employees')->where('id', $this->employee->id)->value($column));
        }
    }

    public function test_hr_filters_missing_contracts_and_updates_encrypted_legal_data(): void
    {
        $complete = $this->makeEmployee('employee', 'EMP-C-2');
        $complete->update($this->contractData());
        $hr = $this->makeEmployee('hr', 'HR-C-1')->user;

        $this->actingAs($hr)->get(route('hr.contracts.index', ['status' => 'missing']))
            ->assertOk()->assertSee('EMP-C-1')->assertDontSee('EMP-C-2');

        $this->actingAs($hr)->put(route('hr.contracts.update', $this->employee), $this->contractData([
            'identity_number' => '012345678901',
        ]))->assertRedirect(route('hr.contracts.show', $this->employee));

        $this->assertSame('fixed-term', $this->employee->fresh()->contract_type);
        $this->assertNotSame('012345678901', DB::table('employees')->where('id', $this->employee->id)->value('identity_number'));
        $this->assertArrayNotHasKey('identity_number', $this->employee->fresh()->toArray());
        $this->actingAs($hr)->get(route('hr.contracts.index'))->assertDontSee('012345678901');
    }

    public function test_contract_validation_and_switch_to_indefinite(): void
    {
        $admin = $this->makeEmployee('admin', 'ADM-C-1')->user;
        $this->actingAs($admin)->put(route('hr.contracts.update', $this->employee), $this->contractData([
            'contract_type' => 'temporary',
            'contract_start_date' => '2026-12-10',
            'contract_end_date' => '2026-12-01',
        ]))->assertSessionHasErrors(['contract_type', 'contract_end_date']);
        $this->actingAs($admin)->put(route('hr.contracts.update', $this->employee), $this->contractData([
            'contract_end_date' => null,
        ]))->assertSessionHasErrors('contract_end_date');

        $this->actingAs($admin)->put(route('hr.contracts.update', $this->employee), $this->contractData())
            ->assertRedirect(route('hr.contracts.show', $this->employee));
        $this->actingAs($admin)->put(route('hr.contracts.update', $this->employee), $this->contractData([
            'contract_type' => 'indefinite',
        ]))->assertRedirect(route('hr.contracts.show', $this->employee));
        $this->assertNull($this->employee->fresh()->contract_end_date);
    }

    public function test_expiring_and_expired_filters_use_server_dates(): void
    {
        $today = Carbon::today(config('attendance.timezone'));
        $this->employee->update($this->contractData([
            'contract_start_date' => $today->copy()->subYear()->toDateString(),
            'contract_end_date' => $today->copy()->addDays(10)->toDateString(),
        ]));
        $expired = $this->makeEmployee('employee', 'EMP-C-2');
        $expired->update($this->contractData([
            'contract_start_date' => $today->copy()->subYear()->toDateString(),
            'contract_end_date' => $today->copy()->subDay()->toDateString(),
        ]));
        $hr = $this->makeEmployee('hr', 'HR-C-1')->user;

        $this->actingAs($hr)->get(route('hr.contracts.index', ['status' => 'expiring']))
            ->assertOk()->assertSee('EMP-C-1')->assertDontSee('EMP-C-2');
        $this->actingAs($hr)->get(route('hr.contracts.index', ['status' => 'expired']))
            ->assertOk()->assertSee('EMP-C-2')->assertDontSee('EMP-C-1');
    }

    public function test_employee_form_does_not_modify_contract(): void
    {
        $admin = $this->makeEmployee('admin', 'ADM-C-1')->user;
        $this->employee->update($this->contractData());

        $this->actingAs($admin)->put(route('hr.employees.update', $this->employee), [
            'name' => $this->employee->user->name,
            'email' => $this->employee->user->email,
            'department_id' => $this->department->id,
            'position' => 'Nhân viên',
            'hire_date' => '2025-01-01',
            'employment_status' => 'active',
            'contract_type' => 'indefinite',
        ])->assertRedirect(route('hr.employees.index'));

        $this->assertSame('fixed-term', $this->employee->fresh()->contract_type);
        $this->assertSame('2026-12-31', $this->employee->fresh()->contract_end_date->format('Y-m-d'));
    }

    public function test_employee_is_forbidden_and_hr_cannot_manage_admin_contract(): void
    {
        $this->actingAs($this->employee->user)->get(route('hr.contracts.index'))->assertForbidden();
        $this->actingAs($this->employee->user)->put(route('hr.contracts.update', $this->employee), $this->contractData())->assertForbidden();
        $this->actingAs($this->employee->user)->patch(route('profile.update'), ['identity_number' => '012345678901'])->assertSessionHasErrors('identity_number');

        $adminEmployee = $this->makeEmployee('admin', 'ADM-C-1');
        $hr = $this->makeEmployee('hr', 'HR-C-1')->user;
        $this->actingAs($hr)->get(route('hr.contracts.index'))->assertDontSee('ADM-C-1');
        $this->actingAs($hr)->get(route('hr.contracts.show', $adminEmployee))->assertForbidden();
        $this->actingAs($hr)->get(route('hr.contracts.edit', $adminEmployee))->assertForbidden();
        $this->actingAs($hr)->put(route('hr.contracts.update', $adminEmployee), $this->contractData())->assertForbidden();
    }

    public function test_expiry_notification_still_uses_contract_end_date(): void
    {
        Notification::fake();
        $hr = $this->makeEmployee('hr', 'HR-C-1')->user;
        $this->employee->update([
            'contract_type' => 'fixed-term',
            'contract_start_date' => now()->subMonth()->toDateString(),
            'contract_end_date' => now()->addDays(15)->toDateString(),
        ]);

        $this->artisan('notify:expiring-contracts')->assertExitCode(0);
        Notification::assertSentTo($hr, ContractExpiringNotification::class);
    }

    public function test_demo_contract_seed_is_repeatable_and_preserves_entered_contracts(): void
    {
        $blank = $this->makeEmployee('employee', 'IT-0002');
        $blank->user->update(['email' => 'it2@emp.hr-management.com']);
        $entered = $this->makeEmployee('employee', 'IT-0003');
        $entered->user->update(['email' => 'it3@emp.hr-management.com']);
        $entered->update($this->contractData(['contract_end_date' => '2028-01-01']));

        $this->seed(DemoContractSeeder::class);
        $this->seed(DemoContractSeeder::class);

        $this->assertSame('fixed-term', $blank->fresh()->contract_type);
        $this->assertSame('2026-01-01', $blank->fresh()->contract_start_date->toDateString());
        $this->assertSame('2028-01-01', $entered->fresh()->contract_end_date->toDateString());
        $this->assertNull($this->employee->fresh()->contract_type);
    }
}
