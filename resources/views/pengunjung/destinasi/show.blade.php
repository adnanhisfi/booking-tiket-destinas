@extends('layouts.app')
@section('title', 'Kawah Putih')
@section('hideNav', '1')

@section('content')
<div class="pb-24">
    <div class="relative">
        <img src="https://placehold.co/800x500/E8F5E9/2E7D32?text=Kawah+Putih" class="w-full h-56 object-cover" alt="Kawah Putih">
        <a href="{{ url('/destinasi') }}" class="absolute top-4 left-4 w-9 h-9 rounded-full bg-white grid place-items-center shadow">‹</a>
        <button class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white grid place-items-center shadow">♡</button>
    </div>
    <div class="p-4 -mt-4 bg-white rounded-t-3xl relative">
        <h1 class="text-lg font-bold">Kawah Putih</h1>
        <p class="text-xs text-gray-500">★ 4.8 (12.4k ulasan) · Ciwidey, Bandung</p>

        <p class="font-semibold text-sm mt-4">Tentang Destinasi</p>
        <p class="text-sm text-gray-600 mt-1">Kawah Putih adalah danau kawah vulkanik dengan air berwarna hijau toska di dataran tinggi Ciwidey, Bandung Selatan.</p>

        <p class="font-semibold text-sm mt-4">Fasilitas</p>
        <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
            @foreach (['Parkir', 'Toilet', 'Mushola', 'Spot Foto'] as $f)
                <span class="flex items-center gap-2"><span class="w-4 h-4 rounded border border-primary bg-soft"></span>{{ $f }}</span>
            @endforeach
        </div>
    </div>

    <div class="fixed bottom-0 inset-x-0 max-w-md mx-auto bg-white border-t p-4 flex items-center gap-4">
        <div><p class="text-[11px] text-gray-400">Harga Tiket</p><p class="font-bold text-primary">Rp 25.000 <span class="text-xs font-normal text-gray-400">/orang</span></p></div>
        <a href="{{ url('/booking/kawah-putih') }}" class="flex-1 text-center bg-primary text-white rounded-xl py-3 text-sm font-semibold">Booking Sekarang</a>
    </div>
</div>
@endsection
