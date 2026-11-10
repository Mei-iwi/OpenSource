<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Employee extends Model
{
    protected static function booted(): void
    {
        static::updating(function (Employee $employee) {
            if ($employee->isDirty('employee_code')) {
                throw ValidationException::withMessages([
                    'employee_code' => 'Mã nhân viên được giữ nguyên từ khi tạo hồ sơ.',
                ]);
            }
        });

        static::updated(function (Employee $employee) {
            if ($employee->wasChanged('department_id')) {
                $oldDepartmentId = $employee->getRawOriginal('department_id');
                if ($oldDepartmentId) {
                    Department::where('id', $oldDepartmentId)
                        ->where('manager_id', $employee->id)
                        ->update(['manager_id' => null]);
                }
            }
        });

        static::deleting(function (Employee $employee) {
            Department::where('manager_id', $employee->id)->update(['manager_id' => null]);
        });
    }

    protected $fillable = [
        'user_id', 'department_id', 'employee_code', 'phone', 'address', 'date_of_birth',
        'position', 'hire_date', 'contract_type', 'contract_start_date', 'contract_end_date',
        'identity_number', 'tax_code', 'social_insurance_number',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
        'employment_status', 'avatar_path',
    ];

    protected $hidden = [
        'face_reference_path',
        'identity_number', 'tax_code', 'social_insurance_number',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'contract_start_date' => 'date',
            'contract_end_date' => 'date',
            'identity_number' => 'encrypted',
            'tax_code' => 'encrypted',
            'social_insurance_number' => 'encrypted',
            'emergency_contact_name' => 'encrypted',
            'emergency_contact_phone' => 'encrypted',
            'emergency_contact_relationship' => 'encrypted',
        ];
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

    public function managedDepartments()
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class, 'position', 'name');
    }

    public function annualLeaveBalances()
    {
        return $this->hasMany(AnnualLeaveBalance::class);
    }

    public function salary()
    {
        return $this->hasOne(EmployeeSalary::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }
}
