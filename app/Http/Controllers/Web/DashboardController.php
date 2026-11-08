<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appliance;
use App\Models\ApplianceCategory;
use App\Models\CalculationPeriod;
use App\Models\Household;
use App\Models\TariffRate;
use App\Services\Calculation\EnergyCalculationEngine;
use App\Services\ExpertSystem\ForwardChainingEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected EnergyCalculationEngine $calcEngine,
        protected ForwardChainingEngine $expertEngine
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $households = Household::with(['tariff', 'appliances.category'])
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->get();
        $activeHouseholdId = $request->query('household_id') ?? $households->first()?->id;

        $household = $activeHouseholdId
            ? Household::with(['tariff', 'appliances.category', 'actualMeterReadings'])->findOrFail($activeHouseholdId)
            : null;

        if ($household) {
            Gate::authorize('view', $household);
        }

        $categories = ApplianceCategory::all();
        $tariffs = TariffRate::where('is_active', true)->get();

        $currentPeriod = null;
        $recommendations = collect();

        if ($household && $household->appliances->where('is_active', true)->isNotEmpty()) {
            // Find or compute current month period
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            $periodName = now()->translatedFormat('F Y');

            $currentPeriod = CalculationPeriod::with(['items.appliance.category', 'recommendations'])
                ->where('household_id', $household->id)
                ->whereDate('start_date', $startOfMonth->toDateString())
                ->latest('id')
                ->first();

            if (! $currentPeriod) {
                $currentPeriod = $this->calcEngine->calculateHouseholdPeriod(
                    $household,
                    $startOfMonth,
                    $endOfMonth,
                    $periodName
                );
                $recommendations = $this->expertEngine->evaluatePeriod($currentPeriod);
                $currentPeriod->load(['items.appliance.category', 'recommendations']);
            } else {
                $recommendations = $currentPeriod->recommendations;
            }
        }

        $recentReadings = $household
            ? $household->actualMeterReadings()->latest('reading_date')->take(5)->get()
            : collect();

        return view('dashboard', [
            'households' => $households,
            'household' => $household,
            'categories' => $categories,
            'tariffs' => $tariffs,
            'currentPeriod' => $currentPeriod,
            'recommendations' => $recommendations,
            'recentReadings' => $recentReadings,
        ]);
    }

    public function reanalyze(Household $household): RedirectResponse
    {
        Gate::authorize('update', $household);

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $periodName = now()->translatedFormat('F Y');

        $period = $this->calcEngine->calculateHouseholdPeriod(
            $household,
            $startOfMonth,
            $endOfMonth,
            $periodName
        );

        $this->expertEngine->evaluatePeriod($period);

        return redirect()->route('dashboard', ['household_id' => $household->id])
            ->with('status', 'Analisis sistem pakar berhasil diperbarui!');
    }

    public function storeAppliance(Request $request, Household $household): RedirectResponse
    {
        Gate::authorize('update', $household);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:appliance_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'power_watt' => ['required', 'numeric', 'min:0.5', 'max:25000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'daily_hours' => ['required', 'numeric', 'min:0.1', 'max:24.0'],
            'days_per_month' => ['required', 'integer', 'min:1', 'max:31'],
            'usage_factor' => ['required', 'numeric', 'min:0.05', 'max:1.0'],
        ]);

        $validated['is_active'] = true;
        $household->appliances()->create($validated);

        // Auto recalculate active period
        return $this->reanalyze($household);
    }

    public function updateAppliance(Request $request, Appliance $appliance): RedirectResponse
    {
        Gate::authorize('update', $appliance->household);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:appliance_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'power_watt' => ['required', 'numeric', 'min:0.5', 'max:25000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'daily_hours' => ['required', 'numeric', 'min:0.1', 'max:24.0'],
            'days_per_month' => ['required', 'integer', 'min:1', 'max:31'],
            'usage_factor' => ['required', 'numeric', 'min:0.05', 'max:1.0'],
        ]);

        $appliance->update($validated);

        return $this->reanalyze($appliance->household);
    }

    public function destroyAppliance(Appliance $appliance): RedirectResponse
    {
        $household = $appliance->household;
        Gate::authorize('update', $household);

        $appliance->delete();

        return $this->reanalyze($household);
    }

    public function storeMeterReading(Request $request, Household $household): RedirectResponse
    {
        Gate::authorize('update', $household);

        $validated = $request->validate([
            'reading_date' => ['required', 'date'],
            'meter_start_kwh' => ['required', 'numeric', 'min:0'],
            'meter_end_kwh' => ['required', 'numeric', 'gte:meter_start_kwh'],
            'calculation_period_id' => ['nullable', 'exists:calculation_periods,id'],
            'actual_cost_billed' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $period = ! empty($validated['calculation_period_id'])
            ? CalculationPeriod::where('household_id', $household->id)->findOrFail($validated['calculation_period_id'])
            : null;

        $this->calcEngine->recordActualReading(
            household: $household,
            meterStartKwh: (float) $validated['meter_start_kwh'],
            meterEndKwh: (float) $validated['meter_end_kwh'],
            readingDate: $validated['reading_date'],
            period: $period,
            actualCostBilled: isset($validated['actual_cost_billed']) ? (float) $validated['actual_cost_billed'] : null,
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('dashboard', ['household_id' => $household->id])
            ->with('status', 'Pencatatan meteran listrik riil berhasil disimpan!');
    }
}
