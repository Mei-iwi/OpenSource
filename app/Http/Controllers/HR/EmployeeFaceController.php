<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\FaceVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EmployeeFaceController extends Controller
{
    public function store(Request $request, Employee $employee, FaceVerificationService $faces): RedirectResponse
    {
        abort_if($request->user()->isHr() && $employee->user->isAdmin(), 403);
        $request->validate(['face_reference' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072']]);

        $image = $request->file('face_reference');
        $faces->assertSingleFace($image->get());

        $disk = Storage::disk(config('filesystems.attendance_proof_disk'));
        $path = 'face-references/employee-'.$employee->id.'/'.Str::uuid().'.'.$image->extension();
        if (! $disk->put($path, $image->get())) {
            throw new RuntimeException('Could not store face reference.');
        }

        $oldPath = $employee->face_reference_path;
        try {
            $employee->forceFill(['face_reference_path' => $path])->save();
        } catch (Throwable $exception) {
            $disk->delete($path);
            throw $exception;
        }
        if ($oldPath) {
            $disk->delete($oldPath);
        }

        return back()->with('success', 'Đã cập nhật ảnh khuôn mặt mẫu cho nhân viên.');
    }
}
