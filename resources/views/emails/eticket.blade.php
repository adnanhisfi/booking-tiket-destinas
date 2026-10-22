<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>E-Tiket {{ $booking->code }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color:#111827;">
    <h1 style="color:#0f766e;">E-Tiket JelajahKu</h1>
    <p>Halo <strong>{{ $booking->customer_name }}</strong>, pembayaran Anda sudah kami terima.</p>

    <table cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
        <tr><td><strong>Kode Booking</strong></td><td>{{ $booking->code }}</td></tr>
        <tr><td><strong>Destinasi</strong></td><td>{{ $booking->destination->name }}</td></tr>
        <tr><td><strong>Tanggal Kunjungan</strong></td><td>{{ $booking->visit_date->toDateString() }}</td></tr>
        <tr><td><strong>Total</strong></td><td>Rp{{ number_format((float) $booking->total_amount, 0, ',', '.') }}</td></tr>
    </table>

    <h2 style="margin-top:24px;">Daftar Tiket</h2>
    <ul>
        @foreach ($tickets as $ticket)
            <li>
                <strong>{{ $ticket->ticket_type_name }}</strong> &mdash; kode QR: <code>{{ $ticket->code }}</code>
            </li>
        @endforeach
    </ul>

    <p>Tunjukkan kode QR pada petugas di pintu masuk. Tiket hanya berlaku pada tanggal kunjungan.</p>
</body>
</html>
