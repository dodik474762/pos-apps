<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Services\ClosingPeriodQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClosingPeriodController extends Controller
{
    public $akses_menu = [];

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/js/controllers/monitoring/closing_period.js'),
        );
    }

    public function getTitleParent()
    {
        return "Monitoring";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Closing Period";
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $data['tabs'] = ClosingPeriodQuery::tabs();

        // Filter periode: period accounting OPEN yang dipilih, default bulan berjalan.
        $period = ClosingPeriodQuery::resolvePeriod($request->query('period_id'));
        $dateStart = $period['start'];
        $dateEnd = $period['end'];
        $data['period'] = $period;
        $data['periods'] = ClosingPeriodQuery::openPeriods();

        $data['summary'] = [];
        foreach (ClosingPeriodQuery::types() as $type) {
            $query = ClosingPeriodQuery::make($type, $dateStart, $dateEnd);
            $data['summary'][$type] = !empty($query) ? (clone $query)->count() : 0;
        }

        $data['period_label'] = $period['label'];
        $data['period_start'] = $dateStart;
        $data['period_end'] = $dateEnd;
        $data['stock_closing'] = DB::table('stock_closing')
            ->whereBetween('closing_date', [$dateStart, $dateEnd])
            ->orderBy('closing_date', 'desc')
            ->first();

        $view = view('web.closing_period.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
