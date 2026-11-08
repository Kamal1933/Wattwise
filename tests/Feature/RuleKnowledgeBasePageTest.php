<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ApplianceCategorySeeder;
use Database\Seeders\ExpertRuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleKnowledgeBasePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ApplianceCategorySeeder::class);
        $this->seed(ExpertRuleSeeder::class);
    }

    public function test_admin_sees_every_rule_as_readable_if_then(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard.rules'))
            ->assertOk()
            ->assertSee('Basis aturan sistem pakar')
            ->assertSee(['R-CAP-01', 'R-PEAK-01', 'R-DOM-01', 'R-COOL-01', 'R-VAMP-01', 'R-LIGHT-01', 'R-WATER-01', 'F-HEAVY-01'])
            ->assertSee('JIKA')
            ->assertSee('MAKA')
            ->assertSee('durasi pemakaian ≥ 8,0 jam/hari')
            ->assertSee('IF (category_code == cooling AND daily_hours &gt;= 8.0) THEN rekomendasi(R-COOL-01)', false);
    }

    public function test_page_shows_or_groups_and_inference_chains(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard.rules'))
            ->assertOk()
            ->assertSee('ATAU')
            ->assertSee('Rantai inferensi')
            ->assertSeeInOrder(['F-HEAVY-01', 'peralatan berkonsumsi tinggi', 'R-DOM-01'])
            ->assertSee('Simpulkan fakta')
            ->assertSee('rumah berisiko kelebihan beban = ya');
    }

    public function test_regular_user_cannot_open_the_rule_base_or_see_its_menu(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard.rules'))->assertForbidden();
        $this->get(route('dashboard'))->assertOk()->assertDontSee('Basis aturan');
    }

    public function test_admin_sees_the_menu_link(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard'))->assertOk()->assertSee(route('dashboard.rules'), false);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard.rules'))->assertRedirect(route('login'));
    }
}
