@extends('auth.layout')

@section('title', 'Masuk')
@section('heading', 'Masuk ke WattWise')
@section('subheading', 'Kelola konsumsi listrik rumah tangga Anda.')

@section('form')
    <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="field-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="field-input">
        </div>
        <div>
            <label for="password" class="field-label">Kata sandi</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="field-input">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 accent-electric">
            Ingat saya
        </label>
        <button type="submit" class="btn-primary w-full">
            Masuk
        </button>
    </form>
@endsection

@section('footer')
    Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-electric hover:underline">Daftar</a>
@endsection
