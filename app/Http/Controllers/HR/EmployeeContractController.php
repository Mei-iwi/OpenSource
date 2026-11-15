<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmployeeContractRequest;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EmployeeContractController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today(config('attendance.timezone'))->toDateString();
        $soon = Carbon::today(config('attendance.timezone'))->addDays(30)->toDateString();
        $status = $request->query('status');

        $employees = Employee::query()
            ->with(['user', 'department'])
            ->when($request->user()->isHr(), fn ($query) => $query->whereHas('user', fn ($user) => $user->where('role', '!=', 'admin')))
            ->when($request->filled('search'), fn ($query) => $query->where(function ($q) use ($request) {
                $term = $request->query('search');
                $q->where('employee_code', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$term}%"));
            }))
            ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->query('department_id')))
            ->when($status === 'missing', fn ($query) => $query->where(function ($q) {
                $q->whereNull('contract_type')->orWhereNull('contract_start_date')
                    ->orWhere(fn ($dated) => $dated->whereIn('contract_type', ['probation', 'fixed-term'])->whereNull('contract_end_date'));
            }))
            ->when($status === 'expiring', fn ($query) => $query->where('contract_type', '!=', 'indefinite')->whereBetween('contract_end_date', [$today, $soon]))
            ->when($status === 'expired', fn ($query) => $query->where('contract_type', '!=', 'indefinite')->whereDate('contract_end_date', '<', $today))
            ->orderBy('employee_code')
            ->paginate(15)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return view('hr.contracts.index', compact('employees', 'departments', 'today', 'soon'));
    }

    public function show(Employee $employee): View
    {
        $this->guardEmployee($employee);
        $employee->load('department');

        return view('hr.contracts.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $this->guardEmployee($employee);

        return view('hr.contracts.edit', compact('employee'));
    }

    public function update(UpdateEmployeeContractRequest $request, Employee $employee): RedirectResponse
    {
        $data = $request->validated();
        if (($data['contract_type'] ?? null) === 'indefinite') {
            $data['contract_end_date'] = null;
        }
        $employee->update($data);

        return redirect()->route('hr.contracts.show', $employee)->with('success', 'Đã cập nhật hợp đồng và thông tin pháp lý.');
    }

    private function guardEmployee(Employee $employee): void
    {
        $employee->load('user');
        abort_if(auth()->user()->isHr() && $employee->user->isAdmin(), 403);
    }
}
