@extends('layouts.app')
@section('title', 'Masuk - JelajahKu')
@section('hideNav', '1')

@section('content')
<div class="p-6 pt-12" x-data="{ tab: 'masuk' }">
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

    <form x-show="tab==='masuk'" method="POST" action="{{ url('/masuk') }}" class="space-y-3">
        @csrf
        <input name="email" placeholder="Email atau No. HP" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <input name="password" type="password" placeholder="Password" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <div class="text-right"><a href="#" class="text-xs text-primary">Lupa password?</a></div>
        <button class="w-full bg-primary text-white rounded-lg py-3 text-sm font-semibold">Masuk</button>
    </form>

    <form x-show="tab==='daftar'" x-cloak method="POST" action="{{ url('/daftar') }}" class="space-y-3">
        @csrf
        <input name="name" placeholder="Nama lengkap" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <input name="email" type="email" placeholder="Email" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <input name="phone" placeholder="No. HP" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <input name="password" type="password" placeholder="Password" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <input name="password_confirmation" type="password" placeholder="Ulangi password" class="w-full border rounded-lg px-3 py-3 text-sm bg-gray-50">
        <button class="w-full bg-primary text-white rounded-lg py-3 text-sm font-semibold">Daftar</button>
    </form>

    <div class="flex items-center gap-3 my-5 text-xs text-gray-400"><span class="flex-1 border-t"></span>atau masuk dengan<span class="flex-1 border-t"></span></div>
    <div class="grid grid-cols-3 gap-3">
        @foreach (['G', 'f', 'A'] as $s)<button type="button" class="border rounded-lg py-2.5 text-sm font-semibold text-gray-600">{{ $s }}</button>@endforeach
    </div>
    <p class="text-center text-xs text-gray-400 mt-6">Belum punya akun? <button type="button" @click="tab='daftar'" class="text-primary font-semibold">Daftar sekarang</button></p>
</div>
@endsection
