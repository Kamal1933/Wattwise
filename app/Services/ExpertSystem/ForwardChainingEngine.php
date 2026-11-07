<?php

namespace App\Services\ExpertSystem;

use App\Models\CalculationPeriod;
use App\Models\ExpertRule;
use App\Models\RecommendationHistory;
use App\Models\RuleCondition;
use Illuminate\Support\Collection;

class ForwardChainingEngine
{
    public function __construct(
        protected ExplanationGenerator $explanationGenerator
    ) {}

    /**
     * Run forward chaining inference over a calculation period and generate explainable recommendations.
     *
     * @return Collection<int, RecommendationHistory>
     */
    public function evaluatePeriod(CalculationPeriod $period): Collection
    {
        $period->loadMissing(['items.appliance.category', 'household.tariff']);
        $workingMemory = $this->extractWorkingMemory($period);
        $rules = ExpertRule::with('conditions')
            ->where('is_active', true)
            ->orderBy('priority', 'asc')
            ->get();

        $firedRecommendations = collect();

        // Evaluate household-level rules
        foreach ($rules as $rule) {
            $isHouseholdRule = in_array($rule->category, ['Capacity', 'Tariff', 'Household']);

            if ($isHouseholdRule) {
                if ($this->ruleMatchesConditions($rule, $workingMemory['household'])) {
                    $firedRecommendations->push($this->createRecommendationRecord(
                        $period,
                        $rule,
                        $workingMemory['household']
                    ));
                }
            } else {
                // Appliance-level rules evaluated against each appliance fact
                foreach ($workingMemory['appliances'] as $applianceFact) {
                    if ($this->ruleMatchesConditions($rule, $applianceFact)) {
                        $firedRecommendations->push($this->createRecommendationRecord(
                            $period,
                            $rule,
                            $applianceFact
                        ));
                    }
                }
            }
        }

        return $firedRecommendations;
    }

    /**
     * Extract structured facts into Working Memory.
     *
     * @return array{household: array<string, mixed>, appliances: array<int, array<string, mixed>>}
     */
    public function extractWorkingMemory(CalculationPeriod $period): array
    {
        $household = $period->household;
        $tariff = $household->tariff;
        $assumptions = $period->calculation_assumptions ?? [];

        $simultaneousActiveWatt = (float) ($assumptions['simultaneous_active_watt'] ?? 0.0);
        $powerLimitVa = (float) ($tariff->power_limit_va ?? 0.0);
        $simultaneousLoadRatio = $powerLimitVa > 0 ? ($simultaneousActiveWatt / $powerLimitVa) : 0.0;

        $householdFacts = [
            'total_estimated_kwh' => (float) $period->total_estimated_kwh,
            'total_estimated_cost' => (float) $period->total_estimated_cost,
            'applied_rate_per_kwh' => (float) $period->applied_rate_per_kwh,
            'tariff_code' => $tariff->code ?? 'N/A',
            'power_limit_va' => $powerLimitVa,
            'simultaneous_active_watt' => $simultaneousActiveWatt,
            'simultaneous_load_ratio' => round($simultaneousLoadRatio, 4),
            'occupant_count' => (int) $household->occupant_count,
            'appliance_count' => $period->items->count(),
        ];

        $applianceFacts = [];
        foreach ($period->items as $item) {
            $appliance = $item->appliance;
            $category = $appliance?->category;

            $applianceFacts[] = [
                'appliance_id' => $item->appliance_id,
                'appliance_name' => $appliance->name ?? 'Peralatan',
                'category_code' => $category?->code ?? 'other',
                'category_name' => $category?->name ?? 'Lainnya',
                'power_watt' => (float) $item->power_watt,
                'quantity' => (int) $item->quantity,
                'daily_hours' => (float) $item->daily_hours,
                'days_per_month' => (int) $item->days_per_month,
                'usage_factor' => (float) $item->usage_factor,
                'monthly_kwh' => (float) $item->monthly_kwh,
                'monthly_cost' => (float) $item->monthly_cost,
                'appliance_share_pct' => (float) $item->percentage_of_total,
                'power_limit_va' => $powerLimitVa,
                'rate_per_kwh' => (float) $period->applied_rate_per_kwh,
            ];
        }

        return [
            'household' => $householdFacts,
            'appliances' => $applianceFacts,
        ];
    }

    /**
     * Check if a rule's conditions are satisfied by the given fact set.
     *
     * @param  array<string, mixed>  $facts
     */
    public function ruleMatchesConditions(ExpertRule $rule, array $facts): bool
    {
        if ($rule->conditions->isEmpty()) {
            return false;
        }

        $allMatch = true;

        foreach ($rule->conditions as $condition) {
            $matched = $this->evaluateCondition($condition, $facts);

            if ($condition->logical_operator === 'AND') {
                if (! $matched) {
                    return false;
                }
            } elseif ($condition->logical_operator === 'OR') {
                if ($matched) {
                    return true;
                }
                $allMatch = false;
            }
        }

        return $allMatch;
    }

    /**
     * Evaluate a single condition predicate against facts.
     *
     * @param  array<string, mixed>  $facts
     */
    public function evaluateCondition(RuleCondition $condition, array $facts): bool
    {
        $param = $condition->parameter_name;

        if (! array_key_exists($param, $facts)) {
            return false;
        }

        $actual = $facts[$param];
        $expected = $condition->comparison_value;

        return match ($condition->operator) {
            '>' => (float) $actual > (float) $expected,
            '>=' => (float) $actual >= (float) $expected,
            '<' => (float) $actual < (float) $expected,
            '<=' => (float) $actual <= (float) $expected,
            '==' => (string) $actual === (string) $expected,
            '!=' => (string) $actual !== (string) $expected,
            'IN' => in_array((string) $actual, array_map('trim', explode(',', $expected))),
            'CONTAINS' => str_contains(strtolower((string) $actual), strtolower($expected)),
            default => false,
        };
    }

    /**
     * Create and persist a RecommendationHistory record for a fired rule.
     *
     * @param  array<string, mixed>  $facts
     */
    protected function createRecommendationRecord(
        CalculationPeriod $period,
        ExpertRule $rule,
        array $facts
    ): RecommendationHistory {
        $ratePerKwh = (float) $period->applied_rate_per_kwh;
        $evidence = $this->explanationGenerator->generateEvidence($rule, $facts);
        $recommendationText = $this->explanationGenerator->formatRecommendation($rule->recommendation_template, $facts);
        $savings = $this->explanationGenerator->estimateSavings($rule, $facts, $ratePerKwh);

        return RecommendationHistory::create([
            'calculation_period_id' => $period->id,
            'rule_id' => $rule->id,
            'rule_code' => $rule->rule_code,
            'title' => $rule->title,
            'severity' => $rule->severity,
            'explanation_evidence' => $evidence,
            'recommendation_text' => $recommendationText,
            'actionable_steps' => $rule->actionable_steps,
            'estimated_kwh_saving' => $savings['kwh'],
            'estimated_cost_saving' => $savings['cost'],
            'status' => 'active',
        ]);
    }
}
