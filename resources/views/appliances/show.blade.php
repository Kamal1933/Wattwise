@extends('layouts.app')

@section('title', $appliance->name)
@section('page-title', $appliance->name)

@php
    $breadcrumbs = [
        ['Data Peralatan Elektronik', route('appliances.index')],
        [$appliance->name, null],
    ];
    $rate = (float) ($summary->period?->applied_rate_per_kwh ?? $household?->tariff?->rate_per_kwh ?? 0);
    $monthlyKwh = (float) $appliance->power_watt * (float) $appliance->daily_hours * (float) $appliance->usage_factor
        * (int) $appliance->days_per_month * (int) $appliance->quantity / 1000;
@endphp

@section('header-actions')
    @include('shared.appliance-actions', ['appliance' => $appliance, 'showDetail' => false])
@endsection

@section('content')
    <div class="space-y-6">
        <section class="card grid grid-cols-1 gap-6 p-6 md:grid-cols-[14rem_minmax(0,1fr)]">
            <x-appliance-art :name="$appliance->name" :category="$appliance->category?->code" :photo="$appliance->photoUrl()" class="aspect-square w-full max-w-56" />
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-md bg-electric-50 px-2 py-0.5 text-xs font-semibold text-electric">{{ $appliance->category?->name }}</span>
                    @unless ($appliance->is_active)
                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">Tidak aktif</span>
                    @endunless
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-4 text-sm sm:grid-cols-3">
                    <div><dt class="text-slate-500">Daya per unit</dt><dd class="mt-0.5 font-semibold text-ink tabular-nums">{{ $number($appliance->power_watt, 0) }} W</dd></div>
                    <div><dt class="text-slate-500">Jumlah unit</dt><dd class="mt-0.5 font-semibold text-ink tabular-nums">{{ $appliance->quantity }}</dd></div>
                    <div><dt class="text-slate-500">Jam per hari</dt><dd class="mt-0.5 font-semibold text-ink tabular-nums">{{ $number($appliance->daily_hours) }} jam</dd></div>
                    <div><dt class="text-slate-500">Hari per bulan</dt><dd class="mt-0.5 font-semibold text-ink tabular-nums">{{ $appliance->days_per_month }} hari</dd></div>
                    <div><dt class="text-slate-500">Faktor pemakaian</dt><dd class="mt-0.5 font-semibold text-ink tabular-nums">{{ $number($appliance->usage_factor, 2) }}</dd></div>
                    <div><dt class="text-slate-500">Rumah tangga</dt><dd class="mt-0.5 font-semibold text-ink">{{ $household?->name }}</dd></div>
                </dl>
                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-xl bg-ice p-4">
                        <p class="text-xs text-slate-500">Konsumsi per bulan</p>
                        <p class="mt-1 text-xl font-bold text-navy tabular-nums">{{ $number($item->monthly_kwh ?? $monthlyKwh) }} kWh</p>
                    </div>
                    <div class="rounded-xl bg-ice p-4">
                        <p class="text-xs text-slate-500">Biaya per bulan</p>
                        <p class="mt-1 text-xl font-bold text-navy tabular-nums">{{ $rupiah($item->monthly_cost ?? $monthlyKwh * $rate) }}</p>
                    </div>
                    <div class="rounded-xl bg-ice p-4">
                        <p class="text-xs text-slate-500">Porsi dari total</p>
                        <p class="mt-1 text-xl font-bold text-navy tabular-nums">{{ $item ? $number($item->percentage_of_total).'%' : '—' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section aria-labelledby="appliance-recs-title">
            <h2 id="appliance-recs-title" class="mb-4 text-xl font-bold tracking-tight text-navy">Rekomendasi untuk peralatan ini</h2>
            @include('shared.recommendation-list', [
                'recommendations' => $relatedRecommendations,
                'severityCounts' => $relatedRecommendations->countBy('severity'),
                'showFilter' => false,
                'emptyMessage' => 'Belum ada rekomendasi khusus. Pemakaian peralatan ini masih dalam batas wajar.',
            ])
        </section>

        <section aria-labelledby="appliance-history-title">
            <h2 id="appliance-history-title" class="mb-4 text-xl font-bold tracking-tight text-navy">Riwayat perhitungan</h2>
            @if ($history->isEmpty())
                <div class="card p-6 text-sm text-slate-500">Peralatan ini belum pernah masuk perhitungan.</div>
            @else
                <div class="card overflow-x-auto">
                    <table class="w-full min-w-[32rem] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-ice text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-medium">Periode</th>
                                <th scope="col" class="px-5 py-3 text-right font-medium">kWh</th>
                                <th scope="col" class="px-5 py-3 text-right font-medium">Biaya</th>
                                <th scope="col" class="px-5 py-3 text-right font-medium">Porsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 tabular-nums">
                            @foreach ($history as $row)
                                <tr>
                                    <td class="px-5 py-3">
                                        @if ($row->calculationPeriod)
                                            <a href="{{ route('history.show', $row->calculationPeriod) }}" class="font-medium text-electric hover:underline">{{ $row->calculationPeriod->period_name }}</a>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right">{{ $number($row->monthly_kwh) }}</td>
                                    <td class="px-5 py-3 text-right">{{ $rupiah($row->monthly_cost) }}</td>
                                    <td class="px-5 py-3 text-right">{{ $number($row->percentage_of_total) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
