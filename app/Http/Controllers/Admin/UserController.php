<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SendPasswordResetLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::query()
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
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.users.create', ['departments' => Department::orderBy('name')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['account_status'] = 'active';
        DB::transaction(function () use ($data) {
            $user = User::create(collect($data)->only(['name', 'email', 'password', 'role', 'account_status'])->all());
            $user->employee()->create(collect($data)->only(['employee_code', 'department_id', 'hire_date'])->all() + ['employment_status' => 'active']);
        });

        return redirect()->route('admin.users.index')->with('success', 'Đã tạo tài khoản và hồ sơ nhân viên.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
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
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($user->is(auth()->user()) && ($data['role'] ?? $user->role) !== 'admin') {
            return back()->withErrors(['role' => 'Không thể hạ quyền tài khoản Admin đang đăng nhập.'])->withInput();
        }

        DB::transaction(function () use ($user, $data) {
            $user->update(collect($data)->only(['name', 'email', 'role', 'account_status'])->all());
            if (isset($data['employee_code']) && ! $user->employee()->exists()) {
                $user->employee()->create(collect($data)->only(['employee_code', 'department_id', 'hire_date'])->all() + ['employment_status' => 'active']);
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
