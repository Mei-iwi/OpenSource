<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceFilterRequest;
use App\Models\Attendance;
use App\Models\Department;
use App\Support\CsvReportResponse;
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

        return CsvReportResponse::download('attendance-report.csv', ['Ngày', 'Mã nhân viên', 'Nhân viên', 'Email', 'Phòng ban', 'Check-in', 'Check-out', 'Trạng thái', 'Ghi chú'], $rows, fn ($row) => [$row->work_date->format('d/m/Y'), $row->employee->employee_code, $row->employee->user->name, $row->employee->user->email, $row->employee->department->name, $row->check_in, $row->check_out, $row->status, $row->note]);
    }

    private function reportData(AttendanceFilterRequest $request, bool $forPrint = false): array
    {
        $query = $this->reportQuery($request);
        $rows = $forPrint ? $query->limit(1000)->get() : $query->paginate(20)->withQueryString();
        $counts = (clone $this->reportQuery($request))->reorder()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return ['attendances' => $rows, 'counts' => $counts, 'totalRecords' => $counts->sum(), 'printTruncated' => $forPrint && $counts->sum() > 1000, 'departments' => Department::orderBy('name')->get()];
    }

    private function reportQuery(AttendanceFilterRequest $request): Builder
    {
        return Attendance::with(['employee.user', 'employee.department'])
            ->filtered($request->validated())
            ->latest('work_date')->latest('id');
    }
}
