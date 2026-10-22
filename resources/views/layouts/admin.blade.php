@extends('layouts.master')

@section('body')
<div class="flex min-h-screen">
    <aside class="w-64 bg-white border-r flex flex-col fixed inset-y-0">
        <div class="flex items-center gap-3 px-6 h-20">
            <div class="w-10 h-10 rounded-xl bg-primary text-white grid place-items-center font-bold">J</div>
            <div><p class="font-bold leading-4">JelajahKu</p><p class="text-xs text-gray-400">Admin Panel</p></div>
        </div>
        <nav class="px-4 space-y-1 flex-1">
            @foreach ([['Dashboard', 'admin', true], ['Destinasi', 'admin/destinasi', false], ['Booking', 'admin/booking', false], ['Laporan', 'admin/laporan', false]] as [$l, $p, $exact])
                @php $aktif = $exact ? request()->is('admin') : request()->is($p . '*'); @endphp
                <a href="{{ url($p) }}" class="block px-4 py-3 rounded-xl text-sm {{ $aktif ? 'bg-soft text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">{{ $l }}</a>
            @endforeach
        </nav>
        <div class="p-4 border-t flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary text-white grid place-items-center text-sm font-semibold">AD</div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold">Admin</p>
                <p class="text-xs text-gray-400 truncate">admin@jelajahku.id</p>
            </div>
            <form method="POST" action="{{ url('/logout') }}">@csrf
                <button class="text-xs text-red-500 hover:underline">Keluar</button>
            </form>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-8">
        <div class="flex items-start justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold">@yield('heading')</h1>
                <p class="text-sm text-gray-500">@yield('sub')</p>
            </div>
            @yield('actions')
        </div>
        @include('partials.flash')
        @yield('content')
    </main>
</div>
@endsection
