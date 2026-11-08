<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HrReportFilterRequest;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Support\CsvReportResponse;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrReportsController extends Controller
{
    public function movement(HrReportFilterRequest $request): View
    {
        $filters = $request->validated();
        $rows = $this->movementQuery($filters)->paginate(20)->withQueryString();

        return view('hr.reports.movement', compact('rows', 'filters'));
    }

    public function movementCsv(HrReportFilterRequest $request): StreamedResponse
    {
        $rows = $this->movementQuery($request->validated())->lazy(500);

        return CsvReportResponse::download('employee-movement.csv', ['Loại biến động', 'Ngày', 'Mã nhân viên', 'Nhân viên', 'Mã phòng ban', 'Phòng ban'], $rows, fn ($row) => [
            $row->movement_type === 'new_hire' ? 'Nhân viên mới' : 'Ngừng làm việc',
            $row->event_date,
            $row->employee_code,
            $row->employee_name,
            $row->department_code,
            $row->department_name,
        ]);
    }

    public function leave(HrReportFilterRequest $request): View
    {
        $filters = $request->validated();
        $rows = $this->leaveQuery($filters)->latest('start_date')->latest('id')->paginate(20)->withQueryString();

        return view('hr.reports.leave', compact('rows', 'filters'));
    }

    public function leaveCsv(HrReportFilterRequest $request): StreamedResponse
    {
        $rows = $this->leaveQuery($request->validated())->latest('start_date')->latest('id')->lazy(500);

        return CsvReportResponse::download('leave-summary.csv', ['Mã nhân viên', 'Nhân viên', 'Phòng ban', 'Loại nghỉ', 'Trạng thái', 'Từ ngày', 'Đến ngày'], $rows, fn ($row) => [
            $row->employee->employee_code,
            $row->employee->user->name,
            $row->employee->department->name,
            $row->leave_type,
            $row->status,
            $row->start_date->toDateString(),
            $row->end_date->toDateString(),
        ]);
    }

    public function late(HrReportFilterRequest $request): View
    {
        $filters = $request->validated();
        [$start, $end] = $this->periodBounds($filters, true);
        $rows = $this->lateQuery($filters, $start, $end)->orderByDesc('late_count')->orderBy('id')->paginate(20)->withQueryString();

        return view('hr.reports.late', compact('rows', 'filters', 'start', 'end'));
    }

    public function lateCsv(HrReportFilterRequest $request): StreamedResponse
    {
        $filters = $request->validated();
        [$start, $end] = $this->periodBounds($filters, true);
        $rows = $this->lateQuery($filters, $start, $end)->orderBy('id')->lazy(500);

        return CsvReportResponse::download('late-attendance.csv', ['Từ ngày', 'Đến ngày', 'Mã nhân viên', 'Nhân viên', 'Phòng ban', 'Số lần đi muộn'], $rows, fn ($row) => [
            $start,
            $end,
            $row->employee_code,
            $row->user->name,
            $row->department->name,
            $row->late_count,
        ]);
    }

    private function movementQuery(array $filters): QueryBuilder
    {
        [$start, $end] = $this->periodBounds($filters);
        $base = function (string $dateColumn, string $type) use ($filters, $start, $end): QueryBuilder {
            return DB::table('employees as e')
                ->join('users as u', 'u.id', '=', 'e.user_id')
                ->join('departments as d', 'd.id', '=', 'e.department_id')
                ->select('e.id as employee_id', 'e.employee_code', 'u.name as employee_name', 'd.code as department_code', 'd.name as department_name')
                ->selectRaw("e.{$dateColumn} as event_date, '{$type}' as movement_type")
                ->when($filters['employee_code'] ?? null, fn ($query, $code) => $query->where('e.employee_code', $code))
                ->when($filters['department_code'] ?? null, fn ($query, $code) => $query->where('d.code', $code))
                ->when($start, fn ($query) => $query->where("e.{$dateColumn}", '>=', $start)->where("e.{$dateColumn}", '<', Carbon::parse($end)->addDay()->toDateString()));
        };

        $hires = $base('hire_date', 'new_hire');
        $inactive = $base('employment_end_date', 'inactive')->whereIn('e.employment_status', ['inactive', 'resigned']);
        $type = $filters['movement_type'] ?? null;
        $source = match ($type) {
            'new_hire' => $hires,
            'inactive' => $inactive,
            default => $hires->unionAll($inactive),
        };

        return DB::query()->fromSub($source, 'movements')->orderByDesc('event_date')->orderByDesc('employee_id');
    }

    private function leaveQuery(array $filters): EloquentBuilder
    {
        return LeaveRequest::query()
            ->select(['id', 'employee_id', 'leave_type', 'status', 'start_date', 'end_date'])
            ->with(['employee:id,user_id,department_id,employee_code', 'employee.user:id,name', 'employee.department:id,name,code'])
            ->when($filters['employee_code'] ?? null, fn ($query, $code) => $query->whereHas('employee', fn ($employee) => $employee->where('employee_code', $code)))
            ->when($filters['department_code'] ?? null, fn ($query, $code) => $query->whereHas('employee.department', fn ($department) => $department->where('code', $code)))
            ->when($filters['leave_type'] ?? null, fn ($query, $type) => $query->where('leave_type', $type))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->where('end_date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->where('start_date', '<', Carbon::parse($date)->addDay()->toDateString()));
    }

    private function lateQuery(array $filters, string $start, string $end): EloquentBuilder
    {
        return Employee::query()
            ->select(['id', 'user_id', 'department_id', 'employee_code'])
            ->with(['user:id,name', 'department:id,name,code'])
            ->where('hire_date', '<', Carbon::parse($end)->addDay()->toDateString())
            ->where(fn ($query) => $query->whereNull('employment_end_date')->orWhere('employment_end_date', '>=', $start))
            ->withCount(['attendances as late_count' => fn ($query) => $query->where('status', 'late')->where('work_date', '>=', $start)->where('work_date', '<', Carbon::parse($end)->addDay()->toDateString())])
            ->when($filters['employee_code'] ?? null, fn ($query, $code) => $query->where('employee_code', $code))
            ->when($filters['department_code'] ?? null, fn ($query, $code) => $query->whereHas('department', fn ($department) => $department->where('code', $code)));
    }

    private function periodBounds(array $filters, bool $defaultCurrent = false): array
    {
        $today = now(config('attendance.timezone'));
        $year = $filters['year'] ?? ($defaultCurrent ? $today->year : null);
        if (! $year) {
            return [null, null];
        }

        $month = $filters['month'] ?? ($defaultCurrent && ! isset($filters['year']) ? $today->month : null);
        $start = Carbon::create((int) $year, $month ? (int) $month : 1, 1);
        $end = $month ? $start->copy()->endOfMonth() : $start->copy()->endOfYear();

        return [$start->toDateString(), $end->toDateString()];
    }
}
