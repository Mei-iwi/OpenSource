<?php

namespace App\Services;

use App\Models\EmployeeSalary;
use App\Models\PayrollPeriod;
use App\Models\User;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function __construct(private PayrollCalculator $calculator) {}

    public function generate(string $month): PayrollPeriod
    {
        $periodMonth = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        $periodEnd = $periodMonth->copy()->endOfMonth();

        return DB::transaction(function () use ($periodMonth, $periodEnd) {
            $period = PayrollPeriod::firstOrCreate(['period_month' => $periodMonth->toDateString()]);
            $period = PayrollPeriod::whereKey($period->id)->lockForUpdate()->firstOrFail();
            if ($period->status !== 'draft') {
                throw new DomainException('Kỳ lương đã chốt không thể tạo lại.');
            }

            $snapshots = [];
            $salaries = EmployeeSalary::with('employee.user')
                ->whereHas('employee', fn ($query) => $query->where('employment_status', 'active')->whereDate('hire_date', '<=', $periodEnd->toDateString()))
                ->get();
            foreach ($salaries as $salary) {
                $snapshots[$salary->employee_id] = $this->calculator->calculate($salary->employee, $salary, $periodMonth);
            }

            $period->payslips()->delete();
            foreach ($snapshots as $employeeId => $snapshot) {
                $period->payslips()->create(['employee_id' => $employeeId] + $snapshot);
            }

            return $period->fresh();
        });
    }

    public function finalize(PayrollPeriod $period, User $actor): PayrollPeriod
    {
        return DB::transaction(function () use ($period, $actor) {
            $locked = PayrollPeriod::whereKey($period->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'draft') {
                throw new DomainException('Kỳ lương đã chốt không thể chốt lại.');
            }
            if (! $locked->payslips()->exists()) {
                throw new DomainException('Kỳ lương chưa có phiếu lương.');
            }

            $locked->update(['status' => 'finalized', 'finalized_by' => $actor->id, 'finalized_at' => now()]);

            return $locked;
        });
    }
}
