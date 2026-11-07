<?php

namespace Tests\Feature;

use App\Models\Appliance;
use App\Models\ApplianceCategory;
use App\Models\Household;
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
        $user = User::factory()->create();
        $tariff = TariffRate::where('code', 'R-1/TR-1300')->first();
        $coolingCat = ApplianceCategory::where('code', 'cooling')->first();

        $household = Household::create([
            'user_id' => $user->id,
            'tariff_id' => $tariff->id,
            'name' => 'Test Household',
            'occupant_count' => 3,
        ]);

        // Heavy AC that dominates consumption (650W * 10h * 0.8 * 30 days = 156 kWh)
        Appliance::create([
            'household_id' => $household->id,
            'category_id' => $coolingCat->id,
            'name' => 'AC Heavy Use',
            'power_watt' => 650.00,
            'quantity' => 1,
            'daily_hours' => 10.00,
            'days_per_month' => 30,
            'usage_factor' => 0.800,
            'is_active' => true,
        ]);

        // Small bulb (10W * 5h * 30 days = 1.5 kWh)
        $lightingCat = ApplianceCategory::where('code', 'lighting')->first();
        Appliance::create([
            'household_id' => $household->id,
            'category_id' => $lightingCat->id,
            'name' => 'Small LED',
            'power_watt' => 10.00,
            'quantity' => 1,
            'daily_hours' => 5.00,
            'days_per_month' => 30,
            'usage_factor' => 1.000,
            'is_active' => true,
        ]);

        // 1. Calculate Period
        $period = $this->calcEngine->calculateHouseholdPeriod(
            $household,
            '2026-11-01',
            '2026-11-30',
            'November 2026'
        );

        $this->assertGreaterThan(150.0, (float) $period->total_estimated_kwh);
        $this->assertCount(2, $period->items);

        // 2. Run Forward Chaining Engine
        $recommendations = $this->expertEngine->evaluatePeriod($period);

        $this->assertNotEmpty($recommendations);

        // Verify R-DOM-01 fired because AC is > 35% of total consumption
        $dominantRec = $recommendations->firstWhere('rule_code', 'R-DOM-01');
        $this->assertNotNull($dominantRec);
        $this->assertStringContainsString('AC Heavy Use', $dominantRec->recommendation_text);
        $this->assertStringContainsString('Bukti Inferensi:', $dominantRec->explanation_evidence);
        $this->assertGreaterThan(0, $dominantRec->estimated_kwh_saving);
        $this->assertGreaterThan(0, $dominantRec->estimated_cost_saving);

        // Verify R-COOL-01 fired because AC daily hours >= 8
        $coolRec = $recommendations->firstWhere('rule_code', 'R-COOL-01');
        $this->assertNotNull($coolRec);
        $this->assertStringContainsString('AC Heavy Use', $coolRec->recommendation_text);
    }
}
