@php
    $tipCatalog = [
        'R-CAP-01' => ['icon' => 'power', 'label' => 'Jangan nyalakan alat besar bersamaan'],
        'R-PEAK-01' => ['icon' => 'clock', 'label' => 'Atur giliran alat berdaya besar'],
        'R-DOM-01' => ['icon' => 'clock', 'label' => 'Kurangi jam pakai alat terboros'],
        'R-COOL-01' => ['art' => 'ac', 'label' => 'Atur suhu AC 24–25°C'],
        'R-VAMP-01' => ['icon' => 'power', 'label' => 'Matikan perangkat saat tidak dipakai'],
        'R-LIGHT-01' => ['art' => 'lamp', 'label' => 'Gunakan lampu LED'],
        'R-WATER-01' => ['art' => 'pump', 'label' => 'Pasang timer pompa air'],
    ];
    $quickTips = $summary->recommendations
        ->unique('rule_code')
        ->map(fn ($rec) => $tipCatalog[$rec->rule_code] ?? ['icon' => 'light', 'label' => $rec->title])
        ->take(4);
    $isGeneric = $quickTips->isEmpty();
    if ($isGeneric) {
        $quickTips = collect([
            ['art' => 'ac', 'label' => 'Atur suhu AC 24–25°C'],
            ['art' => 'lamp', 'label' => 'Gunakan lampu LED'],
            ['icon' => 'power', 'label' => 'Matikan perangkat saat tidak dipakai'],
            ['icon' => 'calendar', 'label' => 'Batasi durasi penggunaan'],
        ]);
    }
@endphp

<div class="card grid grid-cols-1 items-center gap-5 p-5 lg:grid-cols-[minmax(0,16rem)_minmax(0,1fr)_auto]">
    <div class="flex items-center gap-3">
        <span class="grid size-12 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600">
            <x-icon name="sprout" class="size-7" />
        </span>
        <div>
            <h3 class="font-bold text-navy">Rekomendasi cepat hari ini</h3>
            <p class="text-xs text-slate-500">{{ $isGeneric ? 'Tips umum hemat listrik' : 'Berdasarkan analisis sistem pakar' }}</p>
        </div>
    </div>

    <ul class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:border-l lg:border-slate-200 lg:pl-5">
        @foreach ($quickTips as $tip)
            <li class="flex flex-col items-center gap-2 text-center text-xs font-medium text-slate-700">
                <span class="grid size-12 place-items-center rounded-xl bg-ice text-electric">
                    @isset($tip['art'])
                        <x-appliance-art :type="$tip['art']" class="size-10" />
                    @else
                        <x-icon :name="$tip['icon']" class="size-6" />
                    @endisset
                </span>
                {{ $tip['label'] }}
            </li>
        @endforeach
    </ul>

    <a href="{{ route('recommendations.index') }}" class="btn-primary justify-between gap-6 px-5 py-3">
        Lihat semua rekomendasi
        <x-icon name="chevron" class="size-4" />
    </a>
</div>
