<div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-navy via-[#1E3A8A] to-electric text-white">
    <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-40" viewBox="0 0 800 260" preserveAspectRatio="none" aria-hidden="true">
        <path d="M0 220 C 200 160, 420 260, 800 120" fill="none" stroke="#60A5FA" stroke-width="1.5"/>
        <path d="M0 240 C 220 190, 460 280, 800 150" fill="none" stroke="#38BDF8" stroke-width="1"/>
    </svg>

    <div class="relative grid grid-cols-1 items-center gap-4 p-6 sm:p-8 lg:grid-cols-[minmax(0,5fr)_minmax(0,4fr)_minmax(0,3fr)]">
        <div class="min-w-0">
            <p class="text-2xl leading-tight font-extrabold tracking-tight sm:text-3xl">
                Selamat datang di Watt<span class="text-sky-300">Wise</span>
            </p>
            <p class="mt-1 font-medium text-blue-100">{{ $household->name }}</p>
            <p class="mt-3 max-w-md text-sm leading-relaxed text-blue-100/90">
                {{ $household->tariff->code ?? 'Tarif belum diatur' }}, daya {{ $number($household->tariff?->power_limit_va, 0) }} VA, {{ $household->occupant_count }} penghuni.
                Pantau, analisis, dan hemat pemakaian listrik rumah Anda.
            </p>
            <div class="mt-5 flex flex-wrap gap-2">
                <button type="button" class="btn bg-white text-navy hover:bg-blue-50" data-dialog-open="dialog-add-appliance">
                    <x-icon name="plus" class="size-4" />
                    Tambah perangkat
                </button>
                <button type="button" class="btn border border-white/30 bg-white/10 text-white hover:bg-white/20" data-dialog-open="dialog-meter-reading">
                    <x-icon name="meter" class="size-4" />
                    Catat meteran
                </button>
                @if ($activeAppliances->isNotEmpty())
                    <form method="POST" action="{{ route('households.reanalyze', $household) }}">
                        @csrf
                        <button type="submit" class="btn border border-white/30 bg-white/10 text-white hover:bg-white/20">
                            <x-icon name="refresh" class="size-4" />
                            Analisis ulang
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <x-house-scene class="mx-auto hidden w-full max-w-sm sm:block" />

        <ul class="space-y-2.5">
            @foreach (['Pantau konsumsi listrik', 'Temukan perangkat boros', 'Dapatkan rekomendasi hemat'] as $point)
                <li class="flex items-center gap-2.5 rounded-full bg-white/95 py-1.5 pr-4 pl-1.5 text-sm font-semibold text-navy shadow-sm">
                    <span class="grid size-6 shrink-0 place-items-center rounded-full bg-emerald-500 text-white">
                        <x-icon name="check" class="size-4" />
                    </span>
                    {{ $point }}
                </li>
            @endforeach
        </ul>
    </div>
</div>
