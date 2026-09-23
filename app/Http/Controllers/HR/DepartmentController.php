<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $departments = Department::with(['manager.user'])
            ->withCount('employees')
            ->when(request('search'), fn ($query, $search) => $query->where(fn ($q) => $q->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('hr.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $employees = Employee::with('user')
            ->where('employment_status', 'active')
            ->orderBy('employee_code')
            ->get();

        return view('hr.departments.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        Department::create($request->validated());

        return redirect()->route('hr.departments.index')->with('success', 'Đã tạo phòng ban.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department): View
    {
        $department->load(['manager.user'])->loadCount('employees');

        return view('hr.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department): View
    {
        $department->load('manager.user');
        $employees = Employee::with('user')
            ->where('employment_status', 'active')
            ->orderBy('employee_code')
            ->get();

        return view('hr.departments.edit', compact('department', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        return redirect()->route('hr.departments.index')->with('success', 'Đã cập nhật phòng ban.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department): RedirectResponse
    {
        if ($department->employees()->exists()) {
            return back()->with('error', 'Không thể xóa phòng ban đang có nhân viên.');
        }

        $department->delete();

        return redirect()->route('hr.departments.index')->with('success', 'Đã xóa phòng ban.');
    }
}
