<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestSubmittedNotification;
use App\Services\AnnualLeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request, AnnualLeaveService $annualLeaveService): View
    {
        $employee = $request->user()->employee;
        $requests = $employee ? $employee->leaveRequests()->latest()->paginate(10)->withQueryString() : LeaveRequest::whereKey(0)->paginate(10);
        $leaveBalance = $employee ? $annualLeaveService->getBalanceSummary($employee, (int) now()->year) : null;

        return view('employee.leave_requests.index', [
            'requests' => $requests,
            'hasEmployeeProfile' => $employee !== null,
            'leaveBalance' => $leaveBalance,
        ]);
    }

    public function create(Request $request, AnnualLeaveService $annualLeaveService): View|RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return redirect()->route('employee.leave-requests.index')
                ->with('error', 'Tài khoản của bạn chưa được liên kết với hồ sơ nhân viên để tạo đơn nghỉ.');
        }

        $leaveBalance = $annualLeaveService->getBalanceSummary($employee, (int) now()->year);

        return view('employee.leave_requests.create', [
            'leaveBalance' => $leaveBalance,
        ]);
    }

    public function store(StoreLeaveRequest $request, AnnualLeaveService $annualLeaveService): RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return redirect()->route('employee.leave-requests.index')->with('error', 'Tài khoản chưa có hồ sơ nhân viên.');
        }

        $leaveRequest = DB::transaction(function () use ($employee, $request, $annualLeaveService) {
            // Serialize submissions for this employee before checking overlap and quota.
            $employee = Employee::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $data = $request->validated();

            $overlap = $employee->leaveRequests()
                ->whereIn('status', ['pending', 'approved'])
                ->where('start_date', '<=', $data['end_date'])
                ->where('end_date', '>=', $data['start_date'])
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'start_date' => 'Khoảng thời gian nghỉ bị trùng với đơn đang chờ hoặc đã duyệt.',
                ]);
            }

            if ($data['leave_type'] === 'annual') {
                $annualLeaveService->validateAndReserveQuota($employee, $data['start_date'], $data['end_date']);
            }

            return $employee->leaveRequests()->create($data);
        });

        $recipients = User::whereIn('role', ['admin', 'hr'])
            ->where('account_status', 'active')
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new LeaveRequestSubmittedNotification($leaveRequest));
        }

        return redirect()->route('employee.leave-requests.index')->with('success', 'Đã gửi đơn xin nghỉ.');
    }

    public function show(Request $request, LeaveRequest $leaveRequest, AnnualLeaveService $annualLeaveService): View
    {
        abort_unless($leaveRequest->employee_id === $request->user()->employee?->id, 403);

        $leaveBalance = null;
        if ($leaveRequest->leave_type === 'annual') {
            $year = (int) $leaveRequest->start_date->year;
            $leaveBalance = $annualLeaveService->getBalanceSummary($leaveRequest->employee, $year);
        }

        return view('employee.leave_requests.show', [
            'leaveRequest' => $leaveRequest->load(['employee.user', 'employee.department', 'reviewer.employee']),
            'leaveBalance' => $leaveBalance,
        ]);
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
