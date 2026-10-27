<?php

namespace App\Jobs\Billing;

use App\Models\Billing\Subscription;
use App\Services\Billing\BillingInvoiceService;
use App\Services\Billing\BillingPeriodService;
use App\Services\Billing\NotificationService;
use App\Services\Billing\SubscriptionStateService;
use App\Services\Billing\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingDailyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(
        BillingPeriodService $periods,
        BillingInvoiceService $invoices,
        SubscriptionStateService $state,
        SubscriptionService $subscriptions,
        NotificationService $notifications
    ): array {
        $now = $periods->now();
        $leadDays = (int) config('billing.invoice_lead_days', 7);
        $summary = [
            'invoices_created' => 0,
            'past_due' => 0,
            'suspended' => 0,
            'trial_suspended' => 0,
            'expired_invoices' => 0,
            'pending_plans_applied' => 0,
            'notifications_sent' => 0,
        ];

        $upcoming = Subscription::withoutCompanyScope()
            ->with(['plan', 'pendingPlan'])
            ->whereIn('status', [Subscription::STATUS_ACTIVE])
            ->where('current_period_end', '<=', $now->copy()->addDays($leadDays)->endOfDay())
            ->get();

        foreach ($upcoming as $subscription) {
            $invoice = $invoices->createUpcomingInvoice($subscription);
            if ($invoice && $invoice->wasRecentlyCreated) {
                $summary['invoices_created']++;
            }
        }

        $summary['pending_plans_applied'] = $subscriptions->applyPendingPlans();

        $pastDue = Subscription::withoutCompanyScope()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('current_period_end', '<', $now)
            ->get();

        foreach ($pastDue as $subscription) {
            DB::transaction(function () use ($subscription, $state, $now, &$summary) {
                $locked = Subscription::withoutCompanyScope()->lockForUpdate()->find($subscription->id);
                if ($locked && $locked->status === Subscription::STATUS_ACTIVE && $locked->current_period_end < $now) {
                    $state->markPastDue($locked, 'period_ended');
                    $summary['past_due']++;
                }
            });
        }

        $graceExpired = Subscription::withoutCompanyScope()
            ->where('status', Subscription::STATUS_PAST_DUE)
            ->whereNotNull('grace_ends_at')
            ->where('grace_ends_at', '<', $now)
            ->get();

        foreach ($graceExpired as $subscription) {
            DB::transaction(function () use ($subscription, $state, &$summary) {
                $locked = Subscription::withoutCompanyScope()->lockForUpdate()->find($subscription->id);
                if ($locked && $locked->status === Subscription::STATUS_PAST_DUE) {
                    $state->suspend($locked, 'grace_expired');
                    $summary['suspended']++;
                }
            });
        }

        $trialExpired = Subscription::withoutCompanyScope()
            ->where('status', Subscription::STATUS_TRIAL)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', $now)
            ->get();

        foreach ($trialExpired as $subscription) {
            DB::transaction(function () use ($subscription, $state, &$summary) {
                $locked = Subscription::withoutCompanyScope()->lockForUpdate()->find($subscription->id);
                if ($locked && $locked->status === Subscription::STATUS_TRIAL) {
                    $state->suspend($locked, 'trial_expired');
                    $summary['trial_suspended']++;
                }
            });
        }

        $summary['expired_invoices'] = $invoices->expirePending();
        $summary['notifications_sent'] = $notifications->run();

        Log::info('billing.daily_job', $summary);

        return $summary;
    }
}
