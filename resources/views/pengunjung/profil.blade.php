@extends('layouts.app')
@section('title', 'Profil - JelajahKu')

@section('content')
<div class="p-4">
    <h1 class="text-center font-semibold mb-4">Profil</h1>

    <div class="flex items-center gap-3 mb-5">
        <div class="w-14 h-14 rounded-full bg-primary text-white grid place-items-center font-bold text-lg">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="min-w-0">
            <p class="font-semibold truncate">{{ $user->name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
            @if ($user->phone)
                <p class="text-xs text-gray-500">{{ $user->phone }}</p>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" class="space-y-3">
        @csrf
        @method('PUT')

        <div>
            <label class="text-xs font-medium">Nama Lengkap</label>
            <input name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full border rounded-xl px-4 py-3 text-sm mt-1 bg-gray-50">
        </div>

        <div>
            <label class="text-xs font-medium">Email</label>
            <input value="{{ $user->email }}" disabled
                   class="w-full border rounded-xl px-4 py-3 text-sm mt-1 bg-gray-100 text-gray-400">
        </div>

        <div>
            <label class="text-xs font-medium">Nomor HP</label>
            <input name="phone" value="{{ old('phone', $user->phone) }}" required
                   class="w-full border rounded-xl px-4 py-3 text-sm mt-1 bg-gray-50">
        </div>

        <div class="border-t pt-3">
            <p class="text-xs font-semibold mb-2">Ganti Password <span class="font-normal text-gray-400">(opsional)</span></p>
            <input name="password" type="password" placeholder="Password baru"
                   class="w-full border rounded-xl px-4 py-3 text-sm bg-gray-50">
            <input name="password_confirmation" type="password" placeholder="Ulangi password baru"
                   class="w-full border rounded-xl px-4 py-3 text-sm bg-gray-50 mt-2">
        </div>

        <button class="w-full bg-primary text-white rounded-xl py-3 text-sm font-semibold">Simpan Perubahan</button>
    </form>

    <div class="mt-5 space-y-2">
        <a href="{{ route('booking.history') }}" class="flex justify-between border rounded-xl px-4 py-3 text-sm"><span>Riwayat Booking</span><span class="text-gray-300">›</span></a>
        <a href="{{ route('katalog.index') }}" class="flex justify-between border rounded-xl px-4 py-3 text-sm"><span>Cari Destinasi</span><span class="text-gray-300">›</span></a>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-5">@csrf
        <button class="w-full border border-primary text-primary rounded-xl py-3 text-sm font-semibold">Keluar</button>
    </form>
</div>
@endsection
