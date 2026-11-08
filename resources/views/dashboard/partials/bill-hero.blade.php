<div class="overflow-hidden rounded-3xl bg-navy text-white">
    <div class="grid grid-cols-1 gap-8 p-6 sm:p-8 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:gap-12">
        {{-- Estimated bill --}}
        <div class="flex min-w-0 flex-col">
            <p class="text-sm font-medium text-blue-200">Perkiraan tagihan {{ $currentPeriod?->period_name ?? 'bulan ini' }}</p>
            <p class="mt-2 text-4xl font-extrabold tracking-tight tabular-nums sm:text-5xl">
                {{ $rupiah($currentPeriod?->total_estimated_cost) }}
            </p>
            <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-blue-200">Konsumsi</dt>
                    <dd class="mt-0.5 text-lg font-semibold tabular-nums">{{ $number($currentPeriod?->total_estimated_kwh) }} kWh</dd>
                </div>
                <div>
                    <dt class="text-blue-200">Tarif per kWh</dt>
                    <dd class="mt-0.5 text-lg font-semibold tabular-nums">{{ $rupiah($currentPeriod?->applied_rate_per_kwh, 2) }}</dd>
                </div>
            </dl>
            <form method="POST" action="{{ route('households.reanalyze', $household) }}" class="mt-6 lg:mt-auto lg:pt-6">
                @csrf
                <button type="submit" class="btn border border-white/20 bg-white/5 text-white hover:bg-white/10">
                    <x-icon name="refresh" class="size-4" />
                    Analisis ulang
                </button>
            </form>
        </div>

        {{-- Where the energy goes + capacity --}}
        <div class="min-w-0 space-y-8">
            <div>
                <div class="flex items-baseline justify-between gap-4">
                    <h3 class="text-sm font-medium text-blue-200">Ke mana listrik Anda terpakai</h3>
                    <span class="text-xs text-blue-300">{{ $items->count() }} peralatan aktif</span>
                </div>
                <div class="mt-3 flex h-4 gap-0.5 overflow-hidden rounded-full bg-navy-800" role="img" aria-label="Porsi konsumsi per peralatan">
                    @foreach ($shareSegments as $segment)
                        <span class="h-full first:rounded-l-full last:rounded-r-full" style="width: {{ max(1, $segment['percent']) }}%; background: {{ $segment['color'] }}"></span>
                    @endforeach
                </div>
                <ul class="mt-4 grid grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                    @foreach ($shareSegments as $segment)
                        <li class="flex min-w-0 items-center gap-2.5">
                            <span class="size-2.5 shrink-0 rounded-full" style="background: {{ $segment['color'] }}"></span>
                            <span class="min-w-0 flex-1 truncate text-blue-50">{{ $segment['name'] }}</span>
                            <span class="font-semibold tabular-nums">{{ $number($segment['percent']) }}%</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-2xl bg-white/5 p-4">
                <div class="flex items-baseline justify-between gap-4 text-sm">
                    <h3 class="font-medium text-blue-200">Beban jika semua menyala bersamaan</h3>
                    <span class="font-semibold tabular-nums">{{ $number($loadPercent, 0) }}%</span>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-navy-800">
                    <div class="h-full rounded-full {{ $loadTone }}" style="width: {{ min(100, $loadPercent) }}%"></div>
                </div>
                <p class="mt-2 text-sm text-blue-100">
                    {{ $number($simultaneousWatt, 0) }} W dari daya kontrak {{ $number($powerLimitVa, 0) }} VA.
                    @if ($loadPercent >= 100)
                        MCB akan turun bila semua alat dinyalakan bersamaan, jadi atur giliran pemakaian alat berdaya besar.
                    @elseif ($loadPercent >= 85)
                        Mendekati batas, hindari menyalakan alat berdaya besar bersamaan.
                    @else
                        Masih aman dari risiko MCB turun.
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
