@extends('layouts.app')

@section('title', 'Laporan Konsumsi Energi')
@section('page-title', 'Laporan Konsumsi Energi')

@php
    $breadcrumbs = [['Laporan Konsumsi Energi', null]];
    $rows = $periods->map(fn ($period) => [
        'period' => $period,
        'label' => $period->start_date?->translatedFormat('M y') ?? $period->period_name,
        'kwh' => (float) $period->total_estimated_kwh,
        'cost' => (float) $period->total_estimated_cost,
        'actual' => \App\Http\Controllers\Web\ReportsController::actualKwhFor($period),
    ]);
    $maxKwh = max(1, (float) $rows->max(fn ($row) => max($row['kwh'], $row['actual'] ?? 0)));
    $avgKwh = $rows->avg('kwh') ?? 0;
@endphp

@section('header-actions')
    @if ($household)
        <button type="button" class="btn-secondary print:hidden" onclick="window.print()">
            <x-icon name="printer" class="size-4" />
            <span class="hidden sm:inline">Cetak</span>
        </button>
        <button type="button" class="btn-primary print:hidden" data-dialog-open="dialog-meter-reading">
            <x-icon name="meter" class="size-4" />
            <span class="hidden sm:inline">Catat meteran</span>
        </button>
    @endif
@endsection

@section('content')
    @if (! $household)
        @include('shared.no-household')
    @else
        <div class="space-y-8">
            <section aria-label="Ringkasan laporan" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="card p-5">
                    <p class="text-sm text-slate-500">Rumah tangga</p>
                    <p class="mt-1 text-lg font-bold text-navy">{{ $household->name }}</p>
                    <p class="text-xs text-slate-500">{{ $household->tariff?->code }}, {{ $number($household->tariff?->power_limit_va, 0) }} VA</p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-slate-500">Rata-rata per bulan</p>
                    <p class="mt-1 text-2xl font-bold text-navy tabular-nums">{{ $number($avgKwh) }} kWh</p>
                    <p class="text-xs text-slate-500">dari {{ $rows->count() }} periode terakhir</p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-slate-500">Total perkiraan biaya</p>
                    <p class="mt-1 text-2xl font-bold text-navy tabular-nums">{{ $rupiah($rows->sum('cost')) }}</p>
                    <p class="text-xs text-slate-500">{{ $rows->count() }} periode</p>
                </div>
            </section>

            <section aria-labelledby="report-trend-title" class="card p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 id="report-trend-title" class="text-lg font-bold text-navy">Tren konsumsi bulanan</h2>
                    <p class="flex items-center gap-4 text-xs text-slate-600">
                        <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-electric"></span>Perkiraan</span>
                        <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-emerald-500"></span>Meteran</span>
                    </p>
                </div>

                @if ($rows->isEmpty())
                    <p class="mt-4 text-sm text-slate-500">Belum ada data periode.</p>
                @else
                    <div class="mt-6 flex h-56 items-end gap-3 overflow-x-auto border-b border-slate-200 pb-0" role="img" aria-label="Grafik batang konsumsi per bulan">
                        @foreach ($rows as $row)
                            <div class="flex min-w-12 flex-1 flex-col items-center gap-1">
                                <span class="text-[11px] font-semibold text-navy tabular-nums">{{ $number($row['kwh'], 0) }}</span>
                                <div class="flex h-44 w-full items-end justify-center gap-1">
                                    <div class="w-1/2 max-w-6 rounded-t bg-electric" style="height: {{ max(2, $row['kwh'] / $maxKwh * 100) }}%" title="Perkiraan {{ $number($row['kwh']) }} kWh"></div>
                                    @if ($row['actual'] !== null)
                                        <div class="w-1/2 max-w-6 rounded-t bg-emerald-500" style="height: {{ max(2, $row['actual'] / $maxKwh * 100) }}%" title="Meteran {{ $number($row['actual']) }} kWh"></div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-2 flex gap-3 overflow-x-auto">
                        @foreach ($rows as $row)
                            <span class="min-w-12 flex-1 text-center text-xs text-slate-500">{{ $row['label'] }}</span>
                        @endforeach
                    </div>
                @endif
            </section>

            <section aria-labelledby="report-table-title">
                <h2 id="report-table-title" class="mb-4 text-xl font-bold tracking-tight text-navy">Perkiraan dibanding meteran</h2>
                @if ($rows->isEmpty())
                    <div class="card p-6 text-sm text-slate-500">Belum ada data periode.</div>
                @else
                    <div class="card overflow-x-auto">
                        <table class="w-full min-w-[40rem] text-left text-sm">
                            <thead class="border-b border-slate-200 bg-ice text-slate-500">
                                <tr>
                                    <th scope="col" class="px-5 py-3 font-medium">Periode</th>
                                    <th scope="col" class="px-5 py-3 text-right font-medium">Perkiraan kWh</th>
                                    <th scope="col" class="px-5 py-3 text-right font-medium">Perkiraan biaya</th>
                                    <th scope="col" class="px-5 py-3 text-right font-medium">Meteran kWh</th>
                                    <th scope="col" class="px-5 py-3 text-right font-medium">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 tabular-nums">
                                @foreach ($rows->reverse() as $row)
                                    @php $diff = $row['actual'] !== null && $row['kwh'] > 0 ? ($row['actual'] - $row['kwh']) / $row['kwh'] * 100 : null; @endphp
                                    <tr>
                                        <td class="px-5 py-3">
                                            <a href="{{ route('history.show', $row['period']) }}" class="font-medium text-electric hover:underline print:text-ink">{{ $row['period']->period_name }}</a>
                                        </td>
                                        <td class="px-5 py-3 text-right font-semibold text-navy">{{ $number($row['kwh']) }}</td>
                                        <td class="px-5 py-3 text-right whitespace-nowrap">{{ $rupiah($row['cost']) }}</td>
                                        <td class="px-5 py-3 text-right">{{ $row['actual'] !== null ? $number($row['actual']) : '—' }}</td>
                                        <td class="px-5 py-3 text-right {{ $diff === null ? 'text-slate-400' : (abs($diff) <= 10 ? 'text-emerald-700' : 'text-amber-700') }}">
                                            {{ $diff === null ? '—' : ($diff > 0 ? '+' : '').$number($diff, 1).'%' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section aria-labelledby="report-meter-title">
                <h2 id="report-meter-title" class="mb-4 text-xl font-bold tracking-tight text-navy">Catatan meteran PLN</h2>
                @include('shared.meter-readings-table')
            </section>
        </div>
    @endif
@endsection
