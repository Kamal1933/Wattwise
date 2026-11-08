<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesHouseholdContext;
use App\Models\CalculationPeriod;
use App\Services\Calculation\PeriodSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Riwayat Analisis: every saved calculation period and a read-only snapshot of each.
 */
class HistoryController extends Controller
{
    use ResolvesHouseholdContext;

    public function index(Request $request): View
    {
        $context = $this->householdContext($request);

        $periods = $context['household']
            ? CalculationPeriod::where('household_id', $context['household']->id)
                ->withCount(['items', 'recommendations'])
                ->withSum('recommendations', 'estimated_cost_saving')
                ->with('actualMeterReadings')
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->paginate(12)
                ->withQueryString()
            : null;

        return view('history.index', $context + ['periods' => $periods]);
    }

    public function show(Request $request, CalculationPeriod $period): View
    {
        Gate::authorize('view', $period->household);

        $request->query->set('household_id', (string) $period->household_id);
        $context = $this->householdContext($request);
        $period->load(['items.appliance.category', 'recommendations', 'actualMeterReadings', 'household.tariff']);

        return view('history.show', $context + [
            'period' => $period,
            'periodSummary' => new PeriodSummary($period, $period->household),
        ]);
    }
}
