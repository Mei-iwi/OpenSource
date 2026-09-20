<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\EmployeeCodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

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
    public function store(StoreEmployeeRequest $request, EmployeeCodeGenerator $codes): RedirectResponse
    {
        $data = $request->validated();
        $initialPassword = Carbon::parse($data['date_of_birth'])->format('dmY');
        $disk = config('filesystems.avatar_disk');
        $avatarPath = $request->hasFile('avatar') ? $request->file('avatar')->store('avatars', $disk) : null;
        try {
            $employee = DB::transaction(function () use ($data, $avatarPath, $codes, $initialPassword) {
                $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($initialPassword), 'role' => $data['role'], 'account_status' => 'active']);
                $employeeData = collect($data)->except(['name', 'email', 'password', 'password_confirmation', 'avatar', 'role', 'employee_code'])->all();
                $employeeData['avatar_path'] = $avatarPath;
                $employeeData['employee_code'] = $codes->next($user->role);

                return $user->employee()->create($employeeData);
            });
        } catch (Throwable $exception) {
            if ($avatarPath) {
                Storage::disk($disk)->delete($avatarPath);
            }
            throw $exception;
        }

        return redirect()->route('hr.employees.show', $employee)->with([
            'success' => 'Đã tạo hồ sơ nhân viên và tài khoản đăng nhập.',
            'created_credentials' => [
                'employee_code' => $employee->employee_code,
                'email' => $employee->user->email,
                'initial_password' => $initialPassword,
            ],
        ]);
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
        abort_if(auth()->user()->isHr() && $employee->user->isAdmin(), 403);
        $departments = Department::orderBy('name')->get();

        return view('hr.employees.edit', compact('employee', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $data = $request->validated();
        $oldAvatar = $employee->avatar_path;
        $disk = config('filesystems.avatar_disk');
        $newAvatar = $request->hasFile('avatar') ? $request->file('avatar')->store('avatars', $disk) : $oldAvatar;
        try {
            DB::transaction(function () use ($data, $employee, $newAvatar) {
                $employee->user->update(['name' => $data['name'], 'email' => $data['email']]);
                $employeeData = collect($data)->except(['name', 'email', 'avatar'])->all();
                $employeeData['avatar_path'] = $newAvatar;
                $employee->update($employeeData);
            });
        } catch (Throwable $exception) {
            if ($newAvatar && $newAvatar !== $oldAvatar) {
                Storage::disk($disk)->delete($newAvatar);
            }
            throw $exception;
        }
        if ($newAvatar !== $oldAvatar && $oldAvatar) {
            Storage::disk($disk)->delete($oldAvatar);
        }

        return redirect()->route('hr.employees.index')->with('success', 'Đã cập nhật nhân viên.');
    }
}
