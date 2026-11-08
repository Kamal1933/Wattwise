<div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="card p-5">
        <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
            <x-icon name="savings" class="size-4 text-emerald-600" />
            Potensi hemat
        </div>
        <p class="mt-2 text-2xl font-bold text-emerald-700 tabular-nums">{{ $rupiah($recommendations->sum('estimated_cost_saving')) }}<span class="text-sm font-medium text-slate-500"> /bulan</span></p>
        <p class="mt-1 text-sm text-slate-500">Sekitar {{ $number($recommendations->sum('estimated_kwh_saving')) }} kWh bila semua saran dijalankan.</p>
    </div>

    <a href="#rekomendasi" class="card group block p-5 hover:border-electric">
        <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
            <x-icon name="light" class="size-4 text-electric" />
            Rekomendasi
        </div>
        <p class="mt-2 text-2xl font-bold text-navy tabular-nums">{{ $recommendations->count() }}<span class="text-sm font-medium text-slate-500"> saran</span></p>
        <p class="mt-1 text-sm text-slate-500">
            @if ($recommendations->isEmpty())
                Pemakaian Anda sudah efisien.
            @else
                {{ collect($severityMeta)->filter(fn ($meta, $key) => $severityCounts->has($key))->map(fn ($meta, $key) => $severityCounts[$key].' '.strtolower($meta['label']))->implode(', ') }}
            @endif
        </p>
    </a>

    <div class="card p-5">
        <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
            <x-icon name="target" class="size-4 text-electric" />
            Akurasi perkiraan
        </div>
        @if ($latestReading && $latestReading->variance_pct !== null)
            @php $variance = (float) $latestReading->variance_pct; @endphp
            <p class="mt-2 text-2xl font-bold text-navy tabular-nums">{{ $variance > 0 ? '+' : '' }}{{ $number($variance) }}%</p>
            <p class="mt-1 text-sm text-slate-500">
                Meteran {{ $latestReading->reading_date->translatedFormat('j M') }}: {{ $number($latestReading->actual_kwh_consumed) }} kWh,
                {{ abs($variance) <= 10 ? 'perkiraan sudah mendekati.' : ($variance > 0 ? 'pemakaian nyata lebih tinggi.' : 'pemakaian nyata lebih rendah.') }}
            </p>
        @else
            <p class="mt-2 text-2xl font-bold text-slate-300">—</p>
            <button type="button" class="mt-1 text-left text-sm font-medium text-electric hover:underline" data-dialog-open="dialog-meter-reading">
                Catat angka meteran untuk menguji perkiraan
            </button>
        @endif
    </div>
</div>
