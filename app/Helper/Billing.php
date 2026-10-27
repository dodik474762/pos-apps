<?php

use App\Services\Billing\FeatureService;

if (! function_exists('tenant')) {
    function tenant(): FeatureService
    {
        return app(FeatureService::class);
    }
}

if (! function_exists('tenant_can')) {
    function tenant_can(string $feature, ?int $companyId = null): bool
    {
        return tenant()->can($feature, $companyId);
    }
}

if (! function_exists('tenant_plan')) {
    function tenant_plan(?int $companyId = null)
    {
        return tenant()->plan($companyId);
    }
}

if (! function_exists('tenant_subscription')) {
    function tenant_subscription(?int $companyId = null)
    {
        return tenant()->subscription($companyId);
    }
}
