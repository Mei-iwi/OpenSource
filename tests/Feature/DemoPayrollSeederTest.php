<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\PayrollPeriod;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoPayrollSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoPayrollSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_payroll_is_shared_repeatable_and_preserves_edited_salary(): void
    {
        $this->app['env'] = 'local';
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(20, EmployeeSalary::count());
        $september = PayrollPeriod::where('period_month', '2026-09-01')->firstOrFail();
        $october = PayrollPeriod::where('period_month', '2026-10-01')->firstOrFail();
        $this->assertSame('finalized', $september->status);
        $this->assertSame('draft', $october->status);
        $this->assertSame(19, $september->payslips()->count());
        $this->assertSame(19, $october->payslips()->count());

        $employee = Employee::where('employee_code', 'HR-0001')->firstOrFail();
        $employee->salary->update(['base_salary' => '22500000.00']);
        $originalSlip = $september->payslips()->where('employee_id', $employee->id)->firstOrFail();

        $this->seed(DemoPayrollSeeder::class);

        $this->assertSame('22500000.00', $employee->salary->fresh()->base_salary);
        $this->assertSame(20, EmployeeSalary::count());
        $this->assertSame(19, $september->payslips()->count());
        $this->assertSame(19, $october->payslips()->count());
        $this->assertSame($originalSlip->id, $september->payslips()->where('employee_id', $employee->id)->firstOrFail()->id);
    }
}
