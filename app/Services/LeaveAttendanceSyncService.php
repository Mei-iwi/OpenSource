<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

class LeaveAttendanceSyncService
{
    /**
     * Đồng bộ các ngày nghỉ trong khoảng [start_date, end_date] của một LeaveRequest đã được duyệt vào bảng attendances.
     *
     * @param  LeaveRequest  $leaveRequest
     * @return array{created: int, updated: int, skipped: int, dates: array}
     */
    public function sync(LeaveRequest $leaveRequest): array
    {
        $startDate = Carbon::parse($leaveRequest->start_date)->startOfDay();
        $endDate = Carbon::parse($leaveRequest->end_date)->startOfDay();
        $period = CarbonPeriod::create($startDate, $endDate);

        $summary = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'dates' => [],
        ];

        $leaveTypeLabels = [
            'annual' => 'Nghỉ phép năm',
            'sick' => 'Nghỉ ốm',
            'unpaid' => 'Nghỉ không lương',
            'other' => 'Nghỉ khác',
        ];

        $typeLabel = $leaveTypeLabels[$leaveRequest->leave_type] ?? $leaveRequest->leave_type;
        $defaultNote = "Nghỉ phép ({$typeLabel}) theo đơn #{$leaveRequest->id}";

        foreach ($period as $date) {
            $dateString = $date->toDateString();

            $existing = Attendance::where('employee_id', $leaveRequest->employee_id)
                ->where('work_date', $dateString)
                ->first();

            if (! $existing) {
                // Trường hợp 1: Chưa có bản ghi chấm công ngày này -> Tạo mới trạng thái leave
                Attendance::create([
                    'employee_id' => $leaveRequest->employee_id,
                    'work_date' => $dateString,
                    'status' => 'leave',
                    'note' => $defaultNote,
                ]);

                $summary['created']++;
                $summary['dates'][$dateString] = 'created';
                continue;
            }

            // Trường hợp 2: Đã có bản ghi chấm công
            // Nếu đã là leave: giữ nguyên (đảm bảo tính idempotent)
            if ($existing->status === 'leave') {
                $summary['skipped']++;
                $summary['dates'][$dateString] = 'already_leave';
                continue;
            }

            // Nếu đã có dữ liệu làm việc thực tế (check_in, check_out hoặc ảnh minh chứng) -> KHÔNG ghi đè
            $hasActualWork = ! empty($existing->check_in)
                || ! empty($existing->check_out)
                || ! empty($existing->check_in_photo_path)
                || ! empty($existing->check_out_photo_path);

            if ($hasActualWork) {
                $summary['skipped']++;
                $summary['dates'][$dateString] = 'preserved_work';
                continue;
            }

            // Nếu chỉ là bản ghi vắng mặt (absent) không có giờ vào/ra/ảnh -> Chuyển sang leave hợp lệ
            if ($existing->status === 'absent') {
                $existing->update([
                    'status' => 'leave',
                    'note' => $existing->note
                        ? "{$existing->note} | Chuyển sang {$defaultNote}"
                        : $defaultNote,
                ]);

                $summary['updated']++;
                $summary['dates'][$dateString] = 'converted_from_absent';
                continue;
            }

            // Các trường hợp khác (ví dụ trạng thái khác không có giờ làm): giữ an toàn
            $summary['skipped']++;
            $summary['dates'][$dateString] = 'skipped_other';
        }

        return $summary;
    }
}
