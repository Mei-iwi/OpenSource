<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'message', 'image_path', 'display_seconds', 'repeat_seconds', 'is_active'];

    protected function casts(): array
    {
        return [
            'display_seconds' => 'integer',
            'repeat_seconds' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
