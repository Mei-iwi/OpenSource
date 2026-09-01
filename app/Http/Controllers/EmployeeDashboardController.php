<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EmployeeDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $employee = $request->user()->employee?->load(['user', 'department']);
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $rows = $employee ? Attendance::where('employee_id', $employee->id)->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])->get(['status', 'check_in', 'check_out']) : collect();
        $summary = (object) [
            'total' => $rows->count(),
            'present' => $rows->where('status', 'present')->count(),
            'late' => $rows->where('status', 'late')->count(),
            'absent' => $rows->where('status', 'absent')->count(),
            'leave_total' => $rows->where('status', 'leave')->count(),
            'worked_minutes' => $rows->sum(function (Attendance $row): int {
                return $row->check_in && $row->check_out
                    ? Carbon::parse($row->check_in)->diffInMinutes(Carbon::parse($row->check_out))
                    : 0;
            }),
        ];
        $recentAttendance = $employee ? Attendance::where('employee_id', $employee->id)->latest('work_date')->latest('id')->limit(7)->get() : collect();
        $total = (int) ($summary?->total ?? 0);
        $present = (int) ($summary?->present ?? 0);
        $late = (int) ($summary?->late ?? 0);

        return view('dashboard.employee', [
            'employee' => $employee, 'summary' => $summary, 'recentAttendance' => $recentAttendance,
            'attendanceRate' => $total ? round(($present + $late) / $total * 100, 1) : 0,
            'punctualityRate' => ($present + $late) ? round($present / ($present + $late) * 100, 1) : 0,
            'workedHours' => round(((int) ($summary?->worked_minutes ?? 0)) / 60, 1),
            'currentMonth' => Carbon::now()->format('m/Y'),
        ]);
    }
}
