<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpertRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminRuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $rules = ExpertRule::with('conditions')
            ->orderBy('priority', 'asc')
            ->get();

        return response()->json([
            'rules' => $rules,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'rule_code' => ['required', 'string', 'max:50', 'unique:expert_rules,rule_code'],
            'category' => ['required', 'string', 'max:50'],
            'scope' => ['required', Rule::in([ExpertRule::SCOPE_HOUSEHOLD, ExpertRule::SCOPE_APPLIANCE])],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'produces_recommendation' => ['sometimes', 'boolean'],
            'recommendation_template' => [Rule::requiredIf(fn () => $request->boolean('produces_recommendation', true)), 'nullable', 'string'],
            'actionable_steps' => [Rule::requiredIf(fn () => $request->boolean('produces_recommendation', true)), 'nullable', 'string'],
            'conclusion_fact' => ['nullable', 'string', 'max:80', 'regex:/^[a-z][a-z0-9_]*$/'],
            'conclusion_value' => ['nullable', 'string', 'max:100'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'priority' => ['required', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'conditions' => ['required', 'array', 'min:1'],
            'conditions.*.condition_group' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'conditions.*.parameter_name' => ['required', 'string', 'max:80'],
            'conditions.*.operator' => ['required', 'in:>,>=,<,<=,==,!=,IN,CONTAINS'],
            'conditions.*.comparison_value' => ['required', 'string', 'max:100'],
        ]);

        $this->ensureRuleHasEffect($validated);

        $conditions = $validated['conditions'];
        unset($validated['conditions']);

        $rule = ExpertRule::create($validated);
        foreach ($conditions as $cond) {
            $rule->conditions()->create($cond);
        }

        return response()->json([
            'message' => 'Expert rule created successfully',
            'rule' => $rule->load('conditions'),
        ], 201);
    }

    public function show(Request $request, ExpertRule $rule): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json([
            'rule' => $rule->load('conditions'),
        ]);
    }

    public function update(Request $request, ExpertRule $rule): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'rule_code' => ['sometimes', 'string', 'max:50', 'unique:expert_rules,rule_code,'.$rule->id],
            'category' => ['sometimes', 'string', 'max:50'],
            'scope' => ['sometimes', Rule::in([ExpertRule::SCOPE_HOUSEHOLD, ExpertRule::SCOPE_APPLIANCE])],
            'title' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'string'],
            'produces_recommendation' => ['sometimes', 'boolean'],
            'recommendation_template' => ['sometimes', 'nullable', 'string'],
            'actionable_steps' => ['sometimes', 'nullable', 'string'],
            'conclusion_fact' => ['sometimes', 'nullable', 'string', 'max:80', 'regex:/^[a-z][a-z0-9_]*$/'],
            'conclusion_value' => ['sometimes', 'nullable', 'string', 'max:100'],
            'severity' => ['sometimes', 'in:low,medium,high,critical'],
            'priority' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'conditions' => ['nullable', 'array'],
            'conditions.*.condition_group' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'conditions.*.parameter_name' => ['required_with:conditions', 'string', 'max:80'],
            'conditions.*.operator' => ['required_with:conditions', 'in:>,>=,<,<=,==,!=,IN,CONTAINS'],
            'conditions.*.comparison_value' => ['required_with:conditions', 'string', 'max:100'],
        ]);

        $this->ensureRuleHasEffect(array_merge($rule->only([
            'produces_recommendation', 'recommendation_template', 'actionable_steps', 'conclusion_fact',
        ]), $validated));

        if (isset($validated['conditions'])) {
            $conditions = $validated['conditions'];
            unset($validated['conditions']);
            $rule->conditions()->delete();
            foreach ($conditions as $cond) {
                $rule->conditions()->create($cond);
            }
        }

        $rule->update($validated);

        return response()->json([
            'message' => 'Expert rule updated successfully',
            'rule' => $rule->fresh('conditions'),
        ]);
    }

    public function destroy(Request $request, ExpertRule $rule): JsonResponse
    {
        $this->authorizeAdmin($request);

        $rule->delete();

        return response()->json([
            'message' => 'Expert rule deleted successfully',
        ]);
    }

    /**
     * A rule must either produce a recommendation (with its texts) or derive a fact; otherwise it does nothing.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function ensureRuleHasEffect(array $attributes): void
    {
        $producesRecommendation = (bool) ($attributes['produces_recommendation'] ?? true);

        if (! $producesRecommendation && empty($attributes['conclusion_fact'])) {
            throw ValidationException::withMessages([
                'conclusion_fact' => 'Aturan yang tidak menghasilkan rekomendasi wajib menyimpulkan sebuah fakta (conclusion_fact).',
            ]);
        }

        if ($producesRecommendation && (empty($attributes['recommendation_template']) || empty($attributes['actionable_steps']))) {
            throw ValidationException::withMessages([
                'recommendation_template' => 'Aturan yang menghasilkan rekomendasi wajib memiliki recommendation_template dan actionable_steps.',
            ]);
        }
    }

    protected function authorizeAdmin(Request $request): void
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Administrator access required.');
        }
    }
}
