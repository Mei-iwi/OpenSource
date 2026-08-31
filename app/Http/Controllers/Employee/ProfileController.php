<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $employee = $request->user()->employee?->load(['user', 'department']);
        return view('employee.profile.show', compact('employee'));
    }

    public function edit(Request $request): View
    {
        $employee = $request->user()->employee?->load(['user', 'department']);
        return view('employee.profile.edit', compact('employee'));
    }

    public function update(EmployeeProfileUpdateRequest $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return redirect()->route('employee.profile.show')->with('error', 'Tài khoản chưa được gắn hồ sơ nhân viên.');
        }

        Gate::authorize('update', $employee);
        $employee->update($request->validated());
        return redirect()->route('employee.profile.show')->with('success', 'Đã cập nhật thông tin cá nhân.');
    }
}
