@php
    $navItems = [
        ['route' => 'dashboard', 'match' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ['route' => 'analysis.index', 'match' => 'analysis.*', 'label' => 'Analisis Energi', 'icon' => 'chart'],
        ['route' => 'recommendations.index', 'match' => 'recommendations.*', 'label' => 'Rekomendasi', 'icon' => 'leaf'],
        ['route' => 'appliances.index', 'match' => 'appliances.*', 'label' => 'Data Peralatan', 'icon' => 'plug'],
        ['route' => 'history.index', 'match' => 'history.*', 'label' => 'Riwayat Analisis', 'icon' => 'history'],
        ['route' => 'reports.index', 'match' => 'reports.*', 'label' => 'Laporan', 'icon' => 'report'],
    ];
    $accountItems = [
        ['route' => 'profile.index', 'match' => 'profile.*', 'label' => 'Profil', 'icon' => 'user'],
        ['route' => 'settings.index', 'match' => 'settings.*', 'label' => 'Pengaturan', 'icon' => 'cog'],
    ];
    $linkClass = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] font-semibold text-blue-100 hover:bg-white/10 hover:text-white aria-[current=page]:bg-electric aria-[current=page]:text-white aria-[current=page]:shadow-lg aria-[current=page]:shadow-electric/30';
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

        <p class="px-3 pt-5 pb-1 text-xs font-medium text-blue-300/80">Akun</p>
        @foreach ($accountItems as $item)
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

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[15px] font-semibold text-blue-100 hover:bg-red-500/15 hover:text-red-200">
                <x-icon name="logout" class="size-5" />
                Logout
            </button>
        </form>
    </nav>

    <div class="mt-auto hidden px-4 pt-6 pb-6 [@media(min-height:960px)]:block">
        <div class="overflow-hidden rounded-2xl bg-white/5">
            <x-house-scene variant="solar" class="w-full" />
            <div class="px-4 pb-4">
                <p class="text-[15px] leading-snug font-bold">Hemat energi di rumah, lebih baik untuk masa depan</p>
                <span class="mt-3 block h-1 w-10 rounded-full bg-sky-400"></span>
            </div>
        </div>
    </div>
</aside>
