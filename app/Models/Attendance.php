<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['employee_id', 'work_date', 'check_in', 'check_out', 'status', 'note', 'check_in_photo_path', 'check_out_photo_path', 'check_in_method', 'check_out_method'];

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['month'] ?? null, fn ($q, $value) => $q->whereMonth('work_date', $value))
            ->when($filters['year'] ?? null, fn ($q, $value) => $q->whereYear('work_date', $value))
            ->when($filters['department_id'] ?? null, fn ($q, $value) => $q->whereHas('employee', fn ($employee) => $employee->where('department_id', $value)))
            ->when($filters['employee_id'] ?? null, fn ($q, $value) => $q->where('employee_id', $value))
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->when($filters['employment_status'] ?? null, fn ($q, $value) => $q->whereHas('employee', fn ($employee) => $employee->where('employment_status', $value)))
            ->when($filters['search'] ?? null, fn ($q, $value) => $q->whereHas('employee', fn ($employee) => $employee
                ->where(fn ($match) => $match->where('employee_code', 'like', "%{$value}%")
                    ->orWhereHas('user', fn ($user) => $user->where(fn ($person) => $person->where('name', 'like', "%{$value}%")->orWhere('email', 'like', "%{$value}%"))))));
    }

    protected function casts(): array
    {
        return ['work_date' => 'date'];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
