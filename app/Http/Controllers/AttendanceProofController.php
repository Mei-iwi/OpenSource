<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceProofController extends Controller
{
    public function show(Request $request, Attendance $attendance, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['check-in', 'check-out'], true), 404);

        $user = $request->user();
        $isManager = $user->hasRole(['admin', 'hr']);
        $isOwner = $user->employee?->is($attendance->employee);
        abort_unless($isManager || $isOwner, 403);

        $path = $type === 'check-in' ? $attendance->check_in_photo_path : $attendance->check_out_photo_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        $stream = Storage::disk('local')->readStream($path);
        abort_unless(is_resource($stream), 404);

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => Storage::disk('local')->mimeType($path) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
