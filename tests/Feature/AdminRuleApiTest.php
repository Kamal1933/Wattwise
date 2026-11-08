<?php

namespace Tests\Feature;

use App\Models\ExpertRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminRuleApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_admin_can_create_rule_with_or_condition_groups(): void
    {
        $response = $this->postJson('/api/admin/rules', $this->rulePayload([
            'conditions' => [
                ['condition_group' => 1, 'parameter_name' => 'category_code', 'operator' => '==', 'comparison_value' => 'kitchen'],
                ['condition_group' => 1, 'parameter_name' => 'daily_hours', 'operator' => '>=', 'comparison_value' => '3'],
                ['condition_group' => 2, 'parameter_name' => 'power_watt', 'operator' => '>=', 'comparison_value' => '2000'],
            ],
        ]));

        $response->assertCreated()
            ->assertJsonPath('rule.scope', 'appliance')
            ->assertJsonPath('rule.conditions.2.condition_group', 2);
    }

    public function test_admin_can_create_fact_only_rule(): void
    {
        $this->postJson('/api/admin/rules', $this->rulePayload([
            'rule_code' => 'F-TEST-01',
            'produces_recommendation' => false,
            'recommendation_template' => null,
            'actionable_steps' => null,
            'conclusion_fact' => 'is_kitchen_heavy',
        ]))->assertCreated()
            ->assertJsonPath('rule.conclusion_fact', 'is_kitchen_heavy');
    }

    public function test_rule_without_recommendation_or_conclusion_is_rejected(): void
    {
        $this->postJson('/api/admin/rules', $this->rulePayload([
            'produces_recommendation' => false,
            'recommendation_template' => null,
            'actionable_steps' => null,
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors('conclusion_fact');

        $this->assertSame(0, ExpertRule::count());
    }

    public function test_conclusion_fact_must_be_snake_case(): void
    {
        $this->postJson('/api/admin/rules', $this->rulePayload(['conclusion_fact' => 'Bad Fact!']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('conclusion_fact');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function rulePayload(array $overrides = []): array
    {
        return array_merge([
            'rule_code' => 'R-TEST-01',
            'category' => 'Kitchen',
            'scope' => 'appliance',
            'title' => 'Uji',
            'description' => 'Aturan uji',
            'recommendation_template' => ':appliance_name boros.',
            'actionable_steps' => '1. Kurangi pemakaian.',
            'severity' => 'medium',
            'priority' => 50,
            'conditions' => [
                ['parameter_name' => 'category_code', 'operator' => '==', 'comparison_value' => 'kitchen'],
            ],
        ], $overrides);
    }
}
