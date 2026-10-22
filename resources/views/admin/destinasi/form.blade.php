@extends('layouts.admin')
@php
    $edit = isset($destinasi);
    $d = fn ($k, $default = '') => old($k, $edit ? data_get($destinasi, $k, $default) : $default);
    $in = 'w-full border rounded-xl px-4 py-2.5 text-sm mt-1';
@endphp
@section('title', $edit ? 'Edit Destinasi' : 'Tambah Destinasi')
@section('heading', $edit ? 'Edit Destinasi' : 'Tambah Destinasi')
@section('sub', 'Lengkapi data destinasi wisata')

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ url('/admin/destinasi' . ($edit ? '/' . $destinasi->slug : '')) }}"
      class="bg-white rounded-2xl border p-6 max-w-3xl grid grid-cols-2 gap-4">
    @csrf @if ($edit) @method('PUT') @endif

    <div><label class="text-xs font-medium">Nama</label><input name="nama" value="{{ $d('nama') }}" class="{{ $in }}"></div>
    <div><label class="text-xs font-medium">Lokasi</label><input name="lokasi" value="{{ $d('lokasi') }}" class="{{ $in }}"></div>
    <div><label class="text-xs font-medium">Kategori</label>
        <select name="kategori" class="{{ $in }}">@foreach (['Alam', 'Pantai', 'Budaya', 'Edukasi'] as $k)<option @selected($d('kategori') === $k)>{{ $k }}</option>@endforeach</select></div>
    <div><label class="text-xs font-medium">Rating (0-5)</label><input name="rating" type="number" step="0.1" max="5" value="{{ $d('rating', 0) }}" class="{{ $in }}"></div>
    <div><label class="text-xs font-medium">Harga Dewasa (Rp)</label><input name="harga_dewasa" type="number" value="{{ $d('harga_dewasa') }}" class="{{ $in }}"></div>
    <div><label class="text-xs font-medium">Harga Anak (Rp)</label><input name="harga_anak" type="number" value="{{ $d('harga_anak', 0) }}" class="{{ $in }}"></div>
    <div class="col-span-2"><label class="text-xs font-medium">Deskripsi</label><textarea name="deskripsi" rows="4" class="{{ $in }}">{{ $d('deskripsi') }}</textarea></div>
    <div class="col-span-2"><p class="text-xs font-medium mb-1">Fasilitas</p>
        <div class="flex gap-5 text-sm">@foreach (['Parkir', 'Toilet', 'Mushola', 'Spot Foto'] as $f)
            <label class="flex items-center gap-2"><input type="checkbox" name="fasilitas[]" value="{{ $f }}" @checked(in_array($f, (array) $d('fasilitas', [])))> {{ $f }}</label>@endforeach</div></div>
    <div><label class="text-xs font-medium">Status</label>
        <select name="status" class="{{ $in }}">@foreach (['aktif', 'nonaktif'] as $s)<option value="{{ $s }}" @selected($d('status', 'aktif') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
    <div><label class="text-xs font-medium">Gambar</label><input type="file" name="gambar" accept="image/*" class="{{ $in }}"></div>

    <div class="col-span-2 flex gap-3 justify-end">
        <a href="{{ url('/admin/destinasi') }}" class="px-5 py-2.5 text-sm border rounded-xl">Batal</a>
        <button class="px-5 py-2.5 text-sm bg-primary text-white rounded-xl font-semibold">Simpan</button>
    </div>
</form>
@endsection
