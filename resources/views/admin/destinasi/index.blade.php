@extends('layouts.admin')
@section('title', 'Kelola Destinasi')
@section('heading', 'Kelola Destinasi')
@section('sub', 'Tambah, ubah, dan hapus destinasi wisata')

@section('actions')
    <a href="{{ route('admin.destinations.create') }}" class="bg-primary text-white text-sm rounded-xl px-5 py-2.5 font-semibold">+ Tambah Destinasi</a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border p-5">
    <form method="GET" action="{{ route('admin.destinations.index') }}" class="mb-4 flex flex-wrap gap-3">
        <input name="q" value="{{ request('q') }}" placeholder="Cari destinasi..." class="w-full border rounded-xl px-4 py-2 text-sm sm:w-72">
        <select name="category_id" class="border rounded-xl px-3 py-2 text-sm">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) request('category_id') === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="status" class="border rounded-xl px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="active" @selected(request('status') === 'active')>Aktif</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
        </select>
        <button class="border rounded-xl px-4 py-2 text-sm text-gray-600">Terapkan</button>
        <a href="{{ route('admin.destinations.index') }}" class="px-2 py-2 text-sm text-gray-400">Reset</a>
    </form>

    <div class="overflow-x-auto">
    <table class="w-full min-w-[820px] text-sm">
        <thead class="text-xs text-gray-400 text-left">
            <tr><th class="py-2">DESTINASI</th><th>KATEGORI</th><th>RATING</th><th>HARGA TIKET</th><th>STATUS</th><th>AKSI</th></tr>
        </thead>
        <tbody>
            @forelse ($destinations as $destination)
                @php
                    $placeholder = asset('images/destination-placeholder.svg');
                    $thumb = $destination->thumbnailUrl() ?? $placeholder;
                @endphp
                <tr class="border-t">
                    <td class="py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $thumb }}" loading="lazy" decoding="async" class="h-11 w-11 rounded-lg object-cover" alt="{{ $destination->name }}"
                                 onerror="this.onerror=null;this.src='{{ $placeholder }}'">
                            <div>
                                <p class="font-medium">{{ $destination->name }}</p>
                                <p class="text-xs text-gray-400">{{ $destination->location }}</p>
                            </div>
                        </div>
                    </td>
                    <td>{{ $destination->category?->name ?? '-' }}</td>
                    <td>★ {{ $destination->rating_avg ? number_format((float) $destination->rating_avg, 1) : '-' }}</td>
                    <td>
                        @if ($destination->min_price !== null)
                            Rp {{ number_format((float) $destination->min_price, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>@include('partials.badge', ['s' => $destination->status])</td>
                    <td class="space-x-3 whitespace-nowrap">
                        <a href="{{ route('admin.destinations.edit', $destination->slug) }}" class="text-primary">Edit</a>
                        <form method="POST" action="{{ route('admin.destinations.destroy', $destination->slug) }}" class="inline"
                              onsubmit="return confirm('Hapus destinasi {{ addslashes($destination->name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-500">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-gray-400">Belum ada destinasi.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-4">{{ $destinations->links() }}</div>
</div>
@endsection
