<?php

namespace App\Services\Billing;

use App\Models\Billing\Subscription;
use Carbon\Carbon;

class BillingPeriodService
{
    public function now(): Carbon
    {
        return Carbon::now(config('billing.timezone', 'Asia/Jakarta'));
    }

    public function addCycle(Carbon $start, string $cycle): Carbon
    {
        return $cycle === Subscription::CYCLE_YEARLY
            ? $start->copy()->addYearNoOverflow()
            : $start->copy()->addMonthNoOverflow();
    }

    public function periodEndFor(Carbon $start, string $cycle): Carbon
    {
        return $this->addCycle($start, $cycle);
    }

    public function graceEndsAt(Carbon $periodEnd): Carbon
    {
        return $periodEnd->copy()->addDays((int) config('billing.grace_days', 7));
    }

    public function trialEndsAt(Carbon $start): Carbon
    {
        return $start->copy()->addDays((int) config('billing.trial_days', 14));
    }

    public function daysUntil(Carbon $date): int
    {
        return (int) $this->now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);
    }
}
