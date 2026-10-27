<?php

namespace App\Console\Commands\Billing;

use App\Models\Billing\PaymentWebhookLog;
use App\Services\Billing\WebhookService;
use Illuminate\Console\Command;

class ReplayWebhook extends Command
{
    protected $signature = 'billing:replay-webhook {id}';

    protected $description = 'Memproses ulang payment webhook log';

    public function handle(WebhookService $webhooks): int
    {
        $log = PaymentWebhookLog::find($this->argument('id'));

        if (! $log) {
            $this->error('Webhook log tidak ditemukan.');

            return self::FAILURE;
        }

        try {
            $result = $webhooks->replay($log);
            $this->info(json_encode($result['body']));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
