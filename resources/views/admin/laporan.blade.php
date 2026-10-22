@extends('layouts.admin')
@section('title', 'Laporan')
@section('heading', 'Laporan')
@section('sub', 'Analisis pendapatan dan penjualan tiket')

@php
    $periode = (int) request('periode', 30);
    $grafik = collect(['M1' => 38, 'M2' => 52, 'M3' => 45, 'M4' => 51])->map(fn ($v, $k) => ['label' => $k, 'total' => $v * 1000000])->values()->all();
    $perDest = [['Raja Ampat', 131, 98250000], ['Taman Safari Bogor', 205, 41000000], ['Bromo', 268, 26800000], ['Candi Borobudur', 232, 11600000], ['Kawah Putih', 412, 8750000]];
@endphp

@section('actions')
    <div class="flex gap-2 text-sm">
        @foreach ([7 => '7 Hari', 30 => '30 Hari', 365 => 'Tahun Ini'] as $k => $l)
            <a href="{{ url('/admin/laporan?periode=' . $k) }}" class="px-4 py-2 rounded-xl border {{ $periode === $k ? 'bg-primary text-white border-primary' : 'text-gray-500' }}">{{ $l }}</a>
        @endforeach
        <button class="px-4 py-2 rounded-xl bg-primary text-white font-semibold">Unduh Laporan</button>
    </div>
@endsection

@section('content')
<div class="grid grid-cols-4 gap-4 mb-6">
    @foreach ([['Total Pendapatan', 'Rp 186,4 jt', '+12% dari periode lalu'], ['Total Booking', '1.248', '+8,2%'], ['Tiket Terjual', '3.915', '+9,6%'], ['Rata-rata / Booking', 'Rp 149 rb', '+3,1%']] as [$l, $v, $n])
        <div class="bg-white rounded-2xl border p-5"><p class="text-sm text-gray-500">{{ $l }}</p><p class="text-2xl font-bold mt-2">{{ $v }}</p><p class="text-xs text-primary mt-1">{{ $n }}</p></div>
    @endforeach
</div>
<div class="grid grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl border p-5"><h2 class="font-semibold mb-3">Pendapatan per Minggu</h2>@include('partials.grafik', ['grafik' => $grafik])</div>
    <div class="bg-white rounded-2xl border p-5">
        <h2 class="font-semibold mb-3">Pendapatan per Destinasi</h2>
        <table class="w-full text-sm">
            <thead class="text-xs text-gray-400 text-left"><tr><th class="py-2">DESTINASI</th><th>BOOKING</th><th class="text-right">PENDAPATAN</th></tr></thead>
            <tbody>@foreach ($perDest as [$n, $b, $p])
                <tr class="border-t"><td class="py-3">{{ $n }}</td><td>{{ $b }}</td><td class="text-right font-medium">Rp {{ number_format($p, 0, ',', '.') }}</td></tr>
            @endforeach</tbody>
        </table>
    </div>
</div>
@endsection
