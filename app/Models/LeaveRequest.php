<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'leave_type', 'start_date', 'end_date', 'reason', 'status', 'reviewed_by', 'reviewed_at', 'review_note'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'reviewed_at' => 'datetime'];
    }

    public function employee() { return $this->belongsTo(Employee::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
