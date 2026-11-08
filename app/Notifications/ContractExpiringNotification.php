<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(public Employee $employee, public int $daysRemaining) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employeeName = $this->employee->user?->name ?? 'Nhân viên';
        $employeeCode = $this->employee->employee_code;
        $endDate = $this->employee->contract_end_date?->format('d/m/Y') ?? '';
        $daysText = $this->daysRemaining <= 0 ? 'hôm nay' : "sau {$this->daysRemaining} ngày";

        return (new MailMessage)
            ->subject('Hợp đồng lao động sắp hết hạn')
            ->greeting("Xin chào {$notifiable->name},")
            ->line("Hợp đồng của {$employeeName} ({$employeeCode}) sẽ hết hạn vào {$endDate} ({$daysText}).")
            ->action('Xem hồ sơ nhân viên', route('hr.employees.show', $this->employee))
            ->salutation('Trân trọng, '.config('app.name'));
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
