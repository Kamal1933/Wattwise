@extends('layouts.app')

@section('title', 'Riwayat Analisis')
@section('page-title', 'Riwayat Analisis')

@php $breadcrumbs = [['Riwayat Analisis', null]]; @endphp

@section('content')
    @if (! $household)
        @include('shared.no-household')
    @elseif ($periods->isEmpty())
        <div class="card p-6 text-sm text-slate-600">
            Belum ada riwayat. Riwayat terbentuk otomatis setiap kali peralatan disimpan atau analisis dijalankan ulang.
            <a href="{{ route('analysis.index') }}" class="font-semibold text-electric hover:underline">Mulai analisis</a>
        </div>
    @else
        <div class="card overflow-x-auto">
            <table class="w-full min-w-[46rem] text-left text-sm">
                <thead class="border-b border-slate-200 bg-ice text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-medium">Periode</th>
                        <th scope="col" class="px-5 py-3 text-right font-medium">Peralatan</th>
                        <th scope="col" class="px-5 py-3 text-right font-medium">Konsumsi</th>
                        <th scope="col" class="px-5 py-3 text-right font-medium">Perkiraan biaya</th>
                        <th scope="col" class="px-5 py-3 text-right font-medium">Rekomendasi</th>
                        <th scope="col" class="px-5 py-3 text-right font-medium">Potensi hemat</th>
                        <th scope="col" class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 tabular-nums">
                    @foreach ($periods as $period)
                        <tr class="hover:bg-ice/60">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-ink">{{ $period->period_name }}</p>
                                <p class="text-xs text-slate-500">{{ $period->start_date?->translatedFormat('j M Y') }} – {{ $period->end_date?->translatedFormat('j M Y') }}</p>
                            </td>
                            <td class="px-5 py-3 text-right">{{ $period->items_count }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-navy whitespace-nowrap">{{ $number($period->total_estimated_kwh) }} kWh</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">{{ $rupiah($period->total_estimated_cost) }}</td>
                            <td class="px-5 py-3 text-right">{{ $period->recommendations_count }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap text-emerald-700">{{ $rupiah($period->recommendations_sum_estimated_cost_saving ?? 0) }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('history.show', $period) }}" class="btn-ghost px-2 text-electric">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $periods->links() }}</div>
    @endif
@endsection
