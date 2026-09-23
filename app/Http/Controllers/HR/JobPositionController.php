<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobPositionRequest;
use App\Http\Requests\UpdateJobPositionRequest;
use App\Models\Employee;
use App\Models\JobPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JobPositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $jobPositions = JobPosition::withCount('employees')
            ->when(request('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('hr.job_positions.index', compact('jobPositions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('hr.job_positions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobPositionRequest $request): RedirectResponse
    {
        JobPosition::create($request->validated());

        return redirect()->route('hr.job-positions.index')->with('success', 'Đã tạo chức vụ.');
    }

    /**
     * Display the specified resource.
     */
    public function show(JobPosition $jobPosition): View
    {
        $jobPosition->loadCount('employees');
        $employees = $jobPosition->employees()->with(['user', 'department'])->paginate(15);

        return view('hr.job_positions.show', compact('jobPosition', 'employees'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobPosition $jobPosition): View
    {
        return view('hr.job_positions.edit', compact('jobPosition'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJobPositionRequest $request, JobPosition $jobPosition): RedirectResponse
    {
        $data = $request->validated();
        $newName = $data['name'];
        $oldName = $jobPosition->name;

        DB::transaction(function () use ($jobPosition, $oldName, $newName) {
            $jobPosition->update(['name' => $newName]);

            if ($oldName !== $newName) {
                Employee::where('position', $oldName)->update(['position' => $newName]);
            }
        });

        return redirect()->route('hr.job-positions.index')->with('success', 'Đã cập nhật chức vụ.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobPosition $jobPosition): RedirectResponse
    {
        if ($jobPosition->employees()->exists()) {
            return back()->with('error', 'Không thể xóa vị trí công việc đang được nhân viên sử dụng.');
        }

        $jobPosition->delete();

        return redirect()->route('hr.job-positions.index')->with('success', 'Đã xóa chức vụ.');
    }
}
