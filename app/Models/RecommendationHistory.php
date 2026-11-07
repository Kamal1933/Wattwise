<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationHistory extends Model
{
    use HasFactory;

    protected $table = 'recommendation_history';

    protected $fillable = [
        'calculation_period_id',
        'rule_id',
        'rule_code',
        'title',
        'severity',
        'explanation_evidence',
        'recommendation_text',
        'actionable_steps',
        'estimated_kwh_saving',
        'estimated_cost_saving',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'estimated_kwh_saving' => 'decimal:2',
            'estimated_cost_saving' => 'decimal:2',
        ];
    }

    public function calculationPeriod(): BelongsTo
    {
        return $this->belongsTo(CalculationPeriod::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ExpertRule::class);
    }
}
