<?php

namespace Tests\Feature;

use App\Models\TariffRate;
use App\Models\User;
use Database\Seeders\TariffRateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TariffRateSeeder::class);
    }

    public function test_guest_sees_landing_page_with_calculator_and_signup_links(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tahu ke mana listrik rumah Anda terpakai.')
            ->assertSee('Hitung tagihan listrik Anda sekarang')
            ->assertSee('data-calculator-item', false)
            ->assertSee(route('register'), false)
            ->assertSee(route('login'), false)
            ->assertDontSee('Buka dashboard');
    }

    public function test_calculator_uses_active_tariffs_from_the_database(): void
    {
        $tariff = TariffRate::where('code', 'R-1/TR-1300')->firstOrFail();
        TariffRate::where('code', 'R-3/TR-6600+')->update(['is_active' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('"code":"R-1\/TR-1300"', false)
            ->assertSee('<option value="'.$tariff->id.'" selected', false)
            ->assertDontSee('R-3/TR-6600+');
    }

    public function test_signed_in_user_is_offered_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Buka dashboard')
            ->assertSee(route('dashboard'), false);
    }

    public function test_dashboard_lives_at_its_own_url(): void
    {
        $this->assertSame(url('/dashboard'), route('dashboard'));
        $this->get('/dashboard')->assertRedirect(route('login'));
    }
}
