<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HrDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $month = max(1, min(12, (int) ($request->integer('month') ?: now()->month)));
        $year = max(2000, min(2100, (int) ($request->integer('year') ?: now()->year)));
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $attendanceByStatus = Attendance::whereBetween('work_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $employeesByDepartment = Employee::join('departments', 'departments.id', '=', 'employees.department_id')
            ->selectRaw('departments.name, COUNT(employees.id) as total')->groupBy('departments.id', 'departments.name')->orderBy('departments.name')->get();

        $trendStart = $periodStart->copy()->subMonths(5)->startOfMonth();
        $trendRows = Attendance::whereBetween('work_date', [$trendStart->toDateString(), $periodEnd->toDateString()])
            ->selectRaw('YEAR(work_date) as report_year, MONTH(work_date) as report_month, status, COUNT(*) as total')
            ->groupByRaw('YEAR(work_date), MONTH(work_date), status')->get();
        $trend = collect(range(0, 5))->map(function (int $offset) use ($trendStart, $trendRows): array {
            $date = $trendStart->copy()->addMonths($offset);
            $rows = $trendRows->where('report_year', $date->year)->where('report_month', $date->month);
            return ['label' => $date->format('m/Y'), 'present' => (int) $rows->where('status', 'present')->sum('total'), 'late' => (int) $rows->where('status', 'late')->sum('total'), 'absent' => (int) $rows->where('status', 'absent')->sum('total')];
        })->values();
        $recentAttendance = Attendance::with('employee.user', 'employee.department')->latest('work_date')->latest('id')->limit(6)->get();

        return view('dashboard.hr', [
            'totalEmployees' => Employee::count(),
            'activeEmployees' => Employee::where('employment_status', 'active')->count(),
            'inactiveEmployees' => Employee::where('employment_status', 'inactive')->count(),
            'totalDepartments' => Department::count(),
            'employeesByDepartment' => $employeesByDepartment,
            'attendanceByStatus' => $attendanceByStatus,
            'currentMonth' => $periodStart->format('m/Y'), 'selectedMonth' => $month, 'selectedYear' => $year, 'trend' => $trend, 'recentAttendance' => $recentAttendance,
        ]);
    }
}
