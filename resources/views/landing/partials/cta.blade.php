<section class="bg-[radial-gradient(ellipse_at_50%_0%,#0B2A7A_0%,#071A55_50%,#040C33_100%)] py-20 text-white">
    <div class="mx-auto flex max-w-4xl flex-col items-center px-4 text-center sm:px-6">
        <img src="{{ asset('images/logo/wattwise-mark.svg') }}" alt="" class="w-28">
        <h2 class="mt-6 text-3xl font-extrabold tracking-tight sm:text-4xl">Mulai hemat dari rumah sendiri</h2>
        <p class="mt-4 max-w-xl text-lg text-blue-100">
            Simpan peralatan Anda, pantau tagihan setiap bulan, dan dapatkan saran baru setiap kali kebiasaan di rumah berubah.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary px-6 py-3 text-base">Buka dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn-primary px-6 py-3 text-base">Buat akun</a>
                <a href="{{ route('login') }}" class="btn border border-white/20 bg-white/5 px-6 py-3 text-base text-white hover:bg-white/10">Saya sudah punya akun</a>
            @endauth
        </div>
    </div>
</section>
