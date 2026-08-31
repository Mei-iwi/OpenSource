<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['account_status'] = 'active';
        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Đã tạo tài khoản thành công.');
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
        return view('admin.users.edit', compact('user'));
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

        $user->update($data);

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
}
