<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'calculation_period_id',
        'appliance_id',
        'power_watt',
        'quantity',
        'daily_hours',
        'days_per_month',
        'usage_factor',
        'monthly_kwh',
        'monthly_cost',
        'percentage_of_total',
    ];

    protected function casts(): array
    {
        return [
            'power_watt' => 'decimal:2',
            'quantity' => 'integer',
            'daily_hours' => 'decimal:2',
            'days_per_month' => 'integer',
            'usage_factor' => 'decimal:3',
            'monthly_kwh' => 'decimal:3',
            'monthly_cost' => 'decimal:2',
            'percentage_of_total' => 'decimal:2',
        ];
    }

    public function calculationPeriod(): BelongsTo
    {
        return $this->belongsTo(CalculationPeriod::class);
    }

    public function appliance(): BelongsTo
    {
        return $this->belongsTo(Appliance::class);
    }
}
