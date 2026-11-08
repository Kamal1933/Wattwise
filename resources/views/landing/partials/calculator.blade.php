<section id="kalkulator" class="py-20" data-calculator>
    <script type="application/json" data-calculator-tariffs>@json($tariffs)</script>

    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-extrabold tracking-tight text-navy sm:text-4xl">Hitung tagihan listrik Anda sekarang</h2>
            <p class="mt-3 text-lg text-slate-600">
                Centang alat yang ada di rumah, lalu geser berapa jam sehari alat itu menyala. Hasilnya berubah langsung.
            </p>
        </div>

        <div class="mt-10 grid grid-cols-1 items-start gap-8 lg:grid-cols-[minmax(0,7fr)_minmax(0,5fr)]">
            {{-- Inputs --}}
            <div class="min-w-0 space-y-6">
                <div class="card p-5">
                    <label for="calc-tariff" class="field-label">Daya listrik rumah Anda</label>
                    <select id="calc-tariff" class="field-input" data-calculator-tariff>
                        @foreach ($tariffs as $tariff)
                            <option value="{{ $tariff['id'] }}" @selected($tariff['id'] === $defaultTariffId)>
                                {{ number_format($tariff['power_limit_va'], 0, ',', '.') }} VA ({{ $tariff['code'] }}), Rp{{ number_format($tariff['rate_per_kwh'], 2, ',', '.') }}/kWh
                            </option>
                        @endforeach
                    </select>
                    <p class="field-hint">Lihat angka VA di rekening listrik atau di meteran PLN rumah Anda.</p>
                </div>

                <ul class="grid grid-cols-1 gap-3 sm:grid-cols-2" aria-label="Daftar peralatan">
                    @foreach ($appliancePresets as $preset)
                        <li
                            class="card p-4 transition-colors has-[:checked]:border-electric/50 has-[:checked]:bg-electric-50/40"
                            data-calculator-item
                            data-key="{{ $preset['key'] }}"
                            data-name="{{ $preset['name'] }}"
                            data-category="{{ $preset['category'] }}"
                            data-watt="{{ $preset['watt'] }}"
                            data-factor="{{ $preset['factor'] }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <label class="flex min-w-0 flex-1 cursor-pointer items-start gap-3">
                                    <input type="checkbox" class="mt-0.5 size-5 shrink-0 rounded accent-electric" data-calculator-enabled @checked($preset['selected'])>
                                    <span class="min-w-0">
                                        <span class="block font-semibold text-ink">{{ $preset['name'] }}</span>
                                        <span class="block text-sm text-slate-500">{{ number_format($preset['watt'], 0, ',', '.') }} watt</span>
                                    </span>
                                </label>

                                <div class="flex items-center gap-1" role="group" aria-label="Jumlah {{ $preset['name'] }}">
                                    <button type="button" class="btn-secondary size-8 rounded-lg p-0" data-calculator-step="-1" aria-label="Kurangi jumlah {{ $preset['name'] }}">−</button>
                                    <input type="number" min="1" max="20" value="{{ $preset['quantity'] }}" class="field-input w-11 rounded-lg px-1 py-1 text-center tabular-nums" data-calculator-quantity aria-label="Jumlah {{ $preset['name'] }}">
                                    <button type="button" class="btn-secondary size-8 rounded-lg p-0" data-calculator-step="1" aria-label="Tambah jumlah {{ $preset['name'] }}">+</button>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center gap-3">
                                <label for="hours-{{ $preset['key'] }}" class="sr-only">Jam menyala per hari untuk {{ $preset['name'] }}</label>
                                <x-icon name="clock" class="size-4 shrink-0 text-slate-400" />
                                <input id="hours-{{ $preset['key'] }}" type="range" min="0.5" max="24" step="0.5" value="{{ $preset['hours'] }}" class="h-2 w-full cursor-pointer accent-electric" data-calculator-hours>
                                <output for="hours-{{ $preset['key'] }}" class="w-20 shrink-0 text-right text-sm font-semibold text-navy tabular-nums" data-calculator-hours-label>{{ $preset['hours'] }} jam/hari</output>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Results --}}
            <aside id="hasil" class="scroll-mt-20 lg:sticky lg:top-24" aria-labelledby="calc-result-title">
                <div class="overflow-hidden rounded-3xl bg-navy text-white">
                    <div class="p-6">
                        <h3 id="calc-result-title" class="text-sm font-medium text-blue-200">Perkiraan tagihan per bulan</h3>
                        <p class="mt-1 text-4xl font-extrabold tracking-tight tabular-nums" data-calculator-cost aria-live="polite">Rp0</p>
                        <p class="mt-1 text-blue-100"><span class="font-semibold tabular-nums" data-calculator-kwh>0</span> kWh per bulan</p>

                        <div class="mt-5 flex h-3 gap-0.5 overflow-hidden rounded-full bg-navy-800" data-calculator-share aria-hidden="true"></div>
                        <ol class="mt-4 space-y-2 text-sm" data-calculator-top aria-label="Alat paling boros"></ol>
                        <p class="hidden text-sm text-blue-100" data-calculator-empty>Pilih minimal satu peralatan untuk melihat perkiraan.</p>
                    </div>

                    <div class="border-t border-white/10 bg-white/5 p-6">
                        <div class="flex items-baseline justify-between text-sm">
                            <span class="text-blue-200">Beban jika semua menyala bersamaan</span>
                            <span class="font-semibold tabular-nums" data-calculator-load-percent>0%</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-navy-800">
                            <div class="h-full rounded-full bg-emerald-400 transition-[width] duration-300" style="width: 0%" data-calculator-load-bar></div>
                        </div>
                        <p class="mt-2 text-sm text-blue-100" data-calculator-load-text></p>
                    </div>
                </div>

                <div class="card mt-4 p-6">
                    <div class="flex items-baseline justify-between gap-3">
                        <h3 class="font-bold text-navy">Saran hemat untuk Anda</h3>
                        <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-sm font-semibold text-emerald-800 tabular-nums" data-calculator-saving>Rp0/bln</span>
                    </div>
                    <ul class="mt-4 space-y-3 text-sm" data-calculator-tips></ul>
                    <p class="hidden text-sm text-slate-600" data-calculator-no-tips>Pemakaian Anda sudah efisien. Pertahankan!</p>

                    <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="btn-primary mt-6 w-full">
                        {{ auth()->check() ? 'Simpan di dashboard saya' : 'Buat akun untuk analisis lengkap' }}
                    </a>
                    <p class="mt-3 text-xs leading-relaxed text-slate-500">
                        Perkiraan memakai tarif per kWh golongan yang dipilih. Pajak penerangan jalan dan biaya admin belum termasuk.
                    </p>
                </div>
            </aside>
        </div>
    </div>

    <div class="fixed inset-x-0 bottom-0 z-30 translate-y-full border-t border-white/10 bg-navy px-4 py-3 text-white transition-transform duration-200 lg:hidden" data-calculator-mobile-bar aria-hidden="true">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
            <div>
                <p class="text-xs text-blue-200">Perkiraan tagihan per bulan</p>
                <p class="text-xl font-extrabold tabular-nums" data-calculator-mobile-cost>Rp0</p>
            </div>
            <a href="#hasil" class="btn-primary px-4 py-2" tabindex="-1">Lihat hasil</a>
        </div>
    </div>
</section>
