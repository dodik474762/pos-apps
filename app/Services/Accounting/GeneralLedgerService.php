<?php

namespace App\Services\Accounting;

use App\Models\Master\Accounts;
use App\Models\Transaction\AccountingPeriod;
use Illuminate\Support\Facades\DB;

class GeneralLedgerService
{
    public function getLedger($accountId, $periodId)
    {
        $account = Accounts::whereNull('deleted_at')->find($accountId);
        $period = AccountingPeriod::find($periodId);
        if (empty($account) || empty($period)) {
            return [
                'account' => $account,
                'period' => $period,
                'opening_balance' => 0.00,
                'lines' => [],
                'closing_balance' => 0.00,
            ];
        }

        $openingBalance = $this->getOpeningBalance($account, $period);

        $mutations = $this->getMutations($accountId, $periodId);

        $lines = [];
        $running = $openingBalance;
        $normalBalance = strtoupper(trim($account->normal_balance));

        foreach ($mutations as $mutation) {
            $debit = (float) $mutation->debit;
            $credit = (float) $mutation->credit;
            if ($normalBalance === 'DEBIT') {
                $running += ($debit - $credit);
            } elseif ($normalBalance === 'CREDIT') {
                $running += ($credit - $debit);
            } else {
                $running += ($debit - $credit);
            }

            $lines[] = [
                'journal_date' => $mutation->journal_date,
                'journal_no' => $mutation->journal_no,
                'description' => $mutation->description ?? $mutation->detail_description,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $running,
                'reference' => $mutation->reference_type,
                'reference_id' => $mutation->reference_id,
            ];
        }

        return [
            'account' => $account,
            'period' => $period,
            'opening_balance' => $openingBalance,
            'lines' => $lines,
            'closing_balance' => $running,
        ];
    }

    protected function getOpeningBalance($account, $period)
    {
        $previousPeriod = AccountingPeriod::where(function ($q) use ($period) {
            $q->where('year', '<', $period->year)
                ->orWhere(function ($qq) use ($period) {
                    $qq->where('year', $period->year)
                        ->where('month', '<', $period->month);
                });
        })->orderBy('year', 'desc')->orderBy('month', 'desc')->first();

        if (! empty($previousPeriod) && $previousPeriod->status === AccountingPeriodService::STATUS_CLOSED) {
            $prevSnapshot = DB::table('account_period_balances')
                ->where('account_id', $account->id)
                ->where('accounting_period_id', $previousPeriod->id)
                ->first();
            if (! empty($prevSnapshot)) {
                return (float) $prevSnapshot->closing_balance;
            }
        }

        if ($period->status === AccountingPeriodService::STATUS_CLOSED) {
            $snapshot = DB::table('account_period_balances')
                ->where('account_id', $account->id)
                ->where('accounting_period_id', $period->id)
                ->first();
            if (! empty($snapshot)) {
                return (float) $snapshot->opening_balance;
            }
        }

        $opening = DB::table('journal_details as jd')
            ->join('journal_headers as jh', 'jd.journal_id', '=', 'jh.id')
            ->where('jh.status', JournalService::STATUS_POSTED)
            ->where('jd.account_id', $account->id)
            ->where(function ($q) use ($period) {
                $q->where('jh.journal_date', '<', $period->start_date)
                    ->orWhere('jh.accounting_period_id', '<', $period->id);
            })
            ->selectRaw('COALESCE(SUM(CASE WHEN ? = "DEBIT" THEN jd.debit - jd.credit ELSE jd.credit - jd.debit END), 0) as bal', [strtoupper($account->normal_balance)])
            ->first();

        return (float) ($opening->bal ?? 0);
    }

    protected function getMutations($accountId, $periodId)
    {
        return DB::table('journal_details as jd')
            ->join('journal_headers as jh', 'jd.journal_id', '=', 'jh.id')
            ->where('jh.accounting_period_id', $periodId)
            ->where('jh.status', JournalService::STATUS_POSTED)
            ->where('jd.account_id', $accountId)
            ->select([
                'jh.journal_date',
                'jh.journal_no',
                'jh.reference_type',
                'jh.reference_id',
                'jh.description as header_description',
                'jd.description as detail_description',
                'jd.debit',
                'jd.credit',
            ])
            ->orderBy('jh.journal_date')
            ->orderBy('jh.id')
            ->orderBy('jd.id')
            ->get();
    }
}
