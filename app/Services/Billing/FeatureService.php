<?php

namespace App\Services\Billing;

use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use Illuminate\Support\Facades\Cache;

class FeatureService
{
    public function companyId(): ?int
    {
        $companyId = session('id_company');

        return $companyId !== null && $companyId !== '' ? (int) $companyId : null;
    }

    public function subscription(?int $companyId = null): ?Subscription
    {
        $companyId = $companyId ?: $this->companyId();

        if (! $companyId) {
            return null;
        }

        $ttl = (int) config('billing.cache.subscription_ttl', 60);

        return Cache::remember('billing:subscription:'.$companyId, $ttl, function () use ($companyId) {
            return Subscription::withoutCompanyScope()
                ->with('plan')
                ->where('company_id', $companyId)
                ->first();
        });
    }

    public function plan(?int $companyId = null): ?Plan
    {
        return $this->subscription($companyId)?->plan;
    }

    public function can(string $feature, ?int $companyId = null): bool
    {
        $plan = $this->plan($companyId);

        if (! $plan) {
            return false;
        }

        return $plan->hasFeature($feature);
    }

    public function limit(string $key, ?int $companyId = null)
    {
        return $this->plan($companyId)?->limit($key);
    }

    public function hasFullAccess(?int $companyId = null): bool
    {
        $subscription = $this->subscription($companyId);

        if (! $subscription) {
            return true;
        }

        return $subscription->hasFullAccess();
    }
}
