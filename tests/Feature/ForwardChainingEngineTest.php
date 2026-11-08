<?php

namespace Tests\Feature;

use App\Models\Appliance;
use App\Models\ApplianceCategory;
use App\Models\CalculationPeriod;
use App\Models\ExpertRule;
use App\Models\Household;
use App\Models\RuleCondition;
use App\Models\TariffRate;
use App\Models\User;
use App\Services\Calculation\EnergyCalculationEngine;
use App\Services\ExpertSystem\ForwardChainingEngine;
use Database\Seeders\ApplianceCategorySeeder;
use Database\Seeders\ExpertRuleSeeder;
use Database\Seeders\TariffRateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForwardChainingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected EnergyCalculationEngine $calcEngine;

    protected ForwardChainingEngine $expertEngine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TariffRateSeeder::class);
        $this->seed(ApplianceCategorySeeder::class);
        $this->seed(ExpertRuleSeeder::class);

        $this->calcEngine = app(EnergyCalculationEngine::class);
        $this->expertEngine = app(ForwardChainingEngine::class);
    }

    public function test_evaluates_period_and_generates_explainable_recommendations(): void
    {
        $period = $this->calculateHeavyAcPeriod();

        $this->assertGreaterThan(150.0, (float) $period->total_estimated_kwh);
        $this->assertCount(2, $period->items);

        $recommendations = $this->expertEngine->evaluatePeriod($period);

        $this->assertNotEmpty($recommendations);

        $dominantRec = $recommendations->firstWhere('rule_code', 'R-DOM-01');
        $this->assertNotNull($dominantRec);
        $this->assertStringContainsString('AC Heavy Use', $dominantRec->recommendation_text);
        $this->assertStringContainsString('Bukti Inferensi:', $dominantRec->explanation_evidence);
        $this->assertGreaterThan(0, $dominantRec->estimated_kwh_saving);
        $this->assertGreaterThan(0, $dominantRec->estimated_cost_saving);

        $coolRec = $recommendations->firstWhere('rule_code', 'R-COOL-01');
        $this->assertNotNull($coolRec);
        $this->assertStringContainsString('AC Heavy Use', $coolRec->recommendation_text);
    }

    public function test_derived_fact_chains_into_a_later_rule_even_when_its_rule_has_lower_priority(): void
    {
        $factRule = ExpertRule::where('rule_code', 'F-HEAVY-01')->firstOrFail();
        $dominanceRule = ExpertRule::where('rule_code', 'R-DOM-01')->firstOrFail();
        $this->assertGreaterThan($dominanceRule->priority, $factRule->priority);

        $recommendations = $this->expertEngine->evaluatePeriod($this->calculateHeavyAcPeriod());
        $trace = collect($this->expertEngine->lastTrace());

        $factFiring = $trace->first(fn (array $step): bool => $step['rule_code'] === 'F-HEAVY-01' && $step['subject'] === 'AC Heavy Use');
        $dominanceFiring = $trace->firstWhere('rule_code', 'R-DOM-01');

        $this->assertNotNull($factFiring);
        $this->assertNotNull($dominanceFiring);
        $this->assertSame(['is_heavy_consumer' => 'true'], $factFiring['asserted_facts']);
        $this->assertLessThan($dominanceFiring['cycle'], $factFiring['cycle']);
        $this->assertSame(['is_heavy_consumer' => 'F-HEAVY-01'], $dominanceFiring['used_derived_facts']);

        $this->assertNull($recommendations->firstWhere('rule_code', 'F-HEAVY-01'), 'Intermediate fact rules must not produce recommendations.');
        $this->assertStringContainsString(
            'fakta turunan dari aturan F-HEAVY-01',
            $recommendations->firstWhere('rule_code', 'R-DOM-01')->explanation_evidence
        );
    }

    public function test_household_fact_chains_into_appliance_rule(): void
    {
        $period = $this->calculateHeavyAcPeriod(extraAppliances: [[
            'category' => 'kitchen',
            'name' => 'Microwave',
            'power_watt' => 1100,
            'daily_hours' => 0.5,
            'usage_factor' => 1.0,
        ]]);

        $recommendations = $this->expertEngine->evaluatePeriod($period);

        $this->assertNotNull($recommendations->firstWhere('rule_code', 'R-CAP-01'));
        $peakRecommendation = $recommendations->firstWhere('rule_code', 'R-PEAK-01');
        $this->assertNotNull($peakRecommendation);
        $this->assertStringContainsString('Microwave', $peakRecommendation->recommendation_text);
        $this->assertStringContainsString('fakta turunan dari aturan R-CAP-01', $peakRecommendation->explanation_evidence);
        $this->assertStringContainsString('Nyalakan Microwave bergantian', $peakRecommendation->actionable_steps);
    }

    public function test_conditions_are_and_within_a_group_and_or_between_groups(): void
    {
        $rule = $this->makeRule('T-OR-01', [
            [1, 'category_code', '==', 'entertainment'],
            [1, 'daily_hours', '>=', '20'],
            [2, 'category_code', '==', 'other'],
            [2, 'daily_hours', '>=', '20'],
        ]);

        $this->assertSame(1, $this->expertEngine->matchingConditionGroup($rule, ['category_code' => 'entertainment', 'daily_hours' => 24])->first()->condition_group);
        $this->assertSame(2, $this->expertEngine->matchingConditionGroup($rule, ['category_code' => 'other', 'daily_hours' => 22])->first()->condition_group);
        $this->assertNull($this->expertEngine->matchingConditionGroup($rule, ['category_code' => 'entertainment', 'daily_hours' => 5]));
        $this->assertNull($this->expertEngine->matchingConditionGroup($rule, ['category_code' => 'lighting', 'daily_hours' => 24]));
    }

    public function test_numeric_comparison_against_non_numeric_fact_never_matches(): void
    {
        $rule = $this->makeRule('T-NUM-01', [[1, 'category_code', '>=', '1']]);

        $this->assertFalse($this->expertEngine->ruleMatchesConditions($rule, ['category_code' => 'cooling']));
    }

    public function test_cyclic_rules_fire_once_per_context_and_terminate(): void
    {
        $ruleA = $this->makeRule('T-CYC-A', [[1, 'seed', '==', 'true']], conclusionFact: 'fact_b', priority: 1);
        $ruleB = $this->makeRule('T-CYC-B', [[1, 'fact_b', '==', 'true']], conclusionFact: 'fact_c', priority: 2);
        $ruleC = $this->makeRule('T-CYC-C', [[1, 'fact_c', '==', 'true']], conclusionFact: 'seed', priority: 3);

        $result = $this->expertEngine->infer(
            ['household' => ['seed' => 'true'], 'appliances' => []],
            collect([$ruleA, $ruleB, $ruleC])
        );

        $this->assertSame(['T-CYC-A', 'T-CYC-B', 'T-CYC-C'], array_map(fn (array $firing): string => $firing['rule']->rule_code, $result['firings']));
        $this->assertSame('true', $result['working_memory']['household']['fact_c']);
    }

    public function test_explanation_reflects_thresholds_stored_in_the_database(): void
    {
        RuleCondition::query()
            ->whereRelation('rule', 'rule_code', 'R-COOL-01')
            ->where('parameter_name', 'daily_hours')
            ->update(['comparison_value' => '6.5']);

        $recommendations = $this->expertEngine->evaluatePeriod($this->calculateHeavyAcPeriod());
        $evidence = $recommendations->firstWhere('rule_code', 'R-COOL-01')->explanation_evidence;

        $this->assertStringContainsString('durasi pemakaian = 10,0 jam/hari (syarat: ≥ 6,5 jam/hari)', $evidence);
        $this->assertStringNotContainsString('8,0', $evidence);
    }

    /**
     * @param  list<array{category: string, name: string, power_watt: float|int, daily_hours: float|int, usage_factor: float}>  $extraAppliances
     */
    protected function calculateHeavyAcPeriod(array $extraAppliances = []): CalculationPeriod
    {
        $household = Household::create([
            'user_id' => User::factory()->create()->id,
            'tariff_id' => TariffRate::where('code', 'R-1/TR-1300')->value('id'),
            'name' => 'Test Household',
            'occupant_count' => 3,
        ]);

        $appliances = [
            // Heavy AC that dominates consumption (650W * 10h * 0.8 * 30 days = 156 kWh)
            ['category' => 'cooling', 'name' => 'AC Heavy Use', 'power_watt' => 650, 'daily_hours' => 10, 'usage_factor' => 0.8],
            // Small bulb (10W * 5h * 30 days = 1.5 kWh)
            ['category' => 'lighting', 'name' => 'Small LED', 'power_watt' => 10, 'daily_hours' => 5, 'usage_factor' => 1.0],
            ...$extraAppliances,
        ];

        foreach ($appliances as $appliance) {
            Appliance::create([
                'household_id' => $household->id,
                'category_id' => ApplianceCategory::where('code', $appliance['category'])->value('id'),
                'name' => $appliance['name'],
                'power_watt' => $appliance['power_watt'],
                'quantity' => 1,
                'daily_hours' => $appliance['daily_hours'],
                'days_per_month' => 30,
                'usage_factor' => $appliance['usage_factor'],
                'is_active' => true,
            ]);
        }

        return $this->calcEngine->calculateHouseholdPeriod($household, '2026-11-01', '2026-11-30', 'November 2026');
    }

    /**
     * @param  list<array{0: int, 1: string, 2: string, 3: string}>  $conditions  [group, parameter, operator, value]
     */
    protected function makeRule(string $code, array $conditions, ?string $conclusionFact = null, int $priority = 100): ExpertRule
    {
        $rule = ExpertRule::create([
            'rule_code' => $code,
            'category' => 'Test',
            'scope' => ExpertRule::SCOPE_HOUSEHOLD,
            'title' => $code,
            'description' => 'Test rule',
            'conclusion_fact' => $conclusionFact,
            'produces_recommendation' => $conclusionFact === null,
            'recommendation_template' => $conclusionFact === null ? 'Rekomendasi' : null,
            'actionable_steps' => $conclusionFact === null ? 'Langkah' : null,
            'severity' => 'low',
            'priority' => $priority,
        ]);

        foreach ($conditions as [$group, $parameter, $operator, $value]) {
            $rule->conditions()->create([
                'condition_group' => $group,
                'parameter_name' => $parameter,
                'operator' => $operator,
                'comparison_value' => $value,
            ]);
        }

        return $rule->load('conditions');
    }
}
