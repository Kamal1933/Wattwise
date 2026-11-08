@extends('layouts.app')

@section('title', 'Profil Pengguna')
@section('page-title', 'Profil Pengguna')

@php $breadcrumbs = [['Profil Pengguna', null]]; @endphp

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <section class="card p-6 xl:col-span-2" aria-label="Identitas akun">
            <div class="flex flex-wrap items-center gap-4">
                <span class="grid size-14 place-items-center rounded-full bg-navy text-xl font-bold text-white">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <div class="min-w-0">
                    <p class="text-lg font-bold text-navy">{{ $user->name }}</p>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                </div>
                <dl class="ml-auto flex gap-6 text-sm">
                    <div><dt class="text-slate-500">Peran</dt><dd class="font-semibold text-ink">{{ $user->role === 'admin' ? 'Administrator' : 'Pengguna' }}</dd></div>
                    <div><dt class="text-slate-500">Rumah tangga</dt><dd class="font-semibold text-ink tabular-nums">{{ $user->households_count }}</dd></div>
                    <div><dt class="text-slate-500">Bergabung</dt><dd class="font-semibold text-ink">{{ $user->created_at?->translatedFormat('j M Y') }}</dd></div>
                </dl>
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
