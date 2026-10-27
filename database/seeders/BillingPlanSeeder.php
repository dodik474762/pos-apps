<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'starter',
                'name' => 'Starter',
                'price_monthly' => 99000,
                'price_yearly' => 990000,
                'limits' => json_encode(['users' => 5, 'warehouses' => 2, 'invoices_per_month' => 500]),
                'features' => json_encode(['multi_warehouse']),
                'is_active' => true,
            ],
            [
                'code' => 'pro',
                'name' => 'Pro',
                'price_monthly' => 299000,
                'price_yearly' => 2990000,
                'limits' => json_encode(['users' => 20, 'warehouses' => 10, 'invoices_per_month' => 5000]),
                'features' => json_encode(['salesforce', 'multi_warehouse', 'advanced_report']),
                'is_active' => true,
            ],
            [
                'code' => 'business',
                'name' => 'Business',
                'price_monthly' => 999000,
                'price_yearly' => 9990000,
                'limits' => json_encode(['users' => null, 'warehouses' => null, 'invoices_per_month' => null]),
                'features' => json_encode(['salesforce', 'multi_warehouse', 'advanced_report', 'priority_support']),
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            if (DB::table('plans')->where('code', $plan['code'])->exists()) {
                continue;
            }

            DB::table('plans')->insert(array_merge($plan, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
