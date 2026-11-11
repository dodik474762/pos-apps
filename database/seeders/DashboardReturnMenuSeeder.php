<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DashboardReturnMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menuId = $this->upsertMenu([
            'nama' => 'Dashboard Return',
            'url' => 'dashboard/return',
            'icon' => 'ri-arrow-go-back-line',
            'parent' => '14',
            'sort' => 18,
        ]);

        $sourceMenuId = DB::table('menu')
            ->where('url', 'transaksi/sales_return')
            ->whereNull('deleted')
            ->value('id');

        $groups = collect();
        if ($sourceMenuId) {
            $groups = DB::table('users_permissions')
                ->where('menu', $sourceMenuId)
                ->whereNull('deleted')
                ->pluck('users_group');
        }

        if ($groups->isEmpty()) {
            $groups = DB::table('users_group')
                ->where('group', 'SUPERADMIN')
                ->whereNull('deleted')
                ->pluck('id');
        }

        foreach ($groups as $groupId) {
            $this->grant($groupId, $menuId);
        }
    }

    protected function upsertMenu(array $menu): int
    {
        $existing = DB::table('menu')
            ->where('url', $menu['url'])
            ->whereNull('deleted')
            ->first();

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
