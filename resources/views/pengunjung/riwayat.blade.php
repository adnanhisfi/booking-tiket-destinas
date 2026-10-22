@extends('layouts.app')
@section('title', 'Riwayat Booking')

@php
    $tab = request('tab', 'aktif');
    $semua = [
        ['Kawah Putih', '15 Okt 2026 · 2 Dewasa', 'Aktif', 50000],
        ['Raja Ampat', '10 Sep 2026 · 2 Dewasa', 'Selesai', 1500000],
    ];
@endphp

@section('content')
<div class="p-4">
    <h1 class="text-center font-semibold mb-3">Riwayat Booking</h1>
    <div class="grid grid-cols-2 border rounded-lg overflow-hidden text-sm mb-4">
        <a href="{{ url('/riwayat?tab=aktif') }}" class="text-center py-2 {{ $tab === 'aktif' ? 'bg-primary text-white' : 'text-gray-500' }}">Aktif</a>
        <a href="{{ url('/riwayat?tab=selesai') }}" class="text-center py-2 {{ $tab === 'selesai' ? 'bg-primary text-white' : 'text-gray-500' }}">Selesai</a>
    </div>
    <div class="space-y-3">
        @foreach ($semua as [$nama, $info, $status, $total])
            @continue(strtolower($status) !== $tab)
            <a href="{{ url('/e-ticket') }}" class="flex gap-3 border rounded-2xl p-2">
                <img src="https://placehold.co/200x200/E8F5E9/2E7D32?text={{ urlencode(substr($nama, 0, 2)) }}" class="w-20 h-20 rounded-xl object-cover">
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <p class="font-semibold text-sm">{{ $nama }}</p>
                        <span class="text-[10px] px-2 py-0.5 rounded-full border {{ $status === 'Selesai' ? 'text-gray-500' : 'text-primary border-primary' }}">{{ $status }}</span>
                    </div>
                    <p class="text-[11px] text-gray-500">{{ $info }}</p>
                    <p class="text-sm font-semibold text-right mt-2">Rp {{ number_format($total, 0, ',', '.') }}</p>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection
