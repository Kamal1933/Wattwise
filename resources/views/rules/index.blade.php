@extends('layouts.app')

@section('title', 'Basis aturan')
@section('page-title', 'Basis aturan')

@php
    $breadcrumbs = [['Basis Aturan', null]];
    $severityMeta = [
        'critical' => ['label' => 'Kritis', 'pill' => 'bg-red-100 text-red-800'],
        'high' => ['label' => 'Tinggi', 'pill' => 'bg-amber-100 text-amber-900'],
        'medium' => ['label' => 'Sedang', 'pill' => 'bg-electric-100 text-electric-600'],
        'low' => ['label' => 'Rendah', 'pill' => 'bg-slate-100 text-slate-700'],
    ];
@endphp

@section('content')
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-navy">Basis aturan sistem pakar</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-500">
                Semua aturan JIKA–MAKA yang dipakai mesin forward chaining, diurutkan dari prioritas tertinggi (angka terkecil dijalankan lebih dulu).
            </p>
        </div>
        <button type="button" class="btn-secondary print:hidden" onclick="window.print()">
            <x-icon name="printer" class="size-4" />
            Cetak / simpan PDF
        </button>
    </header>

    {{-- Summary --}}
    <dl class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4 print:grid-cols-4">
        @foreach ([
            ['Total aturan', $stats['total']],
            ['Aturan aktif', $stats['active']],
            ['Aturan fakta (tanpa saran)', $stats['fact_only']],
            ['Aturan tingkat rumah', $stats['household']],
        ] as [$label, $value])
            <div class="card p-4">
                <dt class="text-sm text-slate-500">{{ $label }}</dt>
                <dd class="mt-1 text-2xl font-bold text-navy tabular-nums">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>

    {{-- How to read --}}
    <section class="card mb-8 p-5 text-sm text-slate-600 break-inside-avoid">
        <h2 class="font-semibold text-navy">Cara membaca</h2>
        <ul class="mt-2 grid gap-2 md:grid-cols-3 print:grid-cols-3">
            <li><strong class="font-semibold text-ink">DAN</strong>: semua syarat dalam satu kelompok harus terpenuhi.</li>
            <li><strong class="font-semibold text-ink">ATAU</strong>: cukup salah satu kelompok syarat yang terpenuhi.</li>
            <li><strong class="font-semibold text-ink">Fakta turunan</strong>: kesimpulan satu aturan yang menjadi syarat aturan lain (rantai inferensi).</li>
        </ul>
    </section>

    {{-- Inference chains --}}
    @if ($chains->isNotEmpty())
        <section class="mb-10 break-inside-avoid" aria-labelledby="chains-title">
            <h2 id="chains-title" class="mb-3 text-lg font-bold text-navy">Rantai inferensi</h2>
            <div class="grid gap-3 md:grid-cols-2 print:grid-cols-1">
                @foreach ($chains as $chain)
                    <div class="card flex flex-wrap items-center gap-2 p-4 text-sm">
                        <a href="#{{ $chain['from'] }}" class="rounded-md bg-navy px-2 py-1 font-semibold text-white">{{ $chain['from'] }}</a>
                        <x-icon name="arrow" class="size-4 text-slate-400" />
                        <span class="rounded-md bg-emerald-50 px-2 py-1 font-medium text-emerald-800">{{ $chain['fact_label'] }}</span>
                        <x-icon name="arrow" class="size-4 text-slate-400" />
                        <a href="#{{ $chain['to'] }}" class="rounded-md bg-navy px-2 py-1 font-semibold text-white">{{ $chain['to'] }}</a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Rules --}}
    <section aria-labelledby="rules-title">
        <h2 id="rules-title" class="mb-3 text-lg font-bold text-navy break-after-avoid">Daftar aturan</h2>

        @if ($rules->isEmpty())
            <div class="card p-6 text-sm text-slate-500">
                Belum ada aturan. Jalankan <code class="rounded bg-ice px-1.5 py-0.5">php artisan db:seed --class=ExpertRuleSeeder</code>.
            </div>
        @else
            <div class="space-y-4">
                @foreach ($rules as $rule)
                    @php $meta = $severityMeta[$rule['severity']] ?? $severityMeta['low']; @endphp
                    <article id="{{ $rule['code'] }}" class="card scroll-mt-24 overflow-hidden break-inside-avoid {{ $rule['is_active'] ? '' : 'opacity-60' }}">
                        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-ice/60 px-5 py-3 text-xs">
                            <span class="rounded-md bg-navy px-2 py-1 text-sm font-bold text-white">{{ $rule['code'] }}</span>
                            <span class="rounded-md bg-white px-2 py-1 font-medium text-slate-600 ring-1 ring-slate-200">Prioritas {{ $rule['priority'] }}</span>
                            <span class="rounded-md bg-white px-2 py-1 font-medium text-slate-600 ring-1 ring-slate-200">{{ $rule['scope'] === 'household' ? 'Tingkat rumah' : 'Per peralatan' }}</span>
                            @if ($rule['produces_recommendation'])
                                <span class="rounded-md px-2 py-1 font-semibold {{ $meta['pill'] }}">{{ $meta['label'] }}</span>
                            @else
                                <span class="rounded-md bg-emerald-50 px-2 py-1 font-semibold text-emerald-800">Aturan fakta</span>
                            @endif
                            @unless ($rule['is_active'])
                                <span class="rounded-md bg-slate-200 px-2 py-1 font-semibold text-slate-700">Nonaktif</span>
                            @endunless
                        </div>

                        <div class="p-5">
                            <h3 class="font-semibold text-navy">{{ $rule['title'] }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $rule['description'] }}</p>

                            <div class="mt-4 grid gap-4 md:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] print:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
                                {{-- IF --}}
                                <div class="rounded-xl border border-electric/20 bg-electric-50/50 p-4">
                                    <p class="text-sm font-bold text-electric">JIKA</p>
                                    <div class="mt-2 space-y-2">
                                        @foreach ($rule['groups'] as $group)
                                            @if (! $loop->first)
                                                <p class="text-xs font-bold text-amber-700">ATAU</p>
                                            @endif
                                            <ul class="space-y-1.5 text-sm text-ink">
                                                @foreach ($group['readable'] as $condition)
                                                    <li class="flex gap-2">
                                                        <span class="w-9 shrink-0 text-xs font-semibold text-slate-400 pt-0.5">{{ $loop->first ? '' : 'DAN' }}</span>
                                                        <span>{{ $condition }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- THEN --}}
                                <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                                    <p class="text-sm font-bold text-emerald-700">MAKA</p>
                                    <ul class="mt-2 space-y-2 text-sm text-ink">
                                        @if ($rule['conclusion'])
                                            <li>Simpulkan fakta <strong class="font-semibold">{{ $rule['conclusion']['label'] }} = {{ $rule['conclusion']['value'] }}</strong></li>
                                        @endif
                                        @if ($rule['produces_recommendation'])
                                            <li>Berikan rekomendasi <strong class="font-semibold">{{ $rule['title'] }}</strong></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            @if ($rule['uses_facts'] || $rule['used_by'])
                                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-600">
                                    @if ($rule['uses_facts'])
                                        <p>
                                            Memakai fakta dari
                                            @foreach ($rule['uses_facts'] as $use)
                                                <a href="#{{ $use['rule_code'] }}" class="font-semibold text-electric hover:underline">{{ $use['rule_code'] }}</a>@if (! $loop->last), @endif
                                            @endforeach
                                        </p>
                                    @endif
                                    @if ($rule['used_by'])
                                        <p>
                                            Kesimpulannya dipakai oleh
                                            @foreach ($rule['used_by'] as $code)
                                                <a href="#{{ $code }}" class="font-semibold text-electric hover:underline">{{ $code }}</a>@if (! $loop->last), @endif
                                            @endforeach
                                        </p>
                                    @endif
                                </div>
                            @endif

                            <p class="mt-4 overflow-x-auto rounded-lg bg-navy-950 px-3 py-2 font-mono text-xs leading-relaxed whitespace-nowrap text-blue-100 print:whitespace-normal print:bg-white print:text-ink print:ring-1 print:ring-slate-300">{{ $rule['technical'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
