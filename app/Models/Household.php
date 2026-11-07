<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Household extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tariff_id',
        'name',
        'address',
        'occupant_count',
    ];

    protected function casts(): array
    {
        return [
            'occupant_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(TariffRate::class, 'tariff_id');
    }

    public function appliances(): HasMany
    {
        return $this->hasMany(Appliance::class);
    }

    public function calculationPeriods(): HasMany
    {
        return $this->hasMany(CalculationPeriod::class);
    }

    public function actualMeterReadings(): HasMany
    {
        return $this->hasMany(ActualMeterReading::class);
    }
}
