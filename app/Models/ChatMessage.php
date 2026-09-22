<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'channel_id',
        'user_id',
        'message',
        'edited_at',
    ];

    protected $appends = [
        'formatted_html',
        'reactions_summary',
    ];

    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChatChannel::class, 'channel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reactions()
    {
        return $this->hasMany(ChatMessageReaction::class, 'message_id');
    }

    public function attachments()
    {
        return $this->hasMany(ChatMessageAttachment::class, 'message_id');
    }

    public function isEdited(): bool
    {
        return $this->edited_at !== null;
    }

    public function getReactionsSummaryAttribute(): array
    {
        $currentUserId = auth()->id();
        $grouped = [];
        if (! $this->relationLoaded('reactions')) {
            return [];
        }

        foreach ($this->reactions as $r) {
            $emoji = $r->reaction;
            if (! isset($grouped[$emoji])) {
                $grouped[$emoji] = [
                    'reaction' => $emoji,
                    'count' => 0,
                    'users' => [],
                    'user_reacted' => false,
                ];
            }
            $grouped[$emoji]['count']++;
            $grouped[$emoji]['users'][] = $r->user?->name ?? 'Người dùng';
            if ($currentUserId && $r->user_id === $currentUserId) {
                $grouped[$emoji]['user_reacted'] = true;
            }
        }

        return array_values($grouped);
    }

    public function getFormattedHtmlAttribute(): string
    {
        // 1. Luôn escape HTML trước tiên để chống triệt để XSS
        $text = htmlspecialchars($this->message ?? '', ENT_QUOTES, 'UTF-8');

        // 2. Bold: **text**
        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);

        // 3. Italic: *text*
        $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $text);

        // 4. Strikethrough: ~~text~~
        $text = preg_replace('/~~(.+?)~~/s', '<del>$1</del>', $text);

        // 5. Quote: > text
        $text = preg_replace('/^&gt;\s?(.*)$/m', '<blockquote class="border-l-2 border-indigo-500 pl-2 italic my-1 opacity-80">$1</blockquote>', $text);

        // 6. Bullet list: - item hoặc • item
        $text = preg_replace('/^[•\-\*]\s+(.*)$/m', '<li class="ml-4 list-disc">$1</li>', $text);

        // 7. Number list: 1. item
        $text = preg_replace('/^(\d+)\.\s+(.*)$/m', '<li class="ml-4 list-decimal">$2</li>', $text);

        // 8. Tự động nhận diện link hợp lệ và an toàn (chống javascript: và XSS)
        $text = preg_replace_callback(
            '/(https?:\/\/[^\s<]+)/i',
            function ($matches) {
                $url = $matches[1];
                return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" class="underline font-semibold hover:opacity-80 break-all text-indigo-600 dark:text-indigo-400">' . $url . '</a>';
            },
            $text
        );

        // 9. Giữ xuống dòng
        return nl2br($text);
    }
}
