<?php

namespace App\Services\Billing;

use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\Billing\SubscriptionEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SubscriptionStateService
{
    public function __construct(private BillingPeriodService $periods) {}

    public function allowedTransitions(): array
    {
        return [
            Subscription::STATUS_TRIAL => [Subscription::STATUS_ACTIVE, Subscription::STATUS_SUSPENDED, Subscription::STATUS_CANCELED],
            Subscription::STATUS_ACTIVE => [Subscription::STATUS_PAST_DUE, Subscription::STATUS_SUSPENDED, Subscription::STATUS_CANCELED, Subscription::STATUS_ACTIVE],
            Subscription::STATUS_PAST_DUE => [Subscription::STATUS_ACTIVE, Subscription::STATUS_SUSPENDED, Subscription::STATUS_CANCELED],
            Subscription::STATUS_SUSPENDED => [Subscription::STATUS_ACTIVE, Subscription::STATUS_CANCELED],
            Subscription::STATUS_CANCELED => [],
        ];
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->allowedTransitions()[$from] ?? [], true);
    }

    public function startTrial(int $companyId, ?int $planId = null): Subscription
    {
        $existing = Subscription::withoutCompanyScope()->where('company_id', $companyId)->first();
        if ($existing) {
            return $existing;
        }

        $plan = $planId
            ? Plan::find($planId)
            : Plan::where('code', config('billing.default_plan', 'starter'))->first();

        if (! $plan) {
            $plan = Plan::where('is_active', true)->orderBy('price_monthly')->first();
        }

        if (! $plan) {
            throw new \RuntimeException('Belum ada paket langganan yang tersedia.');
        }

        $now = $this->periods->now();
        $trialEnd = $this->periods->trialEndsAt($now);

        $subscription = Subscription::withoutCompanyScope()->create([
            'company_id' => $companyId,
            'plan_id' => $plan->id,
            'status' => Subscription::STATUS_TRIAL,
            'billing_cycle' => Subscription::CYCLE_MONTHLY,
            'trial_ends_at' => $trialEnd,
            'current_period_start' => $now,
            'current_period_end' => $trialEnd,
        ]);

        $this->record($subscription, null, Subscription::STATUS_TRIAL, 'trial_started', 'system');

        return $subscription;
    }

    public function transition(Subscription $subscription, string $to, ?string $reason = null, ?string $actor = 'system', ?int $actorId = null, array $meta = []): Subscription
    {
        $from = $subscription->status;

        if ($from === $to) {
            return $subscription;
        }

        if (! $this->canTransition($from, $to)) {
            Log::warning('billing.invalid_transition', [
                'subscription_id' => $subscription->id,
                'from' => $from,
                'to' => $to,
            ]);

            throw new \InvalidArgumentException("Transisi status {$from} ke {$to} tidak diizinkan.");
        }

        $subscription->status = $to;

        if ($to === Subscription::STATUS_PAST_DUE && ! $subscription->grace_ends_at) {
            $subscription->grace_ends_at = $this->periods->graceEndsAt($subscription->current_period_end);
        }

        if ($to === Subscription::STATUS_ACTIVE) {
            $subscription->grace_ends_at = null;
        }

        if ($to === Subscription::STATUS_CANCELED) {
            $subscription->canceled_at = $this->periods->now();
        }

        $subscription->save();

        $this->record($subscription, $from, $to, $reason, $actor, $actorId, $meta);
        $this->forgetCache($subscription->company_id);

        return $subscription;
    }

    public function markPastDue(Subscription $subscription, string $reason = 'period_ended'): Subscription
    {
        return $this->transition($subscription, Subscription::STATUS_PAST_DUE, $reason);
    }

    public function suspend(Subscription $subscription, string $reason = 'suspended', ?string $actor = 'system', ?int $actorId = null): Subscription
    {
        return $this->transition($subscription, Subscription::STATUS_SUSPENDED, $reason, $actor, $actorId);
    }

    public function activate(Subscription $subscription, string $reason = 'payment_received', ?string $actor = 'system', ?int $actorId = null): Subscription
    {
        return $this->transition($subscription, Subscription::STATUS_ACTIVE, $reason, $actor, $actorId);
    }

    public function cancel(Subscription $subscription, ?string $reason = 'canceled_by_owner', ?string $actor = 'owner', ?int $actorId = null): Subscription
    {
        $subscription->cancel_at_period_end = true;

        return $this->transition($subscription, Subscription::STATUS_CANCELED, $reason, $actor, $actorId);
    }

    public function applyPaidInvoice(Subscription $subscription, $invoice, ?string $actor = 'webhook', ?int $actorId = null): Subscription
    {
        $meta = $invoice->meta ?? [];
        $targetPlanId = $meta['plan_id'] ?? null;
        $targetCycle = $meta['billing_cycle'] ?? $subscription->billing_cycle;

        $subscription->current_period_start = Carbon::parse($invoice->period_start)->startOfDay();
        $subscription->current_period_end = Carbon::parse($invoice->period_end)->endOfDay();
        $subscription->billing_cycle = $targetCycle;
        $subscription->grace_ends_at = null;
        $subscription->trial_ends_at = null;
        $subscription->cancel_at_period_end = false;

        if ($targetPlanId) {
            $subscription->plan_id = $targetPlanId;
        }

        if ($subscription->pending_plan_id && (int) $subscription->pending_plan_id === (int) $subscription->plan_id) {
            $subscription->pending_plan_id = null;
            $subscription->pending_billing_cycle = null;
            $subscription->pending_effective_at = null;
        }

        $subscription->save();

        if ($subscription->status !== Subscription::STATUS_ACTIVE) {
            $this->activate($subscription, 'payment_received', $actor, $actorId);
        } else {
            $this->record($subscription, Subscription::STATUS_ACTIVE, Subscription::STATUS_ACTIVE, 'period_renewed', $actor, $actorId);
            $this->forgetCache($subscription->company_id);
        }

        return $subscription->refresh();
    }

    public function applyPendingPlan(Subscription $subscription): Subscription
    {
        if (! $subscription->pending_plan_id) {
            return $subscription;
        }

        if ($subscription->pending_effective_at && $subscription->pending_effective_at->isFuture()) {
            return $subscription;
        }

        $subscription->plan_id = $subscription->pending_plan_id;
        $subscription->billing_cycle = $subscription->pending_billing_cycle ?: $subscription->billing_cycle;
        $subscription->pending_plan_id = null;
        $subscription->pending_billing_cycle = null;
        $subscription->pending_effective_at = null;
        $subscription->save();

        $this->record($subscription, $subscription->status, $subscription->status, 'pending_plan_applied', 'system');
        $this->forgetCache($subscription->company_id);

        return $subscription;
    }

    public function record(Subscription $subscription, ?string $from, string $to, ?string $reason, ?string $actor = 'system', ?int $actorId = null, array $meta = []): void
    {
        SubscriptionEvent::create([
            'company_id' => $subscription->company_id,
            'subscription_id' => $subscription->id,
            'from_status' => $from,
            'to_status' => $to,
            'reason' => $reason,
            'actor' => $actor,
            'actor_id' => $actorId,
            'meta' => $meta ?: null,
        ]);

        Log::info('billing.status_event', [
            'company_id' => $subscription->company_id,
            'subscription_id' => $subscription->id,
            'from' => $from,
            'to' => $to,
            'reason' => $reason,
        ]);
    }

    public function forgetCache(int $companyId): void
    {
        Cache::forget('billing:subscription:'.$companyId);
    }
}
