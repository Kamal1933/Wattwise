<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_logo_and_favicon_files_exist(): void
    {
        foreach ([
            'favicon.ico',
            'favicon.svg',
            'apple-touch-icon.png',
            'images/logo/wattwise-mark.svg',
            'images/logo/wattwise-mark-light.svg',
            'images/logo/wattwise-app-icon.svg',
        ] as $file) {
            $this->assertFileExists(public_path($file));
        }
    }

    public function test_login_page_shows_logo_and_favicons(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(asset('favicon.svg'), false)
            ->assertSee(asset('apple-touch-icon.png'), false)
            ->assertSee(asset('images/logo/wattwise-mark-light.svg'), false);
    }

    public function test_dashboard_sidebar_shows_logo_and_favicons(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(asset('favicon.svg'), false)
            ->assertSee(asset('images/logo/wattwise-mark.svg'), false);
    }
}
