<?php

namespace App\Services\Accounting;

use App\Models\Transaction\AccountMappingRules;
use Illuminate\Support\Facades\DB;

/**
 * Penghitung akun dari tabel account_mapping_rules.
 *
 * Pencarian dilakukan dari rule paling spesifik ke rule paling umum:
 *   1. product_category_id + warehouse_id
 *   2. product_category_id + warehouse_id NULL
 *   3. product_category_id NULL + warehouse_id
 *   4. product_category_id NULL + warehouse_id NULL (default rule)
 *
 * Bila tidak ada rule aktif yang cocok, proses WAJIB berhenti dengan
 * JournalValidationException.
 */
class AccountMappingResolver
{
    /**
     * Mengembalikan account_id hasil resolusi mapping.
     *
     * @param  string  $transactionType  mis. DELIVERY_ORDER
     * @param  string  $role  mis. COGS, INVENTORY
     * @param  int|null  $productCategoryId  opsional, null = semua kategori
     * @param  int|null  $warehouseId  opsional, null = semua warehouse
     * @return int
     *
     * @throws JournalValidationException
     */
    public function resolve($transactionType, $role, $productCategoryId = null, $warehouseId = null)
    {
        $transactionType = trim((string) $transactionType);
        $role = trim((string) $role);

        $categoryId = $this->normalizeKey($productCategoryId);
        $warehouseKey = $this->normalizeKey($warehouseId);

        $rule = $this->findRule($transactionType, $role, $categoryId, $warehouseKey);

        if (empty($rule)) {
            throw new JournalValidationException(
                'Account Mapping untuk ' . $transactionType . ' / ' . $role . ' belum tersedia'
                . $this->describeScope($categoryId, $warehouseKey)
                . '. Tambahkan di menu Master > Account Mapping Rules '
                . '(Transaction Type ' . $transactionType . ', Role ' . $role . ') sebelum membuat jurnal.'
            );
        }

        return $this->assertUsableAccount($rule, $transactionType, $role);
    }

    /**
     * Mencoba urutan fallback dari paling spesifik ke paling umum.
     */
    protected function findRule($transactionType, $role, $categoryId, $warehouseId)
    {
        foreach ($this->buildScopes($categoryId, $warehouseId) as $scope) {
            $query = AccountMappingRules::where('transaction_type', $transactionType)
                ->where('account_role', $role)
                ->where('is_active', 1)
                ->whereNull('deleted_at');

            $query->where(function ($q) use ($scope) {
                $q->where(function ($q2) use ($scope) {
                    $q2->where('product_category_id', $scope['category']);
                    $q2->where('warehouse_id', $scope['warehouse']);
                })->orWhere(function ($q2) use ($scope) {
                    $q2->where('product_category_id', $scope['category']);
                    $q2->whereNull('warehouse_id');
                })->orWhere(function ($q2) use ($scope) {
                    $q2->whereNull('product_category_id');
                    $q2->where('warehouse_id', $scope['warehouse']);
                })->orWhere(function ($q2) {
                    $q2->whereNull('product_category_id');
                    $q2->whereNull('warehouse_id');
                });
            });

            $rule = $query->orderBy('id', 'asc')->first();
            if (! empty($rule)) {
                return $rule;
            }
        }

        return null;
    }

    /**
     * Urutan_scope resolusi, dari paling spesifik ke paling umum.
     */
    protected function buildScopes($categoryId, $warehouseId)
    {
        $scopes = [];

        if ($categoryId !== null && $warehouseId !== null) {
            $scopes[] = ['category' => $categoryId, 'warehouse' => $warehouseId];
        }
        if ($categoryId !== null) {
            $scopes[] = ['category' => $categoryId, 'warehouse' => null];
        }
        if ($warehouseId !== null) {
            $scopes[] = ['category' => null, 'warehouse' => $warehouseId];
        }
        $scopes[] = ['category' => null, 'warehouse' => null];

        return $scopes;
    }

    /**
     * Akun hasil mapping wajib ada, bukan header, dan aktif.
     */
    protected function assertUsableAccount($rule, $transactionType, $role)
    {
        $account = DB::table('accounts')
            ->select(['id', 'code', 'name', 'is_header', 'is_active'])
            ->where('id', $rule->account_id)
            ->whereNull('deleted_at')
            ->first();

        if (empty($account)) {
            throw new JournalValidationException(
                'Akun untuk ' . $transactionType . ' / ' . $role . ' tidak ditemukan atau sudah dihapus.'
            );
        }
        if ((int) $account->is_header === 1) {
            throw new JournalValidationException(
                'Akun ' . $account->code . ' untuk ' . $transactionType . ' / ' . $role . ' adalah header account.'
            );
        }
        if ((int) $account->is_active !== 1) {
            throw new JournalValidationException(
                'Akun ' . $account->code . ' untuk ' . $transactionType . ' / ' . $role . ' tidak aktif.'
            );
        }

        return (int) $account->id;
    }

    protected function normalizeKey($value)
    {
        if ($value === null || $value === '' || $value === '0' || (int) $value <= 0) {
            return null;
        }
        return (int) $value;
    }

    protected function describeScope($categoryId, $warehouseId)
    {
        $parts = [];
        $parts[] = $categoryId === null ? 'semua kategori' : 'kategori ' . $categoryId;
        $parts[] = $warehouseId === null ? 'semua warehouse' : 'warehouse ' . $warehouseId;
        return ' (' . implode(', ', $parts) . ')';
    }
}
