<?php

namespace App\Console\Commands\Billing;

use App\Jobs\Billing\BillingDailyJob;
use Illuminate\Console\Command;

class RunBillingDaily extends Command
{
    protected $signature = 'billing:daily';

    protected $description = 'Menjalankan proses harian langganan (tagihan, status, notifikasi)';

    public function handle(BillingDailyJob $job): int
    {
        $summary = $job->handle(
            app(\App\Services\Billing\BillingPeriodService::class),
            app(\App\Services\Billing\BillingInvoiceService::class),
            app(\App\Services\Billing\SubscriptionStateService::class),
            app(\App\Services\Billing\SubscriptionService::class),
            app(\App\Services\Billing\NotificationService::class),
        );

        $this->table(array_keys($summary), [array_values($summary)]);

        return self::SUCCESS;
    }
}
