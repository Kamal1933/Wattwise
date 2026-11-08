@extends('layouts.app')

@section('title', 'Analisis Konsumsi Energi')
@section('page-title', 'Analisis Konsumsi Energi')

@php
    $breadcrumbs = [['Analisis Konsumsi Energi', null]];
    $inlineFormId = 'analysis-appliance-form';
    $rate = (float) ($currentPeriod?->applied_rate_per_kwh ?? $household?->tariff?->rate_per_kwh ?? 0);
    $example = $summary->items->first();
@endphp

@section('header-actions')
    @if ($household && $summary->period)
        <form method="POST" action="{{ route('households.reanalyze', $household) }}">
            @csrf
            <button type="submit" class="btn-primary">
                <x-icon name="refresh" class="size-4" />
                <span class="hidden sm:inline">Analisis ulang</span>
            </button>
        </form>
    @endif
@endsection

@section('content')
    @if (! $household)
        @include('shared.no-household')
    @else
        <div class="space-y-8">
            {{-- Input + method --}}
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,7fr)_minmax(0,5fr)]">
                <section class="card p-6" aria-labelledby="analysis-form-title">
                    <h2 id="analysis-form-title" class="text-lg font-bold text-navy">Masukkan peralatan elektronik</h2>
                    <p class="mt-1 text-sm text-slate-500">Setiap peralatan yang disimpan langsung dihitung dan dianalisis ulang oleh sistem pakar.</p>

                    @if ($errors->any() && old('_dialog') === $inlineFormId)
                        <div role="alert" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                            <ul class="list-inside list-disc space-y-0.5">
                                @foreach ($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('households.appliances.store', $household) }}" enctype="multipart/form-data" class="mt-5 space-y-4" id="{{ $inlineFormId }}">
                        @csrf
                        <input type="hidden" name="_dialog" value="{{ $inlineFormId }}">
                        @include('shared.appliance-fields', ['dialogId' => $inlineFormId, 'fieldPrefix' => 'analysis'])
                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary">
                                <x-icon name="chart" class="size-4" />
                                Simpan dan analisis
                            </button>
                        </div>
                    </form>
                </section>

                <section class="card p-6" aria-labelledby="analysis-method-title">
                    <h2 id="analysis-method-title" class="text-lg font-bold text-navy">Cara WattWise menghitung</h2>
                    <ol class="mt-4 space-y-4 text-sm text-slate-700">
                        <li class="flex gap-3">
                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-electric-50 font-semibold text-electric">1</span>
                            <span><strong class="font-semibold text-ink">Energi per bulan (kWh)</strong> = daya (W) × jam per hari × faktor pemakaian × hari per bulan × jumlah unit ÷ 1.000</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-electric-50 font-semibold text-electric">2</span>
                            <span><strong class="font-semibold text-ink">Biaya</strong> = kWh × tarif golongan {{ $household->tariff?->code }} ({{ $rupiah($rate, 2) }}/kWh)</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-electric-50 font-semibold text-electric">3</span>
                            <span><strong class="font-semibold text-ink">Inferensi</strong>: hasilnya dicocokkan dengan basis aturan sistem pakar (forward chaining) untuk menghasilkan rekomendasi.</span>
                        </li>
                    </ol>

                    @if ($example)
                        <div class="mt-5 rounded-xl bg-ice p-4 text-sm">
                            <p class="font-semibold text-navy">Contoh: {{ $example->appliance?->name }}</p>
                            <p class="mt-2 font-mono text-xs leading-relaxed text-slate-700">
                                {{ $number($example->power_watt, 0) }} W × {{ $number($example->daily_hours) }} jam × {{ $number($example->usage_factor, 2) }}
                                × {{ $example->days_per_month }} hari × {{ $example->quantity }} unit ÷ 1.000
                                = <strong>{{ $number($example->monthly_kwh) }} kWh</strong><br>
                                {{ $number($example->monthly_kwh) }} kWh × {{ $rupiah($rate, 2) }} = <strong>{{ $rupiah($example->monthly_cost) }}</strong>
                            </p>
                        </div>
                    @endif
                </section>
            </div>

            @if (! $summary->period)
                @include('shared.no-appliances')
            @else
                {{-- Calculation table --}}
                <section aria-labelledby="analysis-table-title">
                    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 id="analysis-table-title" class="text-xl font-bold tracking-tight text-navy">Rincian perhitungan {{ $summary->period->period_name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">Diurutkan dari pemakaian terbesar.</p>
                        </div>
                        <a href="{{ route('appliances.index') }}" class="text-sm font-semibold text-electric hover:underline">Kelola data peralatan</a>
                    </div>

                    <div class="card overflow-x-auto">
                        <table class="w-full min-w-[52rem] text-left text-sm">
                            <thead class="border-b border-slate-200 bg-ice text-slate-500">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-medium">Peralatan</th>
                                    <th scope="col" class="px-4 py-3 text-right font-medium">Daya</th>
                                    <th scope="col" class="px-4 py-3 text-right font-medium">Unit</th>
                                    <th scope="col" class="px-4 py-3 text-right font-medium">Jam/hari</th>
                                    <th scope="col" class="px-4 py-3 text-right font-medium">Hari</th>
                                    <th scope="col" class="px-4 py-3 text-right font-medium">Faktor</th>
                                    <th scope="col" class="px-4 py-3 text-right font-medium">kWh/bulan</th>
                                    <th scope="col" class="px-4 py-3 text-right font-medium">Biaya</th>
                                    <th scope="col" class="px-4 py-3 text-right font-medium">Porsi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 tabular-nums">
                                @foreach ($summary->items as $item)
                                    <tr class="hover:bg-ice/60">
                                        <td class="px-4 py-2.5">
                                            <a href="{{ $item->appliance ? route('appliances.show', $item->appliance) : '#' }}" class="flex items-center gap-3 font-semibold text-ink hover:text-electric">
                                                <x-appliance-art :name="$item->appliance?->name" :category="$item->appliance?->category?->code" :photo="$item->appliance?->photoUrl()" class="size-9" />
                                                {{ $item->appliance?->name }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2.5 text-right whitespace-nowrap">{{ $number($item->power_watt, 0) }} W</td>
                                        <td class="px-4 py-2.5 text-right">{{ $item->quantity }}</td>
                                        <td class="px-4 py-2.5 text-right">{{ $number($item->daily_hours) }}</td>
                                        <td class="px-4 py-2.5 text-right">{{ $item->days_per_month }}</td>
                                        <td class="px-4 py-2.5 text-right">{{ $number($item->usage_factor, 2) }}</td>
                                        <td class="px-4 py-2.5 text-right font-semibold whitespace-nowrap text-navy">{{ $number($item->monthly_kwh) }}</td>
                                        <td class="px-4 py-2.5 text-right whitespace-nowrap">{{ $rupiah($item->monthly_cost) }}</td>
                                        <td class="px-4 py-2.5 text-right">{{ $number($item->percentage_of_total) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t-2 border-slate-200 bg-ice font-bold text-navy tabular-nums">
                                <tr>
                                    <td class="px-4 py-3" colspan="6">Total {{ $summary->items->count() }} peralatan aktif</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">{{ $number($summary->totalKwh) }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">{{ $rupiah($summary->totalCost) }}</td>
                                    <td class="px-4 py-3 text-right">100%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                {{-- Charts --}}
                <section aria-label="Grafik hasil analisis">
                    @include('shared.consumption-overview')
                </section>

                {{-- Inference trace --}}
                <section aria-labelledby="analysis-trace-title" class="card p-6">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 id="analysis-trace-title" class="text-lg font-bold text-navy">Hasil analisis sistem pakar</h2>
                            <p class="mt-1 text-sm text-slate-500">Urutan aturan yang terpicu dalam siklus forward chaining.</p>
                        </div>
                        <a href="{{ route('recommendations.index') }}" class="btn-secondary">Lihat {{ $summary->recommendations->count() }} rekomendasi</a>
                    </div>

                    @if (empty($inferenceSteps))
                        <p class="mt-4 text-sm text-slate-500">Tidak ada aturan yang terpicu untuk data saat ini.</p>
                    @else
                        <ol class="mt-5 space-y-3">
                            @foreach ($inferenceSteps as $step)
                                <li class="flex gap-4">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-navy text-sm font-bold text-white">{{ $step['cycle'] }}</span>
                                    <div class="min-w-0 flex-1 rounded-xl border border-slate-200 p-3 text-sm">
                                        <p class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-md bg-electric-50 px-2 py-0.5 text-xs font-bold text-electric">{{ $step['rule_code'] }}</span>
                                            <span class="font-semibold text-ink">{{ $step['title'] }}</span>
                                        </p>
                                        <p class="mt-1 text-slate-600">
                                            Terpicu pada <strong class="font-medium text-ink">{{ $step['subject'] }}</strong>
                                            @if ($step['used'])
                                                memakai fakta turunan dari {{ implode(', ', $step['used']) }}
                                            @endif
                                        </p>
                                        <p class="mt-1 text-slate-600">
                                            @if ($step['derived'])
                                                Menyimpulkan fakta <strong class="font-medium text-emerald-700">{{ $step['derived'] }}</strong>.
                                            @endif
                                            @if ($step['recommends'])
                                                Menghasilkan rekomendasi.
                                            @endif
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>
            @endif
        </div>
    @endif
@endsection
