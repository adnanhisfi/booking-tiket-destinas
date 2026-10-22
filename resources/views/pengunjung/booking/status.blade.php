@extends('layouts.app')
@section('title', 'Status Pembayaran')
@section('hideNav', '1')

@section('content')
<div class="p-6 min-h-screen flex flex-col items-center text-center pt-20">
    <div class="w-24 h-24 rounded-full bg-primary text-white grid place-items-center text-5xl">✓</div>
    <h1 class="text-primary font-bold mt-4">Pembayaran Berhasil!</h1>
    <p class="text-xs text-gray-500 mt-1">Tiket kamu sudah berhasil dipesan.<br>Silakan cek e-ticket pada halaman berikutnya atau di riwayat booking.</p>

    <div class="w-full border rounded-xl p-4 text-left mt-6 space-y-3">
        <div><p class="text-[11px] text-gray-400">Kode Booking</p><p class="font-bold">BK-20261015-001</p></div>
        <div><p class="text-[11px] text-gray-400">Total Pembayaran</p><p class="font-bold">Rp 50.000</p></div>
    </div>

    <div class="w-full mt-auto space-y-2 pt-8">
        <a href="{{ url('/e-ticket') }}" class="block bg-primary text-white rounded-xl py-3 text-sm font-semibold">Lihat E-Ticket</a>
        <a href="{{ url('/riwayat') }}" class="block border border-primary text-primary rounded-xl py-3 text-sm font-semibold">Lihat Riwayat Booking</a>
    </div>
</div>
@endsection
