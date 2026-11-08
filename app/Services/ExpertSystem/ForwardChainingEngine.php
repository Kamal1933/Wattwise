<?php

namespace App\Services\ExpertSystem;

use App\Models\CalculationPeriod;
use App\Models\ExpertRule;
use App\Models\RecommendationHistory;
use App\Models\RuleCondition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Forward chaining inference engine using the recognize–act cycle.
 *
 * 1. Match: build the conflict set — every (rule, context) pair whose conditions hold in working memory
 *    and that has not fired before in that context (refraction).
 * 2. Conflict resolution: pick the pair with the lowest priority number (then rule id, then context order).
 * 3. Act: fire it. If the rule has a conclusion, the derived fact is asserted into working memory,
 *    which may enable further rules in the next cycle.
 * 4. Repeat until the conflict set is empty.
 *
 * Contexts: a household-scoped rule is matched once against household facts; an appliance-scoped rule
 * is matched against each appliance's facts merged with the household facts (so appliance rules can
 * use facts derived by household rules).
 */
class ForwardChainingEngine
{
    /**
     * Upper bound on inference cycles, guarding against misconfigured rule bases.
     */
    public const MAX_CYCLES = 1000;

    /**
     * @var list<array<string, mixed>>
     */
    protected array $lastTrace = [];

    public function __construct(
        protected ExplanationGenerator $explanationGenerator
    ) {}

    /**
     * Run forward chaining inference over a calculation period and persist explainable recommendations.
     *
     * Previous recommendations of the period are replaced, so re-running inference never duplicates them.
     *
     * @return Collection<int, RecommendationHistory>
     */
    public function evaluatePeriod(CalculationPeriod $period): Collection
    {
        $period->loadMissing(['items.appliance.category', 'household.tariff']);

        $rules = ExpertRule::with('conditions')
            ->where('is_active', true)
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        $inference = $this->infer($this->extractWorkingMemory($period), $rules);
        $this->lastTrace = $this->summarizeTrace($inference['firings']);

        return DB::transaction(function () use ($period, $inference): Collection {
            $period->recommendations()->delete();

            return collect($inference['firings'])
                ->filter(fn (array $firing): bool => $firing['rule']->produces_recommendation)
                ->map(fn (array $firing): RecommendationHistory => $this->createRecommendationRecord($period, $firing))
                ->values();
        });
    }

    /**
     * Run the recognize–act cycle until no rule can fire.
     *
     * @param  array{household: array<string, mixed>, appliances: array<int, array<string, mixed>>}  $workingMemory
     * @param  Collection<int, ExpertRule>  $rules  Ordered by priority (conflict resolution order).
     * @return array{
     *     working_memory: array{household: array<string, mixed>, appliances: array<int, array<string, mixed>>},
     *     firings: list<array{cycle: int, rule: ExpertRule, context: string, facts: array<string, mixed>, matched_conditions: Collection<int, RuleCondition>, derived_from: array<string, string>, asserted: array<string, string>}>
     * }
     */
    public function infer(array $workingMemory, Collection $rules): array
    {
        $household = $workingMemory['household'];
        $appliances = $workingMemory['appliances'];

        /** @var array{household: array<string, string>, appliances: array<int, array<string, string>>} $provenance fact name => rule code that derived it */
        $provenance = ['household' => [], 'appliances' => []];
        $hasFired = [];
        $firings = [];

        for ($cycle = 1; $cycle <= self::MAX_CYCLES; $cycle++) {
            $selected = $this->selectFromConflictSet($rules, $household, $appliances, $provenance, $hasFired);

            if ($selected === null) {
                break;
            }

            [$rule, $contextKey, $applianceIndex, $facts, $matchedConditions, $factProvenance] = $selected;
            $hasFired[$rule->id.'|'.$contextKey] = true;

            $asserted = [];
            if ($rule->conclusion_fact) {
                $value = $rule->assertedValue();
                $asserted[$rule->conclusion_fact] = $value;
                $facts[$rule->conclusion_fact] = $value;

                if ($applianceIndex === null) {
                    $household[$rule->conclusion_fact] = $value;
                    $provenance['household'][$rule->conclusion_fact] = $rule->rule_code;
                } else {
                    $appliances[$applianceIndex][$rule->conclusion_fact] = $value;
                    $provenance['appliances'][$applianceIndex][$rule->conclusion_fact] = $rule->rule_code;
                }
            }

            $firings[] = [
                'cycle' => $cycle,
                'rule' => $rule,
                'context' => $contextKey,
                'facts' => $facts,
                'matched_conditions' => $matchedConditions,
                'derived_from' => array_intersect_key(
                    $factProvenance,
                    array_flip($matchedConditions->pluck('parameter_name')->all())
                ),
                'asserted' => $asserted,
            ];
        }

        return [
            'working_memory' => ['household' => $household, 'appliances' => $appliances],
            'firings' => $firings,
        ];
    }

    /**
     * Build the conflict set and return its highest-priority member, or null when it is empty.
     *
     * Rules arrive sorted by priority, so the first matching (rule, context) pair wins.
     *
     * @param  Collection<int, ExpertRule>  $rules
     * @param  array<string, mixed>  $household
     * @param  array<int, array<string, mixed>>  $appliances
     * @param  array{household: array<string, string>, appliances: array<int, array<string, string>>}  $provenance
     * @param  array<string, bool>  $hasFired
     * @return array{0: ExpertRule, 1: string, 2: int|null, 3: array<string, mixed>, 4: Collection<int, RuleCondition>, 5: array<string, string>}|null
     */
    protected function selectFromConflictSet(
        Collection $rules,
        array $household,
        array $appliances,
        array $provenance,
        array $hasFired
    ): ?array {
        foreach ($rules as $rule) {
            foreach ($this->contextsFor($rule, $household, $appliances, $provenance) as [$contextKey, $applianceIndex, $facts, $factProvenance]) {
                if (isset($hasFired[$rule->id.'|'.$contextKey])) {
                    continue;
                }

                $matchedConditions = $this->matchingConditionGroup($rule, $facts);

                if ($matchedConditions !== null) {
                    return [$rule, $contextKey, $applianceIndex, $facts, $matchedConditions, $factProvenance];
                }
            }
        }

        return null;
    }

    /**
     * List the fact contexts a rule is matched against.
     *
     * @param  array<string, mixed>  $household
     * @param  array<int, array<string, mixed>>  $appliances
     * @param  array{household: array<string, string>, appliances: array<int, array<string, string>>}  $provenance
     * @return list<array{0: string, 1: int|null, 2: array<string, mixed>, 3: array<string, string>}>
     */
    protected function contextsFor(ExpertRule $rule, array $household, array $appliances, array $provenance): array
    {
        if ($rule->isHouseholdScoped()) {
            return [['household', null, $household, $provenance['household']]];
        }

        $contexts = [];
        foreach ($appliances as $index => $applianceFacts) {
            $contexts[] = [
                'appliance:'.($applianceFacts['appliance_id'] ?? $index),
                $index,
                array_merge($household, $applianceFacts),
                array_merge($provenance['household'], $provenance['appliances'][$index] ?? []),
            ];
        }

        return $contexts;
    }

    /**
     * Return the first condition group whose conditions all hold (AND within a group, OR between groups).
     *
     * @param  array<string, mixed>  $facts
     * @return Collection<int, RuleCondition>|null
     */
    public function matchingConditionGroup(ExpertRule $rule, array $facts): ?Collection
    {
        $groups = $rule->conditions
            ->sortBy([['condition_group', 'asc'], ['id', 'asc']])
            ->groupBy(fn (RuleCondition $condition): int => (int) ($condition->condition_group ?? 1));

        foreach ($groups as $conditions) {
            $allConditionsHold = $conditions->every(
                fn (RuleCondition $condition): bool => $this->evaluateCondition($condition, $facts)
            );

            if ($allConditionsHold) {
                return $conditions->values();
            }
        }

        return null;
    }

    /**
     * Check whether any condition group of the rule is satisfied by the given facts.
     *
     * @param  array<string, mixed>  $facts
     */
    public function ruleMatchesConditions(ExpertRule $rule, array $facts): bool
    {
        return $this->matchingConditionGroup($rule, $facts) !== null;
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
        $isNumericComparison = in_array($condition->operator, ['>', '>=', '<', '<='], true);

        if ($isNumericComparison && (! is_numeric($actual) || ! is_numeric($expected))) {
            return false;
        }

        return match ($condition->operator) {
            '>' => (float) $actual > (float) $expected,
            '>=' => (float) $actual >= (float) $expected,
            '<' => (float) $actual < (float) $expected,
            '<=' => (float) $actual <= (float) $expected,
            '==' => $this->normalizeForEquality($actual) === $this->normalizeForEquality($expected),
            '!=' => $this->normalizeForEquality($actual) !== $this->normalizeForEquality($expected),
            'IN' => in_array(
                $this->normalizeForEquality($actual),
                array_map(fn (string $item): string => $this->normalizeForEquality($item), explode(',', $expected)),
                true
            ),
            'CONTAINS' => str_contains(strtolower((string) $actual), strtolower($expected)),
            default => false,
        };
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
     * The inference trace (which rule fired in which cycle, and what it derived) of the last evaluation.
     *
     * @return list<array<string, mixed>>
     */
    public function lastTrace(): array
    {
        return $this->lastTrace;
    }

    /**
     * @param  list<array<string, mixed>>  $firings
     * @return list<array<string, mixed>>
     */
    protected function summarizeTrace(array $firings): array
    {
        return array_map(fn (array $firing): array => [
            'cycle' => $firing['cycle'],
            'rule_code' => $firing['rule']->rule_code,
            'scope' => $firing['rule']->scope,
            'context' => $firing['context'],
            'subject' => $firing['rule']->isHouseholdScoped() ? 'Rumah tangga' : ($firing['facts']['appliance_name'] ?? null),
            'condition_group' => $firing['matched_conditions']->first()?->condition_group,
            'used_derived_facts' => $firing['derived_from'],
            'asserted_facts' => $firing['asserted'],
            'produces_recommendation' => (bool) $firing['rule']->produces_recommendation,
        ], $firings);
    }

    /**
     * Create and persist a RecommendationHistory record for a fired rule.
     *
     * @param  array{rule: ExpertRule, facts: array<string, mixed>, matched_conditions: Collection<int, RuleCondition>, derived_from: array<string, string>}  $firing
     */
    protected function createRecommendationRecord(CalculationPeriod $period, array $firing): RecommendationHistory
    {
        $rule = $firing['rule'];
        $facts = $firing['facts'];
        $ratePerKwh = (float) $period->applied_rate_per_kwh;

        $evidence = $this->explanationGenerator->generateEvidence(
            $rule,
            $facts,
            $firing['matched_conditions'],
            $firing['derived_from']
        );
        $recommendationText = $this->explanationGenerator->formatRecommendation((string) $rule->recommendation_template, $facts);
        $savings = $this->explanationGenerator->estimateSavings($rule, $facts, $ratePerKwh);

        return RecommendationHistory::create([
            'calculation_period_id' => $period->id,
            'rule_id' => $rule->id,
            'rule_code' => $rule->rule_code,
            'title' => $rule->title,
            'severity' => $rule->severity,
            'explanation_evidence' => $evidence,
            'recommendation_text' => $recommendationText,
            'actionable_steps' => $this->explanationGenerator->formatRecommendation((string) $rule->actionable_steps, $facts),
            'estimated_kwh_saving' => $savings['kwh'],
            'estimated_cost_saving' => $savings['cost'],
            'status' => 'active',
        ]);
    }

    /**
     * Normalize scalar facts for string equality so booleans, numbers and text compare consistently.
     */
    protected function normalizeForEquality(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            return (string) (float) $value;
        }

        return strtolower(trim((string) $value));
    }
}
