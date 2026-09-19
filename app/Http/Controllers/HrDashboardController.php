<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrDashboardController extends DashboardController
{
    public function __invoke(Request $request): View
    {
        $dashboard = $this->dashboardData($request);

        return view('dashboard.hr', [
            'totalEmployees' => Employee::count(),
            'activeEmployees' => Employee::where('employment_status', 'active')->count(),
            'inactiveEmployees' => Employee::where('employment_status', 'inactive')->count(),
            'totalDepartments' => Department::count(),
            'employeesByDepartment' => $dashboard['employeesByDepartment'],
            'attendanceByStatus' => $dashboard['attendanceByStatus'],
            'currentMonth' => $dashboard['periodStart']->format('m/Y'), 'selectedMonth' => $dashboard['selectedMonth'], 'selectedYear' => $dashboard['selectedYear'], 'trend' => $dashboard['trend'], 'recentAttendance' => $dashboard['recentAttendance'], 'pendingLeaveCount' => LeaveRequest::where('status', 'pending')->count(),
        ]);
    }
}
