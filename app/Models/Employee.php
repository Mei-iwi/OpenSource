<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected static function booted(): void
    {
        static::updating(function (Employee $employee) {
            if ($employee->isDirty('employee_code')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'employee_code' => 'Mã nhân viên được giữ nguyên từ khi tạo hồ sơ.',
                ]);
            }
        });
    }

    protected $fillable = [
        'user_id', 'department_id', 'employee_code', 'phone', 'address', 'date_of_birth',
        'position', 'hire_date', 'employment_status', 'avatar_path',
    ];

    protected function casts(): array
    {
        return ['date_of_birth' => 'date', 'hire_date' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class, 'position', 'name');
    }
}
