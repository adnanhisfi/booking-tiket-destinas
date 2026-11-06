@extends('layouts.app')
@section('title', 'E-Ticket - JelajahKu')
@section('hideNav', '1')

@push('styles')
<style>
    .qr svg { width: 100%; height: 100%; }
</style>
@endpush

@section('content')
<div class="p-4">
    <div class="flex items-center mb-4">
        <a href="{{ route('booking.show', $booking->code) }}" class="text-xl">‹</a>
        <h1 class="flex-1 text-center font-semibold">E-Ticket</h1>
    </div>

    <div class="rounded-2xl border bg-soft px-4 py-3 text-xs text-primary mb-4">
        Tunjukkan QR code di pintu masuk destinasi pada tanggal kunjungan. Setiap tiket hanya dapat dipakai satu kali.
    </div>

    <div class="space-y-4">
        @foreach ($bookingTickets as $ticket)
            <div class="border rounded-2xl overflow-hidden">
                <div class="bg-primary text-white px-4 py-2 flex justify-between text-sm">
                    <b>JelajahKu</b>
                    <span class="text-xs">E-Ticket</span>
                </div>
                <div class="p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold">{{ $booking->destination?->name }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->destination?->location }}</p>
                        </div>
                        @include('partials.badge', ['s' => $ticket->status])
                    </div>

                    <div class="flex justify-between text-sm mt-3">
                        <span>{{ $ticket->visit_date->translatedFormat('d F Y') }}</span>
                        <span>{{ $ticket->ticket_type_name }}</span>
                    </div>

                    <div class="my-4 grid place-items-center">
                        <div class="qr w-52 h-52">{!! $qrs[$ticket->id] !!}</div>
                        <p class="font-bold mt-2 text-sm">{{ $ticket->code }}</p>
                        <a href="{{ route('ticket.qr', [$booking->code, $ticket->id]) }}" download
                           class="mt-1 text-xs text-primary underline">Unduh QR (SVG)</a>
                    </div>

                    <div class="border border-primary/40 bg-soft rounded-lg p-3 text-xs text-primary">
                        Kode Booking: <b>{{ $booking->code }}</b><br>
                        Berlaku hanya pada tanggal kunjungan di atas.
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <a href="{{ route('booking.history') }}" class="mt-4 block text-center border rounded-xl py-3 text-sm text-gray-600">Kembali ke Riwayat</a>
</div>
@endsection
