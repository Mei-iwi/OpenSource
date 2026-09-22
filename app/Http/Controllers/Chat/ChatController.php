<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatChannel;
use App\Models\Department;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }

    /**
     * Chuyển hướng đến kênh mặc định hoặc kênh đầu tiên của người dùng.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $channels = $this->chatService->getUserChannels($user);

        $targetChannel = $channels->firstWhere('is_default', true) ?? $channels->first();

        if ($targetChannel) {
            return redirect()->route('chat.channels.show', $targetChannel->slug);
        }

        return view('chat.index', [
            'channels' => $channels,
            'currentChannel' => null,
            'messages' => collect(),
            'departments' => collect(),
            'allUsers' => collect(),
        ]);
    }

    /**
     * Hiển thị nội dung kênh chat được chọn.
     */
    public function show(Request $request, ChatChannel $channel): View
    {
        $user = $request->user();

        // Kiểm tra phân quyền truy cập kênh chống IDOR
        $this->authorize('view', $channel);

        // Đánh dấu đã đọc cho kênh đang mở
        $this->chatService->markAsRead($channel, $user);

        $channels = $this->chatService->getUserChannels($user);
        $messages = $this->chatService->getMessages($channel, limit: 50);

        $channel->load(['department', 'creator', 'members.user.employee.department']);

        $departments = ($user->isAdmin() || $user->isHr())
            ? Department::orderBy('name')->get()
            : collect();

        $allUsers = User::where('account_status', 'active')
            ->where('id', '!=', $user->id)
            ->with(['employee.department'])
            ->orderBy('name')
            ->get();

        return view('chat.index', [
            'channels' => $channels,
            'currentChannel' => $channel,
            'messages' => $messages,
            'departments' => $departments,
            'allUsers' => $allUsers,
        ]);
    }

    /**
     * API đánh dấu đã đọc một kênh.
     */
    public function markAsRead(Request $request, ChatChannel $channel): JsonResponse
    {
        $this->authorize('view', $channel);

        $this->chatService->markAsRead($channel, $request->user());

        return response()->json([
            'success' => true,
            'channel_id' => $channel->id,
        ]);
    }

    /**
     * API lấy tổng hợp số tin nhắn chưa đọc cho từng kênh.
     */
    public function unreadSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        $channels = ChatChannel::forUser($user)->get();
        $unreadMap = $this->chatService->getUnreadCountsMap($channels, $user);

        return response()->json([
            'unread_counts' => $unreadMap,
            'total_unread' => array_sum($unreadMap),
        ]);
    }
}
