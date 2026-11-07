<?php

namespace Tests\Feature;

use App\Models\ApplianceCategory;
use App\Models\Household;
use Database\Seeders\ApplianceCategorySeeder;
use Database\Seeders\ExpertRuleSeeder;
use Database\Seeders\HouseholdSeeder;
use Database\Seeders\TariffRateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWebTest extends TestCase
{
    use RefreshDatabase;

    protected Household $household;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TariffRateSeeder::class);
        $this->seed(ApplianceCategorySeeder::class);
        $this->seed(ExpertRuleSeeder::class);
        $this->seed(HouseholdSeeder::class);

        $this->household = Household::first();
    }

    public function test_dashboard_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('WattWise')
            ->assertSee($this->household->name)
            ->assertSee('Estimasi Konsumsi')
            ->assertSee('Rekomendasi Sistem Pakar')
            ->assertSee('Inventaris Peralatan Listrik');
    }

    public function test_reanalyze_action_triggers_forward_chaining(): void
    {
        $response = $this->post(route('households.reanalyze', $this->household->id));

        $response->assertRedirect(route('dashboard', ['household_id' => $this->household->id]))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('calculation_periods', [
            'household_id' => $this->household->id,
        ]);
        $this->assertDatabaseHas('recommendation_history', []);
    }

    public function test_can_add_appliance_via_dashboard(): void
    {
        $kitchen = ApplianceCategory::where('code', 'kitchen')->first();

        $response = $this->post(route('households.appliances.store', $this->household->id), [
            'category_id' => $kitchen->id,
            'name' => 'Air Fryer Digital',
            'power_watt' => 1200,
            'quantity' => 1,
            'daily_hours' => 1.0,
            'days_per_month' => 20,
            'usage_factor' => 0.8,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('appliances', [
            'household_id' => $this->household->id,
            'name' => 'Air Fryer Digital',
            'power_watt' => 1200,
        ]);
    }

    public function test_can_record_meter_reading_and_compute_variance(): void
    {
        $response = $this->post(route('households.meter.store', $this->household->id), [
            'reading_date' => now()->toDateString(),
            'meter_start_kwh' => 2000,
            'meter_end_kwh' => 2250,
            'actual_cost_billed' => 360000,
            'notes' => 'Token PLN Awal Bulan',
        ]);

        $response->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('actual_meter_readings', [
            'household_id' => $this->household->id,
            'actual_kwh_consumed' => 250.00,
        ]);
    }

    public function test_can_delete_appliance_from_dashboard(): void
    {
        $appliance = $this->household->appliances->first();

        $response = $this->delete(route('appliances.destroy', $appliance->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('appliances', [
            'id' => $appliance->id,
        ]);
    }
}
