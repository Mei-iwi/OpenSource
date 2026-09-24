<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceFilterRequest;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(AttendanceFilterRequest $request): View
    {
        $filtered = Attendance::filtered($request->validated());
        $counts = (clone $filtered)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $attendances = $filtered->with(['employee.user', 'employee.department'])->latest('work_date')->latest('id')->paginate(15)->withQueryString();

        return view('hr.attendances.index', [
            'attendances' => $attendances,
            'departments' => Department::orderBy('name')->get(),
            'employees' => Employee::with('user')->orderBy('employee_code')->get(),
            'counts' => $counts,
        ]);
    }

    public function create(): View
    {
        return view('hr.attendances.create', ['employees' => Employee::with('user')->orderBy('employee_code')->get()]);
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        Attendance::create($request->validated());

        return redirect()->route('hr.attendances.index')->with('success', 'Đã ghi nhận chấm công.');
    }

    public function edit(Attendance $attendance): View
    {
        $attendance->load(['employee.user', 'employee.department']);

        return view('hr.attendances.edit', ['attendance' => $attendance, 'employees' => Employee::with('user')->orderBy('employee_code')->get()]);
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $attendance->update($request->validated());

        return redirect()->route('hr.attendances.index')->with('success', 'Đã cập nhật chấm công.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $this->authorize('delete', $attendance);

        $attendance->loadMissing('employee.user');
        $workDate = $attendance->work_date ? $attendance->work_date->format('d/m/Y') : null;
        $employeeName = $attendance->employee?->user?->name;

        Log::info('Attendance record deleted', [
            'attendance_id' => $attendance->id,
            'employee_id' => $attendance->employee_id,
            'work_date' => $attendance->work_date?->toDateString(),
            'deleted_by' => auth()->id(),
            'deleted_by_role' => auth()->user()?->role,
        ]);

        $attendance->delete();

        $message = $workDate && $employeeName
            ? "Đã xóa bản ghi chấm công ngày {$workDate} của {$employeeName}."
            : 'Đã xóa bản ghi chấm công.';

        return redirect()->route('hr.attendances.index')->with('success', $message);
    }
}
