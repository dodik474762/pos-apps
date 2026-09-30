<?php

namespace App\Http\Controllers\web\report;

use App\Http\Controllers\Controller;
use App\Models\Transaction\StockClosing;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventoryValuationController extends Controller
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
            'js-1' => asset('assets/js/controllers/report/inventory_valuation.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Report";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Inventory Valuation";
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $lastClosing = StockClosing::orderBy('closing_date', 'desc')->first();
        $data['tanggal'] = isset($request->tanggal) ? $request->tanggal : date('Y-m-d');
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        if (!isset($data['date_start'])) {
            $data['date_start'] = !empty($lastClosing) ? Carbon::parse($lastClosing->closing_date)->format('Y-m-d') : Carbon::parse($data['tanggal'])->startOfMonth()->format('Y-m-d');
        }
        $view = view('web.inventory_valuation.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
