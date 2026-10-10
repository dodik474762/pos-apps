<?php

namespace App\Services\Accounting;

use App\Models\Master\Accounts;
use App\Models\Transaction\AccountingPeriod;
use Illuminate\Support\Facades\DB;

class AccountPeriodBalanceService
{
    public function snapshotPeriod($periodId)
    {
        $result['is_valid'] = false;

        $period = AccountingPeriod::find($periodId);
        if (empty($period)) {
            $result['message'] = 'Periode akuntansi tidak ditemukan';
            return $result;
        }

        if ($period->status !== AccountingPeriodService::STATUS_CLOSED) {
            $result['message'] = 'Hanya periode berstatus CLOSED yang dapat disnapshot';
            return $result;
        }

        return DB::transaction(function () use ($periodId, $period, $result) {
            $previousPeriod = AccountingPeriod::where('id', '<>', $periodId)
                ->where(function ($q) use ($period) {
                    $q->where('year', '<', $period->year)
                        ->orWhere(function ($qq) use ($period) {
                            $qq->where('year', $period->year)
                                ->where('month', '<', $period->month);
                        });
                })
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->first();

            $accounts = Accounts::whereNull('deleted_at')
                ->where('is_active', 1)
                ->orderBy('code')
                ->get();

            foreach ($accounts as $account) {
                $openingBalance = 0.00;
                if (! empty($previousPeriod)) {
                    $prevSnapshot = DB::table('account_period_balances')
                        ->where('account_id', $account->id)
                        ->where('accounting_period_id', $previousPeriod->id)
                        ->first();
                    if (! empty($prevSnapshot)) {
                        $openingBalance = (float) $prevSnapshot->closing_balance;
                    }
                }

                $totals = $this->calculatePeriodTotals($account->id, $periodId);
                $totalDebit = $totals['total_debit'];
                $totalCredit = $totals['total_credit'];

                $closingBalance = $this->calculateClosingBalance($account, $openingBalance, $totalDebit, $totalCredit);

                $existing = DB::table('account_period_balances')
                    ->where('account_id', $account->id)
                    ->where('accounting_period_id', $periodId)
                    ->first();

                if (! empty($existing)) {
                    DB::table('account_period_balances')
                        ->where('id', $existing->id)
                        ->update([
                            'opening_balance' => $openingBalance,
                            'total_debit' => $totalDebit,
                            'total_credit' => $totalCredit,
                            'closing_balance' => $closingBalance,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('account_period_balances')->insert([
                        'account_id' => $account->id,
                        'accounting_period_id' => $periodId,
                        'opening_balance' => $openingBalance,
                        'total_debit' => $totalDebit,
                        'total_credit' => $totalCredit,
                        'closing_balance' => $closingBalance,
                        'created_at' => now(),
                    ]);
                }
            }

            $result['is_valid'] = true;
            return $result;
        });
    }

    protected function calculatePeriodTotals($accountId, $periodId)
    {
        $data = DB::table('journal_details as jd')
            ->join('journal_headers as jh', 'jd.journal_id', '=', 'jh.id')
            ->where('jh.accounting_period_id', $periodId)
            ->where('jh.status', JournalService::STATUS_POSTED)
            ->where('jd.account_id', $accountId)
            ->selectRaw('COALESCE(SUM(jd.debit), 0) as total_debit, COALESCE(SUM(jd.credit), 0) as total_credit')
            ->first();

        return [
            'total_debit' => (float) ($data->total_debit ?? 0),
            'total_credit' => (float) ($data->total_credit ?? 0),
        ];
    }

    protected function calculateClosingBalance($account, $openingBalance, $totalDebit, $totalCredit)
    {
        $normalBalance = strtoupper(trim($account->normal_balance));
        $netMovement = $totalDebit - $totalCredit;

        if ($normalBalance === 'DEBIT') {
            return $openingBalance + $netMovement;
        }

        if ($normalBalance === 'CREDIT') {
            return $openingBalance + $totalCredit - $totalDebit;
        }

        return $openingBalance + $netMovement;
    }
}
