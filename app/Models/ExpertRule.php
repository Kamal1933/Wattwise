<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpertRule extends Model
{
    use HasFactory;

    public const SCOPE_HOUSEHOLD = 'household';

    public const SCOPE_APPLIANCE = 'appliance';

    protected $fillable = [
        'rule_code',
        'category',
        'scope',
        'title',
        'description',
        'recommendation_template',
        'actionable_steps',
        'conclusion_fact',
        'conclusion_value',
        'produces_recommendation',
        'severity',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_active' => 'boolean',
            'produces_recommendation' => 'boolean',
        ];
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(RuleCondition::class, 'rule_id')
            ->orderBy('condition_group')
            ->orderBy('id');
    }

    public function isHouseholdScoped(): bool
    {
        return $this->scope === self::SCOPE_HOUSEHOLD;
    }

    /**
     * Value asserted into working memory when the rule fires (defaults to "true").
     */
    public function assertedValue(): string
    {
        return $this->conclusion_value ?? 'true';
    }

    public function history(): HasMany
    {
        return $this->hasMany(RecommendationHistory::class, 'rule_id');
    }
}
