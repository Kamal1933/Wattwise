@props(['id', 'title', 'description' => null])

<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-title" class="m-auto max-h-[calc(100dvh-2rem)] w-[calc(100%-2rem)] max-w-lg overflow-y-auto rounded-2xl bg-white p-0 text-ink shadow-2xl">
    <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
        <div>
            <h2 id="{{ $id }}-title" data-dialog-title class="text-lg font-semibold text-navy">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
            @endif
        </div>
        <button type="button" data-dialog-close class="btn-ghost -mr-2 px-2">
            <x-icon name="close" />
            <span class="sr-only">Tutup</span>
        </button>
    </div>

    @if ($errors->any() && old('_dialog') === $id)
        <div role="alert" class="mx-6 mt-5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{ $slot }}
</dialog>
