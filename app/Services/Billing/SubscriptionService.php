<?php

namespace App\Services\Billing;

use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use Illuminate\Support\Carbon;

class SubscriptionService
{
    public function __construct(
        private SubscriptionStateService $state,
        private BillingInvoiceService $invoices,
        private BillingPeriodService $periods,
        private QuotaService $quota,
        private AuditLogger $audit
    ) {}

    public function current(?int $companyId = null): ?Subscription
    {
        $companyId = $companyId ?: session('id_company');
        if (! $companyId) {
            return null;
        }

        return Subscription::withoutCompanyScope()
            ->with(['plan', 'pendingPlan'])
            ->where('company_id', $companyId)
            ->first();
    }

    public function forCompany(int $companyId): Subscription
    {
        $subscription = $this->current($companyId);

        return $subscription ?: $this->state->startTrial($companyId);
    }

    public function quotaConflicts(Plan $plan, int $companyId): array
    {
        $conflicts = [];

        foreach ($this->quota->definitions() as $key => $definition) {
            $limit = $plan->limit($key);
            if ($limit === null) {
                continue;
            }

            $used = $this->quota->used($key, $companyId);
            if ($used > (int) $limit) {
                $conflicts[] = [
                    'key' => $key,
                    'label' => $definition['label'] ?? $key,
                    'used' => $used,
                    'limit' => (int) $limit,
                ];
            }
        }

        return $conflicts;
    }

    public function choosePlan(Subscription $subscription, Plan $plan, string $cycle, ?int $actorId = null): array
    {
        if (! $plan->is_active) {
            throw new \InvalidArgumentException('Paket tidak aktif dan tidak bisa dipilih.');
        }

        if (! in_array($cycle, [Subscription::CYCLE_MONTHLY, Subscription::CYCLE_YEARLY], true)) {
            throw new \InvalidArgumentException('Siklus tagihan tidak valid.');
        }

        $conflicts = $this->quotaConflicts($plan, $subscription->company_id);
        if ($conflicts) {
            return ['type' => 'blocked', 'conflicts' => $conflicts];
        }

        $currentPlan = $subscription->plan;
        if (! $currentPlan) {
            throw new \RuntimeException('Paket langganan saat ini tidak ditemukan.');
        }

        $currentPrice = $currentPlan->priceFor($cycle);
        $targetPrice = $plan->priceFor($cycle);

        $isSamePlan = (int) $subscription->plan_id === (int) $plan->id
            && $subscription->billing_cycle === $cycle;

        if ($isSamePlan && $subscription->status !== Subscription::STATUS_TRIAL) {
            return ['type' => 'noop'];
        }

        $isUpgrade = $subscription->status === Subscription::STATUS_TRIAL || $targetPrice > $currentPrice;

        if ($isUpgrade) {
            $invoice = $this->invoices->createUpgradeInvoice($subscription, $plan, $cycle);

            $subscription->pending_plan_id = null;
            $subscription->pending_billing_cycle = null;
            $subscription->pending_effective_at = null;
            $subscription->save();

            $this->audit->log(
                'subscription.plan_requested',
                $subscription->company_id,
                Subscription::class,
                $subscription->id,
                'Pilih paket '.$plan->name.' ('.$cycle.')',
                ['plan_id' => $subscription->plan_id, 'billing_cycle' => $subscription->billing_cycle],
                ['plan_id' => $plan->id, 'billing_cycle' => $cycle, 'invoice_id' => $invoice->id]
            );

            return ['type' => 'upgrade', 'invoice' => $invoice];
        }

        $subscription->pending_plan_id = $plan->id;
        $subscription->pending_billing_cycle = $cycle;
        $subscription->pending_effective_at = $subscription->current_period_end;
        $subscription->save();

        $this->audit->log(
            'subscription.downgrade_scheduled',
            $subscription->company_id,
            Subscription::class,
            $subscription->id,
            'Downgrade ke paket '.$plan->name.' ('.$cycle.')',
            ['plan_id' => $subscription->plan_id, 'billing_cycle' => $subscription->billing_cycle],
            ['plan_id' => $plan->id, 'billing_cycle' => $cycle]
        );

        return ['type' => 'downgrade', 'effective_at' => $subscription->pending_effective_at];
    }

    public function cancel(Subscription $subscription, ?int $actorId = null): Subscription
    {
        $this->audit->log(
            'subscription.canceled',
            $subscription->company_id,
            Subscription::class,
            $subscription->id,
            'Pembatalan oleh owner',
            ['status' => $subscription->status],
            ['status' => Subscription::STATUS_CANCELED]
        );

        return $this->state->cancel($subscription, 'canceled_by_owner', 'owner', $actorId);
    }

    public function extend(Subscription $subscription, int $days, ?int $actorId = null, string $reason = 'Perpanjangan manual'): Subscription
    {
        $before = [
            'status' => $subscription->status,
            'current_period_end' => $subscription->current_period_end?->toDateTimeString(),
        ];

        if ($days > 0) {
            $base = $subscription->current_period_end && $subscription->current_period_end->isFuture()
                ? $subscription->current_period_end->copy()
                : $this->periods->now();
            $subscription->current_period_end = $base->copy()->addDays($days);
            if (! $subscription->current_period_start || $subscription->current_period_start->isFuture()) {
                $subscription->current_period_start = $this->periods->now();
            }
        }

        $subscription->grace_ends_at = null;
        if ($subscription->status === Subscription::STATUS_TRIAL) {
            $subscription->trial_ends_at = $subscription->current_period_end;
        }
        $subscription->save();

        if (in_array($subscription->status, [Subscription::STATUS_PAST_DUE, Subscription::STATUS_SUSPENDED], true)) {
            $this->state->activate($subscription, 'manual_extension', 'superadmin', $actorId);
        } else {
            $this->state->record($subscription, $subscription->status, $subscription->status, 'manual_extension', 'superadmin', $actorId);
        }

        $this->audit->log(
            'subscription.extended',
            $subscription->company_id,
            Subscription::class,
            $subscription->id,
            $reason,
            $before,
            ['current_period_end' => $subscription->current_period_end->toDateTimeString(), 'days' => $days]
        );

        return $subscription;
    }

    public function forceChangePlan(Subscription $subscription, Plan $plan, string $cycle, string $reason, ?int $actorId = null): Subscription
    {
        $before = ['plan_id' => $subscription->plan_id, 'billing_cycle' => $subscription->billing_cycle];

        $subscription->plan_id = $plan->id;
        $subscription->billing_cycle = $cycle;
        $subscription->pending_plan_id = null;
        $subscription->pending_billing_cycle = null;
        $subscription->pending_effective_at = null;
        $subscription->save();

        $this->state->record($subscription, $subscription->status, $subscription->status, 'plan_changed_by_admin', 'superadmin', $actorId);

        $this->audit->log(
            'subscription.plan_changed',
            $subscription->company_id,
            Subscription::class,
            $subscription->id,
            $reason,
            $before,
            ['plan_id' => $plan->id, 'billing_cycle' => $cycle]
        );

        return $subscription;
    }

    public function applyPendingPlans(): int
    {
        $now = $this->periods->now();
        $count = 0;

        $subscriptions = Subscription::withoutCompanyScope()
            ->whereNotNull('pending_plan_id')
            ->whereNotNull('pending_effective_at')
            ->where('pending_effective_at', '<=', $now)
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->state->applyPendingPlan($subscription);
            $count++;
        }

        return $count;
    }
}
