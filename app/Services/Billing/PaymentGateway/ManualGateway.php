<?php

namespace App\Services\Billing\PaymentGateway;

use App\Models\Billing\SubscriptionInvoice;
use Illuminate\Http\Request;

class ManualGateway implements PaymentGatewayInterface
{
    public function name(): string
    {
        return 'manual';
    }

    public function createPayment(SubscriptionInvoice $invoice): array
    {
        return [
            'reference' => 'MANUAL-'.$invoice->number,
            'payment_url' => url(config('billing.gateway.manual_payment_url', '/billing/invoices').'/'.$invoice->id),
        ];
    }

    public function validateSignature(Request $request): bool
    {
        $secret = (string) config('billing.gateway.webhook_secret');

        if ($secret === '') {
            return app()->environment(['local', 'testing']);
        }

        $header = (string) config('billing.gateway.signature_header', 'X-Billing-Signature');
        $signature = (string) $request->header($header);

        if ($signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    public function parse(Request $request): array
    {
        $payload = json_decode($request->getContent(), true) ?: $request->all();
        $status = strtoupper((string) ($payload['status'] ?? $payload['transaction_status'] ?? ''));

        return [
            'event_id' => $request->header((string) config('billing.gateway.event_header', 'X-Event-Id'))
                ?: ($payload['event_id'] ?? $payload['id'] ?? null),
            'reference' => $payload['reference'] ?? $payload['gateway_reference'] ?? $payload['order_id'] ?? $payload['transaction_id'] ?? null,
            'status' => in_array($status, ['PAID', 'SETTLEMENT', 'SUCCESS', 'CAPTURE'], true) ? 'PAID' : $status,
            'payload' => $payload,
        ];
    }
}
