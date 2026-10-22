<?php

namespace App\Services\Payments;

use InvalidArgumentException;

class GatewayManager
{
    public function driver(?string $name = null): GatewayInterface
    {
        $name ??= (string) config('payment.driver');

        return match ($name) {
            'manual', 'transfer' => new ManualTransferGateway,
            'sandbox', 'midtrans', 'xendit' => new SandboxGateway,
            default => throw new InvalidArgumentException("Payment driver [{$name}] tidak dikenal."),
        };
    }
}
