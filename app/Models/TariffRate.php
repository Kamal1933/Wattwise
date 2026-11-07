<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TariffRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'power_limit_va',
        'rate_per_kwh',
        'base_fee',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'power_limit_va' => 'decimal:2',
            'rate_per_kwh' => 'decimal:4',
            'base_fee' => 'decimal:2',
            'valid_from' => 'date',
            'valid_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function households(): HasMany
    {
        return $this->hasMany(Household::class, 'tariff_id');
    }
}
