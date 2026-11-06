@extends('layouts.app')
@section('title', 'Booking Tiket - JelajahKu')
@section('hideNav', '1')

@php
    $user = auth()->user();
    $qtyInit = $ticketTypes->mapWithKeys(fn ($t) => [(string) $t->id => 0]);
    $priceMap = $ticketTypes->mapWithKeys(fn ($t) => [(string) $t->id => (float) $t->price]);
    $remainingMap = $availability->mapWithKeys(fn ($value, $key) => [(string) $key => (int) $value]);
    $img = $destination->thumbnail
        ? asset('storage/'.$destination->thumbnail)
        : 'https://placehold.co/200x200/E8F5E9/2E7D32?text='.urlencode($destination->name);
@endphp

@section('content')
<form method="POST" action="{{ route('booking.store', $destination->slug) }}" class="p-4 min-h-screen flex flex-col"
      x-data="{
          qty: {{ Js::from($qtyInit->all()) }},
          prices: {{ Js::from($priceMap->all()) }},
          remaining: {{ Js::from($remainingMap->all()) }},
          get total() {
              return Object.keys(this.prices).reduce((sum, id) => sum + (Number(this.qty[id]) || 0) * Number(this.prices[id]), 0);
          },
          rp(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); },
          dec(id) { this.qty[id] = Math.max(0, (Number(this.qty[id]) || 0) - 1); },
          inc(id) {
              const max = Number(this.remaining[id] || 0);
              if ((Number(this.qty[id]) || 0) < max) this.qty[id] = (Number(this.qty[id]) || 0) + 1;
          }
      }">
    @csrf
    <input type="hidden" name="destination_id" value="{{ $destination->id }}">

    <div class="flex items-center mb-4">
        <a href="{{ route('katalog.detail', $destination->slug) }}" class="text-xl">‹</a>
        <h1 class="flex-1 text-center font-semibold">Booking Tiket</h1>
    </div>

    <div class="flex gap-3 border rounded-2xl p-2 mb-4">
        <img src="{{ $img }}" class="w-16 h-16 rounded-xl object-cover" alt="{{ $destination->name }}">
        <div class="min-w-0">
            <p class="font-semibold text-sm truncate">{{ $destination->name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ $destination->location }}</p>
            <p class="text-xs font-semibold text-primary">
                @if ($ticketTypes->isNotEmpty())
                    Mulai Rp {{ number_format((float) $ticketTypes->min('price'), 0, ',', '.') }} /orang
                @endif
            </p>
        </div>
    </div>

    <label class="text-sm font-semibold">Tanggal Kunjungan</label>
    <input type="date" name="visit_date" value="{{ $visitDate->toDateString() }}"
           min="{{ today()->toDateString() }}" max="{{ $maxDate->toDateString() }}"
           onchange="window.location='{{ route('booking.create', $destination->slug) }}?tanggal='+this.value"
           class="w-full border rounded-xl px-3 py-3 text-sm mt-1 mb-4">
    <p class="text-[11px] text-gray-400 -mt-3 mb-4">Pilih tanggal untuk melihat sisa kuota terbaru.</p>

    <p class="text-sm font-semibold mb-2">Jumlah Tiket</p>

    @forelse ($ticketTypes as $type)
        @php $sisa = (int) ($availability[$type->id] ?? 0); @endphp
        <div class="border rounded-xl p-3 flex items-center justify-between mb-2">
            <div class="min-w-0">
                <p class="text-sm">{{ $type->name }}</p>
                <p class="text-xs text-gray-400">Rp {{ number_format((float) $type->price, 0, ',', '.') }}</p>
                <p class="text-[10px] {{ $sisa > 0 ? 'text-primary' : 'text-red-500' }}">
                    {{ $sisa > 0 ? 'Sisa '.$sisa.' tiket' : 'Kuota habis untuk tanggal ini' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="dec({{ $type->id }})" class="w-7 h-7 border rounded disabled:opacity-40" :disabled="qty[{{ $type->id }}] <= 0">-</button>
                <span x-text="qty[{{ $type->id }}]" class="w-4 text-center">0</span>
                <button type="button" @click="inc({{ $type->id }})" class="w-7 h-7 border rounded disabled:opacity-40" :disabled="qty[{{ $type->id }}] >= {{ $sisa }}">+</button>
                <input type="hidden" name="items[{{ $type->id }}][ticket_type_id]" value="{{ $type->id }}">
                <input type="hidden" name="items[{{ $type->id }}][quantity]" :value="qty[{{ $type->id }}]">
            </div>
        </div>
    @empty
        <div class="border rounded-xl p-4 text-sm text-gray-500">
            Belum ada jenis tiket yang aktif untuk destinasi ini.
        </div>
    @endforelse

    <label class="text-sm font-semibold mt-3">Data Pengunjung</label>
    <input name="customer_name" value="{{ old('customer_name', $user?->name) }}" placeholder="Nama lengkap pengunjung"
           class="w-full border rounded-xl px-3 py-3 text-sm mt-1">
    <input name="customer_phone" value="{{ old('customer_phone', $user?->phone) }}" placeholder="Nomor HP"
           class="w-full border rounded-xl px-3 py-3 text-sm mt-2 mb-4">

    <label class="text-sm font-semibold">Kode Voucher <span class="text-xs font-normal text-gray-400">(opsional)</span></label>
    <input name="voucher_code" value="{{ old('voucher_code') }}" placeholder="Contoh: JELAJAH10"
           class="w-full border rounded-xl px-3 py-3 text-sm mt-1 mb-4 uppercase">

    <div class="mt-auto pt-6">
        <div class="flex justify-between border-t pt-3 mb-3">
            <span class="font-semibold">Total</span>
            <span class="font-bold text-primary" x-text="rp(total)">Rp 0</span>
        </div>
        <button class="w-full bg-primary text-white rounded-xl py-3 text-sm font-semibold disabled:opacity-50"
                :disabled="total <= 0">Lanjutkan ke Pembayaran</button>
    </div>
</form>
@endsection
