@extends('layouts.app')
@section('title', 'Pembayaran')
@section('hideNav', '1')

@section('content')
<form method="POST" action="{{ url('/pembayaran') }}" class="p-4 min-h-screen flex flex-col" x-data="{ m: 'GoPay' }">
    @csrf
    <div class="flex items-center mb-4"><a href="{{ url('/checkout') }}" class="text-xl">‹</a><h1 class="flex-1 text-center font-semibold">Pembayaran</h1></div>
    <p class="text-sm font-semibold mb-2">Metode Pembayaran</p>
    <div class="space-y-2">
        @foreach (['GoPay', 'BCA Virtual Account', 'BRI Virtual Account', 'Mandiri Virtual Account', 'Kartu Kredit / Debit', 'QRIS'] as $x)
            <label class="flex items-center gap-3 border rounded-xl px-3 py-3 text-sm cursor-pointer" :class="m === '{{ $x }}' ? 'border-primary bg-soft' : ''">
                <input type="radio" name="metode" value="{{ $x }}" x-model="m" class="accent-green-700"> {{ $x }}
            </label>
        @endforeach
    </div>

    <div class="mt-auto pt-6">
        <div class="bg-gray-100 rounded-xl p-3 mb-3"><p class="text-[11px] text-gray-500">Total Pembayaran</p><p class="font-bold">Rp 50.000</p></div>
        <button class="w-full bg-primary text-white rounded-xl py-3 text-sm font-semibold">Bayar Sekarang</button>
    </div>
</form>
@endsection
