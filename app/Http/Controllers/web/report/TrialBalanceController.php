<?php

namespace App\Http\Controllers\web\report;

use App\Http\Controllers\Controller;
use App\Models\Transaction\AccountingPeriod;
use Illuminate\Http\Request;

class TrialBalanceController extends Controller
{
    public $akses_menu = [];

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
    }

    public function getHeaderCss()
    {
        return [
            'js-1' => asset('assets/js/controllers/report/trial_balance.js'),
        ];
    }

    public function getTitleParent()
    {
        return "Accounting";
    }

    public function getTitle()
    {
        return "Trial Balance";
    }

    public function index(Request $request)
    {
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $data['periods'] = AccountingPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get();

        $monthList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        foreach ($data['periods'] as $p) {
            $p->label = ($monthList[$p->month] ?? $p->month) . ' ' . $p->year . ' (' . $p->status . ')';
        }

        $view = view('web.trial_balance.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
