@extends('layouts.master')
@section('title', 'Masuk Admin - JelajahKu')

@section('body')
<div class="min-h-screen grid lg:grid-cols-2">
    <div class="hidden lg:flex flex-col justify-between p-14 text-white bg-gradient-to-br from-green-800 via-green-700 to-green-400">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-white text-primary grid place-items-center font-bold text-xl">J</div>
            <p class="text-xl"><b>JelajahKu</b> Admin</p>
        </div>
        <div>
            <h1 class="text-5xl font-bold leading-tight">Kelola destinasi dan booking wisata dalam satu tempat.</h1>
            <p class="mt-4 text-green-50 max-w-md">Pantau pembayaran, atur destinasi, dan lihat laporan pendapatan JelajahKu secara real-time.</p>
        </div>
        <p class="text-xs text-green-100">Tiket Wisata, Cerita Baru</p>
    </div>

    <div class="flex items-center justify-center p-8 bg-white">
        <form method="POST" action="{{ url('/admin/login') }}" class="w-full max-w-sm" x-data="{ lihat: false }">
            @csrf
            <h2 class="text-2xl font-bold">Masuk Admin</h2>
            <p class="text-sm text-gray-400 mb-6">Gunakan akun admin untuk melanjutkan.</p>
            @if ($errors->any())<div class="mb-4 rounded-xl bg-red-50 text-red-600 text-sm px-4 py-3">{{ $errors->first() }}</div>@endif

            <label class="text-xs font-medium">Email Admin</label>
            <input name="email" type="email" value="{{ old('email') }}" placeholder="admin@jelajahku.id" class="w-full mt-1 mb-4 border rounded-xl px-4 py-3 text-sm bg-gray-50">

            <label class="text-xs font-medium">Password</label>
            <div class="relative mt-1 mb-3">
                <input name="password" :type="lihat ? 'text' : 'password'" class="w-full border rounded-xl px-4 py-3 text-sm bg-gray-50">
                <button type="button" @click="lihat=!lihat" class="absolute right-4 top-3 text-xs text-primary" x-text="lihat ? 'Sembunyikan' : 'Tampilkan'"></button>
            </div>
            <div class="flex justify-between mb-6 text-xs">
                <label class="flex items-center gap-2 text-gray-500"><input type="checkbox" name="remember"> Ingat saya</label>
                <a href="#" class="text-primary">Lupa password?</a>
            </div>
            <button class="w-full bg-primary text-white rounded-xl py-3 text-sm font-semibold">Masuk</button>
        </form>
    </div>
</div>
@endsection
