@extends('layouts.app')
@section('title', 'Booking Tiket')
@section('hideNav', '1')

@section('content')
<form method="POST" action="{{ url('/booking/kawah-putih') }}" class="p-4 min-h-screen flex flex-col"
      x-data="{ d: 2, a: 0, hd: 25000, ha: 15000,
                get total() { return this.d * this.hd + this.a * this.ha },
                rp(n) { return 'Rp ' + n.toLocaleString('id-ID') } }">
    @csrf
    <div class="flex items-center mb-4"><a href="{{ url('/destinasi/kawah-putih') }}" class="text-xl">‹</a><h1 class="flex-1 text-center font-semibold">Booking Tiket</h1></div>

    <div class="flex gap-3 border rounded-2xl p-2 mb-4">
        <img src="https://placehold.co/200x200/E8F5E9/2E7D32?text=KP" class="w-16 h-16 rounded-xl object-cover">
        <div><p class="font-semibold text-sm">Kawah Putih</p><p class="text-xs text-gray-500">Ciwidey, Bandung</p><p class="text-xs font-semibold text-primary">Rp 25.000 /orang</p></div>
    </div>

    <label class="text-sm font-semibold">Tanggal Kunjungan</label>
    <input type="date" name="tanggal" value="{{ now()->toDateString() }}" min="{{ now()->toDateString() }}" class="w-full border rounded-xl px-3 py-3 text-sm mt-1 mb-4">

    <p class="text-sm font-semibold mb-2">Jumlah Tiket</p>
    <div class="border rounded-xl p-3 flex items-center justify-between mb-2">
        <div><p class="text-sm">Dewasa</p><p class="text-xs text-gray-400">Rp 25.000</p></div>
        <div class="flex items-center gap-3">
            <button type="button" @click="d = Math.max(1, d - 1)" class="w-7 h-7 border rounded">-</button>
            <span x-text="d" class="w-4 text-center"></span><input type="hidden" name="dewasa" :value="d">
            <button type="button" @click="d++" class="w-7 h-7 border rounded">+</button>
        </div>
    </div>
    <div class="border rounded-xl p-3 flex items-center justify-between">
        <div><p class="text-sm">Anak (3-12 tahun)</p><p class="text-xs text-gray-400">Rp 15.000</p></div>
        <div class="flex items-center gap-3">
            <button type="button" @click="a = Math.max(0, a - 1)" class="w-7 h-7 border rounded">-</button>
            <span x-text="a" class="w-4 text-center"></span><input type="hidden" name="anak" :value="a">
            <button type="button" @click="a++" class="w-7 h-7 border rounded">+</button>
        </div>
    </div>

    <div class="mt-auto pt-6">
        <div class="flex justify-between border-t pt-3 mb-3"><span class="font-semibold">Total</span><span class="font-bold text-primary" x-text="rp(total)"></span></div>
        <button class="w-full bg-primary text-white rounded-xl py-3 text-sm font-semibold">Lanjutkan</button>
    </div>
</form>
@endsection
