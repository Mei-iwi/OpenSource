<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Requests\UpdateChatMessageRequest;
use App\Models\ChatChannel;
use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }

    /**
     * Lấy danh sách tin nhắn của kênh (hỗ trợ polling tin mới qua after_id).
     */
    public function index(Request $request, ChatChannel $channel): JsonResponse
    {
        $this->authorize('view', $channel);

        $afterId = $request->query('after_id') ? (int) $request->query('after_id') : null;
        $messages = $this->chatService->getMessages($channel, $afterId, limit: 50);

        return response()->json([
            'channel_id' => $channel->id,
            'messages' => $messages->map(fn (ChatMessage $m) => [
                'id' => $m->id,
                'user_id' => $m->user_id,
                'sender_name' => $m->user?->name ?? 'Người dùng',
                'sender_avatar' => $m->user?->avatar_url,
                'sender_role' => $m->user?->role,
                'sender_position' => $m->user?->employee?->position,
                'sender_department' => $m->user?->employee?->department?->name,
                'message' => e($m->message),
                'raw_message' => $m->message,
                'is_me' => $m->user_id === $request->user()->id,
                'can_edit' => $request->user()->can('update', $m),
                'can_delete' => $request->user()->can('delete', $m),
                'edited' => $m->isEdited(),
                'created_at_human' => $m->created_at?->format('H:i') ?? '',
                'created_at_full' => $m->created_at?->format('d/m/Y H:i') ?? '',
                'created_at_iso' => $m->created_at?->toIso8601String() ?? '',
            ]),
        ]);
    }

    /**
     * Gửi tin nhắn mới vào kênh.
     */
    public function store(StoreChatMessageRequest $request, ChatChannel $channel): JsonResponse|RedirectResponse
    {
        $text = $request->validated()['message'];

        $message = $this->chatService->sendMessage(
            $channel,
            $request->user(),
            $text
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'user_id' => $message->user_id,
                    'sender_name' => $message->user?->name,
                    'sender_avatar' => $message->user?->avatar_url,
                    'sender_role' => $message->user?->role,
                    'sender_position' => $message->user?->employee?->position,
                    'sender_department' => $message->user?->employee?->department?->name,
                    'message' => e($message->message),
                    'raw_message' => $message->message,
                    'is_me' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'edited' => false,
                    'created_at_human' => $message->created_at?->format('H:i'),
                    'created_at_full' => $message->created_at?->format('d/m/Y H:i'),
                    'created_at_iso' => $message->created_at?->toIso8601String(),
                ],
            ], 201);
        }

        return redirect()
            ->route('chat.channels.show', $channel->slug)
            ->with('success', 'Đã gửi tin nhắn.');
    }

    /**
     * Chỉnh sửa tin nhắn của chính mình.
     */
    public function update(UpdateChatMessageRequest $request, ChatMessage $message): JsonResponse|RedirectResponse
    {
        $message->update([
            'message' => $request->validated()['message'],
            'edited_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'message' => e($message->message),
                    'raw_message' => $message->message,
                    'edited' => true,
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Đã cập nhật tin nhắn.');
    }

    /**
     * Xóa tin nhắn (người gửi hoặc Admin/HR).
     */
    public function destroy(Request $request, ChatMessage $message): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $message);

        $message->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message_id' => $message->id,
                'message' => 'Đã xóa tin nhắn thành công.',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Đã xóa tin nhắn thành công.');
    }
}
