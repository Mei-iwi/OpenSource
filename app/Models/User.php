<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected static function booted(): void
    {
        static::updated(function (User $user) {
            if ($user->wasChanged('role')) {
                $from = $user->getRawOriginal('role');
                $to = $user->role;
                $label = fn ($role) => $role === 'employee' ? 'emp' : $role;
                $user->roleChanges()->create([
                    'changed_by' => auth()->id(),
                    'from_role' => $from,
                    'to_role' => $to,
                    'note' => $label($from).' -> '.$label($to),
                ]);
            }
        });
    }

    public function roleChanges()
    {
        return $this->hasMany(UserRoleChange::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'account_status',
        'avatar_path',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isHr(): bool
    {
        return $this->role === 'hr';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles, true);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $path = $this->avatar_path ?: $this->employee?->avatar_path;

        return $path ? route('avatar.show', $this) : null;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
