<section class="relative overflow-hidden bg-[radial-gradient(ellipse_at_70%_30%,#0B2A7A_0%,#071A55_45%,#040C33_100%)] text-white">
    <svg class="pointer-events-none absolute inset-0 h-full w-full" viewBox="0 0 1440 720" preserveAspectRatio="none" aria-hidden="true">
        <defs>
            <linearGradient id="hero-streak" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#2563EB" stop-opacity="0"/>
                <stop offset="0.6" stop-color="#3B82F6" stop-opacity="0.55"/>
                <stop offset="1" stop-color="#60A5FA" stop-opacity="0"/>
            </linearGradient>
        </defs>
        <path d="M-40 260 C 140 190, 300 190, 480 120" fill="none" stroke="url(#hero-streak)" stroke-width="2"/>
        <path d="M980 700 C 1140 660, 1300 600, 1480 480" fill="none" stroke="url(#hero-streak)" stroke-width="2"/>
    </svg>

    <div class="relative mx-auto grid max-w-6xl grid-cols-1 items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[minmax(0,6fr)_minmax(0,5fr)] lg:py-24">
        <div class="min-w-0">
            <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-sm text-blue-100">
                <span class="size-2 rounded-full bg-emerald-400"></span>
                Sistem pakar hemat listrik rumah tangga
            </p>
            <h1 class="mt-6 text-4xl leading-[1.05] font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                Tahu ke mana listrik rumah Anda terpakai.
            </h1>
            <p class="mt-5 max-w-xl text-lg leading-relaxed text-blue-100">
                Masukkan peralatan di rumah, WattWise menghitung perkiraan tagihan bulanan dan menunjukkan
                alat mana yang paling boros, lengkap dengan saran hemat yang mudah dijalankan.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#kalkulator" class="btn-primary px-5 py-3 text-base">Coba hitung sekarang</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn border border-white/20 bg-white/5 px-5 py-3 text-base text-white hover:bg-white/10">Buka dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="btn border border-white/20 bg-white/5 px-5 py-3 text-base text-white hover:bg-white/10">Buat akun</a>
                @endauth
            </div>
            <p class="mt-4 text-sm text-blue-200/80">Tanpa alat tambahan. Cukup tahu daya (watt) dan berapa lama alat menyala.</p>
        </div>

        <div class="relative mx-auto w-full max-w-md">
            <img src="{{ asset('images/logo/wattwise-mark.svg') }}" alt="" class="mx-auto w-64 sm:w-80">
            <div class="relative -mt-6 rounded-2xl border border-white/10 bg-white p-5 text-ink shadow-2xl shadow-black/30 sm:mx-6">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-slate-500">Contoh perkiraan tagihan</span>
                    <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-800">Hemat Rp64.015</span>
                </div>
                <p class="mt-1 text-3xl font-extrabold tracking-tight text-navy tabular-nums">
                    Rp<span data-count-to="528175">528.175</span><span class="text-base font-medium text-slate-500"> /bulan</span>
                </p>
                <div class="mt-4 flex h-2.5 gap-0.5 overflow-hidden rounded-full bg-slate-100" aria-hidden="true">
                    <span class="bg-navy" style="width: 34%"></span>
                    <span class="bg-electric" style="width: 19%"></span>
                    <span class="bg-sky-400" style="width: 10%"></span>
                    <span class="bg-sky-200" style="width: 37%"></span>
                </div>
                <p class="mt-2 text-sm text-slate-600">AC kamar utama memakai <strong class="font-semibold text-navy">34%</strong> listrik rumah ini.</p>
            </div>
        </div>
    </div>
</section>
