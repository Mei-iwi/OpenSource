<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = ['employee_id', 'leave_type', 'start_date', 'end_date', 'reason', 'status', 'reviewed_by', 'reviewed_at', 'review_note'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'reviewed_at' => 'datetime'];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getReviewerDisplayAttribute(): ?string
    {
        if (! $this->reviewer) {
            return null;
        }

        $roleLabel = match ($this->reviewer->role) {
            'admin' => 'Quản trị viên',
            'hr' => 'Nhân sự',
            default => 'Nhân viên',
        };

        $code = $this->reviewer->employee?->employee_code;

        return $code ? "{$this->reviewer->name} ({$roleLabel} · {$code})" : "{$this->reviewer->name} ({$roleLabel})";
    }
}
