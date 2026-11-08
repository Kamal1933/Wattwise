<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — WattWise</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @include('partials.favicons')
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-ice px-4 py-10">
    <main class="w-full max-w-sm">
        <a href="{{ route('home') }}" class="mb-8 flex flex-col items-center rounded-2xl text-center" title="Kembali ke beranda">
            <img src="{{ asset('images/logo/wattwise-mark-light.svg') }}" alt="" class="h-28 w-auto">
            <span class="mt-2 text-4xl font-extrabold tracking-tight text-navy">Watt<span class="text-electric">Wise</span></span>
            <span class="mt-1 text-sm font-medium text-slate-500">Manajemen energi rumah tangga cerdas</span>
        </a>

        <div class="card p-6 sm:p-8">
            <h1 class="text-xl font-bold tracking-tight text-navy">@yield('heading')</h1>
            <p class="mt-1 mb-6 text-sm text-slate-500">@yield('subheading')</p>

            @if ($errors->any())
                <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('form')
        </div>

        <p class="mt-6 text-center text-sm text-slate-500">@yield('footer')</p>
    </main>
</body>
</html>
