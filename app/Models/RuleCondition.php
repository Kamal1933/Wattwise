<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuleCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_id',
        'condition_group',
        'parameter_name',
        'operator',
        'comparison_value',
    ];

    protected function casts(): array
    {
        return [
            'condition_group' => 'integer',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ExpertRule::class, 'rule_id');
    }
}
