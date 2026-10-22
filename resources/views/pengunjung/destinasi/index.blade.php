@extends('layouts.app')
@section('title', 'Destinasi Wisata')

@php
    $img = fn ($n) => 'https://placehold.co/300x300/E8F5E9/2E7D32?text=' . urlencode($n);
    $list = [
        ['Kawah Putih', 'Bandung', 4.8, 25000, 'Alam'], ['Raja Ampat', 'Papua Barat', 4.9, 750000, 'Pantai'],
        ['Bromo', 'Jawa Timur', 4.7, 50000, 'Alam'], ['Taman Safari Bogor', 'Bogor', 4.6, 120000, 'Edukasi'],
    ];
    $aktif = request('kategori');
@endphp

@section('content')
<div class="p-4">
    <h1 class="text-center font-semibold mb-3">Destinasi Wisata</h1>
    <form class="mb-3"><input name="q" value="{{ request('q') }}" placeholder="Cari destinasi..." class="w-full border rounded-lg px-3 py-2.5 text-sm bg-gray-50"></form>

    <div class="flex gap-2 overflow-x-auto mb-4 text-xs">
        @foreach (['' => 'Semua', 'Alam' => 'Alam', 'Pantai' => 'Pantai', 'Budaya' => 'Budaya', 'Edukasi' => 'Edukasi'] as $k => $l)
            <a href="{{ url('/destinasi' . ($k ? '?kategori=' . $k : '')) }}" class="px-4 py-1.5 rounded-full border {{ (string) $aktif === (string) $k ? 'bg-primary text-white border-primary' : 'text-gray-500' }}">{{ $l }}</a>
        @endforeach
    </div>

    <div class="space-y-3">
        @foreach ($list as [$nama, $lokasi, $rating, $harga, $kat])
            @continue($aktif && $aktif !== $kat)
            <a href="{{ url('/destinasi/' . Str::slug($nama)) }}" class="flex gap-3 p-2 rounded-2xl border bg-white hover:shadow">
                <img src="{{ $img($nama) }}" class="w-24 h-24 rounded-xl object-cover" alt="{{ $nama }}">
                <div class="flex-1 py-1">
                    <p class="font-semibold">{{ $nama }}</p>
                    <p class="text-xs text-gray-500">{{ $lokasi }}</p>
                    <p class="text-xs mt-1">★ {{ number_format($rating, 1) }}</p>
                    <p class="text-sm font-semibold text-primary mt-1">Rp {{ number_format($harga, 0, ',', '.') }} <span class="text-xs font-normal text-gray-400">/orang</span></p>
                </div>
                <span class="self-center text-gray-300 pr-2">›</span>
            </a>
        @endforeach
    </div>
</div>
@endsection
