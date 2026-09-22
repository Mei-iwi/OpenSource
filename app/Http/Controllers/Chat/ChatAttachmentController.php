<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatMessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatAttachmentController extends Controller
{
    /**
     * Tải về hoặc xem trực tiếp tệp đính kèm an toàn, chống IDOR và chống XSS/MIME sniff.
     */
    public function show(Request $request, ChatMessageAttachment $attachment)
    {
        $this->authorize('view', $attachment);

        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'Tệp tin đính kèm không tồn tại.');
        }

        $headers = [
            'Content-Type' => $attachment->mime_type ?? 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
        ];

        // Nếu là ảnh và yêu cầu xem inline
        if ($attachment->is_image || $request->query('inline')) {
            return Storage::disk('local')->response(
                $attachment->file_path,
                $attachment->file_name,
                array_merge($headers, [
                    'Content-Disposition' => 'inline; filename="' . rawurlencode($attachment->file_name) . '"',
                ])
            );
        }

        return Storage::disk('local')->download(
            $attachment->file_path,
            $attachment->file_name,
            $headers
        );
    }
}
