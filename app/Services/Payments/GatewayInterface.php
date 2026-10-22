<?php

namespace App\Services\Payments;

use App\Models\Booking;

interface GatewayInterface
{
    public function name(): string;

    /**
     * Buat transaksi di gateway; mengembalikan data transaksi ternormalisasi.
     *
     * @return array{transaction_ref: string, gateway_order_id: string|null, redirect_url: string|null, instructions: array<string, mixed>}
     */
    public function createTransaction(Booking $booking, string $method): array;

    /**
     * Verifikasi signature webhook dari gateway.
     */
    public function verifySignature(string $rawBody, array $headers): bool;

    /**
     * Normalisasi payload webhook.
     *
     * @param  array<string, mixed>  $payload
     * @return array{transaction_ref: string, status: string, method: string|null, paid_at: string|null, gateway_order_id: string|null}
     */
    public function parseWebhook(array $payload): array;
}
