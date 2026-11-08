@php
    $changeBadge = function (?float $change): array {
        if ($change === null) {
            return ['text' => 'Belum ada data bulan lalu', 'class' => 'text-slate-500', 'arrow' => ''];
        }
        $isLower = $change <= 0;

        return [
            'text' => ($isLower ? '▼ ' : '▲ ').number_format(abs($change), 1, ',', '.').'% dibanding bulan lalu',
            'class' => $isLower ? 'text-emerald-700' : 'text-red-700',
            'arrow' => '',
        ];
    };
    $kwhBadge = $changeBadge($summary->kwhChange);
    $costBadge = $changeBadge($summary->costChange);
    $previewAppliances = $activeAppliances->sortByDesc(fn ($appliance) => (float) ($summary->itemsByAppliance[$appliance->id]->monthly_kwh ?? 0))->take(3);
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-3">
    <div class="card flex items-center gap-4 bg-gradient-to-br from-sky-50 to-white p-5">
        <span class="grid size-14 shrink-0 place-items-center rounded-full bg-electric-100 text-electric">
            <x-icon name="bolt" class="size-7" />
        </span>
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-600">Total konsumsi energi</p>
            <p class="mt-0.5 text-2xl font-extrabold text-navy tabular-nums">{{ $number($summary->totalKwh) }} <span class="text-base font-semibold">kWh</span></p>
            <p class="mt-0.5 text-xs font-medium {{ $kwhBadge['class'] }}">{{ $kwhBadge['text'] }}</p>
        </div>
    </div>

    <div class="card flex items-center gap-4 bg-gradient-to-br from-amber-50 to-white p-5">
        <span class="grid size-14 shrink-0 place-items-center rounded-full bg-amber-100 text-amber-600">
            <x-icon name="coins" class="size-7" />
        </span>
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-600">Estimasi biaya</p>
            <p class="mt-0.5 text-2xl font-extrabold text-navy tabular-nums">{{ $rupiah($summary->totalCost) }}</p>
            <p class="mt-0.5 text-xs font-medium {{ $costBadge['class'] }}">{{ $costBadge['text'] }}</p>
        </div>
    </div>

    <a href="{{ route('appliances.index') }}" class="card flex items-center gap-4 bg-gradient-to-br from-violet-50 to-white p-5 hover:border-violet-300">
        <span class="grid size-14 shrink-0 place-items-center rounded-full bg-violet-100 text-violet-600">
            <x-icon name="plug" class="size-7" />
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium whitespace-nowrap text-slate-600">Jumlah perangkat</p>
            <p class="mt-0.5 text-2xl font-extrabold whitespace-nowrap text-navy tabular-nums">{{ $activeAppliances->count() }} <span class="text-base font-semibold">perangkat</span></p>
            <p class="mt-0.5 text-xs font-medium whitespace-nowrap text-slate-500">{{ $activeAppliances->sum('quantity') }} unit menyala</p>
        </div>
        <div class="flex shrink-0 -space-x-5" aria-hidden="true">
            @foreach ($previewAppliances as $appliance)
                <span class="grid size-12 place-items-center rounded-xl bg-white shadow-md ring-1 ring-slate-200">
                    <x-appliance-art :name="$appliance->name" :category="$appliance->category?->code" :photo="$appliance->photoUrl()" class="size-10" />
                </span>
            @endforeach
        </div>
    </a>
</div>
