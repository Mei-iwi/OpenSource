<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelfAttendanceRequest;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceScheduleService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SelfAttendanceController extends Controller
{
    public function index(Request $request, AttendanceScheduleService $scheduleService): View
    {
        $employee = $this->employee($request)->load(['user', 'department']);
        $timezone = $scheduleService->getTimezone();
        $today = now($timezone)->toDateString();
        $todayAttendance = $employee->attendances()->whereDate('work_date', $today)->first();
        $schedule = $scheduleService->getScheduleSummary(now($timezone));

        return view('attendance.self', compact('employee', 'todayAttendance', 'schedule'));
    }

    public function checkIn(SelfAttendanceRequest $request, AttendanceScheduleService $scheduleService): RedirectResponse
    {
        $employee = $this->employee($request);
        $timezone = $scheduleService->getTimezone();
        $now = now($timezone);

        if (! $scheduleService->isCheckinAllowed($now)) {
            throw ValidationException::withMessages(['photo' => 'Hệ thống không cho phép chấm công vào ngày cuối tuần.']);
        }

        $workDate = $now->toDateString();
        if (Attendance::where('employee_id', $employee->id)->whereDate('work_date', $workDate)->exists()) {
            throw ValidationException::withMessages(['photo' => 'Bạn đã chấm công vào hôm nay.']);
        }

        $status = $scheduleService->determineStatus($now);
        $checkInTime = $now->format('H:i:s');

        $path = $this->storeProof($request, $employee, 'check-in');
        try {
            Attendance::create([
                'employee_id' => $employee->id,
                'work_date' => $workDate,
                'check_in' => $checkInTime,
                'status' => $status,
                'check_in_photo_path' => $path,
                'check_in_method' => $request->validated('method'),
            ]);
        } catch (Throwable $exception) {
            Storage::disk(config('filesystems.attendance_proof_disk'))->delete($path);
            if ($exception instanceof QueryException) {
                throw ValidationException::withMessages(['photo' => 'Bản ghi chấm công hôm nay đã tồn tại.']);
            }
            throw $exception;
        }

        $message = $status === 'late'
            ? 'Đã chấm công vào (Ghi nhận đi muộn) và lưu ảnh xác nhận.'
            : 'Đã chấm công vào và lưu ảnh xác nhận.';

        return redirect()->route('me.attendance.index')->with('success', $message);
    }

    public function checkOut(SelfAttendanceRequest $request, AttendanceScheduleService $scheduleService): RedirectResponse
    {
        $employee = $this->employee($request);
        $timezone = $scheduleService->getTimezone();
        $now = now($timezone);
        $workDate = $now->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)->whereDate('work_date', $workDate)->first();
        if (! $attendance) {
            throw ValidationException::withMessages(['photo' => 'Bạn cần chấm công vào trước khi chấm công ra.']);
        }
        if ($attendance->check_out) {
            throw ValidationException::withMessages(['photo' => 'Bạn đã chấm công ra hôm nay.']);
        }

        $path = $this->storeProof($request, $employee, 'check-out');
        try {
            $attendance->update([
                'check_out' => $now->format('H:i:s'),
                'check_out_photo_path' => $path,
                'check_out_method' => $request->validated('method'),
            ]);
        } catch (Throwable $exception) {
            Storage::disk(config('filesystems.attendance_proof_disk'))->delete($path);
            throw $exception;
        }

        return redirect()->route('me.attendance.index')->with('success', 'Đã chấm công ra và lưu ảnh xác nhận.');
    }

    private function employee(Request $request): Employee
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Tài khoản chưa có hồ sơ nhân viên.');
        abort_if($employee->employment_status !== 'active', 403, 'Hồ sơ nhân viên không ở trạng thái hoạt động.');

        return $employee;
    }

    private function storeProof(SelfAttendanceRequest $request, Employee $employee, string $label): string
    {
        $photo = $request->file('photo');
        $directory = sprintf('attendance-proofs/employee-%d/%s/%s', $employee->id, now()->format('Y'), now()->format('m'));
        $filename = sprintf('%s-%s.%s', $label, Str::uuid(), $photo->extension());

        return Storage::disk(config('filesystems.attendance_proof_disk'))->putFileAs($directory, $photo, $filename);
    }
}
