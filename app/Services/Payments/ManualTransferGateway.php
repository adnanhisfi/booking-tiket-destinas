<?php

namespace App\Services\Payments;

use App\Models\Booking;
use Illuminate\Support\Str;

class ManualTransferGateway implements GatewayInterface
{
    public function name(): string
    {
        return 'manual';
    }

    public function createTransaction(Booking $booking, string $method): array
    {
        $config = config('payment.manual');

        return [
            'transaction_ref' => 'TRF-'.$booking->code.'-'.Str::upper(Str::random(6)),
            'gateway_order_id' => null,
            'redirect_url' => null,
            'instructions' => [
                'type' => 'bank_transfer',
                'bank_name' => $config['bank_name'],
                'bank_account' => $config['bank_account'],
                'bank_holder' => $config['bank_holder'],
                'amount' => (float) $booking->total_amount,
                'deadline' => $booking->expires_at->toIso8601String(),
                'note' => 'Unggah bukti transfer melalui endpoint POST /api/v1/payments/{transaction_ref}/proof.',
            ],
        ];
    }

    public function verifySignature(string $rawBody, array $headers): bool
    {
        return true;
    }

    public function parseWebhook(array $payload): array
    {
        return [
            'transaction_ref' => (string) ($payload['transaction_ref'] ?? ''),
            'status' => (string) ($payload['status'] ?? 'pending'),
            'method' => $payload['method'] ?? 'transfer',
            'paid_at' => $payload['paid_at'] ?? null,
            'gateway_order_id' => null,
        ];
    }
}
