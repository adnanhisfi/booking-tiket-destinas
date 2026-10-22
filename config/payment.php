<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment driver
    |--------------------------------------------------------------------------
    | manual  : pengunjung mengunggah bukti transfer, admin memverifikasi.
    | sandbox : gateway dengan callback/webhook bertanda tangan (mock Midtrans/Xendit).
    */
    'driver' => env('PAYMENT_DRIVER', 'manual'),

    'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET', 'change-me-production-secret'),

    'return_url' => env('PAYMENT_RETURN_URL', 'http://localhost:8000/payment/return'),

    /*
    | Batas waktu pembayaran reservasi (menit) sebelum kuota dikembalikan worker.
    */
    'booking_ttl_minutes' => (int) env('BOOKING_PAYMENT_TTL_MINUTES', 15),

    /*
    | Batas maksimal tanggal kunjungan (hari ke depan).
    */
    'max_advance_days' => 60,

    'manual' => [
        'bank_name' => env('PAYMENT_MANUAL_BANK', 'BCA'),
        'bank_account' => env('PAYMENT_MANUAL_ACCOUNT', '1234567890'),
        'bank_holder' => env('PAYMENT_MANUAL_HOLDER', 'PT JelajahKu Wisata'),
    ],

    'methods' => [
        'qris' => 'QRIS',
        'va' => 'Virtual Account',
        'ewallet' => 'E-Wallet',
        'transfer' => 'Transfer Bank',
    ],
];
