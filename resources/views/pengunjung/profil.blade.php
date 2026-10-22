@extends('layouts.app')
@section('title', 'Profil')

@section('content')
<div class="p-4">
    <h1 class="text-center font-semibold mb-4">Profil</h1>
    <div class="flex items-center gap-3 mb-5">
        <div class="w-14 h-14 rounded-full bg-primary text-white grid place-items-center font-bold text-lg">A</div>
        <div><p class="font-semibold">Andi Pratama</p><p class="text-xs text-gray-500">andipratama@email.com</p></div>
    </div>
    <div class="space-y-2">
        @foreach (['Data Pribadi', 'Metode Pembayaran', 'Notifikasi', 'Bantuan', 'Pengaturan'] as $m)
            <a href="#" class="flex justify-between border rounded-xl px-4 py-3 text-sm"><span>{{ $m }}</span><span class="text-gray-300">›</span></a>
        @endforeach
    </div>
    <form method="POST" action="{{ url('/logout') }}" class="mt-5">@csrf
        <button class="w-full border border-primary text-primary rounded-xl py-3 text-sm font-semibold">Keluar</button>
    </form>
</div>
@endsection
