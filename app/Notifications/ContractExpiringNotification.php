<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(public Employee $employee, public int $daysRemaining)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $employeeName = $this->employee->user?->name ?? 'Nhân viên';
        $employeeCode = $this->employee->employee_code;
        $endDate = $this->employee->contract_end_date?->format('d/m/Y') ?? '';
        $daysText = $this->daysRemaining <= 0 ? 'hôm nay' : "sau {$this->daysRemaining} ngày";

        return [
            'type' => 'contract_expiring',
            'title' => 'Hợp đồng lao động sắp hết hạn',
            'message' => "Hợp đồng của nhân viên {$employeeName} ({$employeeCode}) sẽ hết hạn vào ngày {$endDate} ({$daysText}).",
            'url' => route('hr.employees.show', $this->employee),
            'icon' => 'document-alert',
            'employee_id' => $this->employee->id,
            'contract_end_date' => $this->employee->contract_end_date?->format('Y-m-d'),
            'days_remaining' => $this->daysRemaining,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
