@php $useOld = old('_dialog') === 'dialog-add-household'; @endphp
<form method="POST" action="{{ route('dashboard.households.store') }}" class="space-y-4 px-6 py-5">
    @csrf
    <input type="hidden" name="_dialog" value="dialog-add-household">
    <div>
        <label for="household-name" class="field-label">Nama</label>
        <input id="household-name" name="name" type="text" required maxlength="150" class="field-input" placeholder="Misalnya Rumah Bandung" value="{{ $useOld ? old('name') : '' }}">
    </div>
    <div>
        <label for="household-tariff" class="field-label">Golongan tarif dan daya</label>
        <select id="household-tariff" name="tariff_id" required class="field-input">
            @foreach ($tariffs as $tariff)
                <option value="{{ $tariff->id }}" @selected($useOld && (int) old('tariff_id') === $tariff->id)>
                    {{ $tariff->code }}, {{ number_format((float) $tariff->power_limit_va, 0, ',', '.') }} VA, Rp{{ number_format((float) $tariff->rate_per_kwh, 2, ',', '.') }}/kWh
                </option>
            @endforeach
        </select>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_8rem]">
        <div>
            <label for="household-address" class="field-label">Alamat <span class="font-normal text-slate-400">(opsional)</span></label>
            <input id="household-address" name="address" type="text" maxlength="255" class="field-input" value="{{ $useOld ? old('address') : '' }}">
        </div>
        <div>
            <label for="household-occupants" class="field-label">Penghuni</label>
            <input id="household-occupants" name="occupant_count" type="number" min="1" max="50" required class="field-input" value="{{ $useOld ? old('occupant_count') : 3 }}">
        </div>
    </div>
    <div class="flex justify-end gap-2 pt-2">
        <button type="button" class="btn-ghost" data-dialog-close>Batal</button>
        <button type="submit" class="btn-primary">Tambah rumah tangga</button>
    </div>
</form>
