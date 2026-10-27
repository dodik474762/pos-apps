<?php

namespace App\Services\Billing;

use App\Models\Billing\Subscription;
use Illuminate\Support\Facades\DB;

class QuotaService
{
    public function __construct(private FeatureService $features) {}

    public function definitions(): array
    {
        return config('billing.quota', []);
    }

    public function limit(string $key, ?int $companyId = null)
    {
        return $this->features->limit($key, $companyId);
    }

    public function used(string $key, ?int $companyId = null, ?Subscription $subscription = null): int
    {
        $companyId = $companyId ?: $this->features->companyId();
        $definition = $this->definitions()[$key] ?? null;

        if (! $companyId || ! $definition) {
            return 0;
        }

        $query = DB::table($definition['table'])->where('company_id', $companyId);

        if (($definition['scope'] ?? 'company') === 'period') {
            $subscription = $subscription ?: $this->features->subscription($companyId);
            if ($subscription) {
                $column = $definition['date_column'] ?? 'created_at';
                $query->whereBetween($column, [
                    $subscription->current_period_start->toDateString(),
                    $subscription->current_period_end->toDateString(),
                ]);
            }
        }

        if (in_array('deleted', $this->columns($definition['table']), true)) {
            $query->whereNull('deleted');
        }

        return (int) $query->count();
    }

    public function status(string $key, ?int $companyId = null): array
    {
        $limit = $this->limit($key, $companyId);
        $used = $this->used($key, $companyId);

        return [
            'key' => $key,
            'label' => $this->definitions()[$key]['label'] ?? $key,
            'used' => $used,
            'limit' => $limit,
            'unlimited' => $limit === null,
            'allowed' => $limit === null || $used < (int) $limit,
        ];
    }

    public function all(?int $companyId = null): array
    {
        $result = [];
        foreach (array_keys($this->definitions()) as $key) {
            $result[$key] = $this->status($key, $companyId);
        }

        return $result;
    }

    public function assertCanAdd(string $key, ?int $companyId = null): void
    {
        $status = $this->status($key, $companyId);

        if (! $status['allowed']) {
            throw new QuotaExceededException(
                $key,
                $status['used'],
                (int) $status['limit'],
                'Kuota '.$status['label'].' sudah tercapai ('.$status['used'].'/'.$status['limit'].'). Silakan upgrade paket.'
            );
        }
    }

    protected function columns(string $table): array
    {
        return DB::getSchemaBuilder()->getColumnListing($table);
    }
}
