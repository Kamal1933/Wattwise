@php
    $radius = 15.9155;
    $offset = 25;
    $medals = [
        ['bg' => 'bg-gradient-to-br from-amber-300 to-amber-500', 'text' => 'text-amber-950'],
        ['bg' => 'bg-gradient-to-br from-slate-200 to-slate-400', 'text' => 'text-slate-800'],
        ['bg' => 'bg-gradient-to-br from-orange-300 to-orange-600', 'text' => 'text-orange-950'],
    ];
@endphp
{{-- Expects: $summary (PeriodSummary) --}}

<div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,7fr)_minmax(0,5fr)]">
    {{-- Donut chart --}}
    <div class="card p-6">
        <div class="flex items-center justify-between gap-3">
            <h3 class="flex items-center gap-2 text-lg font-bold text-navy">
                <x-icon name="chart" class="size-5 text-electric" />
                Grafik konsumsi energi
            </h3>
            <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600">Per perangkat, {{ $summary->period?->period_name }}</span>
        </div>

        <div class="mt-6 grid grid-cols-1 items-center gap-6 sm:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
            <div class="relative mx-auto w-full max-w-[13.75rem]">
                <svg viewBox="0 0 42 42" class="w-full -rotate-0" role="img" aria-label="Diagram donat konsumsi per perangkat">
                    <circle cx="21" cy="21" r="{{ $radius }}" fill="none" stroke="#F1F5F9" stroke-width="6"/>
                    @foreach ($summary->chartSegments as $segment)
                        @php $length = max(0, $segment['percent'] - 0.6); @endphp
                        <circle cx="21" cy="21" r="{{ $radius }}" fill="none" stroke="{{ $segment['color'] }}" stroke-width="6"
                            stroke-dasharray="{{ $length }} {{ 100 - $length }}" stroke-dashoffset="{{ $offset }}"></circle>
                        @php $offset -= $segment['percent']; @endphp
                    @endforeach
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                    <x-icon name="bolt" class="size-5 text-amber-500" />
                    <span class="text-xs text-slate-500">Total</span>
                    <span class="text-lg leading-tight font-extrabold text-navy tabular-nums">{{ $number($summary->totalKwh, 0) }} kWh</span>
                </div>
            </div>

            <ul class="space-y-2.5">
                @foreach ($summary->chartSegments as $segment)
                    <li class="grid grid-cols-[auto_minmax(0,1fr)_auto_auto] items-center gap-3 text-sm">
                        <span class="size-3 rounded-full" style="background: {{ $segment['color'] }}"></span>
                        <span class="truncate font-semibold text-ink">{{ $segment['name'] }}</span>
                        <span class="text-right text-slate-600 tabular-nums">{{ $number($segment['kwh']) }} kWh</span>
                        <span class="w-12 text-right font-semibold text-navy tabular-nums">{{ $number($segment['percent'], 0) }}%</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Most wasteful --}}
    <div class="card flex flex-col p-6">
        <h3 class="flex items-center gap-2 text-lg font-bold text-navy">
            <x-icon name="trophy" class="size-5 text-amber-500" />
            Perangkat paling boros
        </h3>

        <ol class="mt-4 flex-1 divide-y divide-slate-100">
            @foreach ($summary->items->take(3) as $index => $item)
                <li class="flex items-center gap-3 py-3">
                    <span class="grid size-8 shrink-0 place-items-center rounded-full text-sm font-extrabold {{ $medals[$index]['bg'] }} {{ $medals[$index]['text'] }}">{{ $index + 1 }}</span>
                    <span class="grid size-14 shrink-0 place-items-center rounded-xl bg-ice">
                        <x-appliance-art :name="$item->appliance?->name" :category="$item->appliance?->category?->code" :photo="$item->appliance?->photoUrl()" class="size-12" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <a href="{{ $item->appliance ? route('appliances.show', $item->appliance) : '#' }}" class="block truncate font-semibold text-ink hover:text-electric">{{ $item->appliance?->name }}</a>
                        <span class="block text-sm text-slate-500 tabular-nums">{{ $number($item->monthly_kwh) }} kWh, {{ $rupiah($item->monthly_cost) }}</span>
                    </span>
                    <span class="rounded-lg px-2 py-1 text-sm font-bold tabular-nums {{ $index === 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800' }}">{{ $number($item->percentage_of_total, 0) }}%</span>
                </li>
            @endforeach
        </ol>

        <div class="mt-4 rounded-xl bg-ice p-4">
            <div class="flex items-baseline justify-between gap-3 text-sm">
                <span class="font-medium text-slate-600">Beban jika semua menyala bersamaan</span>
                <span class="font-bold text-navy tabular-nums">{{ $number($summary->loadPercent, 0) }}%</span>
            </div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                <div class="h-full rounded-full {{ $summary->loadTone() }}" style="width: {{ min(100, $summary->loadPercent) }}%"></div>
            </div>
            <p class="mt-2 text-xs text-slate-500">
                {{ $number($summary->simultaneousWatt, 0) }} W dari daya {{ $number($summary->powerLimitVa, 0) }} VA.
                {{ $summary->loadMessage() }}
            </p>
        </div>
    </div>
</div>
