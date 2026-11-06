@extends('layouts.app')
@section('title', 'Checkout - JelajahKu')
@section('hideNav', '1')

@php
    $pendingPayment = $booking->payments->firstWhere('status', \App\Models\Payment::STATUS_PENDING);
    $isPaid = in_array($booking->status, [\App\Models\Booking::STATUS_PAID, \App\Models\Booking::STATUS_COMPLETED], true);
    $isPending = $booking->status === \App\Models\Booking::STATUS_PENDING;
    $img = $booking->destination?->thumbnail
        ? asset('storage/'.$booking->destination->thumbnail)
        : 'https://placehold.co/200x200/E8F5E9/2E7D32?text='.urlencode($booking->destination?->name ?? 'Wisata');
@endphp

@section('content')
<div class="p-4 min-h-screen flex flex-col">
    <div class="flex items-center mb-4">
        <a href="{{ route('booking.history') }}" class="text-xl">‹</a>
        <h1 class="flex-1 text-center font-semibold">Checkout</h1>
    </div>

    <div class="flex items-center justify-between mb-3">
        <div>
            <p class="text-[11px] text-gray-400">Kode Booking</p>
            <p class="font-bold">{{ $booking->code }}</p>
        </div>
        @include('partials.badge', ['s' => $booking->status])
    </div>

    <p class="text-sm font-semibold mb-2">Detail Pemesanan</p>
    <div class="flex gap-3 border rounded-2xl p-2 mb-4">
        <img src="{{ $img }}" class="w-16 h-16 rounded-xl object-cover" alt="{{ $booking->destination?->name }}">
        <div class="min-w-0">
            <p class="font-semibold text-sm truncate">{{ $booking->destination?->name }}</p>
            <p class="text-xs text-gray-500">
                {{ $booking->visit_date->translatedFormat('d M Y') }} ·
                {{ $booking->items->sum('quantity') }} tiket
            </p>
            <p class="text-xs font-semibold text-primary">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</p>
        </div>
    </div>

    <p class="text-sm font-semibold mb-2">Detail Harga</p>
    <div class="border rounded-xl p-3 text-sm space-y-1 mb-4">
        @foreach ($booking->items as $item)
            <div class="flex justify-between">
                <span>{{ $item->ticket_type_name }} ({{ $item->quantity }})</span>
                <span>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</span>
            </div>
        @endforeach
        @if ((float) $booking->discount_amount > 0)
            <div class="flex justify-between text-primary">
                <span>Diskon {{ $booking->voucher_code ? '('.$booking->voucher_code.')' : '' }}</span>
                <span>- Rp {{ number_format((float) $booking->discount_amount, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="flex justify-between font-semibold border-t pt-1">
            <span>Total</span>
            <span class="text-primary">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <p class="text-sm font-semibold mb-2">Data Pengunjung</p>
    <div class="border rounded-xl p-3 text-sm flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-full bg-primary text-white grid place-items-center font-semibold">
            {{ strtoupper(substr($booking->customer_name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold">{{ $booking->customer_name }}</p>
            <p class="text-xs text-gray-500">{{ $booking->user?->email ?? '-' }}</p>
            <p class="text-xs text-gray-500">{{ $booking->customer_phone }}</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="text-xs text-primary">Edit</a>
    </div>

    @if ($isPending)
        <div class="rounded-xl bg-amber-50 text-amber-700 text-xs px-4 py-3 mb-4">
            Selesaikan pembayaran sebelum {{ $booking->expires_at?->translatedFormat('d M Y H:i') }}.
            Setelah batas waktu, booking otomatis kedaluwarsa dan kuota dikembalikan.
        </div>

        @if ($pendingPayment)
            <a href="{{ route('payment.show', $pendingPayment->transaction_ref) }}"
               class="block text-center bg-primary text-white rounded-xl py-3 text-sm font-semibold">
                Lanjutkan Pembayaran
            </a>
        @else
            <p class="text-sm font-semibold mb-2">Metode Pembayaran</p>
            <form method="POST" action="{{ route('payment.store', $booking->code) }}" class="space-y-2">
                @csrf
                @foreach ($methodOptions as $key => $label)
                    <label class="flex items-center gap-3 border rounded-xl px-3 py-3 text-sm cursor-pointer">
                        <input type="radio" name="method" value="{{ $key }}" @checked($loop->first) class="accent-green-700">
                        {{ $label }}
                    </label>
                @endforeach
                <button class="w-full bg-primary text-white rounded-xl py-3 text-sm font-semibold">Pilih Metode Pembayaran</button>
            </form>
        @endif

        <form method="POST" action="{{ route('booking.cancel', $booking->code) }}" class="mt-3"
              onsubmit="return confirm('Batalkan booking ini? Kuota tiket akan dikembalikan.')">
            @csrf
            <button class="w-full border border-red-200 text-red-500 rounded-xl py-3 text-sm font-semibold">Batalkan Booking</button>
        </form>
    @elseif ($isPaid)
        <div class="rounded-xl bg-soft text-primary text-xs px-4 py-3 mb-4">
            Pembayaran sudah lunas. E-ticket kamu sudah terbit dan siap dipakai.
        </div>
        <a href="{{ route('ticket.show', $booking->code) }}"
           class="block text-center bg-primary text-white rounded-xl py-3 text-sm font-semibold">Lihat E-Ticket</a>
    @else
        <div class="rounded-xl bg-gray-100 text-gray-600 text-xs px-4 py-3 mb-4">
            Booking ini sudah {{ $booking->status === 'expired' ? 'kedaluwarsa' : 'dibatalkan' }}.
            Silakan buat booking baru jika masih ingin berkunjung.
        </div>
        <a href="{{ route('katalog.index') }}" class="block text-center bg-primary text-white rounded-xl py-3 text-sm font-semibold">Cari Destinasi Lain</a>
    @endif

    <a href="{{ route('booking.history') }}" class="mt-3 block text-center border rounded-xl py-3 text-sm text-gray-600">Kembali ke Riwayat</a>
</div>
@endsection
