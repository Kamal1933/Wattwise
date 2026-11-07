<?php

namespace Tests\Feature;

use App\Models\ApplianceCategory;
use App\Models\User;
use Database\Seeders\ApplianceCategorySeeder;
use Database\Seeders\ExpertRuleSeeder;
use Database\Seeders\TariffRateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TariffRateSeeder::class);
        $this->seed(ApplianceCategorySeeder::class);
        $this->seed(ExpertRuleSeeder::class);
    }

    public function test_auth_and_full_energy_management_flow(): void
    {
        // 1. User Registration
        $registerResponse = $this->postJson('/api/auth/register', [
            'name' => 'Ahmad Dani',
            'email' => 'ahmad@wattwise.local',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonStructure(['token', 'user']);

        $token = $registerResponse->json('token');

        // 2. Fetch Tariffs
        $tariffResponse = $this->getJson('/api/tariffs');
        $tariffResponse->assertStatus(200)
            ->assertJsonStructure(['tariffs']);

        $tariffId = $tariffResponse->json('tariffs.0.id');

        // 3. Create Household
        $householdResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/households', [
                'tariff_id' => $tariffId,
                'name' => 'Rumah Ahmad',
                'address' => 'Jl. Anggrek No. 5',
                'occupant_count' => 3,
            ]);

        $householdResponse->assertStatus(201);
        $householdId = $householdResponse->json('household.id');

        // 4. Add Heavy Appliance (AC)
        $coolingCategory = ApplianceCategory::where('code', 'cooling')->first();
        $applianceResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/households/{$householdId}/appliances", [
                'category_id' => $coolingCategory->id,
                'name' => 'AC Ruang Tamu',
                'power_watt' => 800,
                'quantity' => 1,
                'daily_hours' => 10,
                'days_per_month' => 30,
                'usage_factor' => 0.8,
            ]);

        $applianceResponse->assertStatus(201);

        // 5. Trigger Calculation
        $calcResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/households/{$householdId}/calculate", [
                'start_date' => '2026-11-01',
                'end_date' => '2026-11-30',
                'period_name' => 'November 2026',
            ]);

        $calcResponse->assertStatus(201)
            ->assertJsonPath('period.total_estimated_kwh', '192.000');

        $periodId = $calcResponse->json('period.id');

        // 6. Trigger Expert System Analysis
        $analyzeResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/households/{$householdId}/analyze", [
                'start_date' => '2026-11-01',
                'end_date' => '2026-11-30',
            ]);

        $analyzeResponse->assertStatus(201)
            ->assertJsonStructure([
                'period',
                'recommendations',
            ]);

        $this->assertNotEmpty($analyzeResponse->json('recommendations'));

        // 7. Record Actual Meter Reading & Variance Check
        $meterResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/households/{$householdId}/actual-readings", [
                'reading_date' => '2026-11-30',
                'meter_start_kwh' => 1000,
                'meter_end_kwh' => 1210, // 210 kWh actual consumed
                'calculation_period_id' => $periodId,
                'notes' => 'Tagihan PLN bulan November',
            ]);

        $meterResponse->assertStatus(201)
            ->assertJsonPath('reading.actual_kwh_consumed', '210.00')
            ->assertJsonPath('reading.variance_kwh', '18.00'); // 210 - 192 = 18 kWh variance

        // 8. Security / Authorization Check: Another user cannot view Ahmad's household
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        $unauthorizedResponse = $this->getJson("/api/households/{$householdId}");

        $unauthorizedResponse->assertStatus(403);
    }
}
