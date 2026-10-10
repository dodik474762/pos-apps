<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use App\Models\Master\Accounts;
use App\Models\Transaction\AccountingPeriod;
use App\Services\Accounting\GeneralLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralLedgerController extends Controller
{
    protected $service;

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->service = new GeneralLedgerService();
    }

    public function getData(Request $request)
    {
        $accountId = $request->input('account_id');
        $periodId = $request->input('accounting_period_id');
        $account = Accounts::whereNull('deleted_at')->find($accountId);
        $period = AccountingPeriod::find($periodId);

        $result = $this->service->getLedger($accountId, $periodId);

        return response()->json([
            'account' => $account,
            'period' => $period,
            'opening_balance' => $result['opening_balance'],
            'data' => $result['lines'],
            'closing_balance' => $result['closing_balance'],
            'recordsTotal' => count($result['lines']),
            'recordsFiltered' => count($result['lines']),
            'draw' => (int) $request->input('draw', 1),
        ]);
    }

    public function getAccountList(Request $request)
    {
        $accounts = Accounts::select('id', 'code', 'name', 'normal_balance')
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('is_header', 0)
            ->orderBy('code')
            ->get();

        return response()->json(['data' => $accounts]);
    }

    public function getPeriodList(Request $request)
    {
        $periods = AccountingPeriod::orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $monthList = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $data = [];
        foreach ($periods as $p) {
            $data[] = [
                'id' => $p->id,
                'label' => ($monthList[$p->month] ?? $p->month) . ' ' . $p->year . ' (' . $p->status . ')',
            ];
        }

        return response()->json(['data' => $data]);
    }
}
