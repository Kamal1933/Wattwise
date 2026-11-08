@extends('layouts.app')

@section('title', $household?->name ?? 'Dashboard')

@php
    $rupiah = fn ($value, int $decimals = 0): string => 'Rp'.number_format((float) $value, $decimals, ',', '.');
    $number = fn ($value, int $decimals = 1): string => number_format((float) $value, $decimals, ',', '.');

    $activeAppliances = $household?->appliances->where('is_active', true) ?? collect();
    $items = $currentPeriod?->items->sortByDesc('monthly_kwh')->values() ?? collect();
    $itemsByAppliance = $items->keyBy('appliance_id');

    $severityMeta = [
        'critical' => ['label' => 'Kritis', 'pill' => 'bg-red-100 text-red-800', 'rail' => 'bg-red-500'],
        'high' => ['label' => 'Tinggi', 'pill' => 'bg-amber-100 text-amber-900', 'rail' => 'bg-amber-500'],
        'medium' => ['label' => 'Sedang', 'pill' => 'bg-electric-100 text-electric-600', 'rail' => 'bg-electric'],
        'low' => ['label' => 'Rendah', 'pill' => 'bg-slate-100 text-slate-700', 'rail' => 'bg-slate-400'],
    ];
    $sortedRecommendations = $recommendations->sortBy(fn ($rec) => array_search($rec->severity, array_keys($severityMeta)))->values();
    $severityCounts = $recommendations->countBy('severity');

    $assumptions = $currentPeriod?->calculation_assumptions ?? [];
    $simultaneousWatt = (float) ($assumptions['simultaneous_active_watt'] ?? 0);
    $powerLimitVa = (float) ($household?->tariff->power_limit_va ?? 0);
    $loadPercent = $powerLimitVa > 0 ? $simultaneousWatt / $powerLimitVa * 100 : 0;
    $loadTone = $loadPercent >= 100 ? 'bg-red-400' : ($loadPercent >= 85 ? 'bg-amber-400' : 'bg-emerald-400');

    $shareColors = ['#FFFFFF', '#BFDBFE', '#60A5FA', '#3B82F6', '#1D4ED8'];
    $shareSegments = $items->take(5)->values()->map(fn ($item, $index) => [
        'name' => $item->appliance?->name ?? 'Peralatan',
        'percent' => (float) $item->percentage_of_total,
        'kwh' => (float) $item->monthly_kwh,
        'color' => $shareColors[$index],
    ]);
    $otherPercent = max(0, 100 - $shareSegments->sum('percent'));
    if ($items->count() > 5 && $otherPercent > 0) {
        $shareSegments->push([
            'name' => 'Lainnya ('.($items->count() - 5).' alat)',
            'percent' => $otherPercent,
            'kwh' => (float) $items->slice(5)->sum('monthly_kwh'),
            'color' => '#475569',
        ]);
    }

    $latestReading = $recentReadings->first();
    $lastMeterEnd = $latestReading?->meter_end_kwh;
@endphp

@section('content')
    @if (! $household)
        @include('dashboard.partials.welcome')
    @else
        {{-- Page header --}}
        <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h1 class="truncate text-2xl font-bold tracking-tight text-navy sm:text-3xl">{{ $household->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $household->tariff->code ?? 'Tarif belum diatur' }}, {{ $number($powerLimitVa, 0) }} VA, {{ $household->occupant_count }} penghuni
                    @if ($household->address)
                        <span class="hidden md:inline">— {{ $household->address }}</span>
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="btn-secondary" data-dialog-open="dialog-meter-reading">
                    <x-icon name="meter" class="size-4" />
                    Catat meteran
                </button>
                <button type="button" class="btn-primary" data-dialog-open="dialog-add-appliance">
                    <x-icon name="plus" class="size-4" />
                    Tambah peralatan
                </button>
            </div>
        </header>

        <section id="ringkasan" aria-labelledby="ringkasan-title" class="mb-10">
            <h2 id="ringkasan-title" class="sr-only">Ringkasan bulan ini</h2>

            @if ($activeAppliances->isEmpty())
                @include('dashboard.partials.no-appliances')
            @else
                @include('dashboard.partials.bill-hero')
                @include('dashboard.partials.stats')
            @endif
        </section>

        @include('dashboard.partials.recommendations')
        @include('dashboard.partials.appliances')
        @include('dashboard.partials.meter-readings')
    @endif
@endsection

@push('dialogs')
    @include('dashboard.partials.dialogs')
@endpush
