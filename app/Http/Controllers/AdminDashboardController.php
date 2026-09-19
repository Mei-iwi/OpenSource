<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends DashboardController
{
    public function __invoke(Request $request): View
    {
        $dashboard = $this->dashboardData($request);
        $today = now()->toDateString();

        $usersByRole = User::selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');

        return view('dashboard.admin', [
            'totalUsers' => User::count(), 'activeUsers' => User::where('account_status', 'active')->count(), 'lockedUsers' => User::where('account_status', 'locked')->count(),
            'usersByRole' => $usersByRole,
            'totalEmployees' => Employee::count(), 'activeEmployees' => Employee::where('employment_status', 'active')->count(), 'inactiveEmployees' => Employee::where('employment_status', 'inactive')->count(),
            'presentToday' => Attendance::whereDate('work_date', $today)->where('status', 'present')->count(), 'lateToday' => Attendance::whereDate('work_date', $today)->where('status', 'late')->count(), 'absentToday' => Attendance::whereDate('work_date', $today)->where('status', 'absent')->count(),
            'periodStatus' => $dashboard['attendanceByStatus'], 'employeesByDepartment' => $dashboard['employeesByDepartment'], 'trend' => $dashboard['trend'], 'recentAttendance' => $dashboard['recentAttendance'], 'pendingLeaveRequests' => LeaveRequest::where('status', 'pending')->latest()->limit(5)->with('employee.user')->get(), 'selectedMonth' => $dashboard['selectedMonth'], 'selectedYear' => $dashboard['selectedYear'],
        ]);
    }
}
