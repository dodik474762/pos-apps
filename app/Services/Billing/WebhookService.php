<?php

namespace App\Services\Billing;

use App\Models\Billing\PaymentWebhookLog;
use App\Models\Billing\SubscriptionInvoice;
use App\Services\Billing\PaymentGateway\GatewayManager;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function __construct(
        private GatewayManager $gateways,
        private BillingInvoiceService $invoices
    ) {}

    public function handle(Request $request, string $gatewayName): array
    {
        $gateway = $this->gateways->driver($gatewayName);
        $raw = $request->getContent();
        $parsed = $gateway->parse($request);
        $signatureValid = $gateway->validateSignature($request);
        $eventId = $parsed['event_id'] ?? null;

        if ($eventId) {
            $existing = PaymentWebhookLog::where('gateway', $gateway->name())
                ->where('event_id', $eventId)
                ->first();

            if ($existing) {
                if (! $signatureValid) {
                    Log::warning('billing.webhook_invalid_signature', ['gateway' => $gateway->name(), 'event_id' => $eventId]);

                    return ['http' => 401, 'body' => ['status' => 'FAILED', 'message' => 'Invalid signature', 'log_id' => $existing->id]];
                }

                return [
                    'http' => 200,
                    'body' => ['status' => 'IGNORED', 'message' => 'Duplicate event', 'log_id' => $existing->id],
                ];
            }
        }

        try {
            $log = PaymentWebhookLog::create([
                'gateway' => $gateway->name(),
                'event_id' => $eventId,
                'payload' => $raw ?: json_encode($request->all()),
                'signature_valid' => $signatureValid,
                'status' => 'RECEIVED',
                'received_at' => now(),
            ]);
        } catch (QueryException $e) {
            $existing = PaymentWebhookLog::where('gateway', $gateway->name())
                ->where('event_id', $eventId)
                ->first();

            if (! $signatureValid) {
                return ['http' => 401, 'body' => ['status' => 'FAILED', 'message' => 'Invalid signature', 'log_id' => $existing?->id]];
            }

            return [
                'http' => 200,
                'body' => ['status' => 'IGNORED', 'message' => 'Duplicate event', 'log_id' => $existing?->id],
            ];
        }

        if (! $signatureValid) {
            $log->status = 'FAILED';
            $log->error = 'Invalid signature';
            $log->processed_at = now();
            $log->save();

            Log::warning('billing.webhook_invalid_signature', ['log_id' => $log->id, 'gateway' => $gateway->name()]);

            return ['http' => 401, 'body' => ['status' => 'FAILED', 'message' => 'Invalid signature', 'log_id' => $log->id]];
        }

        return $this->processLog($log, $parsed);
    }

    public function replay(PaymentWebhookLog $log): array
    {
        if (! $log->signature_valid) {
            throw new \RuntimeException('Webhook dengan signature tidak valid tidak dapat diproses ulang.');
        }

        $payload = json_decode($log->payload, true) ?: [];
        $status = strtoupper((string) ($payload['status'] ?? $payload['transaction_status'] ?? ''));
        $parsed = [
            'reference' => $payload['reference'] ?? $payload['gateway_reference'] ?? $payload['order_id'] ?? $payload['transaction_id'] ?? null,
            'status' => in_array($status, ['PAID', 'SETTLEMENT', 'SUCCESS', 'CAPTURE'], true) ? 'PAID' : $status,
            'payload' => $payload,
        ];

        $log->status = 'RECEIVED';
        $log->error = null;
        $log->processed_at = null;
        $log->save();

        return $this->processLog($log, $parsed);
    }

    protected function processLog(PaymentWebhookLog $log, array $parsed): array
    {
        if (empty($parsed['reference'])) {
            return $this->finish($log, 'IGNORED', 'Missing gateway reference', 200);
        }

        if (($parsed['status'] ?? null) !== 'PAID') {
            return $this->finish($log, 'IGNORED', 'Payment status is '.($parsed['status'] ?: 'unknown'), 200);
        }

        try {
            $result = DB::transaction(function () use ($log, $parsed) {
                $invoice = SubscriptionInvoice::withoutCompanyScope()
                    ->lockForUpdate()
                    ->where('gateway_reference', $parsed['reference'])
                    ->first();

                if (! $invoice) {
                    return $this->finish($log, 'FAILED', 'Invoice not found for reference '.$parsed['reference'], 200);
                }

                if ($invoice->status === SubscriptionInvoice::STATUS_PAID) {
                    return $this->finish($log, 'IGNORED', 'Invoice already paid', 200);
                }

                $this->invoices->markPaid($invoice, null, $parsed['payload'] ?? []);

                return $this->finish($log, 'PROCESSED', null, 200);
            });

            return $result;
        } catch (\Throwable $e) {
            Log::error('billing.webhook_failed', [
                'log_id' => $log->id,
                'error' => $e->getMessage(),
            ]);

            return $this->finish($log, 'FAILED', $e->getMessage(), 500);
        }
    }

    protected function finish(PaymentWebhookLog $log, string $status, ?string $error, int $http): array
    {
        $log->status = $status;
        $log->error = $error;
        $log->processed_at = now();
        $log->save();

        return [
            'http' => $http,
            'body' => ['status' => $status, 'message' => $error ?: 'OK', 'log_id' => $log->id],
        ];
    }
}
