<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WattWise — Household Electrical Energy Management & Expert System</title>
    
    <!-- Google Fonts: Instrument Sans / Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAFC; /* Ice White primary base */
            color: #0F172A; /* Soft Navy primary text */
        }
        .electric-blue {
            color: #2563EB;
        }
        .bg-electric-blue {
            background-color: #2563EB;
        }
        .border-electric-blue {
            border-color: #2563EB;
        }
        .soft-navy {
            color: #0F172A;
        }
        .bg-soft-navy {
            background-color: #0F172A;
        }
        .bg-card {
            background-color: #FFFFFF;
        }
        /* Custom smooth scroll and scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #F1F5F9;
        }
        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
    </style>
</head>
<body class="min-h-screen antialiased bg-slate-50 text-slate-900">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Logo -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-extrabold tracking-tight text-slate-900">Watt<span class="text-blue-600">Wise</span></span>
                            <span class="px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase bg-blue-50 text-blue-700 border border-blue-200 rounded-full">Sistem Pakar</span>
                        </div>
                        <p class="text-[11px] font-medium text-slate-500 hidden sm:block">Manajemen Energi & Rekomendasi Hemat Listrik</p>
                    </div>
                </div>

                <!-- Household Selector & Quick Actions -->
                <div class="flex items-center gap-3">
                    @if($households->isNotEmpty())
                        <form method="GET" action="{{ route('dashboard') }}" class="hidden sm:flex items-center">
                            <div class="relative">
                                <select name="household_id" onchange="this.form.submit()" class="pl-3 pr-8 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach($households as $h)
                                        <option value="{{ $h->id }}" {{ $household && $household->id === $h->id ? 'selected' : '' }}>
                                            🏡 {{ $h->name }} ({{ $h->tariff->code ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    @endif

                    @if($household)
                        <form method="POST" action="{{ route('households.reanalyze', $household->id) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 animate-spin-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span class="hidden md:inline">Jalankan Inferensi Pakar</span>
                                <span class="md:hidden">Analisis</span>
                            </button>
                        </form>
                    @endif

                    <!-- User Profile Pill -->
                    <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-xs">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <span class="text-xs font-semibold text-slate-700 hidden lg:inline">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-red-600 transition-colors">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Flash Status Message -->
        @if(session('status'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between text-sm shadow-xs">
                <div class="flex items-center gap-2 font-medium">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
            </div>
        @endif

        @if($household)
            <!-- Household Sub-header Details -->
            <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-black tracking-tight text-slate-900">{{ $household->name }}</h1>
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $household->tariff->code ?? 'Tarif' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        📍 {{ $household->address ?? 'Alamat Belum Diatur' }} • 👥 {{ $household->occupant_count }} Penghuni • ⚡ Kapasitas Daya: {{ number_format($household->tariff->power_limit_va ?? 0, 0, ',', '.') }} VA
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="document.getElementById('modalAddAppliance').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-blue-500/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Peralatan
                    </button>
                    <button onclick="document.getElementById('modalMeterReading').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition-all border border-slate-200">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Catat Meteran Riil
                    </button>
                </div>
            </div>

            <!-- 1. Hero Summary KPI Cards -->
            @php
                $assumptions = $currentPeriod?->calculation_assumptions ?? [];
                $simultaneousWatt = (float) ($assumptions['simultaneous_active_watt'] ?? 0.0);
                $powerLimitVa = (float) ($household->tariff->power_limit_va ?? 1300.0);
                $loadRatio = $powerLimitVa > 0 ? ($simultaneousWatt / $powerLimitVa) * 100 : 0.0;
                $totalSavingsCost = $recommendations->sum('estimated_cost_saving');
                $totalSavingsKwh = $recommendations->sum('estimated_kwh_saving');
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- Card 1: Total kWh -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Estimasi Konsumsi</span>
                        <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900">{{ number_format($currentPeriod?->total_estimated_kwh ?? 0, 1, ',', '.') }}</span>
                        <span class="text-xs font-semibold text-slate-500">kWh / bln</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2">Berdasarkan {{ $household->appliances->where('is_active', true)->count() }} peralatan aktif terdaftar</p>
                </div>

                <!-- Card 2: Estimated Cost -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Estimasi Tagihan</span>
                        <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-xs font-bold text-slate-500">Rp</span>
                        <span class="text-2xl font-black text-slate-900">{{ number_format($currentPeriod?->total_estimated_cost ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2">Tarif: Rp {{ number_format($household->tariff->rate_per_kwh ?? 0, 2, ',', '.') }} / kWh</p>
                </div>

                <!-- Card 3: Simultaneous Load vs MCB -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Beban Simultan Puncak</span>
                        <span class="p-2 rounded-xl {{ $loadRatio >= 85 ? 'bg-red-50 text-red-600' : ($loadRatio >= 60 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-700') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-black text-slate-900">{{ number_format($simultaneousWatt, 0, ',', '.') }}</span>
                        <span class="text-xs font-semibold text-slate-500">W / {{ number_format($powerLimitVa, 0, ',', '.') }} VA</span>
                    </div>
                    <!-- MCB Bar -->
                    <div class="w-full bg-slate-100 rounded-full h-2 mt-2 overflow-hidden">
                        <div class="h-2 rounded-full {{ $loadRatio >= 85 ? 'bg-red-500' : ($loadRatio >= 60 ? 'bg-amber-500' : 'bg-blue-600') }}" style="width: {{ min(100, $loadRatio) }}%"></div>
                    </div>
                    <p class="text-[11px] {{ $loadRatio >= 85 ? 'text-red-600 font-semibold' : 'text-slate-400' }} mt-1">
                        {{ number_format($loadRatio, 1) }}% dari ambang kapasitas MCB
                    </p>
                </div>

                <!-- Card 4: Potential Savings -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Peluang Penghematan</span>
                        <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                            </svg>
                        </span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-xs font-bold text-emerald-600">Rp</span>
                        <span class="text-2xl font-black text-emerald-600">{{ number_format($totalSavingsCost, 0, ',', '.') }}</span>
                    </div>
                    <p class="text-[11px] text-emerald-700 font-medium mt-2">
                        Potensi reduksi ~{{ number_format($totalSavingsKwh, 1, ',', '.') }} kWh / bulan
                    </p>
                </div>
            </div>

            <!-- 2. Grid Layout: Left Analysis & Feed | Right Appliance Inventory -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Left Column: Expert System Recommendations & Analytical Charts (7 Cols) -->
                <div class="lg:col-span-7 space-y-8">
                    
                    <!-- Section: Explainable Expert System Recommendations -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                                    💡
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-900">Rekomendasi Sistem Pakar (Forward Chaining)</h2>
                                    <p class="text-xs text-slate-500">Inferensi aturan berbasis parameter beban, durasi & efisiensi alat</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $recommendations->count() }} Aturan Terpicu
                            </span>
                        </div>

                        @if($recommendations->isEmpty())
                            <div class="text-center py-10 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                <p class="text-xs text-slate-500">Belum ada aturan pakar yang terpicu. Klik "Jalankan Inferensi Pakar" untuk mengevaluasi data inventaris.</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($recommendations as $rec)
                                    @php
                                        $severityStyles = [
                                            'critical' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'badge' => 'bg-red-600 text-white', 'title' => 'text-red-900'],
                                            'high' => ['bg' => 'bg-amber-50/60', 'border' => 'border-amber-200', 'badge' => 'bg-amber-600 text-white', 'title' => 'text-amber-950'],
                                            'medium' => ['bg' => 'bg-blue-50/40', 'border' => 'border-blue-200', 'badge' => 'bg-blue-600 text-white', 'title' => 'text-blue-950'],
                                            'low' => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'badge' => 'bg-slate-600 text-white', 'title' => 'text-slate-800'],
                                        ][$rec->severity] ?? ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'badge' => 'bg-slate-600 text-white', 'title' => 'text-slate-800'];
                                    @endphp

                                    <div class="p-4 rounded-xl border {{ $severityStyles['border'] }} {{ $severityStyles['bg'] }} transition-all">
                                        <!-- Header Pill -->
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md {{ $severityStyles['badge'] }}">
                                                    {{ $rec->severity }}
                                                </span>
                                                <span class="text-xs font-mono font-bold text-slate-500">{{ $rec->rule_code }}</span>
                                            </div>
                                            @if($rec->estimated_cost_saving > 0)
                                                <span class="text-xs font-bold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-md">
                                                    Hemat ~Rp {{ number_format($rec->estimated_cost_saving, 0, ',', '.') }}/bln
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Title -->
                                        <h3 class="text-sm font-bold {{ $severityStyles['title'] }}">{{ $rec->title }}</h3>

                                        <!-- Recommendation Advice Text -->
                                        <p class="text-xs text-slate-700 mt-1 leading-relaxed">
                                            {{ $rec->recommendation_text }}
                                        </p>

                                        <!-- Explainability Accordion: The Mathematical Evidence -->
                                        <details class="mt-3 group">
                                            <summary class="cursor-pointer text-[11px] font-semibold text-blue-700 hover:text-blue-800 flex items-center gap-1 select-none">
                                                <span>🔍 Mengapa rekomendasi ini muncul? (Bukti Fakta & Parameter)</span>
                                            </summary>
                                            <div class="mt-2 p-3 bg-white/90 rounded-lg border border-slate-200 text-xs text-slate-600 font-mono leading-relaxed">
                                                <p class="font-bold text-slate-800 mb-1">Data & Fakta Terhitung:</p>
                                                {{ $rec->explanation_evidence }}
                                            </div>
                                        </details>

                                        <!-- Actionable Steps -->
                                        @if($rec->actionable_steps)
                                            <div class="mt-3 pt-2.5 border-t border-slate-200/60">
                                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Langkah Nyata Efisiensi:</p>
                                                <div class="text-xs text-slate-700 space-y-0.5 whitespace-pre-line leading-relaxed">
                                                    {{ $rec->actionable_steps }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Section: Energy Consumption Breakdown Charts -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Distribusi Beban Listrik Rumah Tangga</h2>
                                <p class="text-xs text-slate-500">Porsi konsumsi energi per peralatan dalam periode aktif</p>
                            </div>
                            <span class="text-xs font-semibold text-slate-500">{{ $currentPeriod?->period_name }}</span>
                        </div>

                        @if($currentPeriod && $currentPeriod->items->isNotEmpty())
                            <!-- Breakdown Progress Bars -->
                            <div class="space-y-3">
                                @foreach($currentPeriod->items->sortByDesc('percentage_of_total')->take(6) as $item)
                                    @php
                                        $barColor = $item->percentage_of_total >= 30 ? 'bg-red-500' : ($item->percentage_of_total >= 15 ? 'bg-blue-600' : 'bg-slate-400');
                                    @endphp
                                    <div>
                                        <div class="flex justify-between text-xs font-semibold mb-1">
                                            <span class="text-slate-800 flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full {{ $barColor }}"></span>
                                                {{ $item->appliance->name }}
                                            </span>
                                            <span class="text-slate-600">
                                                {{ number_format($item->monthly_kwh, 1, ',', '.') }} kWh ({{ number_format($item->percentage_of_total, 1) }}%)
                                                <span class="text-slate-400 ml-1">Rp {{ number_format($item->monthly_cost, 0, ',', '.') }}</span>
                                            </span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                            <div class="h-2 rounded-full {{ $barColor }}" style="width: {{ $item->percentage_of_total }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-xs text-slate-500">
                                Belum ada data konsumsi yang dikalkulasi.
                            </div>
                        @endif
                    </div>

                    <!-- Section: Actual vs Estimated Variance Comparison -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Pencatatan Meteran & Uji Akurasi Estimasi</h2>
                                <p class="text-xs text-slate-500">Validasi konsumsi perkiraan dengan pembacaan meteran riil (PLN)</p>
                            </div>
                        </div>

                        @if($recentReadings->isEmpty())
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center text-xs text-slate-500">
                                Belum ada data meteran riil yang tercatat. Klik tombol "Catat Meteran Riil" di atas untuk menambahkan stand meter PLN Anda.
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-slate-700">
                                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
                                        <tr>
                                            <th class="py-2.5 px-3">Tanggal</th>
                                            <th class="py-2.5 px-3">Stand Meter</th>
                                            <th class="py-2.5 px-3">kWh Riil</th>
                                            <th class="py-2.5 px-3">Tagihan Riil</th>
                                            <th class="py-2.5 px-3">Varians Estimasi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium">
                                        @foreach($recentReadings as $read)
                                            <tr>
                                                <td class="py-2.5 px-3 text-slate-900 font-semibold">{{ $read->reading_date->format('d M Y') }}</td>
                                                <td class="py-2.5 px-3 font-mono">{{ number_format($read->meter_start_kwh, 0) }} - {{ number_format($read->meter_end_kwh, 0) }}</td>
                                                <td class="py-2.5 px-3 font-bold text-slate-900">{{ number_format($read->actual_kwh_consumed, 1, ',', '.') }} kWh</td>
                                                <td class="py-2.5 px-3 text-emerald-700 font-bold">
                                                    {{ $read->actual_cost_billed ? 'Rp ' . number_format($read->actual_cost_billed, 0, ',', '.') : '-' }}
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    @if($read->variance_kwh !== null)
                                                        @php
                                                            $isAccurate = abs($read->variance_pct) <= 10;
                                                        @endphp
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $isAccurate ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                            {{ $read->variance_kwh > 0 ? '+' : '' }}{{ number_format($read->variance_kwh, 1) }} kWh ({{ number_format($read->variance_pct, 1) }}%)
                                                        </span>
                                                    @else
                                                        <span class="text-slate-400">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Appliance Inventory (5 Cols) -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs sticky top-24">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Inventaris Peralatan Listrik</h2>
                                <p class="text-xs text-slate-500">{{ $household->appliances->count() }} unit terdaftar dalam sistem</p>
                            </div>
                            <button onclick="document.getElementById('modalAddAppliance').classList.remove('hidden')" class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>

                        <!-- Appliances List -->
                        <div class="space-y-3 max-h-[700px] overflow-y-auto pr-1">
                            @foreach($household->appliances as $app)
                                <div class="p-3.5 rounded-xl border border-slate-200 hover:border-blue-300 transition-all bg-slate-50/50 flex items-start justify-between gap-3 group">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-900 truncate">{{ $app->name }}</span>
                                            @if($app->quantity > 1)
                                                <span class="px-1.5 py-0.2 bg-slate-200 text-slate-700 text-[10px] font-bold rounded">x{{ $app->quantity }}</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            {{ $app->category->name ?? 'Lainnya' }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-2 text-[11px] font-semibold text-slate-600">
                                            <span class="text-blue-700 font-bold">{{ number_format($app->power_watt, 0) }} W</span>
                                            <span>•</span>
                                            <span>{{ number_format($app->daily_hours, 1) }} jam/hari</span>
                                            <span>•</span>
                                            <span>Siklus: {{ number_format($app->usage_factor * 100, 0) }}%</span>
                                        </div>
                                    </div>

                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('appliances.destroy', $app->id) }}" onsubmit="return confirm('Hapus peralatan ini dari inventaris?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-red-600 p-1 rounded transition-colors" title="Hapus Peralatan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        @else
            <div class="text-center py-20 bg-white rounded-3xl border border-slate-200 shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    ⚡
                </div>
                <h2 class="text-xl font-bold text-slate-900">Belum Ada Profil Rumah Tangga</h2>
                <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto">Mulai kelola energi listrik Anda dengan mendaftarkan profil rumah tangga dan golongan tarif listrik PLN.</p>
            </div>
        @endif

    </main>

    <!-- Modal: Tambah Peralatan Listrik -->
    <div id="modalAddAppliance" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-lg bg-blue-100 text-blue-700">⚡</span>
                    <h3 class="text-base font-bold text-slate-900">Tambah Peralatan Listrik</h3>
                </div>
                <button type="button" onclick="document.getElementById('modalAddAppliance').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            @if($household)
                <form method="POST" action="{{ route('households.appliances.store', $household->id) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Peralatan</label>
                        <input type="text" name="name" required placeholder="Contoh: AC Inverter Kamar Utama" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori</label>
                            <select name="category_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kuantitas (Unit)</label>
                            <input type="number" name="quantity" value="1" min="1" max="100" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Daya Aktif (Watt)</label>
                            <input type="number" step="0.1" name="power_watt" required placeholder="Contoh: 650" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jam Operasi/Hari</label>
                            <input type="number" step="0.1" name="daily_hours" value="8" min="0.1" max="24" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Hari Operasi/Bulan</label>
                            <input type="number" name="days_per_month" value="30" min="1" max="31" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Faktor Siklus Kerja (Duty Cycle)</label>
                            <select name="usage_factor" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="1.000">100% (Lampu, TV, PC, Setrika)</option>
                                <option value="0.700" selected>70% (AC Inverter / Pompa Jet)</option>
                                <option value="0.500">50% (Kulkas / Freezer)</option>
                                <option value="0.850">85% (AC Standar Non-Inverter)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button" onclick="document.getElementById('modalAddAppliance').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-all">Simpan & Hitung Ulang</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Modal: Catat Meteran Riil -->
    <div id="modalMeterReading" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-lg bg-emerald-100 text-emerald-700">📊</span>
                    <h3 class="text-base font-bold text-slate-900">Catat Stand Meteran Listrik Riil</h3>
                </div>
                <button type="button" onclick="document.getElementById('modalMeterReading').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            @if($household)
                <form method="POST" action="{{ route('households.meter.store', $household->id) }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="calculation_period_id" value="{{ $currentPeriod?->id }}">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pembacaan Meter</label>
                        <input type="date" name="reading_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Stand Meter Awal</label>
                            <input type="number" step="0.01" name="meter_start_kwh" value="1000.00" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Stand Meter Akhir</label>
                            <input type="number" step="0.01" name="meter_end_kwh" placeholder="Contoh: 1315.00" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Tagihan Riil (Rp, Opsional)</label>
                        <input type="number" step="1" name="actual_cost_billed" placeholder="Contoh: 455000" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan</label>
                        <input type="text" name="notes" placeholder="Contoh: Tagihan PLN Periode November" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button" onclick="document.getElementById('modalMeterReading').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-all">Simpan & Hitung Varians</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

</body>
</html>
