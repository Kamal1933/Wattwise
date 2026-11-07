<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpertRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'recommendation_template' => ['required', 'string'],
            'actionable_steps' => ['required', 'string'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'priority' => ['required', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'conditions' => ['required', 'array', 'min:1'],
            'conditions.*.parameter_name' => ['required', 'string', 'max:80'],
            'conditions.*.operator' => ['required', 'in:>,>=,<,<=,==,!=,IN,CONTAINS'],
            'conditions.*.comparison_value' => ['required', 'string', 'max:100'],
            'conditions.*.logical_operator' => ['required', 'in:AND,OR'],
        ]);

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
            'title' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'string'],
            'recommendation_template' => ['sometimes', 'string'],
            'actionable_steps' => ['sometimes', 'string'],
            'severity' => ['sometimes', 'in:low,medium,high,critical'],
            'priority' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'conditions' => ['nullable', 'array'],
            'conditions.*.parameter_name' => ['required_with:conditions', 'string', 'max:80'],
            'conditions.*.operator' => ['required_with:conditions', 'in:>,>=,<,<=,==,!=,IN,CONTAINS'],
            'conditions.*.comparison_value' => ['required_with:conditions', 'string', 'max:100'],
            'conditions.*.logical_operator' => ['required_with:conditions', 'in:AND,OR'],
        ]);

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

    protected function authorizeAdmin(Request $request): void
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Administrator access required.');
        }
    }
}
