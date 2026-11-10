<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\User;
use Database\Seeders\ApplianceCategorySeeder;
use Database\Seeders\ExpertRuleSeeder;
use Database\Seeders\HouseholdSeeder;
use Database\Seeders\TariffRateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MenuPagesTest extends TestCase
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

    /**
     * @return array<string, array{string, string}>
     */
    public static function menuPages(): array
    {
        return [
            'dashboard' => ['dashboard', 'Dashboard'],
            'analysis' => ['analysis.index', 'Analisis Konsumsi Energi'],
            'recommendations' => ['recommendations.index', 'Rekomendasi Penghematan Energi'],
            'appliances' => ['appliances.index', 'Data Peralatan Elektronik'],
            'history' => ['history.index', 'Riwayat Analisis'],
            'reports' => ['reports.index', 'Laporan Konsumsi Energi'],
            'profile' => ['profile.index', 'Profil Pengguna'],
            'settings' => ['settings.index', 'Pengaturan'],
        ];
    }

    #[DataProvider('menuPages')]
    public function test_each_menu_has_its_own_page_with_sidebar_and_active_state(string $route, string $title): void
    {
        $response = $this->get(route($route))->assertOk()->assertSee($title);

        // Sidebar (main menus) and header user menu (profile, settings) on every page, plus logout.
        foreach (array_keys(self::menuPages()) as $key) {
            $response->assertSee(route(self::menuPages()[$key][0]), false);
        }
        $response->assertSee(route('logout'), false);

        // Exactly one menu link marked as current page (the breadcrumb's last crumb is a span).
        $this->assertSame(1, preg_match_all('#<a [^>]*aria-current="page"#', $response->getContent()));
        $this->assertMatchesRegularExpression(
            '#href="'.preg_quote(route($route), '#').'"[^>]*aria-current="page"#',
            $response->getContent()
        );
    }

    #[DataProvider('menuPages')]
    public function test_each_menu_requires_login(string $route): void
    {
        auth()->logout();

        $this->get(route($route))->assertRedirect(route('login'));
    }

    #[DataProvider('menuPages')]
    public function test_each_menu_works_for_user_without_household(string $route): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route($route))->assertOk()->assertDontSee($this->household->name);
    }

    public function test_analysis_page_shows_input_form_calculation_and_inference(): void
    {
        $this->get(route('analysis.index'))
            ->assertOk()
            ->assertSee('Masukkan peralatan elektronik')
            ->assertSee(route('households.appliances.store', $this->household), false)
            ->assertSee('Rincian perhitungan')
            ->assertSee('Hasil analisis sistem pakar')
            ->assertSee('Cara WattWise menghitung');
    }

    public function test_recommendations_page_shows_kwh_and_rupiah_savings(): void
    {
        $this->get(route('recommendations.index'))
            ->assertOk()
            ->assertSee('Potensi hemat energi')
            ->assertSee('Potensi hemat biaya')
            ->assertSee('Kenapa saran ini muncul?');
    }

    public function test_appliance_detail_page_shows_specs_and_history(): void
    {
        $appliance = $this->household->appliances->first();
        $this->get(route('analysis.index'));

        $this->get(route('appliances.show', $appliance))
            ->assertOk()
            ->assertSee($appliance->name)
            ->assertSee('Daya per unit')
            ->assertSee('Riwayat perhitungan')
            ->assertSee(route('appliances.destroy', $appliance), false);
    }

    public function test_deleting_from_detail_page_returns_to_appliance_list(): void
    {
        $appliance = $this->household->appliances->first();

        $this->from(route('appliances.show', $appliance))
            ->delete(route('appliances.destroy', $appliance))
            ->assertRedirect(route('appliances.index'));

        $this->assertModelMissing($appliance);
    }

    public function test_users_cannot_open_other_users_appliance_or_history(): void
    {
        $this->get(route('analysis.index'));
        $appliance = $this->household->appliances->first();
        $period = $this->household->calculationPeriods()->first();

        $this->actingAs(User::factory()->create());

        $this->get(route('appliances.show', $appliance))->assertForbidden();
        $this->get(route('history.show', $period))->assertForbidden();
    }

    public function test_history_lists_periods_and_shows_a_snapshot(): void
    {
        $this->get(route('analysis.index'));
        $period = $this->household->calculationPeriods()->first();

        $this->get(route('history.index'))
            ->assertOk()
            ->assertSee($period->period_name)
            ->assertSee(route('history.show', $period), false);

        $this->get(route('history.show', $period))
            ->assertOk()
            ->assertSee('Rincian peralatan')
            ->assertSee('Rekomendasi periode ini');
    }

    public function test_reports_page_shows_trend_and_meter_records(): void
    {
        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Tren konsumsi bulanan')
            ->assertSee('Perkiraan dibanding meteran')
            ->assertSee('Catatan meteran PLN')
            ->assertSee('data-dialog-open="dialog-meter-reading"', false);
    }

    public function test_selected_household_is_remembered_across_pages(): void
    {
        $second = $this->household->user->households()->create([
            'name' => 'Rumah Kedua',
            'tariff_id' => $this->household->tariff_id,
            'occupant_count' => 2,
        ]);

        $this->get(route('dashboard', ['household_id' => $second->id]))->assertOk();
        $this->assertSame($second->id, session('active_household_id'));

        $this->get(route('appliances.index'))->assertOk()->assertSee('Rumah Kedua');
    }

    public function test_breadcrumb_is_rendered(): void
    {
        $appliance = $this->household->appliances->first();

        $this->get(route('appliances.show', $appliance))
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee(route('appliances.index'), false);
    }

    public function test_user_can_update_profile_and_password(): void
    {
        $user = $this->household->user;

        $this->put(route('profile.update'), ['name' => 'Budi Baru', 'email' => 'budi.baru@example.com'])
            ->assertRedirect(route('profile.index'))
            ->assertSessionHas('status');
        $this->assertSame('Budi Baru', $user->fresh()->name);

        $this->put(route('profile.password'), [
            'current_password' => 'salah',
            'password' => 'rahasia-baru',
            'password_confirmation' => 'rahasia-baru',
        ])->assertSessionHasErrorsIn('password', 'current_password');

        $this->put(route('profile.password'), [
            'current_password' => 'password123',
            'password' => 'rahasia-baru',
            'password_confirmation' => 'rahasia-baru',
        ])->assertRedirect(route('profile.index'));
        $this->assertTrue(Hash::check('rahasia-baru', $user->fresh()->password));
    }

    public function test_settings_can_update_and_delete_households(): void
    {
        $this->put(route('settings.households.update', $this->household), [
            'name' => 'Rumah Utama',
            'tariff_id' => $this->household->tariff_id,
            'occupant_count' => 5,
        ])->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('households', ['id' => $this->household->id, 'name' => 'Rumah Utama', 'occupant_count' => 5]);

        $this->delete(route('settings.households.destroy', $this->household))->assertRedirect(route('settings.index'));
        $this->assertModelMissing($this->household);
    }

    public function test_settings_cannot_touch_another_users_household(): void
    {
        $this->actingAs(User::factory()->create());

        $this->put(route('settings.households.update', $this->household), [
            'name' => 'Diambil alih',
            'tariff_id' => $this->household->tariff_id,
            'occupant_count' => 1,
        ])->assertForbidden();
        $this->delete(route('settings.households.destroy', $this->household))->assertForbidden();
    }

    public function test_profile_and_settings_live_in_the_user_menu_not_the_sidebar(): void
    {
        $html = $this->get(route('dashboard'))->assertOk()->getContent();
        $start = strpos($html, 'id="sidebar"');
        $sidebar = substr($html, $start, strpos($html, '</aside>') - $start);

        $this->assertStringNotContainsString(route('profile.index'), $sidebar);
        $this->assertStringNotContainsString(route('settings.index'), $sidebar);
        $this->assertStringContainsString(route('logout'), $sidebar);
        $this->assertStringContainsString(route('profile.index'), $html);
        $this->assertStringContainsString(route('settings.index'), $html);
    }
}
