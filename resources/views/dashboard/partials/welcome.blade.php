<section class="card mx-auto mt-6 max-w-2xl overflow-hidden">
    <div class="bg-navy px-8 py-10 text-white">
        <span class="grid size-12 place-items-center rounded-xl bg-electric">
            <x-icon name="home" class="size-7" />
        </span>
        <h1 class="mt-6 text-2xl font-bold tracking-tight sm:text-3xl">Selamat datang di WattWise, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</h1>
        <p class="mt-3 max-w-prose text-blue-100">
            Daftarkan rumah Anda dan peralatan listrik di dalamnya. WattWise akan memperkirakan tagihan bulanan,
            lalu sistem pakar menunjukkan alat mana yang paling boros dan cara menghematnya.
        </p>
    </div>
    <ol class="divide-y divide-slate-100 px-8 py-2 text-sm">
        @foreach ([
            ['Tambah rumah tangga', 'Pilih golongan tarif dan daya PLN sesuai rekening listrik Anda.'],
            ['Catat peralatan', 'Masukkan daya (watt) dan berapa jam per hari alat menyala.'],
            ['Baca rekomendasi', 'Lihat perkiraan tagihan dan saran hemat beserta alasannya.'],
        ] as $index => [$step, $hint])
            <li class="flex gap-4 py-4">
                <span class="grid size-7 shrink-0 place-items-center rounded-full bg-electric-50 text-sm font-semibold text-electric">{{ $index + 1 }}</span>
                <span>
                    <span class="block font-semibold text-ink">{{ $step }}</span>
                    <span class="text-slate-500">{{ $hint }}</span>
                </span>
            </li>
        @endforeach
    </ol>
    <div class="border-t border-slate-100 px-8 py-6">
        <button type="button" class="btn-primary w-full sm:w-auto" data-dialog-open="dialog-add-household">
            <x-icon name="plus" class="size-4" />
            Tambah rumah tangga
        </button>
    </div>
</section>
