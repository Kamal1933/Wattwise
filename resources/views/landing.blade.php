<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WattWise — Hitung dan hemat listrik rumah Anda</title>
    <meta name="description" content="Hitung perkiraan tagihan listrik dari peralatan di rumah Anda, lihat alat yang paling boros, dan dapatkan saran hemat yang jelas alasannya.">
    @include('partials.favicons')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white">
    <a href="#kalkulator" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold">
        Langsung ke kalkulator
    </a>

    @include('landing.partials.header')

    <main>
        @include('landing.partials.hero')
        @include('landing.partials.how-it-works')
        @include('landing.partials.calculator')
        @include('landing.partials.example')
        @include('landing.partials.features')
        @include('landing.partials.faq')
        @include('landing.partials.cta')
    </main>

    @include('landing.partials.footer')
</body>
</html>
