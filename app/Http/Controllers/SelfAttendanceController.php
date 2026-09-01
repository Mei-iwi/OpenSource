<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelfAttendanceRequest;
use App\Models\Attendance;
use App\Models\Employee;
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
    public function index(Request $request): View
    {
        $employee = $this->employee($request)->load(['user', 'department']);
        $todayAttendance = $employee?->attendances()->whereDate('work_date', today())->first();

        return view('attendance.self', compact('employee', 'todayAttendance'));
    }

    public function checkIn(SelfAttendanceRequest $request): RedirectResponse
    {
        $employee = $this->employee($request);
        if (Attendance::where('employee_id', $employee->id)->whereDate('work_date', today())->exists()) {
            throw ValidationException::withMessages(['photo' => 'Bạn đã chấm công vào hôm nay.']);
        }

        $path = $this->storeProof($request, $employee, 'check-in');
        try {
            DB::transaction(fn () => Attendance::create([
                'employee_id' => $employee->id,
                'work_date' => today(),
                'check_in' => now()->format('H:i:s'),
                'status' => 'present',
                'check_in_photo_path' => $path,
                'check_in_method' => $request->validated('method'),
            ]));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            if ($exception instanceof QueryException) {
                throw ValidationException::withMessages(['photo' => 'Bản ghi chấm công hôm nay đã tồn tại.']);
            }
            throw $exception;
        }

        return redirect()->route('me.attendance.index')->with('success', 'Đã chấm công vào và lưu ảnh xác nhận.');
    }

    public function checkOut(SelfAttendanceRequest $request): RedirectResponse
    {
        $employee = $this->employee($request);
        $attendance = Attendance::where('employee_id', $employee->id)->whereDate('work_date', today())->first();
        if (! $attendance) {
            throw ValidationException::withMessages(['photo' => 'Bạn cần chấm công vào trước khi chấm công ra.']);
        }
        if ($attendance->check_out) {
            throw ValidationException::withMessages(['photo' => 'Bạn đã chấm công ra hôm nay.']);
        }

        $path = $this->storeProof($request, $employee, 'check-out');
        try {
            DB::transaction(fn () => $attendance->update([
                'check_out' => now()->format('H:i:s'),
                'check_out_photo_path' => $path,
                'check_out_method' => $request->validated('method'),
            ]));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect()->route('me.attendance.index')->with('success', 'Đã chấm công ra và lưu ảnh xác nhận.');
    }

    private function employee(Request $request): Employee
    {
        abort_unless($request->user()->employee, 403, 'Tài khoản chưa có hồ sơ nhân viên.');

        return $request->user()->employee;
    }

    private function storeProof(SelfAttendanceRequest $request, Employee $employee, string $label): string
    {
        $photo = $request->file('photo');
        $directory = sprintf('attendance-proofs/employee-%d/%s/%s', $employee->id, now()->format('Y'), now()->format('m'));
        $filename = sprintf('%s-%s.%s', $label, Str::uuid(), $photo->extension());

        return Storage::disk('local')->putFileAs($directory, $photo, $filename);
    }
}
