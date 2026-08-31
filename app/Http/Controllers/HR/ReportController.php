<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('hr.reports.index', $this->reportData($request));
    }

    public function print(Request $request): View
    {
        return view('hr.reports.print', $this->reportData($request));
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->reportQuery($request)->get();
        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Ngày', 'Mã nhân viên', 'Nhân viên', 'Email', 'Phòng ban', 'Check-in', 'Check-out', 'Trạng thái', 'Ghi chú']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->work_date->format('d/m/Y'), $row->employee->employee_code, $row->employee->user->name, $row->employee->user->email, $row->employee->department->name, $row->check_in, $row->check_out, $row->status, $row->note]);
            }
            fclose($handle);
        }, 'attendance-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function reportData(Request $request): array
    {
        $rows = $this->reportQuery($request)->paginate(20)->withQueryString();
        $counts = (clone $this->reportQuery($request))->reorder()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $employeesByDepartment = Employee::join('departments', 'departments.id', '=', 'employees.department_id')->selectRaw('departments.name, COUNT(employees.id) as total')->groupBy('departments.id', 'departments.name')->orderBy('departments.name')->get();
        return ['attendances' => $rows, 'counts' => $counts, 'totalRecords' => $counts->sum(), 'departments' => Department::orderBy('name')->get(), 'employees' => Employee::with('user')->orderBy('employee_code')->get(), 'employeesByDepartment' => $employeesByDepartment];
    }

    private function reportQuery(Request $request)
    {
        return Attendance::with(['employee.user', 'employee.department'])
            ->when($request->month, fn ($query, $month) => $query->whereMonth('work_date', $month))
            ->when($request->year, fn ($query, $year) => $query->whereYear('work_date', $year))
            ->when($request->department_id, fn ($query, $id) => $query->whereHas('employee', fn ($employee) => $employee->where('department_id', $id)))
            ->when($request->employee_id, fn ($query, $id) => $query->where('employee_id', $id))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->employment_status, fn ($query, $status) => $query->whereHas('employee', fn ($employee) => $employee->where('employment_status', $status)))
            ->latest('work_date')->latest('id');
    }
}
