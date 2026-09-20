<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SendPasswordResetLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\EmployeeCodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::with('employee')
            ->when(request('search'), fn ($query, $search) => $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when(request('role'), fn ($query, $role) => $query->where('role', $role))
            ->when(request('account_status'), fn ($query, $status) => $query->where('account_status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $roleChanges = $user->roleChanges()->with('actor')->latest('id')->paginate(15);

        return view('admin.users.show', compact('user', 'roleChanges'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $departments = Department::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user, EmployeeCodeGenerator $codes): RedirectResponse
    {
        $data = $request->validated();

        if ($user->is(auth()->user()) && ($data['role'] ?? $user->role) !== 'admin') {
            return back()->withErrors(['role' => 'Không thể hạ quyền tài khoản Admin đang đăng nhập.'])->withInput();
        }

        DB::transaction(function () use ($user, $data, $codes) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $user->update(collect($data)->only(['name', 'email', 'role', 'account_status'])->all());
            if (isset($data['department_id']) && ! $user->employee()->exists()) {
                $user->employee()->create(collect($data)->only(['department_id', 'hire_date'])->all() + ['employee_code' => $codes->next($user->role), 'employment_status' => 'active']);
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'Đã cập nhật tài khoản.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Không thể xóa tài khoản đang đăng nhập.');
        }
        if ($user->employee()->exists()) {
            return back()->with('error', 'Không thể xóa user đã gắn hồ sơ Employee.');
        }
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Đã xóa tài khoản.');
    }

    public function lock(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Không thể khóa tài khoản Admin đang đăng nhập.');
        }
        $user->update(['account_status' => 'locked']);

        return back()->with('success', 'Đã khóa tài khoản.');
    }

    public function unlock(User $user): RedirectResponse
    {
        $user->update(['account_status' => 'active']);

        return back()->with('success', 'Đã mở khóa tài khoản.');
    }

    public function resetPassword(User $user, SendPasswordResetLink $send): RedirectResponse
    {
        return back()->with('success', $send($user->email));
    }
}
