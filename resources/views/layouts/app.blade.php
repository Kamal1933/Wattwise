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
    <div class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-white/10 bg-navy px-4 text-white lg:hidden print:hidden">
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

    @include('layouts.partials.sidebar')

    <div class="lg:pl-72 print:pl-0">
        <main id="konten" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-10 lg:py-8">
            @include('layouts.partials.header')
            @include('layouts.partials.flash')

            @yield('content')
        </main>
    </div>

    @include('layouts.partials.dialogs')
</body>
</html>
