<?php

namespace App\Services\Calculation;

use App\Models\CalculationItem;
use App\Models\CalculationPeriod;
use App\Models\Household;
use App\Models\RecommendationHistory;
use Illuminate\Support\Collection;

/**
 * Read-only view model of one calculation period: totals, per-appliance breakdown,
 * chart segments, load against the PLN contract and the recommendations it produced.
 */
class PeriodSummary
{
    public const SEVERITY_ORDER = ['critical', 'high', 'medium', 'low'];

    public const CHART_COLORS = ['#2563EB', '#14B8A6', '#F59E0B', '#8B5CF6', '#38BDF8', '#94A3B8'];

    /** @var Collection<int, CalculationItem> */
    public Collection $items;

    /** @var Collection<int, CalculationItem> */
    public Collection $itemsByAppliance;

    /** @var Collection<int, RecommendationHistory> */
    public Collection $recommendations;

    /** @var Collection<string, int> */
    public Collection $severityCounts;

    /** @var list<array{name: string, kwh: float, percent: float, color: string}> */
    public array $chartSegments = [];

    public float $totalKwh;

    public float $totalCost;

    public float $simultaneousWatt;

    public float $powerLimitVa;

    public float $loadPercent;

    public ?float $kwhChange = null;

    public ?float $costChange = null;

    public int $urgentCount;

    public float $savingKwh;

    public float $savingCost;

    public function __construct(
        public ?CalculationPeriod $period,
        public ?Household $household,
        public ?CalculationPeriod $previous = null,
    ) {
        $this->items = $period?->items->sortByDesc('monthly_kwh')->values() ?? collect();
        $this->itemsByAppliance = $this->items->keyBy('appliance_id');
        $this->totalKwh = (float) ($period?->total_estimated_kwh ?? 0);
        $this->totalCost = (float) ($period?->total_estimated_cost ?? 0);

        $assumptions = $period?->calculation_assumptions ?? [];
        $this->simultaneousWatt = (float) ($assumptions['simultaneous_active_watt'] ?? 0);
        $this->powerLimitVa = (float) ($assumptions['power_limit_va'] ?? $household?->tariff?->power_limit_va ?? 0);
        $this->loadPercent = $this->powerLimitVa > 0 ? $this->simultaneousWatt / $this->powerLimitVa * 100 : 0;

        if ($previous) {
            $this->kwhChange = self::percentChange($this->totalKwh, (float) $previous->total_estimated_kwh);
            $this->costChange = self::percentChange($this->totalCost, (float) $previous->total_estimated_cost);
        }

        $this->recommendations = ($period?->recommendations ?? collect())
            ->sortBy(fn (RecommendationHistory $rec): int => (int) array_search($rec->severity, self::SEVERITY_ORDER, true))
            ->values();
        $this->severityCounts = $this->recommendations->countBy('severity');
        $this->urgentCount = $this->recommendations->whereIn('severity', ['critical', 'high'])->count();
        $this->savingKwh = (float) $this->recommendations->sum('estimated_kwh_saving');
        $this->savingCost = (float) $this->recommendations->sum('estimated_cost_saving');

        $this->chartSegments = $this->buildChartSegments();
    }

    public static function percentChange(float $current, float $previous): ?float
    {
        return $previous > 0 ? (($current - $previous) / $previous) * 100 : null;
    }

    /**
     * Tailwind class for the load bar colour.
     */
    public function loadTone(): string
    {
        return $this->loadPercent >= 100 ? 'bg-red-500' : ($this->loadPercent >= 85 ? 'bg-amber-500' : 'bg-emerald-500');
    }

    public function loadMessage(): string
    {
        return $this->loadPercent >= 100
            ? 'Jangan nyalakan alat berdaya besar bersamaan.'
            : ($this->loadPercent >= 85 ? 'Mendekati batas daya.' : 'Masih aman.');
    }

    /**
     * @return list<array{name: string, kwh: float, percent: float, color: string}>
     */
    protected function buildChartSegments(): array
    {
        $segments = $this->items->take(5)->values()->map(fn (CalculationItem $item, int $index): array => [
            'name' => $item->appliance?->name ?? 'Peralatan',
            'kwh' => (float) $item->monthly_kwh,
            'percent' => (float) $item->percentage_of_total,
            'color' => self::CHART_COLORS[$index],
        ])->all();

        if ($this->items->count() > 5) {
            $segments[] = [
                'name' => 'Lainnya ('.($this->items->count() - 5).' alat)',
                'kwh' => (float) $this->items->slice(5)->sum('monthly_kwh'),
                'percent' => max(0, 100 - array_sum(array_column($segments, 'percent'))),
                'color' => self::CHART_COLORS[5],
            ];
        }

        return $segments;
    }
}
