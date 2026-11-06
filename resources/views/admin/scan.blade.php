@extends('layouts.admin')
@section('title', 'Scan Tiket')
@section('heading', 'Scan Tiket')
@section('sub', 'Validasi QR code e-ticket pengunjung di pintu masuk')

@section('content')
<div class="grid grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl border p-6">
        <h2 class="font-semibold mb-3">Validasi Kode Tiket</h2>
        <p class="text-sm text-gray-500 mb-4">
            Masukkan kode tiket atau tempel isi QR code (format <code class="text-xs bg-gray-100 px-1 rounded">JLK:...</code>).
        </p>
        <form id="scan-form" method="POST" action="{{ route('admin.scan.validate') }}" class="space-y-3">
            @csrf
            <input name="code" value="{{ old('code', $code ?? '') }}" placeholder="Contoh: JLK:A1B2C3..."
                   class="w-full border rounded-xl px-4 py-3 text-sm bg-gray-50" autofocus required>
            <button class="w-full bg-primary text-white rounded-xl py-3 text-sm font-semibold">Validasi Tiket</button>
        </form>

        <button type="button" id="start-scan" class="mt-3 w-full border border-primary text-primary rounded-xl py-2.5 text-sm font-semibold">
            Scan dengan Kamera
        </button>
        <div id="reader" class="mt-3 hidden rounded-xl overflow-hidden"></div>
        <p id="scan-status" class="mt-2 text-[11px] text-gray-500"></p>

        <div class="mt-4 rounded-xl bg-gray-50 p-3 text-[11px] text-gray-500">
            Tiket hanya valid pada tanggal kunjungan yang tertera dan setelah pembayaran lunas. Tiket yang sudah dipakai tidak dapat digunakan lagi.
        </div>
    </div>

    <div class="bg-white rounded-2xl border p-6">
        <h2 class="font-semibold mb-3">Hasil Validasi</h2>

        @if (isset($result))
            @php
                $valid = $result['result'] === 'valid';
                $ticket = $result['ticket'];
            @endphp
            <div class="rounded-xl px-4 py-3 text-sm mb-4 {{ $valid ? 'bg-soft text-primary' : 'bg-red-50 text-red-600' }}">
                <p class="font-semibold">{{ $valid ? 'Tiket Valid' : 'Tiket Tidak Valid' }}</p>
                <p class="text-xs mt-1">{{ $result['message'] }}</p>
            </div>

            @if ($ticket)
                <div class="text-sm space-y-2">
                    <div class="flex justify-between"><span class="text-gray-500">Kode Tiket</span><span class="font-mono text-xs">{{ $ticket->code }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Kode Booking</span><span>{{ $ticket->booking?->code ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Destinasi</span><span>{{ $ticket->booking?->destination?->name ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Jenis Tiket</span><span>{{ $ticket->ticket_type_name }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Tanggal Kunjungan</span><span>{{ $ticket->visit_date->translatedFormat('d M Y') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Status</span>@include('partials.badge', ['s' => $ticket->status])</div>
                    <div class="flex justify-between"><span class="text-gray-500">Dipakai Pada</span><span>{{ $ticket->used_at?->translatedFormat('d M Y H:i') ?? '-' }}</span></div>
                </div>
            @endif
        @else
            <div class="h-56 grid place-items-center text-center text-gray-400 text-sm">
                Hasil validasi akan muncul di sini.
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const button = document.getElementById('start-scan');
        if (!button) return;

        const form = document.getElementById('scan-form');
        const input = form.querySelector('input[name="code"]');
        const reader = document.getElementById('reader');
        const status = document.getElementById('scan-status');
        let scanner = null;

        button.addEventListener('click', async () => {
            if (typeof Html5Qrcode === 'undefined') {
                status.textContent = 'Pemindai kamera tidak tersedia. Masukkan kode tiket secara manual.';
                return;
            }

            button.disabled = true;
            reader.classList.remove('hidden');
            status.textContent = 'Arahkan kamera ke QR code tiket...';

            try {
                scanner = new Html5Qrcode('reader');
                await scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    async (decodedText) => {
                        input.value = decodedText;
                        status.textContent = 'QR terbaca. Memvalidasi tiket...';

                        try {
                            await scanner.stop();
                        } catch (error) {
                            // Kamera mungkin sudah berhenti; lanjutkan submit.
                        }

                        form.submit();
                    },
                    () => {}
                );
            } catch (error) {
                status.textContent = 'Kamera tidak dapat diakses. Masukkan kode tiket secara manual.';
                button.disabled = false;
                reader.classList.add('hidden');
            }
        });
    });
</script>
@endpush
