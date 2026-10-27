<?php

namespace App\Services\Billing\PaymentGateway;

use App\Models\Billing\SubscriptionInvoice;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function name(): string;

    public function createPayment(SubscriptionInvoice $invoice): array;

    public function validateSignature(Request $request): bool;

    public function parse(Request $request): array;
}
