@extends('layouts.admin')
@section('title', 'Jenis Tiket')
@section('heading', 'Jenis Tiket')
@section('sub', 'Kelola harga dan kuota harian tiket per destinasi')

@section('actions')
    <a href="{{ route('admin.tickets.create') }}" class="bg-primary text-white text-sm rounded-xl px-5 py-2.5 font-semibold">+ Tambah Jenis Tiket</a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border p-5">
    <form method="GET" action="{{ route('admin.tickets.index') }}" class="flex flex-wrap gap-3 mb-4">
        <select name="destination_id" class="border rounded-xl px-3 py-2 text-sm">
            <option value="">Semua destinasi</option>
            @foreach ($destinations as $destination)
                <option value="{{ $destination->id }}" @selected((int) request('destination_id') === $destination->id)>{{ $destination->name }}</option>
            @endforeach
        </select>
        <select name="status" class="border rounded-xl px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="active" @selected(request('status') === 'active')>Aktif</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
        </select>
        <button class="border rounded-xl px-4 py-2 text-sm text-gray-600">Terapkan</button>
        <a href="{{ route('admin.tickets.index') }}" class="px-2 py-2 text-sm text-gray-400">Reset</a>
    </form>

    <table class="w-full text-sm">
        <thead class="text-xs text-gray-400 text-left">
            <tr><th class="py-2">JENIS TIKET</th><th>DESTINASI</th><th>HARGA</th><th>KUOTA HARIAN</th><th>STATUS</th><th>AKSI</th></tr>
        </thead>
        <tbody>
            @forelse ($ticketTypes as $ticketType)
                <tr class="border-t">
                    <td class="py-3">
                        <p class="font-medium">{{ $ticketType->name }}</p>
                        @if ($ticketType->description)
                            <p class="text-xs text-gray-400">{{ $ticketType->description }}</p>
                        @endif
                    </td>
                    <td>{{ $ticketType->destination?->name ?? '-' }}</td>
                    <td>Rp {{ number_format((float) $ticketType->price, 0, ',', '.') }}</td>
                    <td>{{ number_format($ticketType->default_quota, 0, ',', '.') }}</td>
                    <td>@include('partials.badge', ['s' => $ticketType->status])</td>
                    <td class="space-x-3 whitespace-nowrap">
                        <a href="{{ route('admin.tickets.edit', $ticketType->id) }}" class="text-primary">Edit</a>
                        <form method="POST" action="{{ route('admin.tickets.destroy', $ticketType->id) }}" class="inline"
                              onsubmit="return confirm('Hapus jenis tiket {{ addslashes($ticketType->name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-500">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-gray-400">Belum ada jenis tiket.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $ticketTypes->links() }}</div>
</div>
@endsection
