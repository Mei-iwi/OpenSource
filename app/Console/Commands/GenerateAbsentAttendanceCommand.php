<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\AttendanceScheduleService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateAbsentAttendanceCommand extends Command
{
    protected $signature = 'attendance:mark-absent {--date= : Ngày làm việc cần xử lý (YYYY-MM-DD); mặc định là hôm qua theo múi giờ chấm công}';

    protected $description = 'Tạo chấm công vắng mặt hoặc nghỉ đã duyệt cho nhân viên chưa có bản ghi của ngày làm việc đã kết thúc';

    public function handle(AttendanceScheduleService $schedule): int
    {
        $timezone = $schedule->getTimezone();
        $option = $this->option('date');
        if ($option !== null && (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $option)
            || ! checkdate((int) substr($option, 5, 2), (int) substr($option, 8, 2), (int) substr($option, 0, 4)))) {
            $this->error('Ngày phải có định dạng YYYY-MM-DD hợp lệ.');

            return self::FAILURE;
        }

        $today = Carbon::today($timezone);
        $date = $option ? Carbon::createFromFormat('!Y-m-d', $option, $timezone) : $today->copy()->subDay();
        if ($date->greaterThanOrEqualTo($today)) {
            $this->error('Chỉ xử lý ngày đã kết thúc theo múi giờ chấm công.');

            return self::FAILURE;
        }

        $workDays = config('attendance.work_days', [1, 2, 3, 4, 5]);
        if (! in_array($date->dayOfWeekIso, $workDays, true)) {
            Log::info('Automatic attendance skipped non-working day', ['date' => $date->toDateString(), 'timezone' => $timezone]);
            $this->info('Ngày đã chọn không thuộc lịch làm việc.');

            return self::SUCCESS;
        }

        $dateString = $date->toDateString();
        $nextDate = $date->copy()->addDay()->toDateString();
        $createdAbsent = 0;
        $createdLeave = 0;
        $alreadyRecorded = 0;

        Employee::query()
            ->select('id')
            ->where('employment_status', 'active')
            ->where('hire_date', '<', $nextDate)
            ->chunkById(500, function ($employees) use ($dateString, $nextDate, &$createdAbsent, &$createdLeave, &$alreadyRecorded): void {
                DB::transaction(function () use ($employees, $dateString, $nextDate, &$createdAbsent, &$createdLeave, &$alreadyRecorded): void {
                    $employeeIds = $employees->pluck('id')->all();
                    $existing = Attendance::query()
                        ->whereIn('employee_id', $employeeIds)
                        ->where('work_date', '>=', $dateString)
                        ->where('work_date', '<', $nextDate)
                        ->pluck('employee_id')->flip();
                    $approvedLeave = LeaveRequest::query()
                        ->whereIn('employee_id', $employeeIds)
                        ->where('status', 'approved')
                        ->where('start_date', '<', $nextDate)
                        ->where('end_date', '>=', $dateString)
                        ->pluck('employee_id')->flip();

                    $absentRows = [];
                    $leaveRows = [];
                    $timestamp = now();
                    foreach ($employeeIds as $employeeId) {
                        if (isset($existing[$employeeId])) {
                            $alreadyRecorded++;

                            continue;
                        }

                        $row = [
                            'employee_id' => $employeeId,
                            'work_date' => $dateString,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                        if (isset($approvedLeave[$employeeId])) {
                            $leaveRows[] = $row + ['status' => 'leave', 'note' => 'Nghỉ theo đơn đã duyệt (tự động)'];
                        } else {
                            $absentRows[] = $row + ['status' => 'absent', 'note' => 'Vắng mặt tự động sau ngày làm việc'];
                        }
                    }

                    if ($leaveRows) {
                        $createdLeave += DB::table('attendances')->insertOrIgnore($leaveRows);
                    }
                    if ($absentRows) {
                        $createdAbsent += DB::table('attendances')->insertOrIgnore($absentRows);
                    }
                });
            });

        Log::info('Automatic attendance generation completed', [
            'date' => $dateString,
            'timezone' => $timezone,
            'created_absent' => $createdAbsent,
            'created_leave' => $createdLeave,
            'already_recorded' => $alreadyRecorded,
        ]);
        $this->info("Ngày {$dateString}: {$createdAbsent} vắng mặt, {$createdLeave} nghỉ đã duyệt, {$alreadyRecorded} bản ghi sẵn có.");

        return self::SUCCESS;
    }
}
