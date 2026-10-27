<?php

namespace App\Services\Billing;

use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\Billing\SubscriptionInvoice;
use App\Services\Billing\PaymentGateway\GatewayManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingInvoiceService
{
    public function __construct(
        private BillingPeriodService $periods,
        private SubscriptionStateService $state,
        private GatewayManager $gateways
    ) {}

    public function createForSubscription(
        Subscription $subscription,
        Plan $plan,
        string $cycle,
        Carbon $periodStart,
        Carbon $periodEnd,
        ?Carbon $dueDate = null,
        array $meta = []
    ): SubscriptionInvoice {
        $existing = SubscriptionInvoice::withoutCompanyScope()
            ->where('subscription_id', $subscription->id)
            ->whereDate('period_start', $periodStart->toDateString())
            ->whereDate('period_end', $periodEnd->toDateString())
            ->whereIn('status', [SubscriptionInvoice::STATUS_PENDING, SubscriptionInvoice::STATUS_PAID])
            ->first();

        if ($existing) {
            return $existing;
        }

        $base = $plan->priceFor($cycle);
        $taxPercent = (float) config('billing.tax_percent', 0);
        $amount = round($base + ($base * $taxPercent / 100), 2);

        $invoice = new SubscriptionInvoice([
            'company_id' => $subscription->company_id,
            'subscription_id' => $subscription->id,
            'number' => $this->nextNumber(),
            'amount' => $amount,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'due_date' => ($dueDate ?: $periodEnd)->toDateString(),
            'status' => SubscriptionInvoice::STATUS_PENDING,
            'meta' => array_merge([
                'plan_id' => $plan->id,
                'plan_code' => $plan->code,
                'billing_cycle' => $cycle,
                'base_amount' => $base,
                'tax_percent' => $taxPercent,
            ], $meta),
        ]);

        $gateway = $this->gateways->driver();
        $payment = $gateway->createPayment($invoice);
        $invoice->gateway = $gateway->name();
        $invoice->gateway_reference = $payment['reference'];
        $invoice->payment_url = $payment['payment_url'];
        $invoice->save();

        Log::info('billing.invoice_created', [
            'invoice_id' => $invoice->id,
            'company_id' => $invoice->company_id,
            'amount' => $invoice->amount,
            'period_start' => $invoice->period_start->toDateString(),
            'period_end' => $invoice->period_end->toDateString(),
        ]);

        return $invoice;
    }

    public function createUpcomingInvoice(Subscription $subscription): ?SubscriptionInvoice
    {
        if ($subscription->cancel_at_period_end || $subscription->status === Subscription::STATUS_CANCELED) {
            return null;
        }

        $plan = $subscription->pending_plan_id
            ? Plan::find($subscription->pending_plan_id)
            : $subscription->plan;

        if (! $plan) {
            return null;
        }

        $cycle = $subscription->pending_billing_cycle ?: $subscription->billing_cycle;
        $periodStart = $subscription->current_period_end->copy()->startOfDay();
        $periodEnd = $this->periods->periodEndFor($periodStart, $cycle);

        return $this->createForSubscription($subscription, $plan, $cycle, $periodStart, $periodEnd, $periodStart);
    }

    public function createUpgradeInvoice(Subscription $subscription, Plan $plan, string $cycle): SubscriptionInvoice
    {
        $start = $this->periods->now()->startOfDay();
        $end = $this->periods->periodEndFor($start, $cycle);

        return $this->createForSubscription($subscription, $plan, $cycle, $start, $end, $start, [
            'kind' => 'upgrade',
        ]);
    }

    public function markPaid(SubscriptionInvoice $invoice, ?Carbon $paidAt = null, array $payload = []): bool
    {
        return DB::transaction(function () use ($invoice, $paidAt, $payload) {
            $locked = SubscriptionInvoice::withoutCompanyScope()
                ->lockForUpdate()
                ->find($invoice->id);

            if (! $locked) {
                return false;
            }

            if ($locked->status === SubscriptionInvoice::STATUS_PAID) {
                return false;
            }

            $locked->status = SubscriptionInvoice::STATUS_PAID;
            $locked->paid_at = $paidAt ?: $this->periods->now();
            $locked->save();

            $subscription = Subscription::withoutCompanyScope()
                ->lockForUpdate()
                ->find($locked->subscription_id);

            if ($subscription) {
                $this->state->applyPaidInvoice($subscription, $locked, 'webhook');
            }

            Log::info('billing.invoice_paid', [
                'invoice_id' => $locked->id,
                'company_id' => $locked->company_id,
                'payload_reference' => $payload['reference'] ?? null,
            ]);

            return true;
        });
    }

    public function expirePending(): int
    {
        return SubscriptionInvoice::withoutCompanyScope()
            ->where('status', SubscriptionInvoice::STATUS_PENDING)
            ->whereDate('due_date', '<', $this->periods->now()->toDateString())
            ->update(['status' => SubscriptionInvoice::STATUS_EXPIRED]);
    }

    public function nextNumber(): string
    {
        $prefix = 'SUB/'.$this->periods->now()->format('Ym').'/';

        $last = SubscriptionInvoice::withoutCompanyScope()
            ->where('number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('number');

        $sequence = 1;
        if ($last) {
            $parts = explode('/', $last);
            $sequence = ((int) end($parts)) + 1;
        }

        do {
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $exists = SubscriptionInvoice::withoutCompanyScope()->where('number', $number)->exists();
            $sequence++;
        } while ($exists);

        return $number;
    }
}
