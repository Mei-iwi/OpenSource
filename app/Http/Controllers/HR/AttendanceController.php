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
}
