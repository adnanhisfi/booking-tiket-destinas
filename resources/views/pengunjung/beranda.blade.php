@extends('layouts.app')
@section('title', 'Beranda - JelajahKu')

@php
    $user = auth()->user();
    $img = fn ($d) => $d->thumbnail
        ? asset('storage/'.$d->thumbnail)
        : 'https://placehold.co/600x400/E8F5E9/2E7D32?text='.urlencode($d->name);
@endphp

@section('content')
<div class="p-4">
    <div class="flex justify-between items-center">
        <div>
            <p class="font-semibold">Hai, {{ $user?->name ?? 'Tamu' }} 👋</p>
            <p class="text-xs text-gray-500">Mau liburan ke mana hari ini?</p>
        </div>
        <a href="{{ $user ? route('profile.edit') : route('login') }}" class="w-9 h-9 rounded-full bg-primary text-white grid place-items-center text-sm font-semibold">
            {{ strtoupper(substr($user?->name ?? 'T', 0, 1)) }}
        </a>
    </div>

    <form action="{{ route('katalog.index') }}" class="mt-3">
        <input name="q" placeholder="Cari destinasi, kota, atau aktivitas..." class="w-full border rounded-lg px-3 py-2.5 text-sm bg-gray-50">
    </form>

    <div class="mt-4 rounded-2xl p-5 text-white bg-gradient-to-br from-teal-500 to-primary">
        <p class="text-xl font-bold leading-6">Pesona Alam<br>Indonesia</p>
        <p class="text-xs mt-1 mb-3 opacity-90">Temukan destinasi terbaik untuk liburanmu</p>
        <a href="{{ route('katalog.index') }}" class="inline-block bg-white text-primary text-xs font-semibold rounded-full px-4 py-2">Jelajahi Sekarang</a>
    </div>

    <div class="flex justify-between items-center mt-5 mb-2">
        <p class="font-semibold text-sm">Destinasi Populer</p>
        <a href="{{ route('katalog.index') }}" class="text-xs text-primary">Lihat semua ›</a>
    </div>

    @if ($destinations->isEmpty())
        <div class="border rounded-2xl p-6 text-center text-sm text-gray-500">Belum ada destinasi aktif.</div>
    @else
        <div class="flex gap-3 overflow-x-auto pb-2">
            @foreach ($destinations as $destination)
                <a href="{{ route('katalog.detail', $destination->slug) }}" class="min-w-[46%] border rounded-2xl overflow-hidden">
                    <img src="{{ $img($destination) }}" class="h-24 w-full object-cover" alt="{{ $destination->name }}">
                    <div class="p-2">
                        <p class="text-xs font-semibold truncate">{{ $destination->name }}</p>
                        <p class="text-[10px] text-gray-500 truncate">{{ $destination->location }}</p>
                        <p class="text-xs font-semibold text-primary">
                            @if ($destination->min_price !== null)
                                Rp {{ number_format((float) $destination->min_price, 0, ',', '.') }}
                            @else
                                Belum ada tiket
                            @endif
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <p class="font-semibold text-sm mt-4 mb-2">Kategori</p>
    <div class="grid grid-cols-4 gap-2 text-center text-[11px]">
        @forelse ($categories->take(4) as $category)
            <a href="{{ route('katalog.index', ['kategori' => $category->slug]) }}">
                <div class="aspect-square rounded-xl bg-soft border border-primary/30 grid place-items-center text-primary font-bold text-lg">{{ strtoupper(substr($category->name, 0, 1)) }}</div>
                <span class="mt-1 block truncate">{{ $category->name }}</span>
            </a>
        @empty
            <p class="col-span-4 text-xs text-gray-400">Kategori belum tersedia.</p>
        @endforelse
    </div>

    @guest
        <div class="mt-5 border rounded-2xl p-4 text-center">
            <p class="text-sm font-semibold">Ingin memesan tiket?</p>
            <p class="text-xs text-gray-500 mt-1">Masuk atau daftar dulu untuk mulai booking.</p>
            <a href="{{ route('login') }}" class="mt-3 inline-block bg-primary text-white text-xs font-semibold rounded-full px-5 py-2">Masuk / Daftar</a>
        </div>
    @endguest
</div>
@endsection
