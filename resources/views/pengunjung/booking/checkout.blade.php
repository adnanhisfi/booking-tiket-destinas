@extends('layouts.app')
@section('title', 'Checkout')
@section('hideNav', '1')

@section('content')
<div class="p-4 min-h-screen flex flex-col">
    <div class="flex items-center mb-4"><a href="{{ url('/booking/kawah-putih') }}" class="text-xl">‹</a><h1 class="flex-1 text-center font-semibold">Checkout</h1></div>

    <p class="text-sm font-semibold mb-2">Detail Pemesanan</p>
    <div class="flex gap-3 border rounded-2xl p-2 mb-4">
        <img src="https://placehold.co/200x200/E8F5E9/2E7D32?text=KP" class="w-16 h-16 rounded-xl object-cover">
        <div><p class="font-semibold text-sm">Kawah Putih</p><p class="text-xs text-gray-500">15 Okt 2026 · 2 Dewasa</p><p class="text-xs font-semibold text-primary">Rp 50.000</p></div>
    </div>

    <p class="text-sm font-semibold mb-2">Detail Harga</p>
    <div class="border rounded-xl p-3 text-sm space-y-1 mb-4">
        <div class="flex justify-between"><span>Tiket Dewasa (2)</span><span>Rp 50.000</span></div>
        <div class="flex justify-between font-semibold border-t pt-1"><span>Total</span><span class="text-primary">Rp 50.000</span></div>
    </div>

    <p class="text-sm font-semibold mb-2">Data Pengunjung</p>
    <div class="border rounded-xl p-3 text-sm flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-primary text-white grid place-items-center font-semibold">A</div>
        <div class="flex-1"><p class="font-semibold">Andi Pratama</p><p class="text-xs text-gray-500">andipratama@email.com</p><p class="text-xs text-gray-500">0812 3456 7890</p></div>
        <a href="{{ url('/profil') }}" class="text-xs text-primary">Edit</a>
    </div>

    <a href="{{ url('/pembayaran') }}" class="mt-auto block text-center bg-primary text-white rounded-xl py-3 text-sm font-semibold">Pilih Metode Pembayaran</a>
</div>
@endsection
