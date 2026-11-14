@extends('layouts.admin')
@section('title', 'Pembayaran')
@section('heading', 'Pembayaran')
@section('sub', 'Verifikasi bukti transfer dan pantau status pembayaran')

@php
    $statusLabels = [
        '' => 'Semua',
        'pending' => 'Menunggu',
        'paid' => 'Lunas',
        'failed' => 'Gagal',
        'expired' => 'Kedaluwarsa',
    ];
    $filterParams = request()->only(['q']);
@endphp

@section('content')
<div class="bg-white rounded-2xl border p-5">
    <form method="GET" action="{{ route('admin.payments.index') }}" class="flex flex-wrap gap-2 mb-4 text-sm">
        <input name="q" value="{{ request('q') }}" placeholder="Cari referensi / kode booking / nama..."
               class="w-full border rounded-xl px-4 py-2 sm:w-72">
        @if (request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <button class="border rounded-xl px-4 py-2 text-gray-600">Cari</button>
        <a href="{{ route('admin.payments.index') }}" class="px-2 py-2 text-gray-400">Reset</a>
    </form>

    <div class="flex flex-wrap items-center gap-2 mb-4 text-sm">
        @foreach ($statusLabels as $key => $label)
            @php
                $count = $key === '' ? $payments->total() : (int) $counts->get($key, 0);
                $params = array_filter(array_merge($filterParams, $key === '' ? [] : ['status' => $key]));
            @endphp
            <a href="{{ route('admin.payments.index', $params) }}"
               class="px-4 py-1.5 rounded-full border {{ request('status', '') === $key ? 'bg-primary text-white border-primary' : 'text-gray-500' }}">
                {{ $label }} ({{ $count }})
            </a>
        @endforeach

        <a href="{{ route('admin.payments.index', array_filter(array_merge($filterParams, ['awaiting_verification' => 1]))) }}"
           class="px-4 py-1.5 rounded-full border {{ request('awaiting_verification') ? 'bg-amber-500 text-white border-amber-500' : 'text-amber-600 border-amber-200' }}">
            Perlu Verifikasi ({{ $awaitingVerification }})
        </a>
    </div>

    <div class="overflow-x-auto">
    <table class="w-full min-w-[920px] text-sm">
        <thead class="text-xs text-gray-400 text-left">
            <tr><th class="py-2">REFERENSI</th><th>BOOKING</th><th>PENGUNJUNG</th><th>METODE</th><th>JUMLAH</th><th>STATUS</th><th>BUKTI</th><th>AKSI</th></tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr class="border-t">
                    <td class="py-3 text-xs">{{ $payment->transaction_ref }}</td>
                    <td>
                        @if ($payment->booking)
                            <a href="{{ route('admin.bookings.show', $payment->booking->code) }}" class="text-primary">{{ $payment->booking->code }}</a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $payment->booking?->customer_name ?? '-' }}</td>
                    <td>{{ config('payment.methods')[$payment->method] ?? $payment->method }}</td>
                    <td>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                    <td>@include('partials.badge', ['s' => $payment->status])</td>
                    <td>
                        @if ($payment->proof_path)
                            <a href="{{ asset('storage/'.$payment->proof_path) }}" target="_blank" rel="noopener" class="text-primary">Lihat</a>
                        @else
                            <span class="text-xs text-gray-400">Belum ada</span>
                        @endif
                    </td>
                    <td>
                        @if ($payment->status === \App\Models\Payment::STATUS_PENDING && $payment->proof_path)
                            <form method="POST" action="{{ route('admin.payments.verify', $payment->transaction_ref) }}"
                                  onsubmit="return confirm('Verifikasi pembayaran ini?')">
                                @csrf
                                <button class="text-xs px-3 py-1.5 rounded-full bg-primary text-white font-semibold">Verifikasi</button>
                            </form>
                        @elseif ($payment->status === \App\Models\Payment::STATUS_PAID)
                            <span class="text-xs text-primary">Lunas</span>
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="py-8 text-center text-gray-400">Belum ada pembayaran.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
</div>
@endsection
