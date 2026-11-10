<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AdminMessage extends Model
{
    protected $fillable = [
        'admin_id', 'subject', 'body', 'audience', 'recipient_user_ids', 'recipient_count', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'recipient_user_ids' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    protected function subject(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): string => str_starts_with($value ?? '', 'enc:v1:')
                ? Crypt::decryptString(substr($value, 7)) : ($value ?? ''),
            set: fn (?string $value): string => 'enc:v1:'.Crypt::encryptString($value ?? ''),
        );
    }

    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): string => str_starts_with($value ?? '', 'enc:v1:')
                ? Crypt::decryptString(substr($value, 7)) : ($value ?? ''),
            set: fn (?string $value): string => 'enc:v1:'.Crypt::encryptString($value ?? ''),
        );
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
