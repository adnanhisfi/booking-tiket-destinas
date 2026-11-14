@extends('layouts.admin')

@php
    $edit = $ticketType !== null;
    $d = fn ($key, $default = '') => old($key, $edit ? data_get($ticketType, $key, $default) : $default);
    $selectedDestinationId = (int) old('destination_id', $edit ? $ticketType->destination_id : ($selectedDestination ?? 0));
    $in = 'w-full border rounded-xl px-4 py-2.5 text-sm mt-1';
@endphp

@section('title', $edit ? 'Edit Jenis Tiket' : 'Tambah Jenis Tiket')
@section('heading', $edit ? 'Edit Jenis Tiket' : 'Tambah Jenis Tiket')
@section('sub', 'Atur harga, kuota harian, dan status tiket')

@section('content')
<form method="POST"
      action="{{ $edit ? route('admin.tickets.update', $ticketType->id) : route('admin.tickets.store') }}"
      class="grid max-w-2xl grid-cols-1 gap-4 rounded-2xl border bg-white p-5 sm:p-6 md:grid-cols-2">
    @csrf
    @if ($edit)
        @method('PUT')
    @endif

    <div class="md:col-span-2">
        <label class="text-xs font-medium">Destinasi</label>
        <select name="destination_id" class="{{ $in }}" required>
            <option value="">Pilih destinasi</option>
            @foreach ($destinations as $destination)
                <option value="{{ $destination->id }}" @selected($selectedDestinationId === $destination->id)>{{ $destination->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-xs font-medium">Nama Jenis Tiket</label>
        <input name="name" value="{{ $d('name') }}" placeholder="Contoh: Tiket Dewasa" class="{{ $in }}" required>
    </div>
    <div>
        <label class="text-xs font-medium">Status</label>
        <select name="status" class="{{ $in }}">
            <option value="active" @selected($d('status', 'active') === 'active')>Aktif</option>
            <option value="inactive" @selected($d('status') === 'inactive')>Nonaktif</option>
        </select>
    </div>
    <div>
        <label class="text-xs font-medium">Harga (Rp)</label>
        <input name="price" type="number" min="0" step="100" value="{{ $d('price') }}" class="{{ $in }}" required>
    </div>
    <div>
        <label class="text-xs font-medium">Kuota Harian</label>
        <input name="default_quota" type="number" min="0" step="1" value="{{ $d('default_quota', 0) }}" class="{{ $in }}" required>
    </div>
    <div class="md:col-span-2">
        <label class="text-xs font-medium">Deskripsi</label>
        <input name="description" value="{{ $d('description') }}" placeholder="Opsional" class="{{ $in }}">
    </div>

    <div class="flex flex-col-reverse gap-3 md:col-span-2 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.tickets.index') }}" class="px-5 py-2.5 text-sm border rounded-xl">Batal</a>
        <button class="px-5 py-2.5 text-sm bg-primary text-white rounded-xl font-semibold">Simpan</button>
    </div>
</form>
@endsection
