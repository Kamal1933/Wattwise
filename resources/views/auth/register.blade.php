@extends('auth.layout')

@section('title', 'Daftar')
@section('heading', 'Buat akun WattWise')
@section('subheading', 'Mulai catat dan hemat pemakaian listrik di rumah.')

@section('form')
    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="field-label">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="field-input">
        </div>
        <div>
            <label for="email" class="field-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                   class="field-input">
        </div>
        <div>
            <label for="password" class="field-label">Kata sandi</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="field-input">
        </div>
        <div>
            <label for="password_confirmation" class="field-label">Ulangi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="field-input">
        </div>
        <button type="submit" class="btn-primary w-full">
            Daftar
        </button>
    </form>
@endsection

@section('footer')
    Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-electric hover:underline">Masuk</a>
@endsection
