@extends('layouts.admin')
@section('title', 'Kelola Destinasi')
@section('heading', 'Kelola Destinasi')
@section('sub', 'Tambah, ubah, dan hapus destinasi wisata')
@section('actions')<a href="{{ url('/admin/destinasi/create') }}" class="bg-primary text-white text-sm rounded-xl px-5 py-2.5 font-semibold">+ Tambah Destinasi</a>@endsection

@php
    $list = [
        ['Kawah Putih', 'Ciwidey, Bandung', 'Alam', 4.8, 25000, 'aktif'], ['Raja Ampat', 'Papua Barat', 'Pantai', 4.9, 750000, 'aktif'],
        ['Bromo', 'Jawa Timur', 'Alam', 4.7, 50000, 'aktif'], ['Taman Safari Bogor', 'Bogor', 'Edukasi', 4.6, 120000, 'aktif'],
        ['Candi Borobudur', 'Magelang', 'Budaya', 4.8, 75000, 'aktif'], ['Pantai Kuta', 'Bali', 'Pantai', 4.5, 15000, 'nonaktif'],
    ];
@endphp

@section('content')
<div class="bg-white rounded-2xl border p-5">
    <form class="flex gap-3 mb-4">
        <input name="q" placeholder="Cari destinasi..." class="border rounded-xl px-4 py-2 text-sm w-72">
        <select name="kategori" class="border rounded-xl px-3 py-2 text-sm">
            <option value="">Semua</option>@foreach (['Alam', 'Pantai', 'Budaya', 'Edukasi'] as $k)<option>{{ $k }}</option>@endforeach
        </select>
    </form>
    <table class="w-full text-sm">
        <thead class="text-xs text-gray-400 text-left"><tr><th class="py-2">DESTINASI</th><th>KATEGORI</th><th>RATING</th><th>HARGA TIKET</th><th>STATUS</th><th>AKSI</th></tr></thead>
        <tbody>
        @foreach ($list as [$nama, $lokasi, $kat, $rating, $harga, $status])
            <tr class="border-t">
                <td class="py-3"><div class="flex items-center gap-3">
                    <img src="https://placehold.co/80x80/E8F5E9/2E7D32?text={{ urlencode(substr($nama, 0, 2)) }}" class="w-11 h-11 rounded-lg object-cover">
                    <div><p class="font-medium">{{ $nama }}</p><p class="text-xs text-gray-400">{{ $lokasi }}</p></div></div></td>
                <td>{{ $kat }}</td><td>★ {{ number_format($rating, 1) }}</td><td>Rp {{ number_format($harga, 0, ',', '.') }}</td>
                <td>@include('partials.badge', ['s' => $status])</td>
                <td class="space-x-3 whitespace-nowrap">
                    <a href="{{ url('/admin/destinasi/' . Str::slug($nama) . '/edit') }}" class="text-primary">Edit</a>
                    <form method="POST" action="{{ url('/admin/destinasi/' . Str::slug($nama)) }}" class="inline" onsubmit="return confirm('Hapus {{ $nama }}?')">@csrf @method('DELETE')
                        <button class="text-red-500">Hapus</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="flex justify-between items-center mt-4 text-sm text-gray-500">
        <span>Menampilkan 6 dari 12 destinasi</span>
        <div class="flex gap-1"><span class="px-3 py-1 rounded bg-primary text-white">1</span><span class="px-3 py-1 rounded border">2</span></div>
    </div>
</div>
@endsection
