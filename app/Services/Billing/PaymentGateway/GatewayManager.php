<?php

namespace App\Services\Billing\PaymentGateway;

use InvalidArgumentException;

class GatewayManager
{
    public function driver(?string $name = null): PaymentGatewayInterface
    {
        $name = $name ?: config('billing.gateway.default', 'manual');

        return match ($name) {
            'manual' => new ManualGateway(),
            default => throw new InvalidArgumentException("Gateway pembayaran [{$name}] belum didukung."),
        };
    }
}
