@extends('layouts.app')

@section('title', 'Profil Pengguna')
@section('page-title', 'Profil Pengguna')

@php $breadcrumbs = [['Profil Pengguna', null]]; @endphp

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <section class="card p-6 xl:col-span-2" aria-labelledby="profile-photo-title">
            <div class="flex flex-col gap-6 md:flex-row md:items-center">
                <div class="flex items-center gap-5">
                    <div class="relative">
                        <x-user-avatar :user="$user" size="size-24" text="text-3xl" data-avatar-current />
                        <img src="" alt="Pratinjau foto baru" data-avatar-preview class="hidden size-24 rounded-full object-cover ring-4 ring-electric-100">
                    </div>
                    <div class="min-w-0">
                        <p class="text-lg font-bold text-navy">{{ $user->name }}</p>
                        <p class="truncate text-sm text-slate-500">{{ $user->email }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $user->isAdmin() ? 'Administrator' : 'Pengguna' }},
                            {{ $user->households_count }} rumah tangga,
                            bergabung {{ $user->created_at?->translatedFormat('j M Y') }}
                        </p>
                    </div>
                </div>

                <div class="md:ml-auto md:w-96">
                    <h2 id="profile-photo-title" class="text-sm font-semibold text-ink">Foto profil</h2>
                    <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="mt-2 flex flex-wrap items-center gap-2">
                        @csrf
                        <label class="btn-secondary cursor-pointer">
                            <x-icon name="camera" class="size-4" />
                            <span data-avatar-label>{{ $user->photoUrl() ? 'Ganti foto' : 'Pilih foto' }}</span>
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required class="sr-only" data-avatar-input>
                        </label>
                        <button type="submit" class="btn-primary hidden" data-avatar-save>Simpan foto</button>
                    </form>
                    @if ($user->photoUrl())
                        <form method="POST" action="{{ route('profile.photo.destroy') }}" class="mt-2" onsubmit="return confirm('Hapus foto profil?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-medium text-red-700 hover:underline">Hapus foto</button>
                        </form>
                    @endif
                    <p class="field-hint mt-2">Format JPG, PNG, atau WEBP, maksimal 2 MB. Tanpa foto, inisial nama yang ditampilkan.</p>
                    @error('photo', 'photo') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="card p-6" aria-labelledby="profile-form-title">
            <h2 id="profile-form-title" class="text-lg font-bold text-navy">Data akun</h2>
            <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="profile-name" class="field-label">Nama</label>
                    <input id="profile-name" name="name" type="text" required maxlength="150" class="field-input" value="{{ old('name', $user->name) }}" autocomplete="name">
                    @error('name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="profile-email" class="field-label">Email</label>
                    <input id="profile-email" name="email" type="email" required maxlength="191" class="field-input" value="{{ old('email', $user->email) }}" autocomplete="email">
                    @error('email') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Simpan profil</button>
                </div>
            </form>
        </section>

        <section class="card p-6" aria-labelledby="password-form-title">
            <h2 id="password-form-title" class="text-lg font-bold text-navy">Ganti kata sandi</h2>
            <form method="POST" action="{{ route('profile.password') }}" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="current-password" class="field-label">Kata sandi saat ini</label>
                    <input id="current-password" name="current_password" type="password" required class="field-input" autocomplete="current-password">
                    @error('current_password', 'password') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new-password" class="field-label">Kata sandi baru</label>
                    <input id="new-password" name="password" type="password" required minlength="8" class="field-input" autocomplete="new-password">
                    <p class="field-hint">Minimal 8 karakter.</p>
                    @error('password', 'password') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new-password-confirmation" class="field-label">Ulangi kata sandi baru</label>
                    <input id="new-password-confirmation" name="password_confirmation" type="password" required class="field-input" autocomplete="new-password">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Ganti kata sandi</button>
                </div>
            </form>
        </section>
    </div>
@endsection
