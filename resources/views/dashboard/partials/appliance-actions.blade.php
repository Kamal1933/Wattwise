@php
    $appliancePayload = [
        'update_url' => route('appliances.update', $appliance),
        'name' => $appliance->name,
        'category_id' => $appliance->category_id,
        'quantity' => $appliance->quantity,
        'power_watt' => (float) $appliance->power_watt,
        'daily_hours' => (float) $appliance->daily_hours,
        'days_per_month' => $appliance->days_per_month,
        'usage_factor' => (float) $appliance->usage_factor,
    ];
@endphp
<div class="flex shrink-0 items-center justify-end gap-1">
    <button
        type="button"
        class="btn-ghost px-2"
        data-dialog-open="dialog-edit-appliance"
        data-appliance="{{ json_encode($appliancePayload) }}"
        title="Ubah {{ $appliance->name }}"
    >
        <x-icon name="pencil" class="size-4" />
        <span class="sr-only">Ubah {{ $appliance->name }}</span>
    </button>
    <form method="POST" action="{{ route('appliances.destroy', $appliance) }}" onsubmit="return confirm('Hapus {{ addslashes($appliance->name) }} dari daftar peralatan?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-ghost px-2 hover:bg-red-50 hover:text-red-700" title="Hapus {{ $appliance->name }}">
            <x-icon name="trash" class="size-4" />
            <span class="sr-only">Hapus {{ $appliance->name }}</span>
        </button>
    </form>
</div>
