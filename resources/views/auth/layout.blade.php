<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — WattWise</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen antialiased bg-slate-50 text-slate-900 flex items-center justify-center px-4 py-10">
    <main class="w-full max-w-sm">
        <div class="flex items-center justify-center gap-3 mb-8">
            <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <span class="text-2xl font-extrabold tracking-tight text-slate-900">Watt<span class="text-blue-600">Wise</span></span>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <h1 class="text-lg font-bold text-slate-900">@yield('heading')</h1>
            <p class="text-xs text-slate-500 mt-1 mb-5">@yield('subheading')</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('form')
        </div>

        <p class="text-center text-xs text-slate-500 mt-5">@yield('footer')</p>
    </main>
</body>
</html>
