<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Household;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HouseholdController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $households = $user->isAdmin()
            ? Household::with(['tariff', 'appliances.category'])->latest()->get()
            : $user->households()->with(['tariff', 'appliances.category'])->latest()->get();

        return response()->json([
            'households' => $households,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tariff_id' => ['required', 'exists:tariff_rates,id'],
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'occupant_count' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $household = $request->user()->households()->create($validated);

        return response()->json([
            'message' => 'Household created successfully',
            'household' => $household->load('tariff'),
        ], 201);
    }

    public function show(Request $request, Household $household): JsonResponse
    {
        $this->authorizeAccess($request, $household);

        return response()->json([
            'household' => $household->load(['tariff', 'appliances.category', 'calculationPeriods' => fn ($q) => $q->latest()->take(6)]),
        ]);
    }

    public function update(Request $request, Household $household): JsonResponse
    {
        $this->authorizeAccess($request, $household);

        $validated = $request->validate([
            'tariff_id' => ['sometimes', 'exists:tariff_rates,id'],
            'name' => ['sometimes', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'occupant_count' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $household->update($validated);

        return response()->json([
            'message' => 'Household updated successfully',
            'household' => $household->fresh('tariff'),
        ]);
    }

    public function destroy(Request $request, Household $household): JsonResponse
    {
        $this->authorizeAccess($request, $household);

        $household->delete();

        return response()->json([
            'message' => 'Household deleted successfully',
        ]);
    }

    protected function authorizeAccess(Request $request, Household $household): void
    {
        $user = $request->user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        if (! $user->isAdmin() && (int) $household->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized action for this household.');
        }
    }
}
