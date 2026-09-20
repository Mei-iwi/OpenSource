<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\LeaveAttendanceSyncService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = LeaveRequest::with(['employee.user', 'employee.department'])->when($request->status, fn ($q, $v) => $q->where('status', $v))->when($request->leave_type, fn ($q, $v) => $q->where('leave_type', $v))->when($request->department_id, fn ($q, $v) => $q->whereHas('employee', fn ($e) => $e->where('department_id', $v)))->when($request->employee_id, fn ($q, $v) => $q->where('employee_id', $v))->latest()->paginate(15)->withQueryString();

        return view('hr.leave_requests.index', ['requests' => $requests, 'departments' => Department::orderBy('name')->get(), 'employees' => Employee::with('user')->orderBy('employee_code')->get(), 'pendingCount' => LeaveRequest::where('status', 'pending')->count()]);
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        return view('hr.leave_requests.show', ['leaveRequest' => $leaveRequest->load(['employee.user', 'employee.department', 'reviewer'])]);
    }

    public function review(Request $request, LeaveRequest $leaveRequest, LeaveAttendanceSyncService $syncService): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(function () use ($leaveRequest, $validated, $request, $syncService) {
                $locked = LeaveRequest::whereKey($leaveRequest->id)->lockForUpdate()->firstOrFail();

                if ($locked->status !== 'pending') {
                    throw new DomainException('Đơn đã được xử lý hoặc hủy. Vui lòng tải lại trang.');
                }

                $locked->update([
                    'status' => $validated['status'],
                    'review_note' => $validated['review_note'] ?? null,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);

                if ($validated['status'] === 'approved') {
                    $syncService->sync($locked);
                }
            });
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Có lỗi xảy ra khi xử lý đơn nghỉ và đồng bộ chấm công. Vui lòng thử lại.');
        }

        $message = $validated['status'] === 'approved'
            ? 'Đã duyệt đơn nghỉ và đồng bộ chấm công thành công.'
            : 'Đã từ chối đơn nghỉ.';

        return redirect()->route('hr.leave-requests.show', $leaveRequest)->with('success', $message);
    }
}

