@extends('layouts.admin')
@section('title', 'Kuota Tiket')
@section('heading', 'Kuota Tiket')
@section('sub', 'Atur batas kuota tiket harian untuk mencegah overbooking')

@section('content')
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="col-span-1 bg-white rounded-2xl border p-5">
        <h2 class="font-semibold mb-3">Atur Kuota</h2>
        <form method="POST" action="{{ route('admin.quotas.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium">Jenis Tiket</label>
                <select name="ticket_type_id" class="w-full border rounded-xl px-3 py-2.5 text-sm mt-1" required>
                    <option value="">Pilih jenis tiket</option>
                    @foreach ($ticketTypes as $type)
                        <option value="{{ $type->id }}" @selected((int) old('ticket_type_id') === $type->id)>
                            {{ $type->destination?->name }} — {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium">Tanggal Kunjungan</label>
                <input name="visit_date" type="date" value="{{ old('visit_date', now()->toDateString()) }}"
                       min="{{ now()->toDateString() }}" class="w-full border rounded-xl px-3 py-2.5 text-sm mt-1" required>
            </div>
            <div>
                <label class="text-xs font-medium">Kuota</label>
                <input name="quota" type="number" min="0" step="1" value="{{ old('quota', 100) }}"
                       class="w-full border rounded-xl px-3 py-2.5 text-sm mt-1" required>
            </div>
            <button class="w-full bg-primary text-white rounded-xl py-2.5 text-sm font-semibold">Simpan Kuota</button>
        </form>
        <p class="text-[11px] text-gray-400 mt-3">
            Kuota per tanggal akan dibuat otomatis jika belum ada. Nilai tidak boleh lebih kecil dari tiket yang sudah terjual.
        </p>
    </div>

    <div class="col-span-2 bg-white rounded-2xl border p-5">
        <form method="GET" action="{{ route('admin.quotas.index') }}" class="flex flex-wrap gap-2 mb-4">
            <select name="destination_id" class="border rounded-xl px-3 py-2 text-sm">
                <option value="">Semua destinasi</option>
                @foreach ($destinations as $destination)
                    <option value="{{ $destination->id }}" @selected((int) request('destination_id') === $destination->id)>{{ $destination->name }}</option>
                @endforeach
            </select>
            <select name="ticket_type_id" class="border rounded-xl px-3 py-2 text-sm">
                <option value="">Semua jenis tiket</option>
                @foreach ($ticketTypes as $type)
                    <option value="{{ $type->id }}" @selected((int) request('ticket_type_id') === $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="border rounded-xl px-3 py-2 text-sm">
            <input type="date" name="to" value="{{ request('to') }}" class="border rounded-xl px-3 py-2 text-sm">
            <button class="border rounded-xl px-4 py-2 text-sm text-gray-600">Filter</button>
        </form>

        <table class="w-full text-sm">
            <thead class="text-xs text-gray-400 text-left">
                <tr><th class="py-2">TANGGAL</th><th>JENIS TIKET</th><th>DESTINASI</th><th>KUOTA</th><th>TERJUAL</th><th>SISA</th></tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-t">
                        <td class="py-3">{{ $row->visit_date->translatedFormat('d M Y') }}</td>
                        <td>{{ $row->ticketType?->name ?? '-' }}</td>
                        <td>{{ $row->ticketType?->destination?->name ?? '-' }}</td>
                        <td>{{ number_format($row->quota, 0, ',', '.') }}</td>
                        <td>{{ number_format($row->sold, 0, ',', '.') }}</td>
                        <td>
                            @if ($row->remaining() > 0)
                                <span class="text-primary font-medium">{{ number_format($row->remaining(), 0, ',', '.') }}</span>
                            @else
                                <span class="text-red-500 font-medium">Habis</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-gray-400">Belum ada data kuota.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">{{ $rows->links() }}</div>
    </div>
</div>
@endsection
