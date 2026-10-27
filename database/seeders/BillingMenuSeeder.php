<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingMenuSeeder extends Seeder
{
    public function run(): void
    {
        $customerMenus = [
            ['nama' => 'Langganan Saya', 'url' => 'billing/subscription', 'icon' => 'ri-bank-card-line', 'sort' => 1],
            ['nama' => 'Tagihan Saya', 'url' => 'billing/invoices', 'icon' => 'ri-file-list-3-line', 'sort' => 2],
        ];

        $adminMenus = [
            ['nama' => 'Langganan Company', 'url' => 'admin/billing/subscriptions', 'icon' => 'ri-building-4-line', 'sort' => 1],
            ['nama' => 'Paket Langganan', 'url' => 'admin/billing/plans', 'icon' => 'ri-price-tag-3-line', 'sort' => 2],
            ['nama' => 'Tagihan Langganan', 'url' => 'admin/billing/invoices', 'icon' => 'ri-file-list-3-line', 'sort' => 3],
            ['nama' => 'Webhook Log', 'url' => 'admin/billing/webhooks', 'icon' => 'ri-webhook-line', 'sort' => 4],
        ];

        $customerIds = [];
        foreach ($customerMenus as $menu) {
            $customerIds[] = $this->upsertMenu(array_merge($menu, ['parent' => 'billing_customer']));
        }

        $adminIds = [];
        foreach ($adminMenus as $menu) {
            $adminIds[] = $this->upsertMenu(array_merge($menu, ['parent' => 'billing_admin']));
        }

        $groups = DB::table('users_group')->whereNull('deleted')->pluck('id');
        foreach ($groups as $groupId) {
            foreach ($customerIds as $menuId) {
                $this->grant($groupId, $menuId);
            }
        }

        $superadminGroup = DB::table('users_group')->where('group', 'SUPERADMIN')->whereNull('deleted')->first();
        if ($superadminGroup) {
            foreach ($adminIds as $menuId) {
                $this->grant($superadminGroup->id, $menuId);
            }
        }
    }

    protected function upsertMenu(array $menu): int
    {
        $existing = DB::table('menu')->where('url', $menu['url'])->whereNull('deleted')->first();

        if ($existing) {
            DB::table('menu')->where('id', $existing->id)->update([
                'nama' => $menu['nama'],
                'icon' => $menu['icon'],
                'parent' => $menu['parent'],
                'sort' => $menu['sort'],
                'updated_at' => now(),
            ]);

            return (int) $existing->id;
        }

        return (int) DB::table('menu')->insertGetId([
            'nama' => $menu['nama'],
            'icon' => $menu['icon'],
            'menu_alias' => null,
            'url' => $menu['url'],
            'parent' => $menu['parent'],
            'sort' => $menu['sort'],
            'routing' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function grant(int $groupId, int $menuId): void
    {
        $exists = DB::table('users_permissions')
            ->where('users_group', $groupId)
            ->where('menu', $menuId)
            ->whereNull('deleted')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('users_permissions')->insert([
            'users_group' => $groupId,
            'menu' => $menuId,
            'insert' => 1,
            'update' => 1,
            'delete' => 1,
            'view' => 1,
            'print' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
