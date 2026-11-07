<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalculationPeriod;
use App\Models\Household;
use App\Services\Calculation\EnergyCalculationEngine;
use App\Services\ExpertSystem\ForwardChainingEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalculationController extends Controller
{
    public function __construct(
        protected EnergyCalculationEngine $calculationEngine,
        protected ForwardChainingEngine $expertEngine
    ) {}

    public function calculate(Request $request, Household $household): JsonResponse
    {
        $this->authorizeHousehold($request, $household);

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'period_name' => ['nullable', 'string', 'max:100'],
        ]);

        $period = $this->calculationEngine->calculateHouseholdPeriod(
            $household,
            $validated['start_date'],
            $validated['end_date'],
            $validated['period_name'] ?? null
        );

        return response()->json([
            'message' => 'Energy calculation completed successfully',
            'period' => $period->load(['items.appliance.category', 'household.tariff']),
        ], 201);
    }

    public function analyze(Request $request, Household $household): JsonResponse
    {
        $this->authorizeHousehold($request, $household);

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'period_name' => ['nullable', 'string', 'max:100'],
        ]);

        // 1. Calculate Period & Items
        $period = $this->calculationEngine->calculateHouseholdPeriod(
            $household,
            $validated['start_date'],
            $validated['end_date'],
            $validated['period_name'] ?? null
        );

        // 2. Run Forward Chaining Expert System
        $recommendations = $this->expertEngine->evaluatePeriod($period);

        return response()->json([
            'message' => 'Expert analysis and forward chaining completed',
            'period' => $period->load(['items.appliance.category', 'household.tariff']),
            'recommendations' => $recommendations,
        ], 201);
    }

    public function periods(Request $request, Household $household): JsonResponse
    {
        $this->authorizeHousehold($request, $household);

        $periods = $household->calculationPeriods()
            ->with(['recommendations'])
            ->latest('start_date')
            ->paginate(12);

        return response()->json($periods);
    }

    public function showPeriod(Request $request, CalculationPeriod $period): JsonResponse
    {
        $this->authorizeHousehold($request, $period->household);

        return response()->json([
            'period' => $period->load(['items.appliance.category', 'recommendations', 'household.tariff', 'actualMeterReadings']),
        ]);
    }

    public function recordActualReading(Request $request, Household $household): JsonResponse
    {
        $this->authorizeHousehold($request, $household);

        $validated = $request->validate([
            'reading_date' => ['required', 'date'],
            'meter_start_kwh' => ['required', 'numeric', 'min:0'],
            'meter_end_kwh' => ['required', 'numeric', 'gte:meter_start_kwh'],
            'calculation_period_id' => ['nullable', 'exists:calculation_periods,id'],
            'actual_cost_billed' => ['nullable', 'numeric', 'min:0'],
            'receipt_image_url' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $period = null;
        if (! empty($validated['calculation_period_id'])) {
            $period = CalculationPeriod::where('household_id', $household->id)
                ->findOrFail($validated['calculation_period_id']);
        }

        $reading = $this->calculationEngine->recordActualReading(
            household: $household,
            meterStartKwh: (float) $validated['meter_start_kwh'],
            meterEndKwh: (float) $validated['meter_end_kwh'],
            readingDate: $validated['reading_date'],
            period: $period,
            actualCostBilled: isset($validated['actual_cost_billed']) ? (float) $validated['actual_cost_billed'] : null,
            receiptImageUrl: $validated['receipt_image_url'] ?? null,
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'message' => 'Actual meter reading recorded successfully',
            'reading' => $reading->load('calculationPeriod'),
        ], 201);
    }

    public function actualReadings(Request $request, Household $household): JsonResponse
    {
        $this->authorizeHousehold($request, $household);

        $readings = $household->actualMeterReadings()
            ->with('calculationPeriod')
            ->latest('reading_date')
            ->paginate(12);

        return response()->json($readings);
    }

    protected function authorizeHousehold(Request $request, Household $household): void
    {
        $user = $request->user();
        if (! $user->isAdmin() && $household->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this household.');
        }
    }
}
