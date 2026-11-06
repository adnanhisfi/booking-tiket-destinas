@extends('layouts.app')
@section('title', 'Pembayaran - JelajahKu')
@section('hideNav', '1')

@php
    $methodLabel = config('payment.methods')[$payment->method] ?? strtoupper((string) $payment->method);
    $proofUrl = $payment->proof_path ? asset('storage/'.$payment->proof_path) : null;
    $redirectUrl = $instructions['redirect_url'] ?? ($payment->gateway_payload['redirect_url'] ?? null);
    $isBankTransfer = ($instructions['type'] ?? null) === 'bank_transfer';
    $deadline = $instructions['deadline'] ?? $booking->expires_at?->toIso8601String();
@endphp

@section('content')
<div class="p-4 min-h-screen flex flex-col">
    <div class="flex items-center mb-4">
        <a href="{{ route('booking.show', $booking->code) }}" class="text-xl">‹</a>
        <h1 class="flex-1 text-center font-semibold">Pembayaran</h1>
    </div>

    <div class="border rounded-2xl p-4 mb-4">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-[11px] text-gray-400">Total Pembayaran</p>
                <p class="text-xl font-bold text-primary">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
            </div>
            @include('partials.badge', ['s' => $payment->status])
        </div>
        <div class="mt-3 space-y-1 text-xs text-gray-500">
            <p>Kode Booking: <span class="font-medium text-gray-700">{{ $booking->code }}</span></p>
            <p>Referensi: <span class="font-medium text-gray-700">{{ $payment->transaction_ref }}</span></p>
            <p>Metode: <span class="font-medium text-gray-700">{{ $methodLabel }}</span></p>
            @if ($deadline)
                <p>Batas Waktu: <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($deadline)->translatedFormat('d M Y H:i') }}</span></p>
            @endif
        </div>
    </div>

    @if ($payment->status === \App\Models\Payment::STATUS_PAID)
        <div class="rounded-xl bg-soft text-primary text-sm px-4 py-3 mb-4">
            Pembayaran sudah diterima. E-ticket kamu sudah terbit.
        </div>
        <a href="{{ route('ticket.show', $booking->code) }}" class="block text-center bg-primary text-white rounded-xl py-3 text-sm font-semibold">Lihat E-Ticket</a>
    @elseif ($payment->status === \App\Models\Payment::STATUS_PENDING)
        @if ($isBankTransfer)
            <p class="text-sm font-semibold mb-2">Instruksi Transfer</p>
            <div class="border rounded-xl p-3 text-sm space-y-1 mb-4">
                <div class="flex justify-between"><span class="text-gray-500">Bank</span><span class="font-semibold">{{ $instructions['bank_name'] ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Nomor Rekening</span><span class="font-semibold">{{ $instructions['bank_account'] ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Atas Nama</span><span class="font-semibold">{{ $instructions['bank_holder'] ?? '-' }}</span></div>
                <div class="flex justify-between border-t pt-1"><span class="text-gray-500">Jumlah</span><span class="font-semibold text-primary">Rp {{ number_format((float) ($instructions['amount'] ?? $payment->amount), 0, ',', '.') }}</span></div>
            </div>
            <p class="text-[11px] text-gray-500 mb-4">
                Transfer sesuai nominal, lalu unggah bukti pembayaran. Admin akan memverifikasi pembayaranmu.
            </p>
        @elseif ($redirectUrl)
            <a href="{{ $redirectUrl }}" target="_blank" rel="noopener"
               class="block text-center bg-primary text-white rounded-xl py-3 text-sm font-semibold mb-4">Lanjutkan ke Halaman Pembayaran</a>
        @endif

        @if ($proofUrl)
            <div class="rounded-xl bg-amber-50 text-amber-700 text-sm px-4 py-3 mb-3">
                Bukti pembayaran sudah diunggah. Menunggu verifikasi admin.
            </div>
            <a href="{{ $proofUrl }}" target="_blank" rel="noopener" class="block text-center border rounded-xl py-3 text-sm text-gray-600 mb-4">Lihat Bukti yang Diunggah</a>
        @else
            <p class="text-sm font-semibold mb-2">Unggah Bukti Transfer</p>
            <form method="POST" action="{{ route('payment.proof', $payment->transaction_ref) }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="file" name="proof" accept="image/jpeg,image/png,application/pdf" required
                       class="w-full border rounded-xl px-3 py-3 text-sm bg-gray-50">
                <p class="text-[11px] text-gray-400">Format JPG, PNG, atau PDF. Maksimal 4 MB.</p>
                <button class="w-full bg-primary text-white rounded-xl py-3 text-sm font-semibold">Kirim Bukti Pembayaran</button>
            </form>
        @endif
    @else
        <div class="rounded-xl bg-red-50 text-red-600 text-sm px-4 py-3 mb-4">
            Pembayaran ini berstatus {{ $payment->status }}. Silakan buat booking baru atau hubungi admin.
        </div>
    @endif

    <a href="{{ route('booking.show', $booking->code) }}" class="mt-3 block text-center border rounded-xl py-3 text-sm text-gray-600">Kembali ke Detail Booking</a>
</div>
@endsection
