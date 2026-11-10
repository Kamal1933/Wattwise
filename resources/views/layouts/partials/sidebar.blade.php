@php
    $navItems = [
        ['route' => 'dashboard', 'match' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ['route' => 'analysis.index', 'match' => 'analysis.*', 'label' => 'Analisis Energi', 'icon' => 'chart'],
        ['route' => 'recommendations.index', 'match' => 'recommendations.*', 'label' => 'Rekomendasi', 'icon' => 'leaf'],
        ['route' => 'appliances.index', 'match' => 'appliances.*', 'label' => 'Data Peralatan', 'icon' => 'plug'],
        ['route' => 'history.index', 'match' => 'history.*', 'label' => 'Riwayat Analisis', 'icon' => 'history'],
        ['route' => 'reports.index', 'match' => 'reports.*', 'label' => 'Laporan', 'icon' => 'report'],
    ];
    $linkClass = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-[0.9375rem] font-semibold text-blue-100 hover:bg-white/10 hover:text-white aria-[current=page]:bg-electric aria-[current=page]:text-white aria-[current=page]:shadow-lg aria-[current=page]:shadow-electric/30';
@endphp

<aside
    id="sidebar"
    data-sidebar
    class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col overflow-y-auto bg-gradient-to-b from-navy to-navy-950 text-white transition-transform duration-200 lg:translate-x-0 print:hidden"
>
    <div class="flex items-center justify-between px-5 pt-6 pb-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            <img src="{{ asset('images/logo/wattwise-mark.svg') }}" alt="" class="h-14 w-auto">
            <span>
                <span class="block text-2xl leading-tight font-extrabold tracking-tight">Watt<span class="text-sky-400">Wise</span></span>
                <span class="block text-xs leading-snug text-blue-200/80">Manajemen energi<br>rumah tangga cerdas</span>
            </span>
        </a>
        <button type="button" class="btn-ghost -mr-2 text-white hover:bg-white/10 hover:text-white lg:hidden" data-sidebar-toggle>
            <x-icon name="close" />
            <span class="sr-only">Tutup menu</span>
        </button>
    </div>

    @isset($households)
        <div class="px-4">
            <form method="GET" action="{{ url()->current() }}" class="rounded-xl bg-white/5 p-3">
                <label for="household-switcher" class="mb-1.5 block text-xs font-medium text-blue-200/80">Rumah tangga aktif</label>
                @if ($households->isNotEmpty())
                    <select
                        id="household-switcher"
                        name="household_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-white/15 bg-navy-800 px-3 py-2 text-sm font-medium text-white focus:border-electric focus:outline-none"
                    >
                        @foreach ($households as $option)
                            <option value="{{ $option->id }}" @selected($household?->id === $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="btn-primary mt-2 w-full">Tampilkan</button></noscript>
                @else
                    <p class="text-sm text-blue-100">Belum ada rumah tangga.</p>
                @endif
            </form>
        </div>
    @endisset

    <nav class="mt-4 space-y-1 px-4" aria-label="Menu utama">
        @foreach ($navItems as $item)
            <a href="{{ route($item['route']) }}" @if (request()->routeIs($item['match'])) aria-current="page" @endif class="{{ $linkClass }}">
                <x-icon :name="$item['icon']" class="size-5" />
                {{ $item['label'] }}
            </a>
        @endforeach

        @if (auth()->user()->isAdmin())
            <a href="{{ route('dashboard.rules') }}" @if (request()->routeIs('dashboard.rules')) aria-current="page" @endif class="{{ $linkClass }}">
                <x-icon name="rules" class="size-5" />
                Basis Aturan
            </a>
        @endif
    </nav>

    {{-- Bottom block: illustration directly above logout --}}
    <div class="mt-auto space-y-3 px-4 pt-6 pb-5">
        <figure class="relative overflow-hidden rounded-2xl ring-1 ring-white/10">
            <picture>
                <source srcset="{{ asset('images/sidebar/rumah-hemat-energi.webp') }}?v=2" type="image/webp">
                <img src="{{ asset('images/sidebar/rumah-hemat-energi.jpg') }}?v=2" alt="Rumah hemat energi dengan panel surya" width="1024" height="768" loading="lazy" class="aspect-[2/1] w-full object-cover object-[50%_30%] [@media(min-height:52rem)_and_(max-aspect-ratio:19/10)]:aspect-[4/3]">
            </picture>
            <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-navy-950/95 via-navy-950/70 to-transparent px-4 pt-10 pb-3.5">
                <p class="text-sm leading-snug font-bold text-white">Hemat energi di rumah, lebih baik untuk masa depan</p>
                <span class="mt-2 block h-1 w-10 rounded-full bg-sky-400"></span>
            </figcaption>
        </figure>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 pt-3">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[0.9375rem] font-semibold text-blue-100 hover:bg-red-500/15 hover:text-red-200">
                <x-icon name="logout" class="size-5" />
                Logout
            </button>
        </form>
    </div>
</aside>
