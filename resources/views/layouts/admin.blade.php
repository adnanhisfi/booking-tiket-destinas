@extends('layouts.master')

@section('body')
@php
    $admin = auth()->user();
    $nav = [
        ['Dashboard', 'admin', true],
        ['Destinasi', 'admin/destinasi', false],
        ['Jenis Tiket', 'admin/tiket', false],
        ['Kuota', 'admin/kuota', false],
        ['Booking', 'admin/booking', false],
        ['Pembayaran', 'admin/pembayaran', false],
        ['Laporan', 'admin/laporan', false],
        ['Scan Tiket', 'admin/scan', false],
    ];
@endphp
<div x-data="{ sidebarOpen: false }" class="min-h-screen bg-gray-100">
    <a href="#admin-main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-primary focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Lewati ke konten utama
    </a>

    <div x-cloak x-show="sidebarOpen" x-transition.opacity
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
         aria-hidden="true"></div>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 flex w-64 transform flex-col border-r bg-white transition-transform duration-200 lg:translate-x-0"
           aria-label="Navigasi admin">
        <div class="flex h-20 shrink-0 items-center justify-between gap-3 px-6">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-primary font-bold text-white">J</div>
                <div><p class="font-bold leading-4">JelajahKu</p><p class="text-xs text-gray-400">Admin Panel</p></div>
            </div>
            <button type="button" @click="sidebarOpen = false"
                    class="grid h-9 w-9 place-items-center rounded-lg text-gray-400 hover:bg-gray-100 lg:hidden"
                    aria-label="Tutup menu admin">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <nav class="flex-1 space-y-1 overflow-y-auto px-4">
            @foreach ($nav as [$label, $path, $exact])
                @php $aktif = $exact ? request()->is('admin') : request()->is($path . '*'); @endphp
                <a href="{{ url($path) }}" @if($aktif) aria-current="page" @endif
                   class="block rounded-xl px-4 py-3 text-sm {{ $aktif ? 'bg-soft font-semibold text-primary' : 'text-gray-500 hover:bg-gray-50' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="border-t p-4">
            <a href="{{ url('/') }}" class="mb-3 block text-xs text-primary hover:underline">← Lihat situs pengunjung</a>
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-full bg-primary text-sm font-semibold text-white">
                    {{ strtoupper(substr($admin?->name ?? 'AD', 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">{{ $admin?->name ?? 'Admin' }}</p>
                    <p class="truncate text-xs text-gray-400">{{ $admin?->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="text-xs text-red-500 hover:underline">Keluar</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="lg:ml-64">
        <header class="sticky top-0 z-30 flex items-center gap-3 border-b bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
            <button type="button" @click="sidebarOpen = true"
                    class="grid h-10 w-10 place-items-center rounded-xl border text-gray-600"
                    aria-label="Buka menu admin" :aria-expanded="sidebarOpen.toString()">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div>
                <p class="font-semibold leading-4">JelajahKu</p>
                <p class="text-[11px] text-gray-400">Admin Panel</p>
            </div>
        </header>

        <main id="admin-main" class="p-4 sm:p-6 lg:p-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold sm:text-2xl">@yield('heading')</h1>
                    <p class="text-sm text-gray-500">@yield('sub')</p>
                </div>
                <div class="flex flex-wrap gap-2">@yield('actions')</div>
            </div>
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
@endsection
