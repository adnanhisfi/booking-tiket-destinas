@extends('layouts.app')
@section('title', 'Masuk - JelajahKu')
@section('hideNav', '1')

@section('content')
<div class="p-6 pt-12" x-data="{ tab: '{{ $errors->has('name') || $errors->has('phone') || old('name') ? 'daftar' : 'masuk' }}' }">
    <div class="text-center mb-6">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-primary text-white grid place-items-center text-2xl font-bold">J</div>
        <p class="font-semibold text-primary mt-2">JelajahKu</p>
    </div>
    <h1 class="text-xl font-bold">Selamat Datang!</h1>
    <p class="text-xs text-gray-500 mb-4">Masuk atau daftar untuk melanjutkan</p>

    <div class="grid grid-cols-2 border rounded-lg overflow-hidden text-sm mb-5">
        <button type="button" @click="tab='masuk'" :class="tab==='masuk' ? 'bg-primary text-white' : 'text-gray-500'" class="py-2">Masuk</button>
        <button type="button" @click="tab='daftar'" :class="tab==='daftar' ? 'bg-primary text-white' : 'text-gray-500'" class="py-2">Daftar</button>
    </div>

    <form x-show="tab==='masuk'" method="POST" action="{{ route('login.attempt') }}" class="space-y-3">
        @csrf
        <input name="email" value="{{ old('email') }}" placeholder="Email atau No. HP" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <input name="password" type="password" placeholder="Password" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <label class="flex items-center gap-2 text-xs text-gray-500"><input type="checkbox" name="remember" class="accent-green-700"> Ingat saya</label>
        <button class="w-full bg-primary text-white rounded-lg py-3 text-sm font-semibold">Masuk</button>
    </form>

    <form x-show="tab==='daftar'" x-cloak method="POST" action="{{ route('register') }}" class="space-y-3">
        @csrf
        <input name="name" value="{{ old('name') }}" placeholder="Nama lengkap" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <input name="email" type="email" value="{{ old('email') }}" placeholder="Email" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <input name="phone" value="{{ old('phone') }}" placeholder="No. HP" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <input name="password" type="password" placeholder="Password (minimal 8 karakter)" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <input name="password_confirmation" type="password" placeholder="Ulangi password" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <button class="w-full bg-primary text-white rounded-lg py-3 text-sm font-semibold">Daftar</button>
    </form>

    <p class="text-center text-xs text-gray-400 mt-6">Lupa password? Hubungi admin di <a href="mailto:admin@jelajahku.test" class="text-primary">admin@jelajahku.test</a></p>
    <p class="text-center text-xs text-gray-400 mt-2">Masuk sebagai pengelola? <a href="{{ route('admin.login') }}" class="text-primary font-semibold">Login Admin</a></p>
</div>
@endsection
