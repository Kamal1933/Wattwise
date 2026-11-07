<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appliance extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'category_id',
        'name',
        'power_watt',
        'quantity',
        'daily_hours',
        'days_per_month',
        'usage_factor',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'power_watt' => 'decimal:2',
            'quantity' => 'integer',
            'daily_hours' => 'decimal:2',
            'days_per_month' => 'integer',
            'usage_factor' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ApplianceCategory::class, 'category_id');
    }

    public function calculationItems(): HasMany
    {
        return $this->hasMany(CalculationItem::class);
    }
}
