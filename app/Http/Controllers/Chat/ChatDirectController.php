<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatDirectController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }

    /**
     * Mở hoặc tạo mới kênh tin nhắn riêng 1-1 với một đồng nghiệp.
     */
    public function open(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể tạo cuộc trò chuyện với chính mình.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Không thể tạo cuộc trò chuyện với chính mình.');
        }

        if ($user->account_status !== 'active') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản người dùng này hiện không hoạt động.',
                ], 404);
            }

            return redirect()->back()->with('error', 'Tài khoản người dùng này hiện không hoạt động.');
        }

        $channel = $this->chatService->getOrCreateDirectChannel($currentUser, $user);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'channel_id' => $channel->id,
                'channel_slug' => $channel->slug,
                'redirect_url' => route('chat.channels.show', $channel->slug),
            ]);
        }

        return redirect()->route('chat.channels.show', $channel->slug);
    }
}
