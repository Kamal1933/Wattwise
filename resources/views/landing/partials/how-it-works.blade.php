<section id="cara-kerja" class="bg-ice py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="max-w-2xl text-3xl font-extrabold tracking-tight text-navy sm:text-4xl">Tiga langkah, tanpa istilah teknis</h2>
        <p class="mt-3 max-w-2xl text-lg text-slate-600">Kalau Anda bisa membaca stiker di belakang kulkas, Anda bisa memakai WattWise.</p>

        <ol class="mt-10 grid grid-cols-1 gap-5 md:grid-cols-3">
            @foreach ([
                ['plug', 'Catat peralatan', 'Pilih alat yang ada di rumah, misalnya AC, kulkas, dan lampu. Isi berapa jam sehari alat itu menyala.'],
                ['chart', 'Lihat perkiraan tagihan', 'WattWise menghitung pemakaian (kWh) dan biayanya sesuai golongan tarif PLN rumah Anda.'],
                ['light', 'Jalankan saran hemat', 'Sistem pakar menunjukkan alat paling boros dan cara menghematnya, beserta alasan dan perkiraan rupiahnya.'],
            ] as $index => [$icon, $title, $text])
                <li class="card relative p-6">
                    <div class="flex items-center gap-3">
                        <span class="grid size-11 place-items-center rounded-xl bg-navy text-white">
                            <x-icon :name="$icon" class="size-5" />
                        </span>
                        <span class="text-sm font-semibold text-electric">Langkah {{ $index + 1 }}</span>
                    </div>
                    <h3 class="mt-5 text-lg font-bold text-navy">{{ $title }}</h3>
                    <p class="mt-2 leading-relaxed text-slate-600">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
