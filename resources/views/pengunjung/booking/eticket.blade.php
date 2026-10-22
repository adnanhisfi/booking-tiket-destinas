@extends('layouts.app')
@section('title', 'E-Ticket')
@section('hideNav', '1')

@section('content')
<div class="p-4">
    <div class="flex items-center mb-4"><a href="{{ url('/riwayat') }}" class="text-xl">‹</a><h1 class="flex-1 text-center font-semibold">E-Ticket</h1></div>
    <div class="border rounded-2xl overflow-hidden">
        <div class="bg-primary text-white px-4 py-2 flex justify-between text-sm"><b>JelajahKu</b><span class="text-xs">E-Ticket</span></div>
        <div class="p-4">
            <p class="font-bold">Kawah Putih</p>
            <p class="text-xs text-gray-500">Ciwidey, Bandung</p>
            <div class="flex justify-between text-sm mt-2"><span>15 Oktober 2026</span><span>2 Dewasa</span></div>
            <div class="my-4 grid place-items-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=BK-20261015-001" class="w-52 h-52" alt="QR Code">
                <p class="font-bold mt-2">BK-20261015-001</p>
            </div>
            <div class="border border-primary/40 bg-soft rounded-lg p-3 text-xs text-primary">Tunjukkan QR code ini saat tiba di lokasi destinasi.</div>
        </div>
    </div>
</div>
@endsection
