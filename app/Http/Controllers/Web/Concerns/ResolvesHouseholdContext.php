<?php

namespace App\Http\Controllers\Web\Concerns;

use App\Models\CalculationPeriod;
use App\Models\Household;
use App\Services\Calculation\EnergyCalculationEngine;
use App\Services\Calculation\PeriodSummary;
use App\Services\ExpertSystem\ForwardChainingEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/**
 * Shared by every dashboard page: which households the user can see, which one is active
 * (remembered in the session so it survives navigation), and its current-month calculation.
 */
trait ResolvesHouseholdContext
{
    public const ACTIVE_HOUSEHOLD_SESSION_KEY = 'active_household_id';

    /**
     * @return array{households: Collection<int, Household>, household: Household|null}
     */
    protected function householdContext(Request $request): array
    {
        $user = $request->user();
        $households = Household::with('tariff')
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();

        if ($request->filled('household_id')) {
            $household = Household::findOrFail($request->query('household_id'));
            Gate::authorize('view', $household);
            $this->rememberHousehold($household);
        } else {
            $household = $households->firstWhere('id', $request->session()->get(self::ACTIVE_HOUSEHOLD_SESSION_KEY))
                ?? $households->first();
        }

        $household?->loadMissing(['tariff', 'appliances.category']);

        return ['households' => $households, 'household' => $household];
    }

    protected function rememberHousehold(Household $household): void
    {
        session()->put(self::ACTIVE_HOUSEHOLD_SESSION_KEY, $household->id);
    }

    /**
     * Current month's period for the household, calculated (and inferred) on first visit of the month.
     */
    protected function currentPeriodFor(?Household $household): ?CalculationPeriod
    {
        if (! $household || $household->appliances->where('is_active', true)->isEmpty()) {
            return null;
        }

        $period = CalculationPeriod::with(['items.appliance.category', 'recommendations'])
            ->where('household_id', $household->id)
            ->whereDate('start_date', now()->startOfMonth()->toDateString())
            ->latest('id')
            ->first();

        if ($period) {
            return $period;
        }

        $period = app(EnergyCalculationEngine::class)->calculateHouseholdPeriod(
            $household,
            now()->startOfMonth(),
            now()->endOfMonth(),
            now()->translatedFormat('F Y')
        );
        app(ForwardChainingEngine::class)->evaluatePeriod($period);

        return $period->load(['items.appliance.category', 'recommendations']);
    }

    protected function previousPeriodFor(?Household $household): ?CalculationPeriod
    {
        return $household
            ? CalculationPeriod::where('household_id', $household->id)
                ->whereDate('start_date', now()->subMonthNoOverflow()->startOfMonth()->toDateString())
                ->latest('id')
                ->first()
            : null;
    }

    /**
     * Everything the shared layout and a summary-based page need.
     *
     * @return array<string, mixed>
     */
    protected function pageData(Request $request, bool $withPrevious = false): array
    {
        $context = $this->householdContext($request);
        $period = $this->currentPeriodFor($context['household']);

        return $context + [
            'currentPeriod' => $period,
            'summary' => new PeriodSummary(
                $period,
                $context['household'],
                $withPrevious ? $this->previousPeriodFor($context['household']) : null
            ),
        ];
    }

    /**
     * Recalculate the current month and run inference again after household data changed.
     */
    protected function refreshAnalysis(Household $household): CalculationPeriod
    {
        $period = app(EnergyCalculationEngine::class)->calculateHouseholdPeriod(
            $household,
            now()->startOfMonth(),
            now()->endOfMonth(),
            now()->translatedFormat('F Y')
        );
        app(ForwardChainingEngine::class)->evaluatePeriod($period);

        return $period;
    }

    /**
     * Return to the page the user came from (fallback: the given route) with a flash message.
     */
    protected function backWithStatus(string $message, string $fallbackRoute = 'dashboard'): RedirectResponse
    {
        $previous = url()->previous();
        $isUsable = $previous
            && $previous !== url()->current()
            && rtrim($previous, '/') !== rtrim(url('/'), '/')
            && str_starts_with($previous, url('/'));
        $target = $isUsable ? $previous : route($fallbackRoute);

        return redirect()->to($target)->with('status', $message);
    }
}
