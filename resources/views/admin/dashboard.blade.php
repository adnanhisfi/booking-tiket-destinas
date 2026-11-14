@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('heading', 'Dashboard')
@section('sub', 'Ringkasan aktivitas JelajahKu hari ini')

@section('content')
<div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-sm text-gray-500">Total Destinasi</p>
        <p class="text-2xl font-bold mt-2">{{ number_format($totalDestinations, 0, ',', '.') }}</p>
        <p class="text-xs text-primary mt-1">Destinasi terdaftar</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-sm text-gray-500">Total Booking</p>
        <p class="text-2xl font-bold mt-2">{{ number_format($summary['totals']['bookings'], 0, ',', '.') }}</p>
        <p class="text-xs text-primary mt-1">{{ number_format($summary['today']['bookings'], 0, ',', '.') }} booking hari ini</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-sm text-gray-500">Pembayaran Menunggu</p>
        <p class="text-2xl font-bold mt-2">{{ number_format($summary['pending_payments'], 0, ',', '.') }}</p>
        <p class="text-xs {{ $summary['awaiting_verification'] > 0 ? 'text-amber-600' : 'text-primary' }} mt-1">
            {{ number_format($summary['awaiting_verification'], 0, ',', '.') }} bukti perlu diverifikasi
        </p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-sm text-gray-500">Pendapatan Hari Ini</p>
        <p class="text-2xl font-bold mt-2">Rp {{ number_format($summary['today']['revenue'], 0, ',', '.') }}</p>
        <p class="text-xs text-primary mt-1">{{ number_format($summary['today']['tickets_sold'], 0, ',', '.') }} tiket terjual hari ini</p>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
    <div class="bg-white rounded-2xl border p-5 xl:col-span-2">
        <div class="flex justify-between mb-3">
            <h2 class="font-semibold">Booking Terbaru</h2>
            <a href="{{ route('admin.bookings.index') }}" class="text-sm text-primary">Lihat semua ›</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead class="text-xs text-gray-400 text-left">
                    <tr><th class="py-2">KODE</th><th>PENGUNJUNG</th><th>DESTINASI</th><th>TOTAL</th><th>STATUS</th></tr>
                </thead>
                <tbody>
                    @forelse ($recent as $booking)
                        <tr class="border-t">
                            <td class="py-3 font-medium">
                                <a href="{{ route('admin.bookings.show', $booking->code) }}" class="text-primary">{{ $booking->code }}</a>
                            </td>
                            <td>{{ $booking->customer_name }}</td>
                            <td>{{ $booking->destination?->name ?? '-' }}</td>
                            <td>Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</td>
                            <td>@include('partials.badge', ['s' => $booking->status])</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-400">Belum ada booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-2xl border p-5">
            <div class="flex justify-between mb-3">
                <h2 class="font-semibold">Pendapatan 7 Hari</h2>
                <a href="{{ route('admin.reports.index', ['periode' => 7]) }}" class="text-sm text-primary">Laporan ›</a>
            </div>
            @include('partials.grafik', ['grafik' => $grafik])
        </div>

        <div class="bg-white rounded-2xl border p-5">
            <h2 class="font-semibold mb-3">Destinasi Terpopuler</h2>
            @forelse ($popular as $row)
                <div class="flex items-center gap-3 py-2 border-b last:border-0">
                    <div class="w-10 h-10 rounded-lg bg-soft text-primary grid place-items-center text-xs font-bold">
                        {{ strtoupper(substr($row['destination'], 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $row['destination'] }}</p>
                        <p class="text-xs text-gray-400">{{ $row['orders'] }} booking</p>
                    </div>
                    <b class="text-xs">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</b>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada penjualan.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
