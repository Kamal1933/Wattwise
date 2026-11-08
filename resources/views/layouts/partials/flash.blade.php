@if (session('status'))
    <div role="status" class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
        <x-icon name="check" class="mt-0.5 size-5 shrink-0 text-emerald-600" />
        <p class="flex-1">{{ session('status') }}</p>
    </div>
@endif

@if ($errors->any() && ! old('_dialog'))
    <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <ul class="list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
