<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesHouseholdContext;
use App\Models\ActualMeterReading;
use App\Models\CalculationPeriod;
use App\Models\Household;
use App\Services\Calculation\EnergyCalculationEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Laporan Konsumsi Energi: monthly trend, estimate versus real meter readings, printable.
 */
class ReportsController extends Controller
{
    use ResolvesHouseholdContext;

    public function index(Request $request): View
    {
        $data = $this->pageData($request);
        $household = $data['household'];

        $periods = $household
            ? CalculationPeriod::where('household_id', $household->id)
                ->with('actualMeterReadings')
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->take(12)
                ->get()
                ->unique(fn (CalculationPeriod $period): string => $period->start_date->format('Y-m'))
                ->sortBy('start_date')
                ->values()
            : collect();

        $readings = $household
            ? $household->actualMeterReadings()->with('calculationPeriod')->latest('reading_date')->get()
            : collect();

        return view('reports.index', $data + [
            'periods' => $periods,
            'readings' => $readings,
            'lastMeterEnd' => $readings->first()?->meter_end_kwh,
        ]);
    }

    public function storeMeterReading(Request $request, Household $household, EnergyCalculationEngine $engine): RedirectResponse
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

        $engine->recordActualReading(
            household: $household,
            meterStartKwh: (float) $validated['meter_start_kwh'],
            meterEndKwh: (float) $validated['meter_end_kwh'],
            readingDate: $validated['reading_date'],
            period: $period,
            actualCostBilled: isset($validated['actual_cost_billed']) ? (float) $validated['actual_cost_billed'] : null,
            notes: $validated['notes'] ?? null
        );

        $this->rememberHousehold($household);

        return $this->backWithStatus('Catatan meteran disimpan.', 'reports.index');
    }

    /**
     * Total real consumption recorded against a period.
     */
    public static function actualKwhFor(CalculationPeriod $period): ?float
    {
        $readings = $period->actualMeterReadings;

        return $readings->isEmpty() ? null : (float) $readings->sum(fn (ActualMeterReading $reading) => (float) $reading->actual_kwh_consumed);
    }
}
