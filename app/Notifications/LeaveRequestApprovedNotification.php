<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestApprovedNotification extends Notification
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
        $startDate = $this->leaveRequest->start_date->format('d/m/Y');
        $endDate = $this->leaveRequest->end_date->format('d/m/Y');
        $reviewer = $this->leaveRequest->reviewer?->name ?? 'Người quản trị';
        $leaveType = match ($this->leaveRequest->leave_type) {
            'annual' => 'nghỉ phép năm',
            'sick' => 'nghỉ ốm',
            'unpaid' => 'nghỉ không lương',
            default => 'nghỉ khác',
        };

        return [
            'type' => 'leave_approved',
            'title' => 'Đơn xin nghỉ đã được duyệt',
            'message' => "Đơn xin {$leaveType} của bạn (từ {$startDate} đến {$endDate}) đã được {$reviewer} phê duyệt.",
            'url' => route('employee.leave-requests.show', $this->leaveRequest),
            'icon' => 'check-circle',
            'leave_request_id' => $this->leaveRequest->id,
            'review_note' => $this->leaveRequest->review_note,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
