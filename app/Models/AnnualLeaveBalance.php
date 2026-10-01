<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnualLeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'year',
        'total_days',
        'carried_over_days',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'total_days' => 'float',
            'carried_over_days' => 'float',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the total entitled days for this year (allocated + carried over).
     */
    public function getTotalEntitlementAttribute(): float
    {
        return (float) ($this->total_days + $this->carried_over_days);
    }
}
