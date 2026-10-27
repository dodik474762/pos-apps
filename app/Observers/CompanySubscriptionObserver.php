<?php

namespace App\Observers;

use App\Models\Master\CompanyModel;
use App\Services\Billing\SubscriptionStateService;
use Illuminate\Support\Facades\Log;

class CompanySubscriptionObserver
{
    public function __construct(private SubscriptionStateService $state) {}

    public function created(CompanyModel $company): void
    {
        try {
            $this->state->startTrial((int) $company->id);
        } catch (\Throwable $e) {
            Log::warning('billing.auto_trial_failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
