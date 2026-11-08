<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    protected $fillable = [
        'payroll_period_id', 'employee_id', 'employee_name', 'employee_code',
        'scheduled_days', 'worked_days', 'paid_leave_days', 'unpaid_leave_days', 'unpaid_days',
        'base_salary', 'base_pay', 'attendance_deduction', 'allowance',
        'fixed_deduction', 'gross_pay', 'net_pay',
    ];

    protected $hidden = [
        'base_salary', 'base_pay', 'attendance_deduction', 'allowance',
        'fixed_deduction', 'gross_pay', 'net_pay',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'base_pay' => 'decimal:2',
            'attendance_deduction' => 'decimal:2',
            'allowance' => 'decimal:2',
            'fixed_deduction' => 'decimal:2',
            'gross_pay' => 'decimal:2',
            'net_pay' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Payslip $payslip) {
            if (PayrollPeriod::whereKey($payslip->payroll_period_id)->where('status', 'finalized')->exists()) {
                throw new DomainException('Phiếu lương đã chốt không thể sửa.');
            }
        });
        static::deleting(function (Payslip $payslip) {
            if (PayrollPeriod::whereKey($payslip->payroll_period_id)->where('status', 'finalized')->exists()) {
                throw new DomainException('Phiếu lương đã chốt không thể xóa.');
            }
        });
    }

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
