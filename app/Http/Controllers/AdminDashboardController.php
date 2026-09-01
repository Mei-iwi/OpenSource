<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $month = max(1, min(12, (int) ($request->integer('month') ?: now()->month)));
        $year = max(2000, min(2100, (int) ($request->integer('year') ?: now()->year)));
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $today = now()->toDateString();
        $periodStatus = Attendance::whereBetween('work_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $employeesByDepartment = Employee::join('departments', 'departments.id', '=', 'employees.department_id')
            ->selectRaw('departments.name, COUNT(employees.id) as total')->groupBy('departments.id', 'departments.name')->orderBy('departments.name')->get();
        $trendStart = $periodStart->copy()->subMonths(5)->startOfMonth();
        $trendRows = Attendance::whereBetween('work_date', [$trendStart->toDateString(), $periodEnd->toDateString()])
            ->get(['work_date', 'status'])->groupBy(fn (Attendance $row) => $row->work_date->format('Y-m'));
        $trend = collect(range(0, 5))->map(function (int $offset) use ($trendStart, $trendRows): array {
            $date = $trendStart->copy()->addMonths($offset);
            $rows = $trendRows->get($date->format('Y-m'), collect());
            return ['label' => $date->format('m/Y'), 'present' => $rows->where('status', 'present')->count(), 'late' => $rows->where('status', 'late')->count(), 'absent' => $rows->where('status', 'absent')->count()];
        })->values();
        $recentAttendance = Attendance::with('employee.user', 'employee.department')->latest('work_date')->latest('id')->limit(6)->get();

        $usersByRole = User::selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');
        return view('dashboard.admin', [
            'totalUsers' => User::count(), 'activeUsers' => User::where('account_status', 'active')->count(), 'lockedUsers' => User::where('account_status', 'locked')->count(),
            'usersByRole' => $usersByRole,
            'totalEmployees' => Employee::count(), 'activeEmployees' => Employee::where('employment_status', 'active')->count(), 'inactiveEmployees' => Employee::where('employment_status', 'inactive')->count(),
            'presentToday' => Attendance::whereDate('work_date', $today)->where('status', 'present')->count(), 'lateToday' => Attendance::whereDate('work_date', $today)->where('status', 'late')->count(), 'absentToday' => Attendance::whereDate('work_date', $today)->where('status', 'absent')->count(),
            'periodStatus' => $periodStatus, 'employeesByDepartment' => $employeesByDepartment, 'trend' => $trend, 'recentAttendance' => $recentAttendance, 'pendingLeaveRequests' => LeaveRequest::where('status', 'pending')->latest()->limit(5)->with('employee.user')->get(), 'selectedMonth' => $month, 'selectedYear' => $year,
        ]);
    }
}
