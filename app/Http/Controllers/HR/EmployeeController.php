<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $employees = Employee::with(['user', 'department'])
            ->when(request('search'), fn ($query, $search) => $query->where(fn ($q) => $q->where('employee_code', 'like', "%{$search}%")->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))
            ->when(request('department_id'), fn ($query, $department) => $query->where('department_id', $department))
            ->when(request('employment_status'), fn ($query, $status) => $query->where('employment_status', $status))
            ->latest()->paginate(10)->withQueryString();
        $departments = Department::orderBy('name')->get();
        return view('hr.employees.index', compact('employees', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $departments = Department::orderBy('name')->get();
        return view('hr.employees.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($data) {
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']), 'role' => 'employee', 'account_status' => 'active']);
            $user->employee()->create(collect($data)->except(['name', 'email', 'password', 'password_confirmation'])->all());
        });
        return redirect()->route('hr.employees.index')->with('success', 'Đã tạo tài khoản và hồ sơ nhân viên.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee): View
    {
        $employee->load(['user', 'department'])->loadCount('attendances');
        return view('hr.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee): View
    {
        $employee->load('user');
        $departments = Department::orderBy('name')->get();
        return view('hr.employees.edit', compact('employee', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($data, $employee) {
            $employee->user->update(['name' => $data['name'], 'email' => $data['email']]);
            $employee->update(collect($data)->except(['name', 'email'])->all());
        });
        return redirect()->route('hr.employees.index')->with('success', 'Đã cập nhật nhân viên.');
    }

    /**
     * Remove the specified resource from storage.
     */
}
