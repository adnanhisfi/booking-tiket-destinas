@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('heading', 'Dashboard')
@section('sub', 'Ringkasan aktivitas JelajahKu hari ini')

@php
    $terbaru = [
        ['BK-20261015-001', 'Andi Pratama', 'Kawah Putih', 50000, 'menunggu'],
        ['BK-20260910-014', 'Sari Dewi', 'Raja Ampat', 1500000, 'selesai'],
        ['BK-20261014-009', 'Budi Santoso', 'Bromo', 150000, 'aktif'],
        ['BK-20261014-007', 'Rina Lestari', 'Taman Safari Bogor', 375000, 'aktif'],
        ['BK-20261013-021', 'Dimas Putra', 'Kawah Putih', 100000, 'selesai'],
    ];
    $grafik = collect(['Sab', 'Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum'])->zip([8, 11, 9, 14, 10, 13, 12.5])
        ->map(fn ($r) => ['label' => $r[0], 'total' => $r[1] * 1000000])->all();
    $populer = [['Kawah Putih', 'Bandung', 412], ['Bromo', 'Jawa Timur', 268], ['Raja Ampat', 'Papua Barat', 131]];
@endphp

@section('content')
<div class="grid grid-cols-4 gap-4 mb-6">
    @foreach ([['Total Destinasi', '12', '+1 bulan ini'], ['Total Booking', '1.248', '+8,2% dari bulan lalu'], ['Pembayaran Menunggu', '23', 'Perlu dicek'], ['Pendapatan Hari Ini', 'Rp 12.500.000', 'Per ' . now()->translatedFormat('d M Y')]] as [$l, $v, $n])
        <div class="bg-white rounded-2xl border p-5">
            <p class="text-sm text-gray-500">{{ $l }}</p><p class="text-2xl font-bold mt-2">{{ $v }}</p><p class="text-xs text-primary mt-1">{{ $n }}</p>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-3 gap-4">
    <div class="col-span-2 bg-white rounded-2xl border p-5">
        <div class="flex justify-between mb-3"><h2 class="font-semibold">Booking Terbaru</h2><a href="{{ url('/admin/booking') }}" class="text-sm text-primary">Lihat semua ›</a></div>
        <table class="w-full text-sm">
            <thead class="text-xs text-gray-400 text-left"><tr><th class="py-2">KODE</th><th>PENGUNJUNG</th><th>DESTINASI</th><th>TOTAL</th><th>STATUS</th></tr></thead>
            <tbody>
            @foreach ($terbaru as [$kode, $nama, $dest, $total, $status])
                <tr class="border-t"><td class="py-3 font-medium">{{ $kode }}</td><td>{{ $nama }}</td><td>{{ $dest }}</td>
                    <td>Rp {{ number_format($total, 0, ',', '.') }}</td><td>@include('partials.badge', ['s' => $status])</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border p-5">
            <div class="flex justify-between mb-3"><h2 class="font-semibold">Pendapatan 7 Hari</h2><a href="{{ url('/admin/laporan') }}" class="text-sm text-primary">Lihat laporan ›</a></div>
            @include('partials.grafik', ['grafik' => $grafik])
        </div>
        <div class="bg-white rounded-2xl border p-5">
            <h2 class="font-semibold mb-3">Destinasi Terpopuler</h2>
            @foreach ($populer as [$n, $l, $c])
                <div class="flex items-center gap-3 py-2">
                    <img src="https://placehold.co/80x80/E8F5E9/2E7D32?text={{ urlencode(substr($n, 0, 2)) }}" class="w-10 h-10 rounded-lg object-cover">
                    <div class="flex-1"><p class="text-sm font-medium">{{ $n }}</p><p class="text-xs text-gray-400">{{ $l }}</p></div>
                    <b class="text-sm">{{ $c }}</b>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
