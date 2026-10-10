<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use App\Models\Transaction\AccountingPeriod;
use App\Services\Accounting\TrialBalanceService;
use Illuminate\Http\Request;

class TrialBalanceController extends Controller
{
    protected $service;

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->service = new TrialBalanceService();
    }

    public function getData(Request $request)
    {
        $periodId = $request->input('accounting_period_id');
        $result = $this->service->generate($periodId);

        return response()->json([
            'period' => $result['period'],
            'data' => $result['rows'],
            'total_debit' => $result['total_debit'],
            'total_credit' => $result['total_credit'],
            'is_balanced' => $result['is_balanced'],
            'recordsTotal' => count($result['rows']),
            'recordsFiltered' => count($result['rows']),
            'draw' => (int) $request->input('draw', 1),
        ]);
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
