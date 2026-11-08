@extends('layouts.app')

@section('title', 'Riwayat '.$period->period_name)
@section('page-title', 'Analisis '.$period->period_name)

@php
    $breadcrumbs = [
        ['Riwayat Analisis', route('history.index')],
        [$period->period_name, null],
    ];
    $actualKwh = \App\Http\Controllers\Web\ReportsController::actualKwhFor($period);
@endphp

@section('header-actions')
    <a href="{{ route('history.index') }}" class="btn-secondary">
        <x-icon name="arrow-left" class="size-4" />
        <span class="hidden sm:inline">Kembali</span>
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <section aria-label="Ringkasan periode" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card p-5">
                <p class="text-sm text-slate-500">Perkiraan konsumsi</p>
                <p class="mt-1 text-2xl font-bold text-navy tabular-nums">{{ $number($periodSummary->totalKwh) }} kWh</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-slate-500">Perkiraan biaya</p>
                <p class="mt-1 text-2xl font-bold text-navy tabular-nums">{{ $rupiah($periodSummary->totalCost) }}</p>
                <p class="mt-1 text-xs text-slate-500">Tarif {{ $rupiah($period->applied_rate_per_kwh, 2) }}/kWh</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-slate-500">Meteran tercatat</p>
                <p class="mt-1 text-2xl font-bold text-navy tabular-nums">{{ $actualKwh !== null ? $number($actualKwh).' kWh' : '—' }}</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-slate-500">Potensi hemat</p>
                <p class="mt-1 text-2xl font-bold text-emerald-700 tabular-nums">{{ $rupiah($periodSummary->savingCost) }}</p>
            </div>
        </section>

        <section aria-labelledby="history-items-title">
            <h2 id="history-items-title" class="mb-4 text-xl font-bold tracking-tight text-navy">Rincian peralatan</h2>
            @if ($periodSummary->items->isEmpty())
                <div class="card p-6 text-sm text-slate-500">Tidak ada peralatan pada periode ini.</div>
            @else
                <div class="card overflow-x-auto">
                    <table class="w-full min-w-[40rem] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-ice text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-medium">Peralatan</th>
                                <th scope="col" class="px-5 py-3 text-right font-medium">Daya × unit</th>
                                <th scope="col" class="px-5 py-3 text-right font-medium">Jam/hari</th>
                                <th scope="col" class="px-5 py-3 text-right font-medium">kWh</th>
                                <th scope="col" class="px-5 py-3 text-right font-medium">Biaya</th>
                                <th scope="col" class="px-5 py-3 text-right font-medium">Porsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 tabular-nums">
                            @foreach ($periodSummary->items as $item)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-ink">{{ $item->appliance?->name ?? 'Peralatan terhapus' }}</td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap">{{ $number($item->power_watt, 0) }} W × {{ $item->quantity }}</td>
                                    <td class="px-5 py-3 text-right">{{ $number($item->daily_hours) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-navy">{{ $number($item->monthly_kwh) }}</td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap">{{ $rupiah($item->monthly_cost) }}</td>
                                    <td class="px-5 py-3 text-right">{{ $number($item->percentage_of_total) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section aria-labelledby="history-recs-title">
            <h2 id="history-recs-title" class="mb-4 text-xl font-bold tracking-tight text-navy">Rekomendasi periode ini</h2>
            @include('shared.recommendation-list', [
                'recommendations' => $periodSummary->recommendations,
                'severityCounts' => $periodSummary->severityCounts,
            ])
        </section>
    </div>
@endsection
