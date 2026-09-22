<?php

namespace App\Policies;

use App\Models\ChatChannel;
use App\Models\User;

class ChatChannelPolicy
{
    /**
     * Determine whether the user can view any chat channels.
     */
    public function viewAny(User $user): bool
    {
        return $user->account_status === 'active';
    }

    /**
     * Determine whether the user can view the specific channel.
     */
    public function view(User $user, ChatChannel $channel): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        if ($channel->isCompany()) {
            return true;
        }

        return $channel->hasMember($user->id);
    }

    /**
     * Determine whether the user can create channels.
     */
    public function create(User $user): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->isAdmin() || $user->isHr();
    }

    /**
     * Determine whether the user can update the channel.
     */
    public function update(User $user, ChatChannel $channel): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        // Kênh công ty mặc định không được sửa thông tin cấu hình cốt lõi
        if ($channel->is_default || $channel->type === 'company') {
            return false;
        }

        return $user->isAdmin() || $user->isHr();
    }

    /**
     * Determine whether the user can delete the channel.
     */
    public function delete(User $user, ChatChannel $channel): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        // Kênh công ty mặc định không được xóa
        if ($channel->is_default || $channel->type === 'company') {
            return false;
        }

        return $user->isAdmin() || $user->isHr();
    }

    /**
     * Determine whether the user can manage members of the channel.
     */
    public function manageMembers(User $user, ChatChannel $channel): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        // Kênh công ty mặc định không cho phép tùy tiện xóa/sửa danh sách thành viên
        if ($channel->is_default || $channel->type === 'company') {
            return false;
        }

        return $user->isAdmin() || $user->isHr();
    }

    /**
     * Determine whether the user can send messages to the channel.
     */
    public function sendMessage(User $user, ChatChannel $channel): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        if ($channel->isCompany()) {
            return true;
        }

        return $channel->hasMember($user->id);
    }
}
