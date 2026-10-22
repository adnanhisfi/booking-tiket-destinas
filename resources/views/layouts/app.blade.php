@extends('layouts.master')

@section('body')
<div class="max-w-md mx-auto min-h-screen bg-white shadow-lg relative @sectionMissing('hideNav') pb-20 @endif">
    @include('partials.flash')
    @yield('content')

    @sectionMissing('hideNav')
    <nav class="fixed bottom-0 inset-x-0 max-w-md mx-auto bg-white border-t grid grid-cols-4 text-[11px] z-50">
        @php
            $menu = [
                ['Beranda', '/', 'M3 12l9-9 9 9M5 10v10h14V10'],
                ['Booking', '/destinasi', 'M4 6h16M4 12h16M4 18h16'],
                ['Riwayat', '/riwayat', 'M12 8v5l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Profil', '/profil', 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0'],
            ];
        @endphp
        @foreach ($menu as [$label, $path, $ikon])
            @php $aktif = $path === '/' ? request()->is('/') : request()->is(ltrim($path, '/') . '*'); @endphp
            <a href="{{ url($path) }}" class="flex flex-col items-center py-2 gap-1 {{ $aktif ? 'text-primary font-semibold' : 'text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg>
                {{ $label }}
            </a>
        @endforeach
    </nav>
    @endif
</div>
@endsection
