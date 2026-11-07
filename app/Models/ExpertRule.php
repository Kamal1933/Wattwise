<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpertRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_code',
        'category',
        'title',
        'description',
        'recommendation_template',
        'actionable_steps',
        'severity',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(RuleCondition::class, 'rule_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(RecommendationHistory::class, 'rule_id');
    }
}
