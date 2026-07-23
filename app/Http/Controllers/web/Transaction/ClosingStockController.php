<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\StockClosing;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClosingStockController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/closing_stock.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Transaction";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Closing Stock";
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $data['tanggal'] = isset($request->tanggal) ? $request->tanggal : date('Y-m-d');
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $data['date_start'] = Carbon::parse($data['tanggal'])->startOfMonth()->format('Y-m-d');
        $data['closing'] = StockClosing::where('closing_date', $data['tanggal'])->first();
        $view = view('web.closing_stock.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
