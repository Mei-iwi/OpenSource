<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $employee = $this->leaveRequest->employee;
        $employeeName = $employee?->user?->name ?? 'Nhân viên';
        $employeeCode = $employee?->employee_code ?? '';
        $leaveType = match ($this->leaveRequest->leave_type) {
            'annual' => 'nghỉ phép năm',
            'sick' => 'nghỉ ốm',
            'unpaid' => 'nghỉ không lương',
            default => 'nghỉ khác',
        };
        $startDate = $this->leaveRequest->start_date->format('d/m/Y');
        $endDate = $this->leaveRequest->end_date->format('d/m/Y');
        $days = (int) $this->leaveRequest->start_date->diffInDays($this->leaveRequest->end_date) + 1;

        return [
            'type' => 'leave_submitted',
            'title' => 'Đơn xin nghỉ mới',
            'message' => "{$employeeName} ({$employeeCode}) đã gửi đơn xin {$leaveType} ({$days} ngày: từ {$startDate} đến {$endDate}).",
            'url' => route('hr.leave-requests.show', $this->leaveRequest),
            'icon' => 'calendar-clock',
            'leave_request_id' => $this->leaveRequest->id,
            'employee_id' => $employee?->id,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
