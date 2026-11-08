@extends('auth.layout')

@section('title', 'Masuk')
@section('heading', 'Masuk ke WattWise')
@section('subheading', 'Kelola konsumsi listrik rumah tangga Anda.')

@section('form')
    <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Kata sandi</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <label class="flex items-center gap-2 text-xs text-slate-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-blue-600">
            Ingat saya
        </label>
        <button type="submit" class="w-full py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500">
            Masuk
        </button>
    </form>
@endsection

@section('footer')
    Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:underline">Daftar</a>
@endsection
