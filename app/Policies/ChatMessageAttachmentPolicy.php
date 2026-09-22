<?php

namespace App\Policies;

use App\Models\ChatMessageAttachment;
use App\Models\User;

class ChatMessageAttachmentPolicy
{
    /**
     * Determine whether the user can view/download the attachment.
     */
    public function view(User $user, ChatMessageAttachment $attachment): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        $message = $attachment->message;
        if (! $message) {
            return false;
        }

        $channel = $message->channel;
        if (! $channel) {
            return false;
        }

        if ($channel->isCompany()) {
            return true;
        }

        return $channel->hasMember($user->id);
    }
}
