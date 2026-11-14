@extends('layouts.admin')
@section('title', 'Detail Booking')
@section('heading', 'Detail Booking')
@section('sub', $booking->code)

@section('actions')
    <a href="{{ route('admin.bookings.index') }}" class="border rounded-xl px-5 py-2.5 text-sm text-gray-600">← Kembali</a>
@endsection

@section('content')
<div class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
    <div class="bg-white rounded-2xl border p-5 xl:col-span-2">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-xs text-gray-400">Kode Booking</p>
                <p class="text-xl font-bold">{{ $booking->code }}</p>
            </div>
            @include('partials.badge', ['s' => $booking->status])
        </div>

        <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
            <div>
                <p class="text-xs text-gray-400">Destinasi</p>
                <p class="font-medium">{{ $booking->destination?->name ?? '-' }}</p>
                <p class="text-xs text-gray-500">{{ $booking->destination?->location }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Tanggal Kunjungan</p>
                <p class="font-medium">{{ $booking->visit_date->translatedFormat('d F Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Dibuat</p>
                <p class="font-medium">{{ $booking->created_at?->translatedFormat('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Batas Pembayaran</p>
                <p class="font-medium">{{ $booking->expires_at?->translatedFormat('d M Y H:i') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Dibayar</p>
                <p class="font-medium">{{ $booking->paid_at?->translatedFormat('d M Y H:i') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Voucher</p>
                <p class="font-medium">{{ $booking->voucher_code ?? '-' }}</p>
            </div>
        </div>

        <div class="border-t mt-4 pt-4">
            <p class="text-sm font-semibold mb-2">Rincian Tiket</p>
            <div class="overflow-x-auto">
            <table class="w-full min-w-[520px] text-sm">
                <thead class="text-xs text-gray-400 text-left"><tr><th class="py-1">JENIS</th><th>HARGA</th><th>JUMLAH</th><th class="text-right">SUBTOTAL</th></tr></thead>
                <tbody>
                    @foreach ($booking->items as $item)
                        <tr class="border-t">
                            <td class="py-2">{{ $item->ticket_type_name }}</td>
                            <td>Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td class="text-right">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @if ((float) $booking->discount_amount > 0)
                        <tr class="border-t"><td colspan="3" class="py-2 text-right text-gray-500">Diskon</td><td class="text-right text-primary">- Rp {{ number_format((float) $booking->discount_amount, 0, ',', '.') }}</td></tr>
                    @endif
                    <tr class="border-t"><td colspan="3" class="py-2 text-right font-semibold">Total</td><td class="text-right font-bold text-primary">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</td></tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-2xl border p-5">
            <p class="text-sm font-semibold mb-3">Data Pengunjung</p>
            <p class="font-medium">{{ $booking->customer_name }}</p>
            <p class="text-xs text-gray-500">{{ $booking->user?->email }}</p>
            <p class="text-xs text-gray-500">{{ $booking->customer_phone }}</p>
        </div>

        <div class="bg-white rounded-2xl border p-5">
            <p class="text-sm font-semibold mb-3">Aksi</p>
            @if ($booking->status === \App\Models\Booking::STATUS_PAID)
                <form method="POST" action="{{ route('admin.bookings.complete', $booking->code) }}" onsubmit="return confirm('Tandai booking ini selesai?')">
                    @csrf
                    <button class="w-full bg-primary text-white rounded-xl py-2.5 text-sm font-semibold">Tandai Selesai</button>
                </form>
            @elseif ($booking->status === \App\Models\Booking::STATUS_PENDING)
                <form method="POST" action="{{ route('admin.bookings.cancel', $booking->code) }}" onsubmit="return confirm('Batalkan booking ini? Kuota akan dikembalikan.')">
                    @csrf
                    <button class="w-full border border-red-200 text-red-500 rounded-xl py-2.5 text-sm font-semibold">Batalkan Booking</button>
                </form>
            @else
                <p class="text-xs text-gray-400">Tidak ada aksi tersedia untuk status ini.</p>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-sm font-semibold mb-3">E-Ticket</p>
        <div class="overflow-x-auto">
        <table class="w-full min-w-[420px] text-sm">
            <thead class="text-xs text-gray-400 text-left"><tr><th class="py-1">KODE TIKET</th><th>JENIS</th><th>STATUS</th><th>DIPAKAI</th></tr></thead>
            <tbody>
                @forelse ($booking->tickets as $ticket)
                    <tr class="border-t">
                        <td class="py-2 font-mono text-xs">{{ $ticket->code }}</td>
                        <td>{{ $ticket->ticket_type_name }}</td>
                        <td>@include('partials.badge', ['s' => $ticket->status])</td>
                        <td class="text-xs text-gray-500">{{ $ticket->used_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-400">E-ticket belum diterbitkan.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl border p-5">
        <p class="text-sm font-semibold mb-3">Pembayaran</p>
        <div class="overflow-x-auto">
        <table class="w-full min-w-[520px] text-sm">
            <thead class="text-xs text-gray-400 text-left"><tr><th class="py-1">REFERENSI</th><th>METODE</th><th>JUMLAH</th><th>STATUS</th><th>BUKTI</th></tr></thead>
            <tbody>
                @forelse ($booking->payments as $payment)
                    <tr class="border-t">
                        <td class="py-2 text-xs">{{ $payment->transaction_ref }}</td>
                        <td>{{ config('payment.methods')[$payment->method] ?? $payment->method }}</td>
                        <td>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        <td>@include('partials.badge', ['s' => $payment->status])</td>
                        <td>
                            @if ($payment->proof_path)
                                <a href="{{ asset('storage/'.$payment->proof_path) }}" target="_blank" rel="noopener" class="text-primary">Lihat</a>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-gray-400">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <a href="{{ route('admin.payments.index', ['q' => $booking->code]) }}" class="mt-3 inline-block text-sm text-primary">Kelola pembayaran ›</a>
    </div>
</div>
@endsection
