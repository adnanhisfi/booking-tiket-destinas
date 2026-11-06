@extends('layouts.admin')
@section('title', 'Kelola Booking')
@section('heading', 'Kelola Booking')
@section('sub', 'Pantau dan kelola seluruh pemesanan tiket')

@php
    $filterParams = request()->only(['q', 'destination_id', 'from', 'to']);
    $statusLabels = [
        '' => 'Semua',
        'pending' => 'Menunggu',
        'paid' => 'Lunas',
        'completed' => 'Selesai',
        'expired' => 'Kedaluwarsa',
        'cancelled' => 'Dibatalkan',
    ];
@endphp

@section('actions')
    <a href="{{ route('admin.bookings.export', array_filter(array_merge($filterParams, ['status' => request('status')]))) }}"
       class="border rounded-xl px-5 py-2.5 text-sm text-gray-600">Ekspor Data</a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border p-5">
    <div class="flex flex-wrap items-center gap-2 mb-4 text-sm">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-wrap gap-2 mr-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari kode / nama / HP..." class="border rounded-xl px-4 py-2 w-60">
            <select name="destination_id" class="border rounded-xl px-3 py-2 text-sm">
                <option value="">Semua destinasi</option>
                @foreach ($destinations as $destination)
                    <option value="{{ $destination->id }}" @selected((int) request('destination_id') === $destination->id)>{{ $destination->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="border rounded-xl px-3 py-2 text-sm">
            <input type="date" name="to" value="{{ request('to') }}" class="border rounded-xl px-3 py-2 text-sm">
            <button class="border rounded-xl px-4 py-2 text-sm text-gray-600">Cari</button>
        </form>

        @foreach ($statusLabels as $key => $label)
            @php
                $count = $key === '' ? $bookings->total() : (int) $counts->get($key, 0);
                $params = array_filter(array_merge($filterParams, ['status' => $key]));
            @endphp
            <a href="{{ route('admin.bookings.index', $params) }}"
               class="px-4 py-1.5 rounded-full border {{ request('status', '') === $key ? 'bg-primary text-white border-primary' : 'text-gray-500' }}">
                {{ $label }} ({{ $count }})
            </a>
        @endforeach
    </div>

    <table class="w-full text-sm">
        <thead class="text-xs text-gray-400 text-left">
            <tr><th class="py-2">KODE</th><th>PENGUNJUNG</th><th>DESTINASI</th><th>TANGGAL</th><th>TOTAL</th><th>STATUS</th><th>AKSI</th></tr>
        </thead>
        <tbody>
            @forelse ($bookings as $booking)
                <tr class="border-t">
                    <td class="py-3 font-medium">
                        <a href="{{ route('admin.bookings.show', $booking->code) }}" class="text-primary">{{ $booking->code }}</a>
                    </td>
                    <td>
                        <p>{{ $booking->customer_name }}</p>
                        <p class="text-xs text-gray-400">{{ $booking->user?->email }}</p>
                    </td>
                    <td>{{ $booking->destination?->name ?? '-' }}</td>
                    <td>{{ $booking->visit_date->translatedFormat('d M Y') }}</td>
                    <td>Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</td>
                    <td>@include('partials.badge', ['s' => $booking->status])</td>
                    <td><a href="{{ route('admin.bookings.show', $booking->code) }}" class="text-primary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-8 text-center text-gray-400">Belum ada booking.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $bookings->links() }}</div>
</div>
@endsection
