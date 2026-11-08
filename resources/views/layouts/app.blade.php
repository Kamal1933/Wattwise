<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — WattWise</title>
    @include('partials.favicons')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-screen"
    @if ($errors->any() && old('_dialog')) data-reopen-dialog="{{ old('_dialog') }}" @endif
>
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold">
        Lewati ke konten
    </a>

    {{-- Mobile top bar --}}
    <div class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-white/10 bg-navy px-4 text-white lg:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <img src="{{ asset('images/logo/wattwise-mark.svg') }}" alt="" class="h-10 w-auto">
            <span class="text-xl font-extrabold tracking-tight">Watt<span class="text-sky-400">Wise</span></span>
        </a>
        <button type="button" class="btn-ghost text-white hover:bg-white/10 hover:text-white" data-sidebar-toggle aria-expanded="false" aria-controls="sidebar">
            <x-icon name="menu" class="size-6" />
            <span class="sr-only">Buka menu</span>
        </button>
    </div>

    <div data-sidebar-overlay class="fixed inset-0 z-40 hidden bg-navy-950/60 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside
        id="sidebar"
        data-sidebar
        class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-navy text-white transition-transform duration-200 lg:translate-x-0"
    >
        <div class="flex h-24 items-center justify-between px-5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo/wattwise-mark.svg') }}" alt="" class="h-14 w-auto">
                <span>
                    <span class="block text-2xl leading-tight font-extrabold tracking-tight">Watt<span class="text-sky-400">Wise</span></span>
                    <span class="block text-xs text-blue-200/80">Sistem pakar hemat listrik</span>
                </span>
            </a>
            <button type="button" class="btn-ghost -mr-2 text-white hover:bg-white/10 hover:text-white lg:hidden" data-sidebar-toggle>
                <x-icon name="close" />
                <span class="sr-only">Tutup menu</span>
            </button>
        </div>

        @isset($households)
            <div class="px-4">
                <form method="GET" action="{{ route('dashboard') }}" class="rounded-xl bg-white/5 p-3">
                    <label for="household-switcher" class="mb-1.5 block text-xs font-medium text-blue-200/80">Rumah tangga</label>
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
                <button type="button" data-dialog-open="dialog-add-household" class="mt-2 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-blue-100 hover:bg-white/5 hover:text-white">
                    <x-icon name="plus" class="size-4" />
                    Tambah rumah tangga
                </button>
            </div>
        @endisset

        <nav class="mt-6 flex-1 space-y-1 px-4" aria-label="Bagian dashboard">
            @foreach ([
                ['#ringkasan', 'Ringkasan', 'chart'],
                ['#rekomendasi', 'Rekomendasi', 'light'],
                ['#peralatan', 'Peralatan', 'plug'],
                ['#meteran', 'Meteran PLN', 'meter'],
            ] as [$href, $label, $icon])
                <a href="{{ $href }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-blue-100 hover:bg-white/10 hover:text-white">
                    <x-icon :name="$icon" class="size-5 text-blue-300" />
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-white/10 text-sm font-semibold">
                    {{ collect(explode(' ', auth()->user()->name))->filter()->take(2)->map(fn ($word) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($word, 0, 1)))->implode('') }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-blue-200/80">{{ auth()->user()->isAdmin() ? 'Administrator' : auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost px-2 text-blue-200 hover:bg-white/10 hover:text-white" title="Keluar">
                        <x-icon name="logout" />
                        <span class="sr-only">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="lg:pl-72">
        <main id="konten" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
            @if (session('status'))
                <div role="status" class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    <x-icon name="check" class="mt-0.5 size-5 shrink-0 text-emerald-600" />
                    <p class="flex-1">{{ session('status') }}</p>
                </div>
            @endif

            @if ($errors->any() && ! old('_dialog'))
                <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('dialogs')
</body>
</html>
