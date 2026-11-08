<?php

namespace Tests\Feature;

use App\Models\ApplianceCategory;
use App\Models\CalculationPeriod;
use App\Models\Household;
use App\Models\RecommendationHistory;
use App\Models\User;
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
        $this->actingAs($this->household->user);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        auth()->logout();

        $this->get('/')->assertRedirect(route('login'));
        $this->post(route('households.reanalyze', $this->household->id))->assertRedirect(route('login'));
    }

    public function test_user_can_log_in_and_out(): void
    {
        auth()->logout();

        $this->post(route('login.attempt'), [
            'email' => 'budi@wattwise.local',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($this->household->user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_rejects_wrong_password(): void
    {
        auth()->logout();

        $this->post(route('login.attempt'), [
            'email' => 'budi@wattwise.local',
            'password' => 'salah-total',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_new_user_without_household_sees_own_empty_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/')
            ->assertOk()
            ->assertDontSee($this->household->name);
    }

    public function test_user_cannot_access_or_modify_another_users_household(): void
    {
        $this->actingAs(User::factory()->create());
        $appliance = $this->household->appliances->first();

        $this->get(route('dashboard', ['household_id' => $this->household->id]))->assertForbidden();
        $this->post(route('households.reanalyze', $this->household->id))->assertForbidden();
        $this->delete(route('appliances.destroy', $appliance->id))->assertForbidden();
        $this->post(route('households.meter.store', $this->household->id), [
            'reading_date' => now()->toDateString(),
            'meter_start_kwh' => 1,
            'meter_end_kwh' => 2,
        ])->assertForbidden();

        $this->assertDatabaseHas('appliances', ['id' => $appliance->id]);
    }

    public function test_admin_can_view_any_household(): void
    {
        $this->actingAs(User::where('role', 'admin')->first());

        $this->get(route('dashboard', ['household_id' => $this->household->id]))
            ->assertOk()
            ->assertSee($this->household->name);
    }

    public function test_repeated_reanalysis_keeps_a_single_period_per_month(): void
    {
        $this->get('/')->assertOk();
        $this->post(route('households.reanalyze', $this->household->id));
        $this->post(route('households.reanalyze', $this->household->id));

        $periods = CalculationPeriod::where('household_id', $this->household->id)->get();
        $this->assertCount(1, $periods);

        $recommendationCount = RecommendationHistory::where('calculation_period_id', $periods->first()->id)->count();
        $this->post(route('households.reanalyze', $this->household->id));

        $this->assertSame(1, CalculationPeriod::where('household_id', $this->household->id)->count());
        $this->assertSame($recommendationCount, RecommendationHistory::count());
    }

    public function test_appliance_changes_update_the_existing_period_instead_of_duplicating(): void
    {
        $this->post(route('households.reanalyze', $this->household->id));
        $period = CalculationPeriod::where('household_id', $this->household->id)->sole();
        $kwhBefore = (float) $period->total_estimated_kwh;

        $this->delete(route('appliances.destroy', $this->household->appliances->first()->id));

        $updatedPeriod = CalculationPeriod::where('household_id', $this->household->id)->sole();
        $this->assertSame($period->id, $updatedPeriod->id);
        $this->assertLessThan($kwhBefore, (float) $updatedPeriod->total_estimated_kwh);
        $this->assertSame(
            $this->household->appliances()->where('is_active', true)->count(),
            $updatedPeriod->items()->count()
        );
    }

    public function test_meter_reading_cannot_link_to_another_households_period(): void
    {
        $otherHousehold = Household::create([
            'user_id' => User::factory()->create()->id,
            'tariff_id' => $this->household->tariff_id,
            'name' => 'Rumah Lain',
            'occupant_count' => 2,
        ]);
        $foreignPeriod = CalculationPeriod::create([
            'household_id' => $otherHousehold->id,
            'period_name' => 'Asing',
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'total_estimated_kwh' => 10,
            'total_estimated_cost' => 10000,
            'applied_rate_per_kwh' => 1000,
        ]);

        $this->post(route('households.meter.store', $this->household->id), [
            'reading_date' => now()->toDateString(),
            'meter_start_kwh' => 100,
            'meter_end_kwh' => 200,
            'calculation_period_id' => $foreignPeriod->id,
        ])->assertNotFound();

        $this->assertDatabaseMissing('actual_meter_readings', ['household_id' => $this->household->id]);
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
