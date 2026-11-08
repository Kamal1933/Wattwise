<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The public landing page renders, while the dashboard sends guests to the login page.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertOk();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk();
    }
}
