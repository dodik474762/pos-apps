<?php

namespace App\Http\Controllers\web\report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportStockController extends Controller
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
            'js-1' => asset('assets/js/controllers/report/report_stock.js'),
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
        return "Report Stock + SCD";
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $view = view('web.report_stock.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

}
