<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    protected $fillable = ['period_month', 'status', 'finalized_by', 'finalized_at'];

    public function setPeriodMonthAttribute(string $value): void
    {
        $this->attributes['period_month'] = substr($value, 0, 10);
    }

    protected function casts(): array
    {
        return ['period_month' => 'date', 'finalized_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(function (PayrollPeriod $period) {
            if (PayrollPeriod::whereKey($period->id)->where('status', 'finalized')->exists()) {
                throw new DomainException('Kỳ lương đã chốt không thể sửa.');
            }
        });
        static::deleting(function (PayrollPeriod $period) {
            if (PayrollPeriod::whereKey($period->id)->where('status', 'finalized')->exists()) {
                throw new DomainException('Kỳ lương đã chốt không thể xóa.');
            }
        });
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function finalizer()
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }
}
