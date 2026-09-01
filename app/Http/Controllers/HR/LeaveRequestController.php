<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewLeaveRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = LeaveRequest::with(['employee.user', 'employee.department'])->when($request->status, fn ($q, $v) => $q->where('status', $v))->when($request->leave_type, fn ($q, $v) => $q->where('leave_type', $v))->when($request->department_id, fn ($q, $v) => $q->whereHas('employee', fn ($e) => $e->where('department_id', $v)))->when($request->employee_id, fn ($q, $v) => $q->where('employee_id', $v))->latest()->paginate(15)->withQueryString();
        return view('hr.leave_requests.index', ['requests' => $requests, 'departments' => Department::orderBy('name')->get(), 'employees' => Employee::with('user')->orderBy('employee_code')->get(), 'pendingCount' => LeaveRequest::where('status', 'pending')->count()]);
    }

    public function show(LeaveRequest $leaveRequest): View { return view('hr.leave_requests.show', ['leaveRequest' => $leaveRequest->load(['employee.user', 'employee.department', 'reviewer'])]); }

    public function review(ReviewLeaveRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        if ($leaveRequest->status !== 'pending') return back()->with('error', 'Chỉ có thể xử lý đơn đang chờ duyệt.');
        $leaveRequest->update(['status' => $request->validated('status'), 'review_note' => $request->validated('review_note'), 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
        return redirect()->route('hr.leave-requests.show', $leaveRequest)->with('success', 'Đã cập nhật trạng thái đơn nghỉ.');
    }
}
