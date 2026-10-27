<?php

namespace App\Console\Commands\Billing;

use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Services\Billing\BillingPeriodService;
use App\Services\Billing\SubscriptionStateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillSubscriptions extends Command
{
    protected $signature = 'billing:backfill {--plan=business : Kode paket untuk company lama} {--trial : Buat trial, bukan ACTIVE}';

    protected $description = 'Membuat langganan untuk company yang belum memiliki langganan';

    public function handle(BillingPeriodService $periods, SubscriptionStateService $state): int
    {
        $planCode = $this->option('plan');
        $plan = Plan::where('code', $planCode)->first();

        if (! $plan) {
            $this->error("Paket [{$planCode}] tidak ditemukan. Jalankan seeder BillingSeeder dahulu.");

            return self::FAILURE;
        }

        $companies = DB::table('company')->whereNull('deleted')->get();
        $created = 0;

        foreach ($companies as $company) {
            $exists = Subscription::withoutCompanyScope()->where('company_id', $company->id)->exists();
            if ($exists) {
                continue;
            }

            if ($this->option('trial')) {
                $state->startTrial($company->id, $plan->id);
            } else {
                $now = $periods->now();
                $subscription = Subscription::withoutCompanyScope()->create([
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                    'status' => Subscription::STATUS_ACTIVE,
                    'billing_cycle' => Subscription::CYCLE_MONTHLY,
                    'current_period_start' => $now,
                    'current_period_end' => $now->copy()->addYearNoOverflow(),
                ]);

                $state->record($subscription, null, Subscription::STATUS_ACTIVE, 'backfill_existing_company', 'system');
            }

            $created++;
            $this->line("Company #{$company->id} {$company->nama_company} -> langganan dibuat");
        }

        $this->info("Selesai. {$created} langganan dibuat.");

        return self::SUCCESS;
    }
}
