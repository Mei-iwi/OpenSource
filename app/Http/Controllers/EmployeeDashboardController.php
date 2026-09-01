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
        $summary = $employee ? Attendance::where('employee_id', $employee->id)->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])->selectRaw("COUNT(*) as total, SUM(status = 'present') as present, SUM(status = 'late') as late, SUM(status = 'absent') as absent, SUM(status = 'leave') as leave_total, SUM(CASE WHEN check_in IS NOT NULL AND check_out IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, check_in, check_out) ELSE 0 END) as worked_minutes")->first() : null;
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
