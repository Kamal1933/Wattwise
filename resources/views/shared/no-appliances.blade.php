<div class="card flex flex-col items-start gap-4 border-dashed p-8 sm:flex-row sm:items-center">
    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-electric-50 text-electric">
        <x-icon name="plug" class="size-6" />
    </span>
    <div class="flex-1">
        <h3 class="text-lg font-semibold text-navy">Belum ada peralatan aktif</h3>
        <p class="mt-1 max-w-prose text-sm text-slate-500">
            Tambahkan peralatan seperti AC, kulkas, lampu, atau pompa air. Perkiraan tagihan dan rekomendasi muncul setelah peralatan pertama tersimpan.
        </p>
    </div>
    <button type="button" class="btn-primary" data-dialog-open="dialog-add-appliance">
        <x-icon name="plus" class="size-4" />
        Tambah peralatan
    </button>
</div>
