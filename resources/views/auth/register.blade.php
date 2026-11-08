@extends('auth.layout')

@section('title', 'Daftar')
@section('heading', 'Buat akun WattWise')
@section('subheading', 'Mulai catat dan hemat pemakaian listrik di rumah.')

@section('form')
    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Kata sandi</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">Ulangi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="w-full py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500">
            Daftar
        </button>
    </form>
@endsection

@section('footer')
    Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:underline">Masuk</a>
@endsection
