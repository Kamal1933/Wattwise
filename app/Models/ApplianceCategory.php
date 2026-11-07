<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplianceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'icon',
        'description',
    ];

    public function appliances(): HasMany
    {
        return $this->hasMany(Appliance::class, 'category_id');
    }
}
