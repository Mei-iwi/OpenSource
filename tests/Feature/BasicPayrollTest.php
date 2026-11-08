<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\PayrollMoney;
use App\Services\PayrollService;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BasicPayrollTest extends TestCase
{
    private User $hr;

    private User $user;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('attendance.work_days', [1]);
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
            $table->string('name');
        });
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('department_id');
            $table->string('employee_code');
            $table->date('hire_date');
            $table->string('employment_status');
            $table->string('avatar_path')->nullable();
            $table->timestamps();
        });
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->date('work_date');
            $table->string('status');
            $table->unique(['employee_id', 'work_date']);
            $table->timestamps();
        });
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->string('status');
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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->boolean('is_active');
            $table->timestamps();
        });
        DB::table('departments')->insert(['id' => 1, 'name' => 'Operations']);
        (require database_path('migrations/2026_11_08_010000_create_payroll_tables.php'))->up();

        $this->hr = $this->createUser('hr', 'hr@example.com');
        $this->user = $this->createUser('employee', 'employee@example.com');
        $this->employee = $this->createEmployee($this->user, 'EMP-1');
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

    private function createEmployee(User $user, string $code): Employee
    {
        return Employee::create([
            'user_id' => $user->id,
            'department_id' => 1,
            'employee_code' => $code,
            'hire_date' => '2025-01-01',
            'employment_status' => 'active',
        ]);
    }

    private function salary(Employee $employee, string $base = '100.01', string $allowance = '10.00', string $deduction = '2.00'): EmployeeSalary
    {
        return EmployeeSalary::create([
            'employee_id' => $employee->id,
            'base_salary' => $base,
            'allowance' => $allowance,
            'fixed_deduction' => $deduction,
        ]);
    }

    private function leave(Employee $employee, string $type, string $start, string $end, string $status = 'approved'): LeaveRequest
    {
        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $type,
            'start_date' => $start,
            'end_date' => $end,
            'reason' => 'Test leave',
            'status' => $status,
        ]);
    }

    public function test_salary_and_attendance_calculation_uses_exact_cents(): void
    {
        $this->salary($this->employee);
        Attendance::create(['employee_id' => $this->employee->id, 'work_date' => '2026-02-02', 'status' => 'present']);
        Attendance::create(['employee_id' => $this->employee->id, 'work_date' => '2026-02-09', 'status' => 'late']);
        $this->leave($this->employee, 'annual', '2026-02-16', '2026-02-16');
        $this->leave($this->employee, 'unpaid', '2026-02-23', '2026-02-23');

        $period = app(PayrollService::class)->generate('2026-02');
        $slip = $period->payslips()->firstOrFail();
        $this->assertSame([4, 2, 1, 1, 1], [$slip->scheduled_days, $slip->worked_days, $slip->paid_leave_days, $slip->unpaid_leave_days, $slip->unpaid_days]);
        $this->assertSame('75.01', $slip->base_pay);
        $this->assertSame('25.00', $slip->attendance_deduction);
        $this->assertSame('85.01', $slip->gross_pay);
        $this->assertSame('83.01', $slip->net_pay);
        $this->assertSame(5001, PayrollMoney::prorate(10001, 1, 2));
        $this->assertSame('50.01', PayrollMoney::decimal(5001));
    }

    public function test_cross_month_leave_is_clipped_and_pending_leave_is_ignored(): void
    {
        $this->salary($this->employee, '100.00', '0.00', '0.00');
        $this->leave($this->employee, 'annual', '2026-01-30', '2026-02-02');
        $this->leave($this->employee, 'unpaid', '2026-02-09', '2026-03-02');
        $this->leave($this->employee, 'annual', '2026-02-16', '2026-02-16', 'pending');

        $slip = app(PayrollService::class)->generate('2026-02')->payslips()->firstOrFail();
        $this->assertSame(1, $slip->paid_leave_days);
        $this->assertSame(3, $slip->unpaid_leave_days);
        $this->assertSame('25.00', $slip->base_pay);
    }

    public function test_unsupported_approved_leave_rolls_back_generation(): void
    {
        $this->salary($this->employee);
        $this->leave($this->employee, 'sick', '2026-02-02', '2026-02-02');

        try {
            app(PayrollService::class)->generate('2026-02');
            $this->fail('Expected unsupported leave to stop generation.');
        } catch (DomainException $exception) {
            $this->assertStringContainsString('chính sách', $exception->getMessage());
        }
        $this->assertDatabaseCount('payroll_periods', 0);
        $this->assertDatabaseCount('payslips', 0);
    }

    public function test_fixed_deduction_is_capped_at_gross_without_negative_pay(): void
    {
        $this->salary($this->employee, '100.00', '1.00', '200.00');

        $slip = app(PayrollService::class)->generate('2026-02')->payslips()->firstOrFail();
        $this->assertSame('1.00', $slip->gross_pay);
        $this->assertSame('1.00', $slip->fixed_deduction);
        $this->assertSame('0.00', $slip->net_pay);
    }

    public function test_draft_regeneration_is_unique_and_finalized_snapshot_is_immutable(): void
    {
        $salary = $this->salary($this->employee, '100.00', '0.00', '0.00');
        Attendance::create(['employee_id' => $this->employee->id, 'work_date' => '2026-02-02', 'status' => 'present']);
        $service = app(PayrollService::class);
        $period = $service->generate('2026-02');
        $salary->update(['base_salary' => '200.00']);
        $service->generate('2026-02');
        $this->assertDatabaseCount('payroll_periods', 1);
        $this->assertDatabaseCount('payslips', 1);
        $slip = $period->payslips()->firstOrFail();
        $this->assertSame('50.00', $slip->base_pay);

        $service->finalize($period, $this->hr);
        $this->expectException(DomainException::class);
        $service->generate('2026-02');
    }

    public function test_finalized_payslip_cannot_be_updated(): void
    {
        $this->salary($this->employee);
        $service = app(PayrollService::class);
        $period = $service->generate('2026-02');
        $slip = $period->payslips()->firstOrFail();
        $slip->load('period');
        $service->finalize($period, $this->hr);

        $this->expectException(DomainException::class);
        $slip->update(['net_pay' => '999.00']);
    }

    public function test_payroll_authorization(): void
    {
        $this->actingAs($this->user)->put(route('hr.payroll.salaries.update', $this->employee), [
            'base_salary' => '100.00', 'allowance' => '0.00', 'fixed_deduction' => '0.00',
        ])->assertForbidden();
        $this->actingAs($this->user)->post(route('hr.payroll.generate'), ['month' => '2026-02'])->assertForbidden();
    }

    public function test_salary_and_month_validation(): void
    {
        $this->actingAs($this->hr)->put(route('hr.payroll.salaries.update', $this->employee), [
            'base_salary' => '1.234', 'allowance' => '-1', 'fixed_deduction' => '0.00',
        ])->assertSessionHasErrors(['base_salary', 'allowance']);
        $this->actingAs($this->hr)->post(route('hr.payroll.generate'), ['month' => '2026-13'])->assertSessionHasErrors(['month']);

        $this->actingAs($this->hr)->put(route('hr.payroll.salaries.update', $this->employee), [
            'base_salary' => '100.01', 'allowance' => '10.00', 'fixed_deduction' => '2.00',
        ])->assertRedirect(route('hr.payroll.salaries.index'));
        $this->assertSame('100.01', $this->employee->salary->base_salary);
    }

    public function test_employee_only_sees_own_finalized_payslip(): void
    {
        $otherUser = $this->createUser('employee', 'other@example.com');
        $otherEmployee = $this->createEmployee($otherUser, 'EMP-2');
        $this->salary($this->employee);
        $this->salary($otherEmployee);
        $service = app(PayrollService::class);
        $period = $service->generate('2026-02');
        $own = $period->payslips()->where('employee_id', $this->employee->id)->firstOrFail();
        $other = $period->payslips()->where('employee_id', $otherEmployee->id)->firstOrFail();

        $this->actingAs($this->user)->get(route('me.payslips.show', $own))->assertNotFound();
        $service->finalize($period, $this->hr);
        $this->actingAs($this->user)->get(route('me.payslips.show', $own))->assertOk()->assertSee('EMP-1');
        $this->actingAs($this->user)->get(route('me.payslips.show', $other))->assertNotFound();
        $this->actingAs($this->user)->get(route('me.payslips.index'))->assertOk()->assertSee('02/2026')->assertDontSee('EMP-2');
    }
}
