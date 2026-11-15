<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Database\Seeder;

class DemoPayrollSeeder extends Seeder
{
    /** Synthetic monthly amounts in VND for the accounts in DatabaseSeeder. */
    private const SALARIES = [
        'ADM-0001' => ['30000000.00', '3000000.00', '500000.00'],
        'HR-0001' => ['22000000.00', '2000000.00', '300000.00'],
        'HR-0002' => ['14000000.00', '1000000.00', '150000.00'],
        'IT-0001' => ['20000000.00', '2000000.00', '250000.00'],
        'IT-0002' => ['15000000.00', '1000000.00', '150000.00'],
        'IT-0003' => ['13000000.00', '800000.00', '100000.00'],
        'IT-0004' => ['18000000.00', '1500000.00', '200000.00'],
        'KD-0001' => ['24000000.00', '2500000.00', '300000.00'],
        'KD-0002' => ['15000000.00', '1000000.00', '150000.00'],
        'KD-0003' => ['14000000.00', '1000000.00', '150000.00'],
        'KD-0004' => ['12000000.00', '800000.00', '100000.00'],
        'KD-0005' => ['12000000.00', '800000.00', '100000.00'],
        'KT-0001' => ['23000000.00', '2000000.00', '300000.00'],
        'KT-0002' => ['16000000.00', '1200000.00', '180000.00'],
        'KT-0003' => ['14000000.00', '1000000.00', '150000.00'],
        'CS-0001' => ['17000000.00', '1200000.00', '180000.00'],
        'CS-0002' => ['12000000.00', '800000.00', '100000.00'],
        'CS-0003' => ['11000000.00', '700000.00', '100000.00'],
        'HC-0001' => ['16000000.00', '1000000.00', '150000.00'],
        'HC-0002' => ['11000000.00', '700000.00', '100000.00'],
    ];

    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $demoEmployeeIds = [];
        foreach (self::SALARIES as $code => [$base, $allowance, $deduction]) {
            $employee = Employee::query()
                ->where('employee_code', $code)
                ->whereHas('user', fn ($query) => $query->where('email', 'like', '%@%.hr-management.com'))
                ->first();

            if (! $employee) {
                continue;
            }

            $demoEmployeeIds[] = $employee->id;
            EmployeeSalary::firstOrCreate(['employee_id' => $employee->id], [
                'base_salary' => $base,
                'allowance' => $allowance,
                'fixed_deduction' => $deduction,
            ]);
        }

        // Generate sample payslips only when the database contains demo salary
        // configurations. Never replace a period with existing payslips.
        if (count($demoEmployeeIds) !== count(self::SALARIES)
            || EmployeeSalary::whereNotIn('employee_id', $demoEmployeeIds)->exists()) {
            return;
        }

        $this->seedApprovedLeaves($demoEmployeeIds);

        $payroll = app(PayrollService::class);
        foreach (['2026-09' => true, '2026-10' => false] as $month => $finalize) {
            $period = PayrollPeriod::where('period_month', $month.'-01')->first();
            if ($period && ($period->status !== 'draft' || $period->payslips()->exists())) {
                continue;
            }

            $period = $payroll->generate($month);
            if ($finalize && $period->payslips()->exists()) {
                $admin = User::where('email', 'quan.nm@admin.hr-management.com')->first();
                if ($admin) {
                    $payroll->finalize($period, $admin);
                }
            }
        }
    }

    private function seedApprovedLeaves(array $demoEmployeeIds): void
    {
        $reviewer = User::where('email', 'quan.nm@admin.hr-management.com')->first();
        if (! $reviewer) {
            return;
        }

        foreach (['2026-09', '2026-10'] as $month) {
            $period = PayrollPeriod::where('period_month', $month.'-01')->first();
            if ($period?->status === 'finalized') {
                continue;
            }

            $lastDay = date('Y-m-t', strtotime($month.'-01'));
            $days = Attendance::query()
                ->whereIn('employee_id', $demoEmployeeIds)
                ->where('status', 'leave')
                ->whereBetween('work_date', [$month.'-01', $lastDay])
                ->get();

            foreach ($days as $attendance) {
                $date = $attendance->work_date->toDateString();
                if (LeaveRequest::where('employee_id', $attendance->employee_id)
                    ->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date)->exists()) {
                    continue;
                }

                LeaveRequest::create([
                    'employee_id' => $attendance->employee_id,
                    'leave_type' => str_contains(mb_strtolower($attendance->note ?? ''), 'cá nhân') ? 'unpaid' : 'annual',
                    'start_date' => $date,
                    'end_date' => $date,
                    'reason' => 'Dữ liệu nghỉ phép demo theo ngày chấm công mẫu.',
                    'status' => 'approved',
                    'reviewed_by' => $reviewer->id,
                    'reviewed_at' => $attendance->work_date->copy()->endOfDay(),
                    'review_note' => 'Dữ liệu demo.',
                ]);
            }
        }
    }
}
