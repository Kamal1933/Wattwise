{{-- Expects: $appliances (Collection<Appliance>), $summary (PeriodSummary) --}}
@php
    $appliancesSorted = $appliances->sortByDesc(fn ($appliance) => (float) ($summary->itemsByAppliance[$appliance->id]->monthly_kwh ?? -1));
    $maxKwh = max(1, (float) $summary->items->max('monthly_kwh'));
@endphp

<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <label class="relative block sm:w-72">
        <span class="sr-only">Cari perangkat</span>
        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
        <input type="search" data-appliance-search placeholder="Cari perangkat" class="field-input pl-9">
    </label>
    <p class="text-sm text-slate-500">{{ $appliances->count() }} perangkat, konsumsi {{ $summary->period?->period_name ?? 'bulan ini' }}</p>
</div>

<div class="card overflow-hidden">
    {{-- Desktop table --}}
    <table class="hidden w-full text-left text-sm md:table">
        <thead class="border-b border-slate-200 bg-ice text-slate-500">
            <tr>
                <th scope="col" class="px-5 py-3 font-medium">Perangkat</th>
                <th scope="col" class="px-5 py-3 text-right font-medium">Daya</th>
                <th scope="col" class="px-5 py-3 text-right font-medium">Pemakaian</th>
                <th scope="col" class="w-1/5 px-5 py-3 font-medium">Konsumsi per bulan</th>
                <th scope="col" class="px-5 py-3 text-right font-medium">Biaya</th>
                <th scope="col" class="px-5 py-3"><span class="sr-only">Aksi</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($appliancesSorted as $appliance)
                @php $item = $summary->itemsByAppliance[$appliance->id] ?? null; @endphp
                <tr data-appliance-row="{{ strtolower($appliance->name.' '.$appliance->category?->name) }}" class="align-middle hover:bg-ice/60 {{ $appliance->is_active ? '' : 'opacity-60' }}">
                    <td class="px-5 py-3">
                        <a href="{{ route('appliances.show', $appliance) }}" class="group flex items-center gap-3">
                            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-ice">
                                <x-appliance-art :name="$appliance->name" :category="$appliance->category?->code" :photo="$appliance->photoUrl()" class="size-10" />
                            </span>
                            <span class="min-w-0">
                                <span class="block font-semibold text-ink group-hover:text-electric">{{ $appliance->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $appliance->category?->name ?? 'Lainnya' }}@if ($appliance->quantity > 1), {{ $appliance->quantity }} unit @endif @unless ($appliance->is_active), nonaktif @endunless</span>
                            </span>
                        </a>
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap tabular-nums">{{ $number($appliance->power_watt, 0) }} W</td>
                    <td class="px-5 py-3 text-right whitespace-nowrap text-slate-600 tabular-nums">{{ $number($appliance->daily_hours) }} jam/hari</td>
                    <td class="px-5 py-3">
                        @if ($item)
                            <div class="flex items-center gap-3">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $item->percentage_of_total >= 30 ? 'bg-navy' : 'bg-electric' }}" style="width: {{ (float) $item->monthly_kwh / $maxKwh * 100 }}%"></div>
                                </div>
                                <span class="w-20 text-right font-medium whitespace-nowrap tabular-nums">{{ $number($item->monthly_kwh) }} kWh</span>
                            </div>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right font-medium whitespace-nowrap tabular-nums">{{ $item ? $rupiah($item->monthly_cost) : '—' }}</td>
                    <td class="px-5 py-3">
                        @include('shared.appliance-actions')
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Mobile list --}}
    <ul class="divide-y divide-slate-100 md:hidden">
        @foreach ($appliancesSorted as $appliance)
            @php $item = $summary->itemsByAppliance[$appliance->id] ?? null; @endphp
            <li data-appliance-row="{{ strtolower($appliance->name.' '.$appliance->category?->name) }}" class="p-4 {{ $appliance->is_active ? '' : 'opacity-60' }}">
                <div class="flex items-start justify-between gap-3">
                    <a href="{{ route('appliances.show', $appliance) }}" class="flex min-w-0 flex-1 items-start gap-3">
                        <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-ice">
                            <x-appliance-art :name="$appliance->name" :category="$appliance->category?->code" :photo="$appliance->photoUrl()" class="size-10" />
                        </span>
                        <span class="min-w-0">
                            <span class="block font-semibold text-ink">{{ $appliance->name }}</span>
                            <span class="block text-xs text-slate-500">{{ $appliance->category?->name ?? 'Lainnya' }}, {{ $number($appliance->power_watt, 0) }} W, {{ $number($appliance->daily_hours) }} jam/hari</span>
                        </span>
                    </a>
                    @include('shared.appliance-actions')
                </div>
                @if ($item)
                    <div class="mt-3 flex items-center gap-3 text-sm">
                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $item->percentage_of_total >= 30 ? 'bg-navy' : 'bg-electric' }}" style="width: {{ (float) $item->monthly_kwh / $maxKwh * 100 }}%"></div>
                        </div>
                        <span class="font-medium tabular-nums">{{ $number($item->monthly_kwh) }} kWh</span>
                        <span class="text-slate-500 tabular-nums">{{ $rupiah($item->monthly_cost) }}</span>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>

    <p data-appliance-search-empty hidden class="p-6 text-center text-sm text-slate-500">Tidak ada perangkat yang cocok dengan pencarian.</p>
</div>
