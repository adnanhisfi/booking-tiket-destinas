@extends('layouts.app')
@section('title', 'Status Pembayaran - JelajahKu')
@section('hideNav', '1')

@php
    $status = $payment?->status;
    $isPaid = $status === \App\Models\Payment::STATUS_PAID;
    $isPending = $status === \App\Models\Payment::STATUS_PENDING;
    $title = $isPaid ? 'Pembayaran Berhasil!' : ($isPending ? 'Menunggu Verifikasi' : 'Status Pembayaran');
    $description = $isPaid
        ? 'Pembayaran kamu sudah lunas. E-ticket sudah diterbitkan dan bisa langsung dipakai.'
        : ($isPending
            ? 'Bukti pembayaran sudah diterima. Admin akan memverifikasi dalam waktu singkat.'
            : 'Pembayaran belum berhasil. Silakan cek detail booking untuk informasi lebih lanjut.');
@endphp

@section('content')
<div class="p-6 min-h-screen flex flex-col items-center text-center pt-16">
    <div class="w-24 h-24 rounded-full {{ $isPaid ? 'bg-primary' : ($isPending ? 'bg-amber-400' : 'bg-gray-300') }} text-white grid place-items-center text-5xl">
        {{ $isPaid ? '✓' : ($isPending ? '…' : '!') }}
    </div>
    <h1 class="font-bold mt-4 {{ $isPaid ? 'text-primary' : ($isPending ? 'text-amber-600' : 'text-gray-600') }}">{{ $title }}</h1>
    <p class="text-xs text-gray-500 mt-1">{{ $description }}</p>

    <div class="w-full border rounded-xl p-4 text-left mt-6 space-y-3">
        <div class="flex justify-between text-sm">
            <div>
                <p class="text-[11px] text-gray-400">Kode Booking</p>
                <p class="font-bold">{{ $booking->code }}</p>
            </div>
            @include('partials.badge', ['s' => $booking->status])
        </div>
        <div>
            <p class="text-[11px] text-gray-400">Total Pembayaran</p>
            <p class="font-bold">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</p>
        </div>
        @if ($payment)
            <div>
                <p class="text-[11px] text-gray-400">Referensi Pembayaran</p>
                <p class="text-sm font-medium">{{ $payment->transaction_ref }}</p>
            </div>
        @endif
        <div>
            <p class="text-[11px] text-gray-400">Destinasi</p>
            <p class="text-sm font-medium">{{ $booking->destination?->name }}</p>
        </div>
    </div>

    <div class="w-full mt-auto space-y-2 pt-8">
        @if (in_array($booking->status, [\App\Models\Booking::STATUS_PAID, \App\Models\Booking::STATUS_COMPLETED], true))
            <a href="{{ route('ticket.show', $booking->code) }}" class="block bg-primary text-white rounded-xl py-3 text-sm font-semibold">Lihat E-Ticket</a>
        @endif
        <a href="{{ route('booking.show', $booking->code) }}" class="block border border-primary text-primary rounded-xl py-3 text-sm font-semibold">Detail Booking</a>
        <a href="{{ route('booking.history') }}" class="block border rounded-xl py-3 text-sm text-gray-600">Riwayat Booking</a>
    </div>
</div>
@endsection
