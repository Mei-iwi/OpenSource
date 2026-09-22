<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ChatReactionController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }

    /**
     * Thả hoặc rút lại biểu cảm trên một tin nhắn.
     */
    public function toggle(Request $request, ChatMessage $message): JsonResponse
    {
        // Kiểm tra quyền xem tin nhắn trong kênh
        $this->authorize('view', $message->channel);

        $request->validate([
            'reaction' => ['required', 'string', 'max:10'],
        ]);

        try {
            $reactionsSummary = $this->chatService->toggleReaction(
                $message,
                $request->user(),
                $request->input('reaction')
            );

            return response()->json([
                'success' => true,
                'message_id' => $message->id,
                'reactions' => $reactionsSummary,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
