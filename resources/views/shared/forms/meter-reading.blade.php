@php $useOld = old('_dialog') === 'dialog-meter-reading'; @endphp
<form method="POST" action="{{ route('households.meter.store', $household) }}" class="space-y-4 px-6 py-5">
    @csrf
    <input type="hidden" name="_dialog" value="dialog-meter-reading">
    <input type="hidden" name="calculation_period_id" value="{{ $currentPeriod?->id }}">
    <div>
        <label for="meter-date" class="field-label">Tanggal pencatatan</label>
        <input id="meter-date" name="reading_date" type="date" required class="field-input" value="{{ $useOld ? old('reading_date') : now()->toDateString() }}">
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label for="meter-start" class="field-label">Angka awal (kWh)</label>
            <input id="meter-start" name="meter_start_kwh" type="number" step="0.01" min="0" required class="field-input" value="{{ $useOld ? old('meter_start_kwh') : $lastMeterEnd }}">
            @if ($lastMeterEnd && ! $useOld)
                <p class="field-hint">Diisi dari angka akhir catatan terakhir.</p>
            @endif
        </div>
        <div>
            <label for="meter-end" class="field-label">Angka akhir (kWh)</label>
            <input id="meter-end" name="meter_end_kwh" type="number" step="0.01" min="0" required class="field-input" value="{{ $useOld ? old('meter_end_kwh') : '' }}">
        </div>
    </div>
    <div>
        <label for="meter-cost" class="field-label">Tagihan atau token yang dibayar (Rp) <span class="font-normal text-slate-400">(opsional)</span></label>
        <input id="meter-cost" name="actual_cost_billed" type="number" step="1" min="0" class="field-input" value="{{ $useOld ? old('actual_cost_billed') : '' }}">
    </div>
    <div>
        <label for="meter-notes" class="field-label">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
        <input id="meter-notes" name="notes" type="text" maxlength="255" class="field-input" placeholder="Misalnya tagihan November" value="{{ $useOld ? old('notes') : '' }}">
    </div>
    <div class="flex justify-end gap-2 pt-2">
        <button type="button" class="btn-ghost" data-dialog-close>Batal</button>
        <button type="submit" class="btn-primary">Simpan catatan</button>
    </div>
</form>
