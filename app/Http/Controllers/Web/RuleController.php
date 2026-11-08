<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApplianceCategory;
use App\Models\ExpertRule;
use App\Models\RuleCondition;
use App\Services\ExpertSystem\ExplanationGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class RuleController extends Controller
{
    public function __construct(
        protected ExplanationGenerator $explanationGenerator
    ) {}

    /**
     * Show the expert system knowledge base as readable IF–THEN rules (administrators only).
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403, 'Halaman ini hanya untuk administrator.');

        $rules = ExpertRule::with('conditions')
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        $valueLabels = ApplianceCategory::pluck('name', 'code')->all() + ['true' => 'ya', 'false' => 'tidak'];
        $producersByFact = $rules
            ->filter(fn (ExpertRule $rule): bool => filled($rule->conclusion_fact))
            ->groupBy('conclusion_fact')
            ->map(fn (Collection $producers): array => $producers->pluck('rule_code')->all());

        $describedRules = $rules->map(fn (ExpertRule $rule): array => $this->describeRule($rule, $valueLabels, $producersByFact, $rules));

        return view('rules.index', [
            'rules' => $describedRules,
            'chains' => $describedRules->flatMap(fn (array $rule): array => array_map(
                fn (array $use): array => ['from' => $use['rule_code'], 'fact' => $use['fact'], 'fact_label' => $use['fact_label'], 'to' => $rule['code']],
                $rule['uses_facts']
            ))->values(),
            'stats' => [
                'total' => $rules->count(),
                'active' => $rules->where('is_active', true)->count(),
                'fact_only' => $rules->where('produces_recommendation', false)->count(),
                'household' => $rules->where('scope', ExpertRule::SCOPE_HOUSEHOLD)->count(),
            ],
        ]);
    }

    /**
     * @param  array<string, string>  $valueLabels
     * @param  Collection<string, list<string>>  $producersByFact
     * @param  Collection<int, ExpertRule>  $allRules
     * @return array<string, mixed>
     */
    protected function describeRule(ExpertRule $rule, array $valueLabels, Collection $producersByFact, Collection $allRules): array
    {
        $groups = $rule->conditions
            ->groupBy(fn (RuleCondition $condition): int => (int) ($condition->condition_group ?? 1))
            ->sortKeys()
            ->values()
            ->map(fn (Collection $conditions): array => [
                'readable' => $conditions->map(fn (RuleCondition $condition): string => $this->explanationGenerator->describeCondition($condition, $valueLabels))->all(),
                'technical' => $conditions->map(fn (RuleCondition $condition): string => "{$condition->parameter_name} {$condition->operator} {$condition->comparison_value}")->all(),
            ]);

        $usesFacts = $rule->conditions
            ->filter(fn (RuleCondition $condition): bool => $producersByFact->has($condition->parameter_name))
            ->flatMap(fn (RuleCondition $condition): array => array_map(fn (string $producer): array => [
                'rule_code' => $producer,
                'fact' => $condition->parameter_name,
                'fact_label' => $this->explanationGenerator->labelFor($condition->parameter_name),
            ], $producersByFact[$condition->parameter_name]))
            ->unique(fn (array $use): string => $use['rule_code'].$use['fact'])
            ->values()
            ->all();

        $usedBy = $rule->conclusion_fact
            ? $allRules
                ->filter(fn (ExpertRule $other): bool => $other->conditions->contains('parameter_name', $rule->conclusion_fact))
                ->pluck('rule_code')
                ->all()
            : [];

        $technicalIf = $groups->map(fn (array $group): string => '('.implode(' AND ', $group['technical']).')')->implode(' OR ');
        $technicalThen = collect([
            $rule->conclusion_fact ? "{$rule->conclusion_fact} = {$rule->assertedValue()}" : null,
            $rule->produces_recommendation ? "rekomendasi({$rule->rule_code})" : null,
        ])->filter()->implode(' AND ');

        return [
            'code' => $rule->rule_code,
            'title' => $rule->title,
            'description' => $rule->description,
            'category' => $rule->category,
            'scope' => $rule->scope,
            'severity' => $rule->severity,
            'priority' => $rule->priority,
            'is_active' => $rule->is_active,
            'produces_recommendation' => $rule->produces_recommendation,
            'groups' => $groups->all(),
            'conclusion' => $rule->conclusion_fact ? [
                'label' => $this->explanationGenerator->labelFor($rule->conclusion_fact),
                'fact' => $rule->conclusion_fact,
                'value' => $this->explanationGenerator->formatFact($rule->conclusion_fact, $rule->assertedValue()),
            ] : null,
            'uses_facts' => $usesFacts,
            'used_by' => $usedBy,
            'technical' => "IF {$technicalIf} THEN {$technicalThen}",
        ];
    }
}
