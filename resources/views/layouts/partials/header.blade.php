@php
    $user = auth()->user();
    $crumbs = $breadcrumbs ?? [];
@endphp

<header class="mb-6 print:hidden">
    <nav aria-label="Breadcrumb" class="mb-2">
        <ol class="flex flex-wrap items-center gap-1.5 text-sm text-slate-500">
            <li>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 hover:text-electric">
                    <x-icon name="home" class="size-4" />
                    <span class="sr-only sm:not-sr-only">WattWise</span>
                </a>
            </li>
            @foreach ($crumbs as [$label, $url])
                <li class="flex items-center gap-1.5">
                    <x-icon name="chevron" class="size-3.5 text-slate-400" />
                    @if ($url && ! $loop->last)
                        <a href="{{ $url }}" class="hover:text-electric">{{ $label }}</a>
                    @else
                        <span @if ($loop->last) aria-current="page" class="font-medium text-navy" @endif>{{ $label }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    <div class="flex items-center justify-between gap-4">
        <h1 class="min-w-0 text-2xl font-extrabold tracking-tight text-navy sm:text-3xl">@yield('page-title', 'Dashboard')</h1>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            @yield('header-actions')

            @if (isset($summary) && ($household ?? null))
                <a href="{{ route('recommendations.index') }}" class="relative grid size-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 hover:bg-slate-50" title="{{ $summary->urgentCount }} saran mendesak">
                    <x-icon name="bell" class="size-5" />
                    @if ($summary->urgentCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 grid size-5 place-items-center rounded-full bg-red-500 text-[0.6875rem] font-bold text-white ring-2 ring-white">{{ $summary->urgentCount }}</span>
                    @endif
                    <span class="sr-only">{{ $summary->urgentCount }} saran mendesak</span>
                </a>
            @endif

            <details class="relative" data-user-menu>
                <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pr-3 pl-1 hover:bg-slate-50">
                    <x-user-avatar :user="$user" />
                    <span class="hidden max-w-44 truncate text-sm font-semibold text-ink sm:inline">{{ $user->name }}</span>
                    <x-icon name="chevron" class="size-4 rotate-90 text-slate-500" />
                </summary>
                <div class="absolute right-0 z-40 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                    <div class="px-3 py-2">
                        <p class="truncate text-sm font-semibold text-ink">{{ $user->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $user->isAdmin() ? 'Administrator' : $user->email }}</p>
                    </div>
                    <a href="{{ route('profile.index') }}" @if (request()->routeIs('profile.*')) aria-current="page" @endif class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-ice aria-[current=page]:bg-electric-50 aria-[current=page]:font-semibold aria-[current=page]:text-electric">
                        <x-icon name="user" class="size-4" /> Profil pengguna
                    </a>
                    <a href="{{ route('settings.index') }}" @if (request()->routeIs('settings.*')) aria-current="page" @endif class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-ice aria-[current=page]:bg-electric-50 aria-[current=page]:font-semibold aria-[current=page]:text-electric">
                        <x-icon name="cog" class="size-4" /> Pengaturan
                    </a>
                    <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-ice">
                        <x-icon name="home" class="size-4" /> Beranda WattWise
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50">
                            <x-icon name="logout" class="size-4" /> Logout
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </div>
</header>
