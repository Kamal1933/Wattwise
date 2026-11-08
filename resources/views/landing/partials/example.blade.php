<section class="bg-ice py-20">
    <div class="mx-auto grid max-w-6xl grid-cols-1 items-center gap-10 px-4 sm:px-6 lg:grid-cols-2">
        <div class="min-w-0">
            <h2 class="text-3xl font-extrabold tracking-tight text-navy sm:text-4xl">Saran yang jelas alasannya</h2>
            <p class="mt-4 text-lg leading-relaxed text-slate-600">
                WattWise memakai <strong class="font-semibold text-navy">sistem pakar</strong>: kumpulan aturan dari pengetahuan hemat energi.
                Data peralatan Anda dicocokkan dengan aturan itu satu per satu. Setiap saran menyebutkan angka yang membuatnya muncul,
                jadi Anda tidak perlu menebak-nebak.
            </p>
            <ul class="mt-6 space-y-3 text-slate-700">
                @foreach ([
                    'Saran diurutkan dari yang paling mendesak.',
                    'Setiap saran punya perkiraan penghematan dalam rupiah.',
                    'Langkahnya praktis, bisa langsung dicoba hari ini.',
                ] as $point)
                    <li class="flex gap-3">
                        <span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700">
                            <x-icon name="check" class="size-4" />
                        </span>
                        {{ $point }}
                    </li>
                @endforeach
            </ul>
        </div>

        <article class="card relative overflow-hidden p-6 pl-7">
            <span class="absolute inset-y-0 left-0 w-1.5 bg-electric" aria-hidden="true"></span>
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-md bg-electric-100 px-2 py-0.5 text-xs font-semibold text-electric-600">Sedang</span>
                <span class="text-xs text-slate-400">Contoh saran</span>
                <span class="ml-auto rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-800">Hemat Rp31.942/bln</span>
            </div>
            <h3 class="mt-3 text-lg font-semibold text-navy">Atur suhu AC kamar utama</h3>
            <p class="mt-1.5 text-slate-600">
                AC kamar utama menyala 9 jam sehari dan memakai 122,9 kWh per bulan. Menaikkan suhu ke 24–25°C dapat menghemat sekitar 15–20% listrik AC.
            </p>
            <ol class="mt-4 space-y-2 text-sm text-slate-700">
                <li class="flex gap-2.5"><span class="grid size-5 shrink-0 place-items-center rounded-full bg-electric-50 text-xs font-semibold text-electric">1</span>Setel suhu 24–25°C dan pakai mode Eco.</li>
                <li class="flex gap-2.5"><span class="grid size-5 shrink-0 place-items-center rounded-full bg-electric-50 text-xs font-semibold text-electric">2</span>Bersihkan filter AC setiap 2–4 minggu.</li>
            </ol>
            <details class="group mt-4 border-t border-slate-100 pt-3" open>
                <summary class="flex cursor-pointer list-none items-center gap-1.5 text-sm font-medium text-electric select-none">
                    <x-icon name="chevron" class="size-4 transition-transform group-open:rotate-90" />
                    Kenapa saran ini muncul?
                </summary>
                <p class="mt-2 rounded-lg bg-ice p-3 text-sm leading-relaxed text-slate-600">
                    Aturan terpicu karena kategori = pendingin ruangan dan durasi pemakaian = 9,0 jam/hari (syarat: ≥ 8,0 jam/hari).
                </p>
            </details>
        </article>
    </div>
</section>
