@extends('layouts.app')

@section('title', 'Rekomendasi Penghematan Energi')
@section('page-title', 'Rekomendasi Penghematan Energi')

@php $breadcrumbs = [['Rekomendasi Penghematan Energi', null]]; @endphp

@section('header-actions')
    @if ($household)
        <a href="{{ route('analysis.index') }}" class="btn-secondary">
            <x-icon name="chart" class="size-4" />
            <span class="hidden sm:inline">Lihat analisis</span>
        </a>
    @endif
@endsection

@section('content')
    @if (! $household)
        @include('shared.no-household')
    @elseif (! $summary->period)
        @include('shared.no-appliances')
    @else
        <div class="space-y-6">
            <section aria-label="Ringkasan potensi penghematan" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="card p-5">
                    <p class="text-sm text-slate-500">Potensi hemat energi</p>
                    <p class="mt-1 text-2xl font-bold text-navy tabular-nums">{{ $number($summary->savingKwh) }} <span class="text-base font-semibold">kWh/bln</span></p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-slate-500">Potensi hemat biaya</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-700 tabular-nums">{{ $rupiah($summary->savingCost) }}<span class="text-base font-semibold">/bln</span></p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-slate-500">Jumlah rekomendasi</p>
                    <p class="mt-1 text-2xl font-bold text-navy tabular-nums">{{ $summary->recommendations->count() }}</p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-slate-500">Perlu segera ditangani</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums {{ $summary->urgentCount ? 'text-red-700' : 'text-navy' }}">{{ $summary->urgentCount }}</p>
                </div>
            </section>

            @if ($summary->totalCost > 0 && $summary->savingCost > 0)
                <p class="text-sm text-slate-600">
                    Jika semua saran dijalankan, perkiraan tagihan {{ $summary->period->period_name }} turun dari
                    <strong class="font-semibold text-ink">{{ $rupiah($summary->totalCost) }}</strong> menjadi
                    <strong class="font-semibold text-emerald-700">{{ $rupiah(max(0, $summary->totalCost - $summary->savingCost)) }}</strong>
                    (hemat {{ $number(min(100, $summary->savingCost / $summary->totalCost * 100), 1) }}%).
                </p>
            @endif

            <section aria-labelledby="recs-list-title">
                <h2 id="recs-list-title" class="sr-only">Daftar rekomendasi</h2>
                @include('shared.recommendation-list', [
                    'recommendations' => $summary->recommendations,
                    'severityCounts' => $summary->severityCounts,
                ])
            </section>
        </div>
    @endif
@endsection
