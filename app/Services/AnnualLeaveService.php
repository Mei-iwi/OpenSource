<?php

namespace App\Services;

use App\Models\AnnualLeaveBalance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AnnualLeaveService
{
    /**
     * Tính số ngày trong khoảng nghỉ (inclusive).
     */
    public function calculateDays(CarbonInterface|string $startDate, CarbonInterface|string $endDate): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        return (int) $start->diffInDays($end) + 1;
    }

    /**
     * Lấy hoặc khởi tạo bản ghi số dư phép năm cho nhân viên theo chính sách cấu hình.
     */
    public function getOrCreateBalance(Employee $employee, int $year): AnnualLeaveBalance
    {
        $defaultDays = (float) config('leave.default_annual_days', 12);
        $totalDays = $defaultDays;

        // Nếu nhân viên vào làm trong năm xét duyệt và bật chính sách tính theo tháng vào làm
        if (config('leave.prorate_join_year', true) && $employee->hire_date) {
            $hireDate = Carbon::parse($employee->hire_date);
            if ((int) $hireDate->year === $year) {
                $hireMonth = (int) $hireDate->month;
                $monthsRemaining = max(1, 12 - $hireMonth + 1);
                $totalDays = round(($defaultDays / 12) * $monthsRemaining, 1);
            }
        }

        return AnnualLeaveBalance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'year' => $year,
            ],
            [
                'total_days' => $totalDays,
                'carried_over_days' => 0.0,
                'note' => "Khởi tạo tự động cho năm {$year}",
            ]
        );
    }

    /**
     * Lấy thông tin tóm tắt số dư phép năm của nhân viên.
     *
     * @return array{year: int, total_days: float, carried_over_days: float, entitlement: float, used_days: float, pending_days: float, remaining_days: float, available_days: float}
     */
    public function getBalanceSummary(Employee $employee, int $year): array
    {
        $balance = $this->getOrCreateBalance($employee, $year);

        $usedDays = (float) LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', 'annual')
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->get()
            ->sum('days_count');

        $pendingDays = (float) LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', 'annual')
            ->where('status', 'pending')
            ->whereYear('start_date', $year)
            ->get()
            ->sum('days_count');

        $entitlement = (float) ($balance->total_days + $balance->carried_over_days);
        $remaining = max(0.0, (float) ($entitlement - $usedDays));
        $available = max(0.0, (float) ($remaining - $pendingDays));

        return [
            'year' => $year,
            'total_days' => (float) $balance->total_days,
            'carried_over_days' => (float) $balance->carried_over_days,
            'entitlement' => $entitlement,
            'used_days' => $usedDays,
            'pending_days' => $pendingDays,
            'remaining_days' => $remaining,
            'available_days' => $available,
        ];
    }

    /**
     * Kiểm tra và bảo đảm quota khả dụng trước khi tạo đơn xin nghỉ phép năm.
     *
     * @throws ValidationException
     */
    public function validateAndReserveQuota(Employee $employee, CarbonInterface|string $startDate, CarbonInterface|string $endDate): void
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $year = (int) $start->year;

        // Đảm bảo đơn nghỉ phép năm không vắt qua 2 năm khác nhau
        if ((int) $end->year !== $year) {
            throw ValidationException::withMessages([
                'end_date' => 'Đơn xin nghỉ phép năm không được vượt qua ranh giới năm. Vui lòng tách thành 2 đơn riêng cho từng năm.',
            ]);
        }

        $daysRequested = $this->calculateDays($start, $end);

        // Khóa pessimistic trên dòng số dư phép năm
        $balance = AnnualLeaveBalance::where('employee_id', $employee->id)
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        if (! $balance) {
            $balance = $this->getOrCreateBalance($employee, $year);
            // Re-lock
            $balance = AnnualLeaveBalance::whereKey($balance->id)->lockForUpdate()->first();
        }

        $summary = $this->getBalanceSummary($employee, $year);

        if (! config('leave.allow_negative_balance', false) && $daysRequested > $summary['available_days']) {
            throw ValidationException::withMessages([
                'start_date' => "Số ngày nghỉ phép năm yêu cầu ({$daysRequested} ngày) vượt quá số ngày khả dụng còn lại ({$summary['available_days']} ngày) trong năm {$year}.",
            ]);
        }
    }

    /**
     * Kiểm tra lại hạn mức khi HR phê duyệt đơn nghỉ phép năm.
     *
     * @throws DomainException
     */
    public function validateAndApproveQuota(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->leave_type !== 'annual') {
            return;
        }

        $employee = $leaveRequest->employee;
        $year = (int) $leaveRequest->start_date->year;

        $balance = AnnualLeaveBalance::where('employee_id', $employee->id)
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        if (! $balance) {
            $balance = $this->getOrCreateBalance($employee, $year);
            $balance = AnnualLeaveBalance::whereKey($balance->id)->lockForUpdate()->first();
        }

        $entitlement = (float) ($balance->total_days + $balance->carried_over_days);

        $currentUsed = (float) LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', 'annual')
            ->where('status', 'approved')
            ->where('id', '!=', $leaveRequest->id)
            ->whereYear('start_date', $year)
            ->get()
            ->sum('days_count');

        $needed = $leaveRequest->days_count;

        if (! config('leave.allow_negative_balance', false) && ($currentUsed + $needed) > $entitlement) {
            $availableRemaining = max(0.0, $entitlement - $currentUsed);
            throw new DomainException("Không thể duyệt: Nhân viên không đủ số ngày phép năm còn lại (Cần: {$needed} ngày, Quỹ còn: {$availableRemaining} ngày).");
        }
    }
}
