<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminMessage extends Model
{
    use HasFactory;

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

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
