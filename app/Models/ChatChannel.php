<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatChannel extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'department_id',
        'created_by',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ChatChannelMember::class, 'channel_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_channel_members', 'channel_id', 'user_id')
            ->withPivot('joined_at', 'last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'channel_id');
    }

    public function isCompany(): bool
    {
        return $this->is_default || $this->type === 'company';
    }

    public function hasMember(int $userId): bool
    {
        if ($this->isCompany()) {
            return true;
        }

        if ($this->relationLoaded('members')) {
            return $this->members->contains('user_id', $userId);
        }

        return $this->members()->where('user_id', $userId)->exists();
    }

    public function unreadCountFor(int $userId): int
    {
        $member = $this->relationLoaded('members')
            ? $this->members->firstWhere('user_id', $userId)
            : $this->members()->where('user_id', $userId)->first();

        // If not a member and not company channel, 0 unread
        if (! $member && ! $this->isCompany()) {
            return 0;
        }

        $lastReadAt = $member?->last_read_at;

        $query = $this->messages()->where('user_id', '!=', $userId);
        if ($lastReadAt) {
            $query->where('created_at', '>', $lastReadAt);
        }

        return $query->count();
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $sub) use ($user) {
            $sub->where('is_default', true)
                ->orWhere('type', 'company')
                ->orWhereHas('members', fn (Builder $m) => $m->where('user_id', $user->id));
        });
    }
}
