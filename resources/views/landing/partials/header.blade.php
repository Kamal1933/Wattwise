<header class="sticky top-0 z-40 border-b border-white/10 bg-navy-950/90 text-white backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="{{ asset('images/logo/wattwise-mark.svg') }}" alt="" class="h-10 w-auto">
            <span class="text-xl font-extrabold tracking-tight">Watt<span class="text-sky-400">Wise</span></span>
        </a>

        <nav aria-label="Navigasi utama" class="hidden items-center gap-1 text-sm font-medium text-blue-100 md:flex">
            <a href="#cara-kerja" class="rounded-lg px-3 py-2 hover:bg-white/10 hover:text-white">Cara kerja</a>
            <a href="#kalkulator" class="rounded-lg px-3 py-2 hover:bg-white/10 hover:text-white">Kalkulator</a>
            <a href="#fitur" class="rounded-lg px-3 py-2 hover:bg-white/10 hover:text-white">Fitur</a>
            <a href="#tanya-jawab" class="rounded-lg px-3 py-2 hover:bg-white/10 hover:text-white">Tanya jawab</a>
        </nav>

        <div class="flex items-center gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary px-4 py-2">Buka dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn px-3 py-2 text-blue-100 hover:bg-white/10 hover:text-white">Masuk</a>
                <a href="{{ route('register') }}" class="btn-primary px-4 py-2">Daftar</a>
            @endauth
        </div>
    </div>
</header>
