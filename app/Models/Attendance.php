<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    protected $fillable = ['employee_id', 'work_date', 'check_in', 'check_out', 'status', 'note', 'check_in_photo_path', 'check_out_photo_path', 'check_in_method', 'check_out_method'];

    protected function casts(): array
    {
        return ['work_date' => 'date'];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
