@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@php
    $breadcrumbs = [['Dashboard', null]];
    $activeAppliances = $household?->appliances->where('is_active', true) ?? collect();
@endphp

@section('content')
    @if (! $household)
        @include('shared.no-household')
    @else
        <div class="space-y-6">
            @include('dashboard.partials.welcome-banner')

            @if ($activeAppliances->isEmpty())
                @include('shared.no-appliances')
            @else
                @include('dashboard.partials.kpis')
                @include('shared.consumption-overview')
                @include('dashboard.partials.quick-tips')

                {{-- Recommendation summary --}}
                <section aria-labelledby="dashboard-recs-title" class="card p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 id="dashboard-recs-title" class="text-lg font-bold text-navy">Ringkasan rekomendasi</h2>
                            <p class="text-sm text-slate-500">
                                {{ $summary->recommendations->count() }} saran, potensi hemat
                                <strong class="font-semibold text-emerald-700">{{ $rupiah($summary->savingCost) }}/bulan</strong>
                                ({{ $number($summary->savingKwh) }} kWh).
                            </p>
                        </div>
                        <a href="{{ route('recommendations.index') }}" class="btn-secondary">Lihat semua</a>
                    </div>

                    @if ($summary->recommendations->isNotEmpty())
                        <ul class="mt-4 divide-y divide-slate-100">
                            @foreach ($summary->recommendations->take(3) as $rec)
                                @php $meta = $severityMeta[$rec->severity] ?? $severityMeta['low']; @endphp
                                <li class="flex flex-wrap items-center gap-3 py-3">
                                    <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $meta['pill'] }}">{{ $meta['label'] }}</span>
                                    <span class="min-w-0 flex-1 font-medium text-ink">{{ $rec->title }}</span>
                                    @if ($rec->estimated_cost_saving > 0)
                                        <span class="text-sm font-semibold text-emerald-700 tabular-nums">Hemat {{ $rupiah($rec->estimated_cost_saving) }}/bln</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif
        </div>
    @endif
@endsection
