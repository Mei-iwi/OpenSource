<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\ContractExpiringNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmployeeContractLegalTest extends TestCase
{
    private Migration $migration;

    private User $employeeUser;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->string('account_status');
            $table->string('avatar_path')->nullable();
            $table->timestamps();
        });
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('department_id');
            $table->string('employee_code');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('position')->nullable();
            $table->date('hire_date');
            $table->date('contract_end_date')->nullable();
            $table->string('employment_status');
            $table->string('avatar_path')->nullable();
            $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        DB::table('departments')->insert(['id' => 1, 'code' => 'HR', 'name' => 'HR']);
        DB::table('job_positions')->insert(['id' => 1, 'name' => 'Staff']);

        $this->employeeUser = $this->createUser('employee', 'employee@example.com');
        DB::table('employees')->insert([
            'id' => 1,
            'user_id' => $this->employeeUser->id,
            'department_id' => 1,
            'employee_code' => 'EMP-1',
            'position' => 'Staff',
            'hire_date' => '2024-01-01',
            'contract_end_date' => '2026-12-01',
            'employment_status' => 'active',
        ]);

        $this->migration = require database_path('migrations/2026_11_08_000000_add_contract_and_legal_information_to_employees_table.php');
        $this->migration->up();
        $this->employee = Employee::findOrFail(1);
    }

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst($role).' User',
            'email' => $email,
            'password' => 'Password123!',
            'role' => $role,
            'account_status' => 'active',
        ]);
    }

    private function employeePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => $this->employeeUser->name,
            'email' => $this->employeeUser->email,
            'department_id' => 1,
            'position' => 'Staff',
            'hire_date' => '2024-01-01',
            'employment_status' => 'active',
        ], $overrides);
    }

    public function test_migration_preserves_existing_employee_and_adds_nullable_columns(): void
    {
        foreach (['contract_type', 'contract_start_date', 'identity_number', 'tax_code', 'social_insurance_number', 'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship'] as $column) {
            $this->assertTrue(Schema::hasColumn('employees', $column));
            $this->assertNull(DB::table('employees')->where('id', 1)->value($column));
        }
        $this->assertSame('2026-12-01', $this->employee->contract_end_date->format('Y-m-d'));

        $this->migration->down();
        $this->assertFalse(Schema::hasColumn('employees', 'identity_number'));
    }

    public function test_hr_can_update_contract_and_legal_fields_without_plaintext_storage(): void
    {
        $hr = $this->createUser('hr', 'hr@example.com');
        $this->actingAs($hr)->put(route('hr.employees.update', $this->employee), $this->employeePayload([
            'contract_type' => 'fixed-term',
            'contract_start_date' => '2026-01-01',
            'contract_end_date' => '2026-12-31',
            'identity_number' => '012345678901',
            'tax_code' => '1234567890',
            'social_insurance_number' => 'BHXH-123',
            'emergency_contact_name' => 'Contact Person',
            'emergency_contact_phone' => '0901234567',
            'emergency_contact_relationship' => 'Family',
        ]))->assertRedirect(route('hr.employees.index'));

        $employee = $this->employee->fresh();
        $this->assertSame('fixed-term', $employee->contract_type);
        $this->assertSame('2026-01-01', $employee->contract_start_date->format('Y-m-d'));
        $this->assertSame('2026-12-31', $employee->contract_end_date->format('Y-m-d'));
        $this->assertSame('012345678901', $employee->identity_number);
        $this->assertSame('Contact Person', $employee->emergency_contact_name);
        $this->assertNotSame('012345678901', DB::table('employees')->where('id', 1)->value('identity_number'));
        $this->assertArrayNotHasKey('identity_number', $employee->toArray());
        $this->assertArrayNotHasKey('emergency_contact_phone', $employee->toArray());
    }

    public function test_contract_validation_rejects_invalid_type_and_dates(): void
    {
        $hr = $this->createUser('hr', 'hr@example.com');
        $this->actingAs($hr)->put(route('hr.employees.update', $this->employee), $this->employeePayload([
            'contract_type' => 'temporary',
            'contract_start_date' => '2026-12-10',
            'contract_end_date' => '2026-12-01',
            'identity_number' => str_repeat('9', 21),
        ]))->assertSessionHasErrors(['contract_type', 'contract_end_date', 'identity_number']);

        $this->actingAs($hr)->put(route('hr.employees.update', $this->employee), $this->employeePayload([
            'contract_type' => 'indefinite',
            'contract_end_date' => '2027-01-01',
        ]))->assertSessionHasErrors(['contract_end_date']);
        $this->assertNull($this->employee->fresh()->contract_type);
    }

    public function test_employee_cannot_manage_contract(): void
    {
        $this->actingAs($this->employeeUser)->put(route('hr.employees.update', $this->employee), $this->employeePayload([
            'identity_number' => '999999999999',
        ]))->assertForbidden();
    }

    public function test_hr_cannot_view_or_edit_admin_record(): void
    {
        $admin = $this->createUser('admin', 'admin@example.com');
        $adminEmployee = Employee::create([
            'user_id' => $admin->id,
            'department_id' => 1,
            'employee_code' => 'ADM-1',
            'position' => 'Staff',
            'hire_date' => '2024-01-01',
            'employment_status' => 'active',
        ]);
        $hr = $this->createUser('hr', 'hr@example.com');
        $this->actingAs($hr)->get(route('hr.employees.show', $adminEmployee))->assertForbidden();
        $this->actingAs($hr)->put(route('hr.employees.update', $adminEmployee), $this->employeePayload([
            'name' => $admin->name,
            'email' => $admin->email,
        ]))->assertForbidden();
    }

    public function test_admin_can_manage_employee_contract(): void
    {
        $admin = $this->createUser('admin', 'admin@example.com');
        $this->actingAs($admin)->put(route('hr.employees.update', $this->employee), $this->employeePayload([
            'contract_type' => 'probation',
        ]))->assertRedirect(route('hr.employees.index'));
        $this->assertSame('probation', $this->employee->fresh()->contract_type);
    }

    public function test_employee_profile_updates_only_own_contact_information(): void
    {
        $other = $this->createUser('employee', 'other@example.com');
        $otherEmployee = Employee::create([
            'user_id' => $other->id,
            'department_id' => 1,
            'employee_code' => 'EMP-2',
            'position' => 'Staff',
            'hire_date' => '2024-01-01',
            'employment_status' => 'active',
        ]);

        $this->actingAs($this->employeeUser)->patch(route('profile.update'), [
            'phone' => '0900000000',
            'emergency_contact_name' => 'My Contact',
            'employee_id' => $otherEmployee->id,
            'identity_number' => '999999999999',
        ])->assertSessionHasErrors(['employee_id', 'identity_number']);

        $this->actingAs($this->employeeUser)->patch(route('profile.update'), [
            'phone' => '0900000000',
            'emergency_contact_name' => 'My Contact',
        ])->assertRedirect(route('profile.edit'));
        $this->assertSame('My Contact', $this->employee->fresh()->emergency_contact_name);
        $this->assertSame('0900000000', $this->employee->fresh()->phone);
        $this->assertNull($otherEmployee->fresh()->emergency_contact_name);
        $this->assertNull($this->employee->fresh()->identity_number);
    }

    public function test_contract_reminder_skips_indefinite_contracts_and_preserves_legacy_alerts(): void
    {
        Notification::fake();
        $hr = $this->createUser('hr', 'hr@example.com');
        $this->employee->update(['contract_end_date' => now()->addDays(15)->toDateString()]);
        $otherUser = $this->createUser('employee', 'other@example.com');
        $indefinite = Employee::create([
            'user_id' => $otherUser->id,
            'department_id' => 1,
            'employee_code' => 'EMP-INDEFINITE',
            'position' => 'Staff',
            'hire_date' => '2024-01-01',
            'contract_type' => 'indefinite',
            'contract_end_date' => now()->addDays(15)->toDateString(),
            'employment_status' => 'active',
        ]);

        $this->artisan('notify:expiring-contracts')->assertExitCode(0);

        Notification::assertSentTo($hr, ContractExpiringNotification::class, function ($notification) {
            return $notification->employee->id === $this->employee->id;
        });
        Notification::assertCount(1);
        $this->assertSame('indefinite', $indefinite->fresh()->contract_type);
    }
}
