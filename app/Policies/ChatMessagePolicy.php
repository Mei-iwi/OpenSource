<?php

namespace App\Policies;

use App\Models\ChatMessage;
use App\Models\User;

class ChatMessagePolicy
{
    /**
     * Determine whether the user can update the message.
     */
    public function update(User $user, ChatMessage $message): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->id === $message->user_id;
    }

    /**
     * Determine whether the user can delete the message.
     */
    public function delete(User $user, ChatMessage $message): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        // Người gửi hoặc Admin / HR có thể xóa tin nhắn
        return $user->id === $message->user_id || $user->isAdmin() || $user->isHr();
    }
}
