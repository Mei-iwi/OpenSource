<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Throwable;
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
        $oldAvatar = $employee->avatar_path;
        $newAvatar = $request->hasFile('avatar') ? $request->file('avatar')->store('avatars', 'public') : $oldAvatar;
        try {
            $profileData = $request->validated();
            unset($profileData['avatar']);
            $profileData['avatar_path'] = $newAvatar;
            $employee->update($profileData);
        } catch (Throwable $exception) {
            if ($newAvatar && $newAvatar !== $oldAvatar) Storage::disk('public')->delete($newAvatar);
            throw $exception;
        }
        if ($newAvatar !== $oldAvatar && $oldAvatar) Storage::disk('public')->delete($oldAvatar);
        return redirect()->route('employee.profile.show')->with('success', 'Đã cập nhật thông tin cá nhân.');
    }
}
