@extends('layouts.admin')

@php
    $edit = $destinasi !== null;
    $d = fn ($key, $default = '') => old($key, $edit ? data_get($destinasi, $key, $default) : $default);
    $facilitiesValue = old('facilities', $edit && is_array($destinasi->facilities) ? implode(', ', $destinasi->facilities) : '');
    $in = 'w-full border rounded-xl px-4 py-2.5 text-sm mt-1';
@endphp

@section('title', $edit ? 'Edit Destinasi' : 'Tambah Destinasi')
@section('heading', $edit ? 'Edit Destinasi' : 'Tambah Destinasi')
@section('sub', 'Lengkapi data destinasi wisata')

@section('content')
<form method="POST" enctype="multipart/form-data"
      action="{{ $edit ? route('admin.destinations.update', $destinasi->slug) : route('admin.destinations.store') }}"
      class="grid max-w-3xl grid-cols-1 gap-4 rounded-2xl border bg-white p-5 sm:p-6 md:grid-cols-2">
    @csrf
    @if ($edit)
        @method('PUT')
    @endif

    <div>
        <label class="text-xs font-medium">Nama Destinasi</label>
        <input name="name" value="{{ $d('name') }}" class="{{ $in }}" required>
    </div>
    <div>
        <label class="text-xs font-medium">Kategori</label>
        <select name="category_id" class="{{ $in }}" required>
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) $d('category_id') === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-xs font-medium">Lokasi</label>
        <input name="location" value="{{ $d('location') }}" placeholder="Contoh: Bandung, Jawa Barat" class="{{ $in }}" required>
    </div>
    <div>
        <label class="text-xs font-medium">Alamat Lengkap</label>
        <input name="address" value="{{ $d('address') }}" class="{{ $in }}">
    </div>
    <div>
        <label class="text-xs font-medium">Jam Buka</label>
        <input name="opening_time" type="time" value="{{ $d('opening_time') }}" class="{{ $in }}">
    </div>
    <div>
        <label class="text-xs font-medium">Jam Tutup</label>
        <input name="closing_time" type="time" value="{{ $d('closing_time') }}" class="{{ $in }}">
    </div>
    <div class="md:col-span-2">
        <label class="text-xs font-medium">Deskripsi</label>
        <textarea name="description" rows="4" class="{{ $in }}" required>{{ $d('description') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="text-xs font-medium">Fasilitas</label>
        <input name="facilities" value="{{ $facilitiesValue }}" placeholder="Pisahkan dengan koma: Parkir, Toilet, Mushola" class="{{ $in }}">
        <p class="text-[11px] text-gray-400 mt-1">Pisahkan setiap fasilitas dengan tanda koma.</p>
    </div>
    <div>
        <label class="text-xs font-medium">Status</label>
        <select name="status" class="{{ $in }}">
            <option value="active" @selected($d('status', 'active') === 'active')>Aktif</option>
            <option value="inactive" @selected($d('status') === 'inactive')>Nonaktif</option>
        </select>
    </div>
    <div>
        <label class="text-xs font-medium">Gambar / Thumbnail</label>
        <input type="file" name="thumbnail" accept="image/*" class="{{ $in }}">
        @if ($edit && $destinasi->thumbnail)
            <div class="mt-2 flex items-center gap-2">
                <img src="{{ asset('storage/'.$destinasi->thumbnail) }}" class="w-16 h-16 rounded-lg object-cover" alt="Thumbnail">
                <span class="text-[11px] text-gray-400">Biarkan kosong jika tidak ingin mengganti gambar.</span>
            </div>
        @endif
    </div>
    <div class="md:col-span-2">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_featured" value="1" @checked((bool) old('is_featured', $edit ? $destinasi->is_featured : false)) class="accent-green-700">
            Tampilkan sebagai destinasi unggulan
        </label>
    </div>

    <div class="flex flex-col-reverse gap-3 md:col-span-2 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.destinations.index') }}" class="px-5 py-2.5 text-sm border rounded-xl">Batal</a>
        <button class="px-5 py-2.5 text-sm bg-primary text-white rounded-xl font-semibold">Simpan</button>
    </div>
</form>
@endsection
