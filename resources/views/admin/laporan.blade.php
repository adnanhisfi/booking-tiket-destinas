@extends('layouts.admin')
@section('title', 'Laporan')
@section('heading', 'Laporan')
@section('sub', 'Analisis pendapatan dan penjualan tiket')

@section('actions')
    <div class="flex flex-wrap gap-2 text-sm">
        @foreach ([7 => '7 Hari', 30 => '30 Hari', 365 => 'Tahun Ini'] as $key => $label)
            <a href="{{ route('admin.reports.index', array_filter(['periode' => $key, 'destination_id' => $destinationId])) }}"
               class="px-4 py-2 rounded-xl border {{ $period === $key ? 'bg-primary text-white border-primary' : 'text-gray-500' }}">{{ $label }}</a>
        @endforeach
        <a href="{{ route('admin.reports.export', array_filter(['from' => $from->toDateString(), 'to' => $to->toDateString(), 'destination_id' => $destinationId])) }}"
           class="px-4 py-2 rounded-xl bg-primary text-white font-semibold">Unduh Laporan</a>
    </div>
@endsection

@section('content')
<form method="GET" action="{{ route('admin.reports.index') }}" class="bg-white rounded-2xl border p-4 mb-4 flex flex-wrap items-end gap-3 text-sm">
    <div>
        <label class="text-xs text-gray-400 block">Dari</label>
        <input type="date" name="from" value="{{ $from->toDateString() }}" class="border rounded-xl px-3 py-2 mt-1">
    </div>
    <div>
        <label class="text-xs text-gray-400 block">Sampai</label>
        <input type="date" name="to" value="{{ $to->toDateString() }}" class="border rounded-xl px-3 py-2 mt-1">
    </div>
    <div>
        <label class="text-xs text-gray-400 block">Destinasi</label>
        <select name="destination_id" class="border rounded-xl px-3 py-2 mt-1">
            <option value="">Semua destinasi</option>
            @foreach ($destinations as $destination)
                <option value="{{ $destination->id }}" @selected($destinationId === $destination->id)>{{ $destination->name }}</option>
            @endforeach
        </select>
    </div>
    <button class="border rounded-xl px-4 py-2 text-gray-600">Terapkan</button>
</form>

<div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-sm text-gray-500">Total Pendapatan</p>
        <p class="text-2xl font-bold mt-2">Rp {{ number_format((float) $summary['revenue'], 0, ',', '.') }}</p>
        <p class="text-xs text-primary mt-1">{{ $from->translatedFormat('d M Y') }} – {{ $to->translatedFormat('d M Y') }}</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-sm text-gray-500">Total Booking</p>
        <p class="text-2xl font-bold mt-2">{{ number_format((int) $summary['orders'], 0, ',', '.') }}</p>
        <p class="text-xs text-primary mt-1">Booking lunas</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-sm text-gray-500">Total Diskon</p>
        <p class="text-2xl font-bold mt-2">Rp {{ number_format((float) $summary['discount'], 0, ',', '.') }}</p>
        <p class="text-xs text-primary mt-1">Voucher terpakai</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-sm text-gray-500">Rata-rata / Booking</p>
        <p class="text-2xl font-bold mt-2">Rp {{ number_format((float) $average, 0, ',', '.') }}</p>
        <p class="text-xs text-primary mt-1">Pendapatan bersih</p>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <div class="bg-white rounded-2xl border p-5">
        <h2 class="font-semibold mb-3">Pendapatan {{ $period === 365 ? 'per Bulan' : 'per Hari' }}</h2>
        @include('partials.grafik', ['grafik' => $grafik])
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <h2 class="font-semibold mb-3">Pendapatan per Destinasi</h2>
        <div class="overflow-x-auto">
        <table class="w-full min-w-[420px] text-sm">
            <thead class="text-xs text-gray-400 text-left">
                <tr><th class="py-2">DESTINASI</th><th>BOOKING</th><th class="text-right">PENDAPATAN</th></tr>
            </thead>
            <tbody>
                @forelse ($sales['by_destination'] as $row)
                    <tr class="border-t">
                        <td class="py-3">{{ $row['destination'] }}</td>
                        <td>{{ number_format($row['orders'], 0, ',', '.') }}</td>
                        <td class="text-right font-medium">Rp {{ number_format((float) $row['revenue'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-gray-400">Belum ada penjualan pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
