<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActualMeterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'calculation_period_id',
        'reading_date',
        'meter_start_kwh',
        'meter_end_kwh',
        'actual_kwh_consumed',
        'actual_cost_billed',
        'variance_kwh',
        'variance_pct',
        'receipt_image_url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reading_date' => 'date',
            'meter_start_kwh' => 'decimal:2',
            'meter_end_kwh' => 'decimal:2',
            'actual_kwh_consumed' => 'decimal:2',
            'actual_cost_billed' => 'decimal:2',
            'variance_kwh' => 'decimal:2',
            'variance_pct' => 'decimal:2',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function calculationPeriod(): BelongsTo
    {
        return $this->belongsTo(CalculationPeriod::class);
    }
}
