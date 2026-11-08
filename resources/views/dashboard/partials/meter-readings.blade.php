<section id="meteran" aria-labelledby="meteran-title" class="mb-10">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 id="meteran-title" class="text-xl font-bold tracking-tight text-navy">Catatan meteran PLN</h2>
            <p class="mt-1 text-sm text-slate-500">Bandingkan perkiraan WattWise dengan angka di kWh meter rumah Anda.</p>
        </div>
        <button type="button" class="btn-secondary" data-dialog-open="dialog-meter-reading">
            <x-icon name="plus" class="size-4" />
            Catat meteran
        </button>
    </div>

    @if ($recentReadings->isEmpty())
        <div class="card p-6 text-sm text-slate-500">
            Belum ada catatan. Foto angka di kWh meter pada awal dan akhir bulan, lalu catat di sini untuk melihat seberapa tepat perkiraan.
        </div>
    @else
        <div class="card overflow-x-auto">
            <table class="w-full min-w-[36rem] text-left text-sm">
                <thead class="border-b border-slate-200 bg-ice text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-medium">Tanggal</th>
                        <th scope="col" class="px-5 py-3 text-right font-medium">Angka meter</th>
                        <th scope="col" class="px-5 py-3 text-right font-medium">Pemakaian</th>
                        <th scope="col" class="px-5 py-3 text-right font-medium">Tagihan</th>
                        <th scope="col" class="px-5 py-3 text-right font-medium">Selisih dari perkiraan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($recentReadings as $reading)
                        @php $variance = $reading->variance_pct !== null ? (float) $reading->variance_pct : null; @endphp
                        <tr>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-ink">{{ $reading->reading_date->translatedFormat('j F Y') }}</p>
                                @if ($reading->notes)
                                    <p class="text-xs text-slate-500">{{ $reading->notes }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap text-slate-600 tabular-nums">{{ $number($reading->meter_start_kwh, 0) }} ke {{ $number($reading->meter_end_kwh, 0) }}</td>
                            <td class="px-5 py-3.5 text-right font-semibold tabular-nums">{{ $number($reading->actual_kwh_consumed) }} kWh</td>
                            <td class="px-5 py-3.5 text-right tabular-nums">{{ $reading->actual_cost_billed ? $rupiah($reading->actual_cost_billed) : '—' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                @if ($variance === null)
                                    <span class="text-slate-400">—</span>
                                @else
                                    <span class="rounded-md px-2 py-0.5 text-xs font-semibold tabular-nums {{ abs($variance) <= 10 ? 'bg-emerald-50 text-emerald-800' : (abs($variance) <= 25 ? 'bg-amber-50 text-amber-900' : 'bg-red-50 text-red-800') }}">
                                        {{ $variance > 0 ? '+' : '' }}{{ $number($variance) }}%
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
