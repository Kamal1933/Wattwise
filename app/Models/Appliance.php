<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Appliance extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'category_id',
        'name',
        'photo_path',
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

    /**
     * Public URL of the uploaded photo, or null when the appliance has none.
     * Built from the current host (not APP_URL) so it also works on Laragon's *.test domains.
     */
    public function photoUrl(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }

    /**
     * Remove the stored photo file, if any.
     */
    public function deletePhoto(): void
    {
        if ($this->photo_path) {
            Storage::disk('public')->delete($this->photo_path);
        }
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
