<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-4 px-6 py-5">
    @csrf
    @isset($method)
        @method($method)
        <input type="hidden" name="update_url" value="{{ $formAction }}">
    @endisset
    <input type="hidden" name="_dialog" value="{{ $dialogId }}">
    @include('shared.appliance-fields')
    <div class="flex justify-end gap-2 pt-2">
        <button type="button" class="btn-ghost" data-dialog-close>Batal</button>
        <button type="submit" class="btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
