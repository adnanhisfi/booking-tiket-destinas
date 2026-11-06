@extends('layouts.app')
@section('title', 'Riwayat Booking - JelajahKu')

@php
    $img = fn ($b) => $b->destination?->thumbnail
        ? asset('storage/'.$b->destination->thumbnail)
        : 'https://placehold.co/200x200/E8F5E9/2E7D32?text='.urlencode($b->destination?->name ?? 'Wisata');
@endphp

@section('content')
<div class="p-4">
    <h1 class="text-center font-semibold mb-3">Riwayat Booking</h1>

    <div class="grid grid-cols-3 border rounded-lg overflow-hidden text-sm mb-4">
        @foreach (['aktif' => 'Aktif', 'selesai' => 'Selesai', 'batal' => 'Batal'] as $key => $label)
            <a href="{{ route('booking.history', ['tab' => $key]) }}"
               class="text-center py-2 {{ $tab === $key ? 'bg-primary text-white' : 'text-gray-500' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($bookings as $booking)
            <div class="flex gap-3 border rounded-2xl p-2">
                <img src="{{ $img($booking) }}" class="w-20 h-20 rounded-xl object-cover" alt="{{ $booking->destination?->name }}">
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start gap-2">
                        <p class="font-semibold text-sm truncate">{{ $booking->destination?->name }}</p>
                        @include('partials.badge', ['s' => $booking->status])
                    </div>
                    <p class="text-[11px] text-gray-500">
                        {{ $booking->visit_date->translatedFormat('d M Y') }} ·
                        {{ $booking->items->sum('quantity') }} tiket
                    </p>
                    <p class="text-[11px] text-gray-400">{{ $booking->code }}</p>
                    <div class="flex justify-between items-center mt-2">
                        <p class="text-sm font-semibold">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</p>
                        <div class="flex gap-2">
                            @if (in_array($booking->status, [\App\Models\Booking::STATUS_PAID, \App\Models\Booking::STATUS_COMPLETED], true))
                                <a href="{{ route('ticket.show', $booking->code) }}" class="text-[11px] px-2 py-1 rounded-full bg-primary text-white">E-Ticket</a>
                            @endif
                            <a href="{{ route('booking.show', $booking->code) }}" class="text-[11px] px-2 py-1 rounded-full border text-primary">Detail</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="border rounded-2xl p-8 text-center text-sm text-gray-500">
                Belum ada booking pada tab ini.
                <a href="{{ route('katalog.index') }}" class="block mt-3 text-primary font-semibold">Cari destinasi</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>
</div>
@endsection
