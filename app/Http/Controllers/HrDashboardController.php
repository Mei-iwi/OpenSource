<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HrDashboardController extends Controller
{
    public function __invoke(): View
    {
        $attendanceByStatus = Attendance::whereBetween('work_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $employeesByDepartment = Employee::join('departments', 'departments.id', '=', 'employees.department_id')
            ->selectRaw('departments.name, COUNT(employees.id) as total')->groupBy('departments.id', 'departments.name')->orderBy('departments.name')->get();

        return view('dashboard.hr', [
            'totalEmployees' => Employee::count(),
            'activeEmployees' => Employee::where('employment_status', 'active')->count(),
            'inactiveEmployees' => Employee::where('employment_status', 'inactive')->count(),
            'totalDepartments' => Department::count(),
            'employeesByDepartment' => $employeesByDepartment,
            'attendanceByStatus' => $attendanceByStatus,
            'currentMonth' => Carbon::now()->format('m/Y'),
        ]);
    }
}
