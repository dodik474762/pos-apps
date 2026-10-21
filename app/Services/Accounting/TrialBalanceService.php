<?php

namespace App\Services\Accounting;

use App\Models\Master\Accounts;
use App\Models\Transaction\AccountingPeriod;
use Illuminate\Support\Facades\DB;

class TrialBalanceService
{
    public function generate($periodId)
    {
        $period = AccountingPeriod::find($periodId);
        if (empty($period)) {
            return [
                'period' => null,
                'rows' => [],
                'total_debit' => 0.00,
                'total_credit' => 0.00,
                'is_balanced' => true,
            ];
        }

        $companyId = $period->company_id ?? null;

        if ($period->status === AccountingPeriodService::STATUS_CLOSED) {
            $rows = $this->generateFromSnapshot($periodId, $companyId);
        } else {
            $rows = $this->generateLive($periodId, $companyId);
        }

        $totalDebit = 0.00;
        $totalCredit = 0.00;
        foreach ($rows as $row) {
            $totalDebit += (float) $row['debit'];
            $totalCredit += (float) $row['credit'];
        }

        return [
            'period' => $period,
            'rows' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    protected function generateFromSnapshot($periodId, $companyId = null)
    {
        $snapshots = DB::table('account_period_balances as apb')
            ->join('accounts as a', 'apb.account_id', '=', 'a.id')
            ->select(['a.id', 'a.code', 'a.name', 'a.normal_balance', 'apb.closing_balance'])
            ->where('apb.accounting_period_id', $periodId)
            ->whereNull('a.deleted_at')
            ->where('a.is_header', 0)
            ->when($companyId !== null && $companyId !== '', function ($q) use ($companyId) {
                $q->where('apb.company_id', $companyId)->where('a.company_id', $companyId);
            })
            ->orderBy('a.code')
            ->get();

        $rows = [];
        foreach ($snapshots as $snap) {
            $closing = (float) $snap->closing_balance;
            if (abs($closing) < 0.0001) {
                continue;
            }

            $normalBalance = strtoupper(trim($snap->normal_balance));
            if ($closing > 0) {
                if ($normalBalance === 'CREDIT') {
                    $debit = 0.00;
                    $credit = abs($closing);
                } else {
                    $debit = abs($closing);
                    $credit = 0.00;
                }
            } elseif ($closing < 0) {
                if ($normalBalance === 'CREDIT') {
                    $debit = abs($closing);
                    $credit = 0.00;
                } else {
                    $debit = 0.00;
                    $credit = abs($closing);
                }
            } else {
                $debit = 0.00;
                $credit = 0.00;
            }

            $rows[] = [
                'account_id' => $snap->id,
                'code' => $snap->code,
                'name' => $snap->name,
                'debit' => $debit,
                'credit' => $credit,
            ];
        }

        return $rows;
    }

    protected function generateLive($periodId, $companyId = null)
    {
        $accounts = Accounts::whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('is_header', 0)
            ->when($companyId !== null && $companyId !== '', fn ($q) => $q->where('company_id', $companyId))
            ->orderBy('code')
            ->get();

        $rows = [];
        foreach ($accounts as $account) {
            $totals = $this->calculatePeriodTotals($account->id, $periodId, $companyId);
            $openingBalance = $this->getOpeningBalanceForLive($account, $periodId, $companyId);
            $closingBalance = $this->calculateClosingBalance($account, $openingBalance, $totals['total_debit'], $totals['total_credit']);
            if (abs($closingBalance) < 0.0001) {
                continue;
            }

            $normalBalance = strtoupper(trim($account->normal_balance));
            if ($closingBalance > 0) {
                if ($normalBalance === 'CREDIT') {
                    $debit = 0.00;
                    $credit = abs($closingBalance);
                } else {
                    $debit = abs($closingBalance);
                    $credit = 0.00;
                }
            } elseif ($closingBalance < 0) {
                if ($normalBalance === 'CREDIT') {
                    $debit = abs($closingBalance);
                    $credit = 0.00;
                } else {
                    $debit = 0.00;
                    $credit = abs($closingBalance);
                }
            } else {
                $debit = 0.00;
                $credit = 0.00;
            }

            $rows[] = [
                'account_id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'debit' => $debit,
                'credit' => $credit,
            ];
        }

        return $rows;
    }

    protected function calculatePeriodTotals($accountId, $periodId, $companyId = null)
    {
        $data = DB::table('journal_details as jd')
            ->join('journal_headers as jh', 'jd.journal_id', '=', 'jh.id')
            ->where('jh.accounting_period_id', $periodId)
            ->where('jh.status', JournalService::STATUS_POSTED)
            ->where('jd.account_id', $accountId)
            ->when($companyId !== null && $companyId !== '', fn ($q) => $q->where('jh.company_id', $companyId))
            ->selectRaw('COALESCE(SUM(jd.debit), 0) as total_debit, COALESCE(SUM(jd.credit), 0) as total_credit')
            ->first();

        return [
            'total_debit' => (float) ($data->total_debit ?? 0),
            'total_credit' => (float) ($data->total_credit ?? 0),
        ];
    }

    protected function getOpeningBalanceForLive($account, $periodId, $companyId = null)
    {
        $period = AccountingPeriod::find($periodId);
        if (empty($period)) {
            return 0.00;
        }

        if ($companyId === null || $companyId === '') {
            $companyId = $period->company_id ?? null;
        }

        $previousPeriod = AccountingPeriod::where(function ($q) use ($period) {
            $q->where('year', '<', $period->year)
                ->orWhere(function ($qq) use ($period) {
                    $qq->where('year', $period->year)
                        ->where('month', '<', $period->month);
                });
        })
            ->when($companyId !== null && $companyId !== '', fn ($q) => $q->where('company_id', $companyId))
            ->orderBy('year', 'desc')->orderBy('month', 'desc')->first();

        if (! empty($previousPeriod) && $previousPeriod->status === AccountingPeriodService::STATUS_CLOSED) {
            $prevSnapshot = DB::table('account_period_balances')
                ->where('account_id', $account->id)
                ->where('accounting_period_id', $previousPeriod->id)
                ->when($companyId !== null && $companyId !== '', fn ($q) => $q->where('company_id', $companyId))
                ->first();
            if (! empty($prevSnapshot)) {
                return (float) $prevSnapshot->closing_balance;
            }
        }

        return 0.00;
    }

    protected function calculateClosingBalance($account, $openingBalance, $totalDebit, $totalCredit)
    {
        $normalBalance = strtoupper(trim($account->normal_balance));
        if ($normalBalance === 'CREDIT') {
            return $openingBalance + $totalCredit - $totalDebit;
        }

        return $openingBalance + $totalDebit - $totalCredit;
    }
}
