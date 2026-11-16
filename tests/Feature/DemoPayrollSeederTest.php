<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\PayrollPeriod;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoPayrollSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoPayrollSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_payroll_is_shared_repeatable_and_preserves_edited_salary(): void
    {
        $this->app['env'] = 'local';
        Carbon::setTestNow(Carbon::parse('2026-11-16 10:00:00', 'Asia/Ho_Chi_Minh'));
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(20, EmployeeSalary::count());
        $september = PayrollPeriod::where('period_month', '2026-09-01')->firstOrFail();
        $october = PayrollPeriod::where('period_month', '2026-10-01')->firstOrFail();
        $this->assertSame('finalized', $september->status);
        $this->assertSame('draft', $october->status);
        $this->assertSame(19, $september->payslips()->count());
        $this->assertSame(19, $october->payslips()->count());
        $this->assertGreaterThan(0, $september->payslips()->sum('paid_leave_days'));
        $this->assertGreaterThan(0, $october->payslips()->sum('unpaid_leave_days'));

        $employee = Employee::where('employee_code', 'HR-0001')->firstOrFail();
        $employee->salary->update(['base_salary' => '22500000.00']);
        $originalSlip = $september->payslips()->where('employee_id', $employee->id)->firstOrFail();

        $this->seed(DemoPayrollSeeder::class);

        $this->assertSame('22500000.00', $employee->salary->fresh()->base_salary);
        $this->assertSame(20, EmployeeSalary::count());
        $this->assertSame(19, $september->payslips()->count());
        $this->assertSame(19, $october->payslips()->count());
        $this->assertSame($originalSlip->id, $september->payslips()->where('employee_id', $employee->id)->firstOrFail()->id);
        $this->assertSame(25, \App\Models\LeaveRequest::whereBetween('start_date', ['2026-09-01', '2026-10-31'])->count());

        $this->app['env'] = 'testing';
        $hr = User::where('email', 'anh.tn@hr.hr-management.com')->firstOrFail();
        $this->actingAs($hr)->get(route('hr.employees.show', $employee))->assertOk()->assertSee('Lương nhân viên');
        $this->actingAs($hr)->put(route('hr.payroll.salaries.update', $employee), [
            'base_salary' => '23000000.00',
            'allowance' => '2000000.00',
            'fixed_deduction' => '300000.00',
            'return_to' => 'employee',
        ])->assertRedirect(route('hr.employees.show', $employee));
        $this->assertSame('23000000.00', $employee->salary->fresh()->base_salary);

        $adminEmployee = Employee::where('employee_code', 'ADM-0001')->firstOrFail();
        $this->actingAs($hr)->put(route('hr.payroll.salaries.update', $adminEmployee), [
            'base_salary' => '1.00', 'allowance' => '0.00', 'fixed_deduction' => '0.00',
        ])->assertForbidden();
    }
}
