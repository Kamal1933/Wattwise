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
        'parameter_name',
        'operator',
        'comparison_value',
        'logical_operator',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ExpertRule::class, 'rule_id');
    }
}
