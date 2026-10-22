@extends('layouts.admin')
@section('title', 'Kelola Booking')
@section('heading', 'Kelola Booking')
@section('sub', 'Pantau dan kelola seluruh pemesanan tiket')

@php
    $rows = [
        ['BK-20261015-001', 'Andi Pratama', 'Kawah Putih', '15 Okt 2026', '2 Dewasa', 50000, 'menunggu'],
        ['BK-20261015-002', 'Maya Anggraini', 'Bromo', '15 Okt 2026', '2 Dewasa', 100000, 'menunggu'],
        ['BK-20261014-009', 'Budi Santoso', 'Bromo', '14 Okt 2026', '3 Dewasa', 150000, 'aktif'],
        ['BK-20261014-007', 'Rina Lestari', 'Taman Safari Bogor', '14 Okt 2026', '2 Dewasa, 1 Anak', 375000, 'aktif'],
        ['BK-20261013-021', 'Dimas Putra', 'Kawah Putih', '13 Okt 2026', '4 Dewasa', 100000, 'selesai'],
        ['BK-20260910-014', 'Sari Dewi', 'Raja Ampat', '10 Sep 2026', '2 Dewasa', 1500000, 'selesai'],
        ['BK-20260908-003', 'Hendra Wijaya', 'Candi Borobudur', '08 Sep 2026', '2 Dewasa', 150000, 'selesai'],
    ];
    $filter = request('status');
@endphp

@section('actions')<button class="border rounded-xl px-5 py-2.5 text-sm text-gray-600">Ekspor Data</button>@endsection

@section('content')
<div class="bg-white rounded-2xl border p-5">
    <div class="flex items-center gap-3 mb-4 text-sm">
        <form class="mr-4"><input name="q" placeholder="Cari kode booking..." class="border rounded-xl px-4 py-2 w-64"></form>
        @foreach ([null => 'Semua (1.248)', 'menunggu' => 'Menunggu (23)', 'aktif' => 'Aktif', 'selesai' => 'Selesai'] as $k => $l)
            <a href="{{ url('/admin/booking' . ($k ? '?status=' . $k : '')) }}" class="px-4 py-1.5 rounded-full border {{ $filter == $k ? 'bg-primary text-white border-primary' : 'text-gray-500' }}">{{ $l }}</a>
        @endforeach
    </div>
    <table class="w-full text-sm">
        <thead class="text-xs text-gray-400 text-left"><tr><th class="py-2">KODE</th><th>PENGUNJUNG</th><th>DESTINASI</th><th>TANGGAL</th><th>TIKET</th><th>TOTAL</th><th>STATUS</th></tr></thead>
        <tbody>
        @foreach ($rows as [$kode, $nama, $dest, $tgl, $tiket, $total, $status])
            @continue($filter && $filter !== $status)
            <tr class="border-t">
                <td class="py-3 font-medium">{{ $kode }}</td><td>{{ $nama }}</td><td>{{ $dest }}</td><td>{{ $tgl }}</td><td>{{ $tiket }}</td>
                <td>Rp {{ number_format($total, 0, ',', '.') }}</td>
                <td>
                    <form method="POST" action="{{ url('/admin/booking/' . $kode) }}">@csrf @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="text-xs border rounded-full px-2 py-1">
                            @foreach (['menunggu', 'aktif', 'selesai'] as $s)<option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>@endforeach
                        </select>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="flex justify-between items-center mt-4 text-sm text-gray-500">
        <span>Menampilkan 7 dari 1.248 booking</span>
        <div class="flex gap-1"><span class="px-3 py-1 rounded bg-primary text-white">1</span><span class="px-3 py-1 rounded border">2</span><span class="px-3 py-1 rounded border">3</span></div>
    </div>
</div>
@endsection
