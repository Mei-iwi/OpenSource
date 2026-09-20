<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceFilterRequest;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(AttendanceFilterRequest $request): View
    {
        return view('hr.reports.index', $this->reportData($request));
    }

    public function print(AttendanceFilterRequest $request): View
    {
        return view('hr.reports.print', $this->reportData($request, true));
    }

    public function export(AttendanceFilterRequest $request): StreamedResponse
    {
        $rows = $this->reportQuery($request)->lazy(500);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Ngày', 'Mã nhân viên', 'Nhân viên', 'Email', 'Phòng ban', 'Check-in', 'Check-out', 'Trạng thái', 'Ghi chú']);
            foreach ($rows as $row) {
                fputcsv($handle, array_map($this->csvCell(...), [$row->work_date->format('d/m/Y'), $row->employee->employee_code, $row->employee->user->name, $row->employee->user->email, $row->employee->department->name, $row->check_in, $row->check_out, $row->status, $row->note]));
            }
            fclose($handle);
        }, 'attendance-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function reportData(AttendanceFilterRequest $request, bool $forPrint = false): array
    {
        $query = $this->reportQuery($request);
        $rows = $forPrint ? $query->get() : $query->paginate(20)->withQueryString();
        $counts = (clone $this->reportQuery($request))->reorder()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $employeesByDepartment = Employee::join('departments', 'departments.id', '=', 'employees.department_id')->selectRaw('departments.name, COUNT(employees.id) as total')->groupBy('departments.id', 'departments.name')->orderBy('departments.name')->get();

        return ['attendances' => $rows, 'counts' => $counts, 'totalRecords' => $counts->sum(), 'departments' => Department::orderBy('name')->get(), 'employees' => Employee::with('user')->orderBy('employee_code')->get(), 'employeesByDepartment' => $employeesByDepartment];
    }

    private function reportQuery(AttendanceFilterRequest $request): Builder
    {
        return Attendance::with(['employee.user', 'employee.department'])
            ->filtered($request->validated())
            ->latest('work_date')->latest('id');
    }

    private function csvCell(mixed $value): string
    {
        $text = (string) $value;

        // Keep user-supplied values as text when opened in spreadsheet software.
        return preg_match('/^[\s]*[=+@-]/u', $text) ? "'".$text : $text;
    }
}
