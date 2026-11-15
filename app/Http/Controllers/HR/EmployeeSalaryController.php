<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmployeeSalaryRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeSalaryController extends Controller
{
    public function index(): View
    {
        $employees = Employee::with(['user', 'salary'])
            ->orderBy('employee_code')->paginate(20);

        return view('hr.payroll.salaries', compact('employees'));
    }

    public function edit(Employee $employee): View
    {
        $employee->load(['user', 'salary']);
        abort_if(auth()->user()->isHr() && $employee->user->isAdmin(), 403);

        return view('hr.payroll.salary-edit', compact('employee'));
    }

    public function update(UpdateEmployeeSalaryRequest $request, Employee $employee): RedirectResponse
    {
        $employee->load('user');
        abort_if($request->user()->isHr() && $employee->user->isAdmin(), 403);
        DB::transaction(fn () => $employee->salary()->updateOrCreate([], $request->validated()));

        $route = $request->input('return_to') === 'employee' ? 'hr.employees.show' : 'hr.payroll.salaries.index';

        return redirect()->route($route, $route === 'hr.employees.show' ? $employee : [])->with('success', 'Đã cập nhật cấu hình lương.');
    }
}
