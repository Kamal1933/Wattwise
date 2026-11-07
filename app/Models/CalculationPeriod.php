<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalculationPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'period_name',
        'start_date',
        'end_date',
        'total_estimated_kwh',
        'total_estimated_cost',
        'applied_rate_per_kwh',
        'calculation_assumptions',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_estimated_kwh' => 'decimal:3',
            'total_estimated_cost' => 'decimal:2',
            'applied_rate_per_kwh' => 'decimal:4',
            'calculation_assumptions' => 'array',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CalculationItem::class, 'calculation_period_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(RecommendationHistory::class, 'calculation_period_id');
    }

    public function actualMeterReadings(): HasMany
    {
        return $this->hasMany(ActualMeterReading::class, 'calculation_period_id');
    }
}
