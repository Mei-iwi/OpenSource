<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\LeaveRequest;
use DomainException;
use Illuminate\Support\Carbon;

class PayrollCalculator
{
    public function calculate(Employee $employee, EmployeeSalary $salary, Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        if ($employee->hire_date && $employee->hire_date->greaterThan($start)) {
            $start = $employee->hire_date->copy();
        }

        $scheduled = [];
        for ($day = $start->copy(); $day->lessThanOrEqualTo($end); $day->addDay()) {
            if (in_array($day->dayOfWeekIso, config('attendance.work_days', [1, 2, 3, 4, 5]), true)) {
                $scheduled[$day->toDateString()] = true;
            }
        }

        $periodStart = $month->copy()->startOfMonth()->toDateString();
        $periodEnd = $end->toDateString();
        $worked = [];
        foreach (Attendance::where('employee_id', $employee->id)->whereBetween('work_date', [$periodStart, $periodEnd])->get() as $attendance) {
            $date = $attendance->work_date->toDateString();
            if (isset($scheduled[$date]) && in_array($attendance->status, ['present', 'late'], true)) {
                $worked[$date] = true;
            }
        }

        $paidLeave = [];
        $unpaidLeave = [];
        $leaves = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $periodEnd)
            ->where('end_date', '>=', $periodStart)
            ->get();

        foreach ($leaves as $leave) {
            $leaveStart = $leave->start_date->greaterThan($start) ? $leave->start_date->copy() : $start->copy();
            $leaveEnd = $leave->end_date->lessThan($end) ? $leave->end_date->copy() : $end->copy();
            for ($day = $leaveStart; $day->lessThanOrEqualTo($leaveEnd); $day->addDay()) {
                $date = $day->toDateString();
                if (! isset($scheduled[$date])) {
                    continue;
                }
                if (in_array($leave->leave_type, ['sick', 'other'], true)) {
                    throw new DomainException("Chưa có chính sách trả lương cho đơn nghỉ #{$leave->id} của {$employee->employee_code}.");
                }
                if ($leave->leave_type === 'annual') {
                    $paidLeave[$date] = true;
                } elseif ($leave->leave_type === 'unpaid') {
                    $unpaidLeave[$date] = true;
                }
            }
        }

        if (array_intersect_key($paidLeave, $unpaidLeave)) {
            throw new DomainException("Đơn nghỉ có xung đột ngày của {$employee->employee_code}.");
        }

        $paidLeave = array_diff_key($paidLeave, $worked);
        $unpaidLeave = array_diff_key($unpaidLeave, $worked);
        $paidDays = count($worked) + count($paidLeave);
        $scheduledDays = count($scheduled);
        $baseSalary = PayrollMoney::cents($salary->base_salary);
        $allowance = PayrollMoney::cents($salary->allowance);
        $fixedDeduction = PayrollMoney::cents($salary->fixed_deduction);
        $basePay = PayrollMoney::prorate($baseSalary, $paidDays, $scheduledDays);
        $gross = $basePay + $allowance;
        $appliedDeduction = min($fixedDeduction, $gross);

        return [
            'employee_name' => $employee->user->name,
            'employee_code' => $employee->employee_code,
            'scheduled_days' => $scheduledDays,
            'worked_days' => count($worked),
            'paid_leave_days' => count($paidLeave),
            'unpaid_leave_days' => count($unpaidLeave),
            'unpaid_days' => $scheduledDays - $paidDays,
            'base_salary' => PayrollMoney::decimal($baseSalary),
            'base_pay' => PayrollMoney::decimal($basePay),
            'attendance_deduction' => PayrollMoney::decimal($baseSalary - $basePay),
            'allowance' => PayrollMoney::decimal($allowance),
            'fixed_deduction' => PayrollMoney::decimal($appliedDeduction),
            'gross_pay' => PayrollMoney::decimal($gross),
            'net_pay' => PayrollMoney::decimal($gross - $appliedDeduction),
        ];
    }
}
