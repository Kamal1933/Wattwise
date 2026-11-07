<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appliance;
use App\Models\ApplianceCategory;
use App\Models\Household;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplianceController extends Controller
{
    public function categories(): JsonResponse
    {
        return response()->json([
            'categories' => ApplianceCategory::all(),
        ]);
    }

    public function index(Request $request, Household $household): JsonResponse
    {
        $this->authorizeHousehold($request, $household);

        return response()->json([
            'appliances' => $household->appliances()->with('category')->get(),
        ]);
    }

    public function store(Request $request, Household $household): JsonResponse
    {
        $this->authorizeHousehold($request, $household);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:appliance_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'power_watt' => ['required', 'numeric', 'min:0.5', 'max:25000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'daily_hours' => ['required', 'numeric', 'min:0.1', 'max:24.0'],
            'days_per_month' => ['required', 'integer', 'min:1', 'max:31'],
            'usage_factor' => ['required', 'numeric', 'min:0.05', 'max:1.0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $appliance = $household->appliances()->create($validated);

        return response()->json([
            'message' => 'Appliance added successfully',
            'appliance' => $appliance->load('category'),
        ], 201);
    }

    public function update(Request $request, Appliance $appliance): JsonResponse
    {
        $this->authorizeHousehold($request, $appliance->household);

        $validated = $request->validate([
            'category_id' => ['sometimes', 'exists:appliance_categories,id'],
            'name' => ['sometimes', 'string', 'max:150'],
            'power_watt' => ['sometimes', 'numeric', 'min:0.5', 'max:25000'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'daily_hours' => ['sometimes', 'numeric', 'min:0.1', 'max:24.0'],
            'days_per_month' => ['sometimes', 'integer', 'min:1', 'max:31'],
            'usage_factor' => ['sometimes', 'numeric', 'min:0.05', 'max:1.0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $appliance->update($validated);

        return response()->json([
            'message' => 'Appliance updated successfully',
            'appliance' => $appliance->fresh('category'),
        ]);
    }

    public function destroy(Request $request, Appliance $appliance): JsonResponse
    {
        $this->authorizeHousehold($request, $appliance->household);

        $appliance->delete();

        return response()->json([
            'message' => 'Appliance deleted successfully',
        ]);
    }

    protected function authorizeHousehold(Request $request, Household $household): void
    {
        $user = $request->user();
        if (! $user->isAdmin() && $household->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this household.');
        }
    }
}
