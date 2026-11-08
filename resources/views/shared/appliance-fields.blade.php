{{-- Shared fields for the add/edit appliance forms. $fieldPrefix keeps ids unique per dialog. --}}
@php $useOld = old('_dialog') === $dialogId; @endphp
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="{{ $fieldPrefix }}-name" class="field-label">Nama peralatan</label>
        <input id="{{ $fieldPrefix }}-name" name="name" type="text" required maxlength="150" class="field-input" placeholder="Misalnya AC kamar utama" value="{{ $useOld ? old('name') : '' }}">
    </div>
    <div>
        <label for="{{ $fieldPrefix }}-category" class="field-label">Kategori</label>
        <select id="{{ $fieldPrefix }}-category" name="category_id" required class="field-input">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected($useOld && (int) old('category_id') === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="{{ $fieldPrefix }}-quantity" class="field-label">Jumlah unit</label>
        <input id="{{ $fieldPrefix }}-quantity" name="quantity" type="number" min="1" max="100" required class="field-input" value="{{ $useOld ? old('quantity') : 1 }}">
    </div>
    <div>
        <label for="{{ $fieldPrefix }}-power" class="field-label">Daya per unit (watt)</label>
        <input id="{{ $fieldPrefix }}-power" name="power_watt" type="number" step="0.1" min="0.5" max="25000" required class="field-input" placeholder="650" value="{{ $useOld ? old('power_watt') : '' }}">
        <p class="field-hint">Lihat label di belakang atau bawah alat.</p>
    </div>
    <div>
        <label for="{{ $fieldPrefix }}-hours" class="field-label">Menyala per hari (jam)</label>
        <input id="{{ $fieldPrefix }}-hours" name="daily_hours" type="number" step="0.1" min="0.1" max="24" required class="field-input" value="{{ $useOld ? old('daily_hours') : 8 }}">
    </div>
    <div>
        <label for="{{ $fieldPrefix }}-days" class="field-label">Hari pemakaian per bulan</label>
        <input id="{{ $fieldPrefix }}-days" name="days_per_month" type="number" min="1" max="31" required class="field-input" value="{{ $useOld ? old('days_per_month') : 30 }}">
    </div>
    <div>
        <label for="{{ $fieldPrefix }}-factor" class="field-label">Faktor pemakaian</label>
        <input id="{{ $fieldPrefix }}-factor" name="usage_factor" type="number" step="0.05" min="0.05" max="1" required class="field-input" value="{{ $useOld ? old('usage_factor') : 1 }}">
        <p class="field-hint">1 untuk lampu atau TV, 0,7 AC inverter, 0,5 kulkas.</p>
    </div>
    <div class="sm:col-span-2">
        <label for="{{ $fieldPrefix }}-photo" class="field-label">Foto perangkat <span class="font-normal text-slate-400">(opsional)</span></label>
        <div class="flex items-center gap-4">
            <span class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-xl bg-ice ring-1 ring-slate-200">
                <img src="" alt="" class="hidden size-full object-cover" data-photo-preview>
                <x-icon name="camera" class="size-6 text-slate-400" data-photo-placeholder />
            </span>
            <input id="{{ $fieldPrefix }}-photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="field-input file:mr-3 file:rounded-lg file:border-0 file:bg-electric-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-electric" data-photo-input>
        </div>
        <p class="field-hint">Foto alat Anda sendiri, format JPG, PNG, atau WEBP, maksimal 2 MB. Di HP bisa langsung memotret.</p>
        @if ($fieldPrefix === 'edit')
            <label class="mt-2 flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remove_photo" value="1" class="rounded border-slate-300 accent-electric" data-photo-remove>
                Hapus foto yang ada dan pakai gambar bawaan
            </label>
        @endif
    </div>
</div>
