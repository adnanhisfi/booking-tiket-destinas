@extends('layouts.app')
@section('title', 'Destinasi Wisata - JelajahKu')

@php
    $img = fn ($d) => $d->thumbnail
        ? asset('storage/'.$d->thumbnail)
        : 'https://placehold.co/300x300/E8F5E9/2E7D32?text='.urlencode($d->name);
    $params = array_filter(request()->only(['q', 'sort', 'location', 'min_price', 'max_price']), fn ($v) => $v !== null && $v !== '');
@endphp

@section('content')
<div class="p-4">
    <h1 class="text-center font-semibold mb-3">Destinasi Wisata</h1>

    <form method="GET" action="{{ route('katalog.index') }}" class="mb-3">
        @if ($category)
            <input type="hidden" name="kategori" value="{{ $category }}">
        @endif
        @if ($sort !== 'featured')
            <input type="hidden" name="sort" value="{{ $sort }}">
        @endif
        @if ($location)
            <input type="hidden" name="location" value="{{ $location }}">
        @endif
        @if ($minPrice !== null)
            <input type="hidden" name="min_price" value="{{ $minPrice }}">
        @endif
        @if ($maxPrice !== null)
            <input type="hidden" name="max_price" value="{{ $maxPrice }}">
        @endif
        <input name="q" value="{{ request('q') }}" placeholder="Cari destinasi..." class="w-full border rounded-lg px-3 py-2.5 text-sm bg-gray-50">
    </form>

    <div class="flex gap-2 overflow-x-auto mb-3 text-xs">
        <a href="{{ route('katalog.index', $params) }}" class="px-4 py-1.5 rounded-full border whitespace-nowrap {{ ! $category ? 'bg-primary text-white border-primary' : 'text-gray-500' }}">Semua</a>
        @foreach ($categories as $cat)
            <a href="{{ route('katalog.index', array_merge($params, ['kategori' => $cat->slug])) }}"
               class="px-4 py-1.5 rounded-full border whitespace-nowrap {{ $category === $cat->slug ? 'bg-primary text-white border-primary' : 'text-gray-500' }}">{{ $cat->name }}</a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('katalog.index') }}" class="mb-4 flex justify-end">
        <input type="hidden" name="q" value="{{ request('q') }}">
        @if ($category)
            <input type="hidden" name="kategori" value="{{ $category }}">
        @endif
        @if ($location)
            <input type="hidden" name="location" value="{{ $location }}">
        @endif
        @if ($minPrice !== null)
            <input type="hidden" name="min_price" value="{{ $minPrice }}">
        @endif
        @if ($maxPrice !== null)
            <input type="hidden" name="max_price" value="{{ $maxPrice }}">
        @endif
        <select name="sort" onchange="this.form.submit()" class="border rounded-lg px-3 py-2 text-xs bg-gray-50">
            <option value="featured" @selected($sort === 'featured')>Paling populer</option>
            <option value="price_asc" @selected($sort === 'price_asc')>Harga termurah</option>
            <option value="price_desc" @selected($sort === 'price_desc')>Harga tertinggi</option>
            <option value="name" @selected($sort === 'name')>Nama A-Z</option>
        </select>
    </form>

    <details class="mb-4 rounded-xl border p-3" @if ($location || $minPrice !== null || $maxPrice !== null) open @endif>
        <summary class="cursor-pointer text-xs font-semibold text-gray-600">Filter lokasi & harga</summary>
        <form method="GET" action="{{ route('katalog.index') }}" class="mt-3 grid grid-cols-2 gap-2">
            <input type="hidden" name="q" value="{{ request('q') }}">
            @if ($category)
                <input type="hidden" name="kategori" value="{{ $category }}">
            @endif
            @if ($sort !== 'featured')
                <input type="hidden" name="sort" value="{{ $sort }}">
            @endif
            <input name="location" value="{{ $location }}" placeholder="Lokasi (contoh: Bali)"
                   class="col-span-2 border rounded-lg px-3 py-2.5 text-sm bg-gray-50">
            <input name="min_price" type="number" min="0" value="{{ $minPrice }}" placeholder="Harga min"
                   class="border rounded-lg px-3 py-2.5 text-sm bg-gray-50">
            <input name="max_price" type="number" min="0" value="{{ $maxPrice }}" placeholder="Harga maks"
                   class="border rounded-lg px-3 py-2.5 text-sm bg-gray-50">
            <button class="col-span-2 bg-primary text-white rounded-xl py-2.5 text-sm font-semibold">Terapkan Filter</button>
            @if ($location || $minPrice !== null || $maxPrice !== null)
                <a href="{{ route('katalog.index', array_filter(['q' => request('q'), 'kategori' => $category, 'sort' => $sort !== 'featured' ? $sort : null])) }}"
                   class="col-span-2 text-center text-xs text-gray-500">Reset filter</a>
            @endif
        </form>
    </details>

    <div class="space-y-3">
        @forelse ($destinations as $destination)
            <a href="{{ route('katalog.detail', $destination->slug) }}" class="flex gap-3 p-2 rounded-2xl border bg-white hover:shadow">
                <img src="{{ $img($destination) }}" class="w-24 h-24 rounded-xl object-cover" alt="{{ $destination->name }}">
                <div class="flex-1 py-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-semibold truncate">{{ $destination->name }}</p>
                        @if ($destination->category)
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-soft text-primary shrink-0">{{ $destination->category->name }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500">📍 {{ $destination->location }}</p>
                    <p class="text-xs mt-1">★ {{ $destination->rating_avg ? number_format((float) $destination->rating_avg, 1) : 'Baru' }}</p>
                    <p class="text-sm font-semibold text-primary mt-1">
                        @if ($destination->min_price !== null)
                            Rp {{ number_format((float) $destination->min_price, 0, ',', '.') }}
                            <span class="text-xs font-normal text-gray-400">/orang</span>
                        @else
                            <span class="text-xs font-normal text-gray-400">Belum ada tiket</span>
                        @endif
                    </p>
                </div>
                <span class="self-center text-gray-300 pr-2">›</span>
            </a>
        @empty
            <div class="border rounded-2xl p-8 text-center text-sm text-gray-500">
                Tidak ada destinasi yang cocok dengan pencarianmu.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $destinations->links() }}</div>
</div>
@endsection
