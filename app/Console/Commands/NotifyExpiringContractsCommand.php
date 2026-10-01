<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\ContractExpiringNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class NotifyExpiringContractsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:expiring-contracts {--days=30 : Số ngày trước khi hết hạn để gửi thông báo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra và gửi thông báo hợp đồng sắp hết hạn tới HR và Admin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $today = Carbon::today();
        $targetDate = Carbon::today()->addDays($days);

        $expiringEmployees = Employee::with('user')
            ->where('employment_status', 'active')
            ->whereNotNull('contract_end_date')
            ->whereBetween('contract_end_date', [$today->toDateString(), $targetDate->toDateString()])
            ->get();

        if ($expiringEmployees->isEmpty()) {
            $this->info("Không có hợp đồng lao động nào sắp hết hạn trong {$days} ngày tới.");

            return self::SUCCESS;
        }

        // Người nhận: toàn bộ tài khoản active có role admin hoặc hr
        $recipients = User::whereIn('role', ['admin', 'hr'])
            ->where('account_status', 'active')
            ->get();

        if ($recipients->isEmpty()) {
            $this->warn('Không tìm thấy tài khoản Quản trị hoặc HR đang hoạt động để nhận thông báo.');

            return self::SUCCESS;
        }

        $sentCount = 0;

        foreach ($expiringEmployees as $employee) {
            $daysRemaining = (int) $today->diffInDays(Carbon::parse($employee->contract_end_date), false);

            foreach ($recipients as $recipient) {
                // Kiểm tra xem đã gửi thông báo cho nhân viên này trong vòng 7 ngày qua chưa để tránh spam
                $alreadyNotified = $recipient->notifications()
                    ->where('type', ContractExpiringNotification::class)
                    ->where('data->employee_id', $employee->id)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->exists();

                if (! $alreadyNotified) {
                    $recipient->notify(new ContractExpiringNotification($employee, $daysRemaining));
                    $sentCount++;
                }
            }
        }

        $this->info("Đã kiểm tra {$expiringEmployees->count()} nhân viên có hợp đồng sắp hết hạn. Đã gửi {$sentCount} thông báo.");

        return self::SUCCESS;
    }
}
