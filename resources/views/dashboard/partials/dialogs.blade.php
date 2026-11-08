<x-dialog id="dialog-add-household" title="Tambah rumah tangga" description="Samakan golongan tarif dan daya dengan rekening listrik PLN Anda.">
    @include('dashboard.partials.forms.household')
</x-dialog>

@if ($household)
    <x-dialog id="dialog-add-appliance" title="Tambah peralatan" description="Perkiraan tagihan dan rekomendasi langsung diperbarui setelah disimpan.">
        @include('dashboard.partials.forms.appliance', [
            'dialogId' => 'dialog-add-appliance',
            'fieldPrefix' => 'add',
            'formAction' => route('households.appliances.store', $household),
            'submitLabel' => 'Simpan peralatan',
        ])
    </x-dialog>

    <x-dialog id="dialog-edit-appliance" title="Ubah peralatan" description="Perubahan langsung dihitung ulang.">
        @include('dashboard.partials.forms.appliance', [
            'dialogId' => 'dialog-edit-appliance',
            'fieldPrefix' => 'edit',
            'formAction' => old('_dialog') === 'dialog-edit-appliance' ? old('update_url') : '#',
            'method' => 'PUT',
            'submitLabel' => 'Simpan perubahan',
        ])
    </x-dialog>

    <x-dialog id="dialog-meter-reading" title="Catat meteran" description="Masukkan angka kWh meter di awal dan akhir periode.">
        @include('dashboard.partials.forms.meter-reading')
    </x-dialog>
@endif
