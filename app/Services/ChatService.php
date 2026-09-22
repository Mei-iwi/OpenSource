<?php

namespace App\Services;

use App\Events\ChatMessageSent;
use App\Models\ChatChannel;
use App\Models\ChatChannelMember;
use App\Models\ChatMessage;
use App\Models\ChatMessageAttachment;
use App\Models\ChatMessageReaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ChatService
{
    public const ALLOWED_REACTIONS = ['👍', '❤️', '😂', '😮', '😢', '🎉'];

    /**
     * Lấy danh sách các kênh khả dụng cho người dùng cùng với số tin chưa đọc.
     *
     * @return Collection<int, ChatChannel>
     */
    public function getUserChannels(User $user): Collection
    {
        $channels = ChatChannel::forUser($user)
            ->with(['department', 'users.employee.department'])
            ->orderBy('is_default', 'desc')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $unreadMap = $this->getUnreadCountsMap($channels, $user);

        foreach ($channels as $channel) {
            $channel->setAttribute('unread_count', $unreadMap[$channel->id] ?? 0);
        }

        return $channels;
    }

    /**
     * Tính toán unread counts cho tập hợp kênh của người dùng mà không bị N+1.
     */
    public function getUnreadCountsMap(Collection $channels, User $user): array
    {
        if ($channels->isEmpty()) {
            return [];
        }

        $channelIds = $channels->pluck('id')->all();
        $memberships = ChatChannelMember::where('user_id', $user->id)
            ->whereIn('channel_id', $channelIds)
            ->get()
            ->keyBy('channel_id');

        $unreadMap = [];

        foreach ($channels as $channel) {
            $member = $memberships->get($channel->id);
            $lastReadAt = $member?->last_read_at;

            // Nếu không phải là member và không phải company channel thì unread = 0
            if (! $member && ! $channel->isCompany()) {
                $unreadMap[$channel->id] = 0;
                continue;
            }

            $query = ChatMessage::where('channel_id', $channel->id)
                ->where('user_id', '!=', $user->id);

            if ($lastReadAt) {
                $query->where('created_at', '>', $lastReadAt);
            }

            $unreadMap[$channel->id] = $query->count();
        }

        return $unreadMap;
    }

    /**
     * Lấy tin nhắn trong kênh. Nếu có afterId thì lấy các tin nhắn mới hơn (cho polling).
     */
    public function getMessages(ChatChannel $channel, ?int $afterId = null, int $limit = 50)
    {
        $query = $channel->messages()
            ->with(['user.employee.department', 'attachments', 'reactions.user'])
            ->orderBy('id', 'asc');

        if ($afterId) {
            return $query->where('id', '>', $afterId)->get();
        }

        // Lấy $limit tin nhắn gần nhất
        $latestMessages = $channel->messages()
            ->with(['user.employee.department', 'attachments', 'reactions.user'])
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return $latestMessages;
    }

    /**
     * Gửi tin nhắn mới vào kênh, hỗ trợ đính kèm tệp và ảnh.
     *
     * @param  array<UploadedFile>  $uploadedFiles
     */
    public function sendMessage(ChatChannel $channel, User $sender, string $text = '', array $uploadedFiles = []): ChatMessage
    {
        return DB::transaction(function () use ($channel, $sender, $text, $uploadedFiles) {
            $message = ChatMessage::create([
                'channel_id' => $channel->id,
                'user_id' => $sender->id,
                'message' => $text ?? '',
            ]);

            // Lưu các file đính kèm an toàn vào disk private
            foreach ($uploadedFiles as $file) {
                if (! ($file instanceof UploadedFile) || ! $file->isValid()) {
                    continue;
                }

                $mimeType = $file->getMimeType() ?? 'application/octet-stream';
                $isImage = str_starts_with($mimeType, 'image/');
                $storedPath = $file->store('chat_attachments/' . date('Y/m'), 'local');

                $message->attachments()->create([
                    'file_path' => $storedPath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $mimeType,
                    'is_image' => $isImage,
                ]);
            }

            // Cập nhật mốc đọc tin nhắn cho chính người gửi
            ChatChannelMember::updateOrCreate(
                ['channel_id' => $channel->id, 'user_id' => $sender->id],
                ['last_read_at' => now()]
            );

            // Bắn event broadcast cho realtime nếu có cấu hình
            try {
                event(new ChatMessageSent($message));
            } catch (\Throwable $e) {
                // Tiếp tục bình thường nếu driver broadcast không bật
            }

            return $message->load(['user.employee.department', 'attachments', 'reactions.user']);
        });
    }

    /**
     * Tìm hoặc khởi tạo kênh tin nhắn riêng 1-1 giữa 2 người dùng (idempotent, không trùng lặp).
     */
    public function getOrCreateDirectChannel(User $userA, User $userB): ChatChannel
    {
        if ($userA->id === $userB->id) {
            throw new InvalidArgumentException('Không thể tạo kênh nhắn tin với chính mình.');
        }

        $minId = min($userA->id, $userB->id);
        $maxId = max($userA->id, $userB->id);
        $slug = "dm-{$minId}-{$maxId}";

        return DB::transaction(function () use ($userA, $userB, $slug) {
            $channel = ChatChannel::where('slug', $slug)
                ->where('type', 'direct')
                ->first();

            if (! $channel) {
                $channel = ChatChannel::create([
                    'name' => "{$userA->name} & {$userB->name}",
                    'slug' => $slug,
                    'type' => 'direct',
                    'is_default' => false,
                    'created_by' => $userA->id,
                ]);

                ChatChannelMember::firstOrCreate(
                    ['channel_id' => $channel->id, 'user_id' => $userA->id],
                    ['joined_at' => now(), 'last_read_at' => now()]
                );

                ChatChannelMember::firstOrCreate(
                    ['channel_id' => $channel->id, 'user_id' => $userB->id],
                    ['joined_at' => now()]
                );
            }

            return $channel->load(['users.employee.department']);
        });
    }

    /**
     * Thả hoặc hủy thả cảm xúc trên tin nhắn.
     */
    public function toggleReaction(ChatMessage $message, User $user, string $reaction): array
    {
        if (! in_array($reaction, self::ALLOWED_REACTIONS, true)) {
            throw new InvalidArgumentException('Biểu cảm không hợp lệ.');
        }

        $existing = ChatMessageReaction::where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->where('reaction', $reaction)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            ChatMessageReaction::firstOrCreate([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'reaction' => $reaction,
            ]);
        }

        $message->load('reactions.user');

        return $message->reactions_summary;
    }

    /**
     * Đánh dấu đã đọc tin nhắn của kênh.
     */
    public function markAsRead(ChatChannel $channel, User $user): void
    {
        ChatChannelMember::updateOrCreate(
            ['channel_id' => $channel->id, 'user_id' => $user->id],
            ['last_read_at' => now()]
        );
    }

    /**
     * Tạo kênh mới kèm danh sách thành viên.
     */
    public function createChannel(array $data, User $creator): ChatChannel
    {
        return DB::transaction(function () use ($data, $creator) {
            $slug = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

            $channel = ChatChannel::create([
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'type' => $data['type'] ?? 'group',
                'department_id' => $data['department_id'] ?? null,
                'created_by' => $creator->id,
                'is_default' => false,
            ]);

            // Người tạo tự động là thành viên kênh
            ChatChannelMember::firstOrCreate(
                ['channel_id' => $channel->id, 'user_id' => $creator->id],
                ['joined_at' => now(), 'last_read_at' => now()]
            );

            // Nếu là kênh phòng ban và không chỉ định thành viên thủ công, tự động thêm nhân viên phòng ban
            if ($channel->type === 'department' && $channel->department_id && empty($data['members'])) {
                $deptEmployees = User::whereHas('employee', fn ($q) => $q->where('department_id', $channel->department_id))
                    ->where('account_status', 'active')
                    ->pluck('id');

                foreach ($deptEmployees as $empUserId) {
                    ChatChannelMember::firstOrCreate(
                        ['channel_id' => $channel->id, 'user_id' => $empUserId],
                        ['joined_at' => now(), 'last_read_at' => now()]
                    );
                }
            }

            // Thêm các thành viên được chọn thủ công
            if (! empty($data['members']) && is_array($data['members'])) {
                foreach ($data['members'] as $userId) {
                    ChatChannelMember::firstOrCreate(
                        ['channel_id' => $channel->id, 'user_id' => (int) $userId],
                        ['joined_at' => now(), 'last_read_at' => now()]
                    );
                }
            }

            return $channel;
        });
    }

    /**
     * Thêm danh sách thành viên vào kênh.
     */
    public function addMembers(ChatChannel $channel, array $userIds): void
    {
        foreach ($userIds as $userId) {
            ChatChannelMember::firstOrCreate(
                ['channel_id' => $channel->id, 'user_id' => (int) $userId],
                ['joined_at' => now(), 'last_read_at' => now()]
            );
        }
    }

    /**
     * Xóa một thành viên khỏi kênh.
     */
    public function removeMember(ChatChannel $channel, int $userId): void
    {
        ChatChannelMember::where('channel_id', $channel->id)
            ->where('user_id', $userId)
            ->delete();
    }
}
