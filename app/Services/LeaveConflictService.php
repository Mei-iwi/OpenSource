<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

class LeaveConflictService
{
    /**
     * Phân tích xung đột nhân sự khi duyệt đơn nghỉ phép.
     *
     * @param  LeaveRequest  $leaveRequest
     * @return array
     */
    public function analyze(LeaveRequest $leaveRequest): array
    {
        $employee = $leaveRequest->employee;
        if (! $employee && $leaveRequest->employee_id) {
            $employee = Employee::find($leaveRequest->employee_id);
        }

        // Trường hợp biên: Nhân viên chưa có phòng ban hoặc không tìm thấy hồ sơ
        if (! $employee || ! $employee->department_id) {
            return [
                'has_department' => false,
                'message' => 'Không thể phân tích do nhân viên chưa được phân bổ phòng ban.',
                'department' => null,
                'total_active_employees' => 0,
                'has_conflict' => false,
                'highest_percentage' => 0.0,
                'highest_level' => 'normal',
                'highest_day' => null,
                'days' => [],
            ];
        }

        $department = $employee->department ?? Department::find($employee->department_id);
        $departmentId = $employee->department_id;
        $departmentName = $department?->name ?? 'Phòng ban';

        // 1. Lấy tổng số nhân viên active cùng phòng
        $totalActiveEmployees = Employee::query()
            ->where('department_id', $departmentId)
            ->where('employment_status', 'active')
            ->count();

        // Trường hợp biên: Phòng không có nhân viên active
        if ($totalActiveEmployees === 0) {
            return [
                'has_department' => true,
                'message' => 'Phòng ban hiện không có nhân viên đang làm việc (active).',
                'department' => [
                    'id' => $departmentId,
                    'name' => $departmentName,
                    'code' => $department?->code,
                ],
                'total_active_employees' => 0,
                'has_conflict' => false,
                'highest_percentage' => 0.0,
                'highest_level' => 'normal',
                'highest_day' => null,
                'days' => [],
            ];
        }

        // 2. Xác định khoảng ngày của đơn nghỉ
        $startDate = Carbon::parse($leaveRequest->start_date)->startOfDay();
        $endDate = Carbon::parse($leaveRequest->end_date)->startOfDay();
        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $period = CarbonPeriod::create($startDate, $endDate);

        // 3. Lấy tất cả đơn nghỉ APPROVED của các nhân viên active trong phòng
        // Loại trừ chính đơn đang xét (id != leaveRequest->id)
        // Điều kiện giao nhau: existing.start_date <= request.end_date AND existing.end_date >= request.start_date
        $approvedLeaves = LeaveRequest::query()
            ->where('status', 'approved')
            ->where('id', '!=', $leaveRequest->id)
            ->whereHas('employee', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId)
                      ->where('employment_status', 'active');
            })
            ->where('start_date', '<=', $endDate->toDateString())
            ->where('end_date', '>=', $startDate->toDateString())
            ->select(['id', 'employee_id', 'start_date', 'end_date'])
            ->get();

        // 4. Xử lý tính toán theo từng ngày trong PHP
        $days = [];
        $highestPercentage = 0.0;
        $highestLevel = 'normal';
        $highestDay = null;

        $levelPriority = [
            'normal' => 1,
            'warning' => 2,
            'danger' => 3,
        ];

        foreach ($period as $date) {
            $dateString = $date->toDateString();

            // Tìm các employee_id duy nhất đang có đơn nghỉ approved vào ngày này (loại trừ người gửi đơn hiện tại)
            $approvedEmployeeIds = $approvedLeaves->filter(function ($leave) use ($dateString) {
                $start = Carbon::parse($leave->start_date)->toDateString();
                $end = Carbon::parse($leave->end_date)->toDateString();

                return $start <= $dateString && $end >= $dateString;
            })->pluck('employee_id')->reject(function ($empId) use ($leaveRequest) {
                return $empId == $leaveRequest->employee_id;
            })->unique()->values();

            $approvedLeaveCount = $approvedEmployeeIds->count();

            // Mô phỏng nếu duyệt đơn này: cộng thêm người gửi đơn
            $projectedLeaveCount = min($totalActiveEmployees, $approvedLeaveCount + 1);

            // Tính tỷ lệ nghỉ phép dự kiến
            $percentage = round(($projectedLeaveCount / $totalActiveEmployees) * 100, 1);

            // Xác định cấp độ theo ngưỡng:
            // < 30% : normal
            // 30% - < 50% : warning
            // >= 50% : danger
            $level = match (true) {
                $percentage >= 50.0 => 'danger',
                $percentage >= 30.0 => 'warning',
                default => 'normal',
            };

            $levelLabel = match ($level) {
                'danger' => 'Nguy cơ',
                'warning' => 'Lưu ý',
                default => 'Bình thường',
            };

            $dayData = [
                'date' => $dateString,
                'formatted_date' => $date->format('d/m/Y'),
                'short_date' => $date->format('d/m'),
                'approved_leave_count' => $approvedLeaveCount,
                'projected_leave_count' => $projectedLeaveCount,
                'total_employees' => $totalActiveEmployees,
                'percentage' => $percentage,
                'percentage_formatted' => ($percentage == (int) $percentage ? (int) $percentage : $percentage).'%',
                'level' => $level,
                'level_label' => $levelLabel,
            ];

            $days[] = $dayData;

            // Cập nhật ngày có rủi ro / tỷ lệ cao nhất
            if (
                $highestDay === null ||
                $percentage > $highestPercentage ||
                ($percentage === $highestPercentage && $levelPriority[$level] > $levelPriority[$highestLevel])
            ) {
                $highestPercentage = $percentage;
                $highestLevel = $level;
                $highestDay = $dayData;
            }
        }

        // Đảm bảo highest_level thể hiện mức độ nghiêm trọng nhất trong các ngày
        foreach ($days as $day) {
            if ($levelPriority[$day['level']] > $levelPriority[$highestLevel]) {
                $highestLevel = $day['level'];
            }
            if ($day['percentage'] > $highestPercentage) {
                $highestPercentage = $day['percentage'];
            }
        }

        $hasConflict = ($highestLevel !== 'normal');

        return [
            'has_department' => true,
            'message' => null,
            'department' => [
                'id' => $departmentId,
                'name' => $departmentName,
                'code' => $department?->code,
            ],
            'total_active_employees' => $totalActiveEmployees,
            'has_conflict' => $hasConflict,
            'highest_percentage' => $highestPercentage,
            'highest_percentage_formatted' => ($highestPercentage == (int) $highestPercentage ? (int) $highestPercentage : $highestPercentage).'%',
            'highest_level' => $highestLevel,
            'highest_day' => $highestDay,
            'days' => $days,
        ];
    }
}
