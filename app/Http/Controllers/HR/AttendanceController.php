<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $attendances = Attendance::with(['employee.user', 'employee.department'])
            ->when(request('month'), fn ($query, $month) => $query->whereMonth('work_date', $month))
            ->when(request('year'), fn ($query, $year) => $query->whereYear('work_date', $year))
            ->when(request('department_id'), fn ($query, $id) => $query->whereHas('employee', fn ($employee) => $employee->where('department_id', $id)))
            ->when(request('employee_id'), fn ($query, $id) => $query->where('employee_id', $id))
            ->when(request('search'), fn ($query, $search) => $query->whereHas('employee', fn ($employee) => $employee->where('employee_code', 'like', "%{$search}%")->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest('work_date')->latest('id')->paginate(15)->withQueryString();

        return view('hr.attendances.index', [
            'attendances' => $attendances,
            'departments' => Department::orderBy('name')->get(),
            'employees' => Employee::with('user')->orderBy('employee_code')->get(),
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
