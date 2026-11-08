<section id="fitur" class="py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="max-w-2xl text-3xl font-extrabold tracking-tight text-navy sm:text-4xl">Setelah daftar, Anda mendapat</h2>

        <div class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['chart', 'Dashboard tagihan', 'Perkiraan tagihan bulan ini, porsi tiap alat, dan beban terhadap daya PLN di satu layar.'],
                ['light', 'Saran sistem pakar', 'Rekomendasi hemat yang diperbarui setiap kali Anda menambah atau mengubah peralatan.'],
                ['meter', 'Catat meteran PLN', 'Masukkan angka meteran untuk melihat seberapa tepat perkiraan dibanding pemakaian nyata.'],
                ['home', 'Beberapa rumah', 'Kelola rumah sendiri, rumah orang tua, atau kos dalam satu akun.'],
            ] as [$icon, $title, $text])
                <div class="card p-6">
                    <span class="grid size-11 place-items-center rounded-xl bg-electric-50 text-electric">
                        <x-icon :name="$icon" class="size-5" />
                    </span>
                    <h3 class="mt-5 font-bold text-navy">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
