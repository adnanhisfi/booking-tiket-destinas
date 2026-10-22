@extends('layouts.app')
@section('title', 'Beranda - JelajahKu')

@php
    $img = fn ($n) => 'https://placehold.co/600x400/E8F5E9/2E7D32?text=' . urlencode($n);
    $populer = [['Kawah Putih', 'Bandung', 25000], ['Raja Ampat', 'Papua Barat', 750000], ['Bromo', 'Jawa Timur', 50000]];
@endphp

@section('content')
<div class="p-4">
    <div class="flex justify-between items-center">
        <div><p class="font-semibold">Hai, Andi</p><p class="text-xs text-gray-500">Mau liburan ke mana hari ini?</p></div>
        <div class="w-9 h-9 rounded-full bg-primary text-white grid place-items-center text-sm font-semibold">A</div>
    </div>

    <form action="{{ url('/destinasi') }}" class="mt-3">
        <input name="q" placeholder="Cari destinasi, kota, atau aktivitas..." class="w-full border rounded-lg px-3 py-2.5 text-sm bg-gray-50">
    </form>

    <div class="mt-4 rounded-2xl p-5 text-white bg-gradient-to-br from-teal-500 to-primary">
        <p class="text-xl font-bold leading-6">Pesona Alam<br>Indonesia</p>
        <p class="text-xs mt-1 mb-3 opacity-90">Temukan destinasi terbaik untuk liburanmu</p>
        <a href="{{ url('/destinasi') }}" class="inline-block bg-white text-primary text-xs font-semibold rounded-full px-4 py-2">Jelajahi Sekarang</a>
    </div>

    <div class="flex justify-between items-center mt-5 mb-2">
        <p class="font-semibold text-sm">Destinasi Populer</p>
        <a href="{{ url('/destinasi') }}" class="text-xs text-primary">Lihat semua ›</a>
    </div>
    <div class="flex gap-3 overflow-x-auto pb-2">
        @foreach ($populer as [$nama, $lokasi, $harga])
            <a href="{{ url('/destinasi/' . Str::slug($nama)) }}" class="min-w-[46%] border rounded-2xl overflow-hidden">
                <img src="{{ $img($nama) }}" class="h-24 w-full object-cover" alt="{{ $nama }}">
                <div class="p-2">
                    <p class="text-xs font-semibold">{{ $nama }}</p><p class="text-[10px] text-gray-500">{{ $lokasi }}</p>
                    <p class="text-xs font-semibold text-primary">Rp {{ number_format($harga, 0, ',', '.') }}</p>
                </div>
            </a>
        @endforeach
    </div>

    <p class="font-semibold text-sm mt-4 mb-2">Kategori</p>
    <div class="grid grid-cols-4 gap-2 text-center text-[11px]">
        @foreach (['Alam', 'Pantai', 'Budaya', 'Edukasi'] as $k)
            <a href="{{ url('/destinasi?kategori=' . $k) }}">
                <div class="aspect-square rounded-xl bg-soft border border-primary/30 grid place-items-center text-primary font-bold text-lg">{{ substr($k, 0, 1) }}</div>
                <span class="mt-1 block">{{ $k }}</span>
            </a>
        @endforeach
    </div>
</div>
@endsection
