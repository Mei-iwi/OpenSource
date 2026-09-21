<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;
        $requests = $employee ? $employee->leaveRequests()->latest()->paginate(10)->withQueryString() : LeaveRequest::whereKey(0)->paginate(10);

        return view('employee.leave_requests.index', compact('requests'));
    }

    public function create(): View
    {
        return view('employee.leave_requests.create');
    }

    public function store(StoreLeaveRequest $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return redirect()->route('employee.leave-requests.index')->with('error', 'Tài khoản chưa có hồ sơ nhân viên.');
        }
        $employee->leaveRequests()->create($request->validated());

        return redirect()->route('employee.leave-requests.index')->with('success', 'Đã gửi đơn xin nghỉ.');
    }

    public function show(Request $request, LeaveRequest $leaveRequest): View
    {
        abort_unless($leaveRequest->employee_id === $request->user()->employee?->id, 403);

        return view('employee.leave_requests.show', ['leaveRequest' => $leaveRequest->load(['employee.user', 'reviewer'])]);
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        abort_unless($leaveRequest->employee_id === $request->user()->employee?->id, 403);
        $updated = LeaveRequest::whereKey($leaveRequest->id)->where('status', 'pending')->update(['status' => 'cancelled']);
        if (! $updated) {
            return back()->with('error', 'Đơn đã được xử lý hoặc hủy. Vui lòng tải lại trang.');
        }

        return redirect()->route('employee.leave-requests.index')->with('success', 'Đã hủy đơn xin nghỉ.');
    }
}
