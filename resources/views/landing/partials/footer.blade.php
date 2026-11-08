<footer class="bg-navy-950 text-blue-200">
    <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-white">
            <img src="{{ asset('images/logo/wattwise-mark.svg') }}" alt="" class="h-8 w-auto">
            <span class="font-extrabold tracking-tight">Watt<span class="text-sky-400">Wise</span></span>
        </a>
        <p>Manajemen energi rumah tangga cerdas. © {{ now()->year }} WattWise.</p>
    </div>
</footer>
