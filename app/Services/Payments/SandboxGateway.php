<?php

namespace App\Services\Payments;

use App\Models\Booking;
use Illuminate\Support\Str;

/**
 * Gateway simulasi (mewakili Midtrans/Xendit pada lingkungan sandbox).
 * Verifikasi signature memakai HMAC-SHA256 dari raw body dengan webhook secret.
 */
class SandboxGateway implements GatewayInterface
{
    public function name(): string
    {
        return 'sandbox';
    }

    public function createTransaction(Booking $booking, string $method): array
    {
        $ref = 'SBX-'.$booking->code.'-'.Str::upper(Str::random(6));

        return [
            'transaction_ref' => $ref,
            'gateway_order_id' => 'ORDER-'.Str::upper(Str::random(10)),
            'redirect_url' => rtrim(config('payment.return_url'), '/').'?ref='.$ref,
            'instructions' => [
                'type' => 'redirect',
                'method' => $method,
                'redirect_url' => rtrim(config('payment.return_url'), '/').'?ref='.$ref,
                'amount' => (float) $booking->total_amount,
                'deadline' => $booking->expires_at->toIso8601String(),
            ],
        ];
    }

    public function verifySignature(string $rawBody, array $headers): bool
    {
        $signature = $headers['x-signature'] ?? $headers['X-Signature'] ?? null;

        if (is_array($signature)) {
            $signature = $signature[0] ?? null;
        }

        if (! is_string($signature) || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, (string) config('payment.webhook_secret'));

        return hash_equals($expected, $signature);
    }

    public function parseWebhook(array $payload): array
    {
        $status = strtolower((string) ($payload['transaction_status'] ?? $payload['status'] ?? 'pending'));

        $status = match ($status) {
            'settlement', 'capture', 'paid', 'success', 'succeeded' => 'paid',
            'expire', 'expired' => 'expired',
            'deny', 'cancel', 'failed', 'failure' => 'failed',
            default => 'pending',
        };

        return [
            'transaction_ref' => (string) ($payload['transaction_ref'] ?? $payload['order_id'] ?? ''),
            'status' => $status,
            'method' => $payload['payment_type'] ?? $payload['method'] ?? null,
            'paid_at' => $payload['settlement_time'] ?? $payload['paid_at'] ?? null,
            'gateway_order_id' => $payload['order_id'] ?? null,
        ];
    }
}
