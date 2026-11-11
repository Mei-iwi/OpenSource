<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelfAttendanceRequest;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceLocationService;
use App\Services\AttendanceScheduleService;
use App\Services\FaceVerificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $openAttendance = $employee->attendances()->whereNotNull('check_in')->whereNull('check_out')
            ->where('check_in_method', 'camera')->orderBy('work_date')->first();
        $schedule = $scheduleService->getScheduleSummary(now($timezone));

        return view('attendance.self', compact('employee', 'todayAttendance', 'openAttendance', 'schedule'));
    }

    public function checkIn(SelfAttendanceRequest $request, AttendanceScheduleService $scheduleService, AttendanceLocationService $location, FaceVerificationService $faces): RedirectResponse
    {
        $employee = $this->employee($request);
        $timezone = $scheduleService->getTimezone();
        $now = now($timezone);

        if (! $scheduleService->isCheckinAllowed($now)) {
            throw ValidationException::withMessages(['photo' => 'Hệ thống không cho phép chấm công vào ngày cuối tuần.']);
        }

        $workDate = $now->toDateString();
        if ($employee->attendances()->whereNotNull('check_in')->whereNull('check_out')
            ->where('check_in_method', 'camera')->where('work_date', '<', $workDate)->exists()) {
            throw ValidationException::withMessages(['photo' => 'Hãy chấm công ra cho ca trước trước khi bắt đầu ca mới.']);
        }
        if (Attendance::where('employee_id', $employee->id)->whereDate('work_date', $workDate)->exists()) {
            throw ValidationException::withMessages(['photo' => 'Bạn đã chấm công vào hôm nay.']);
        }

        $distance = $location->distanceMeters((float) $request->validated('latitude'), (float) $request->validated('longitude'), (float) $request->validated('accuracy'));
        $this->verifyIdentity($employee, $request->file('photo')->get(), $faces);
        $recordedAt = now($timezone);
        if ($recordedAt->toDateString() !== $workDate) {
            throw ValidationException::withMessages(['photo' => 'Ngày làm việc vừa thay đổi. Vui lòng chấm công lại.']);
        }
        $status = $scheduleService->determineStatus($recordedAt);
        $checkInTime = $recordedAt->format('H:i:s');

        $path = $this->storeProof($request, $employee, 'check-in');
        try {
            DB::transaction(function () use ($employee, $workDate, $checkInTime, $recordedAt, $status, $path, $distance): void {
                Employee::whereKey($employee->id)->lockForUpdate()->firstOrFail();
                if ($employee->attendances()->whereNotNull('check_in')->whereNull('check_out')
                    ->where('check_in_method', 'camera')->where('work_date', '<', $workDate)->exists()) {
                    throw ValidationException::withMessages(['photo' => 'Hãy chấm công ra cho ca trước trước khi bắt đầu ca mới.']);
                }
                Attendance::create([
                    'employee_id' => $employee->id,
                    'work_date' => $workDate,
                    'check_in' => $checkInTime,
                    'checked_in_at' => $recordedAt->copy()->utc(),
                    'check_in_distance_m' => $distance,
                    'status' => $status,
                    'check_in_photo_path' => $path,
                    'check_in_method' => 'camera',
                ]);
            });
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

    public function checkOut(SelfAttendanceRequest $request, AttendanceScheduleService $scheduleService, AttendanceLocationService $location, FaceVerificationService $faces): RedirectResponse
    {
        $employee = $this->employee($request);
        $timezone = $scheduleService->getTimezone();
        $now = now($timezone);
        $workDate = $now->toDateString();

        $attendance = $employee->attendances()->whereNotNull('check_in')->whereNull('check_out')
            ->where('check_in_method', 'camera')->where('work_date', '<=', $workDate)
            ->orderBy('work_date')->first();
        if (! $attendance) {
            throw ValidationException::withMessages(['photo' => 'Bạn cần chấm công vào trước khi chấm công ra.']);
        }

        $distance = $location->distanceMeters((float) $request->validated('latitude'), (float) $request->validated('longitude'), (float) $request->validated('accuracy'));
        $image = $request->file('photo')->get();
        $this->verifyIdentity($employee, $image, $faces);
        $disk = Storage::disk(config('filesystems.attendance_proof_disk'));
        if (! $attendance->check_in_photo_path || ! $disk->exists($attendance->check_in_photo_path)) {
            throw ValidationException::withMessages(['photo' => 'Không tìm thấy ảnh xác thực của lần chấm công vào.']);
        }
        $faces->assertSameFace($image, $disk->get($attendance->check_in_photo_path));
        $recordedAt = now($timezone);
        $path = $this->storeProof($request, $employee, 'check-out');
        try {
            DB::transaction(function () use ($attendance, $recordedAt, $path, $distance): void {
                $locked = Attendance::whereKey($attendance->id)->lockForUpdate()->firstOrFail();
                if ($locked->check_out) {
                    throw ValidationException::withMessages(['photo' => 'Ca làm này đã chấm công ra.']);
                }
                $locked->update([
                    'check_out' => $recordedAt->format('H:i:s'),
                    'checked_out_at' => $recordedAt->copy()->utc(),
                    'check_out_distance_m' => $distance,
                    'check_out_photo_path' => $path,
                    'check_out_method' => 'camera',
                ]);
            });
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

    private function verifyIdentity(Employee $employee, string $image, FaceVerificationService $faces): void
    {
        $disk = Storage::disk(config('filesystems.attendance_proof_disk'));
        if (! $employee->face_reference_path || ! $disk->exists($employee->face_reference_path)) {
            throw ValidationException::withMessages(['photo' => 'Nhân sự chưa đăng ký khuôn mặt mẫu cho tài khoản của bạn.']);
        }

        $faces->assertSameFace($image, $disk->get($employee->face_reference_path));
    }

    private function storeProof(SelfAttendanceRequest $request, Employee $employee, string $label): string
    {
        $photo = $request->file('photo');
        $directory = sprintf('attendance-proofs/employee-%d/%s/%s', $employee->id, now()->format('Y'), now()->format('m'));
        $filename = sprintf('%s-%s.%s', $label, Str::uuid(), $photo->extension());

        return Storage::disk(config('filesystems.attendance_proof_disk'))->putFileAs($directory, $photo, $filename);
    }
}
