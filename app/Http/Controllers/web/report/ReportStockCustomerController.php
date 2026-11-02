<?php

namespace App\Http\Controllers\web\report;

use App\Http\Controllers\Controller;
use App\Models\Master\Customer;
use App\Models\Master\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReportStockCustomerController extends Controller
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
            'js-1' => asset('assets/js/controllers/report/report_stock_customer.js'),
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
        return "Report Stock Customer";
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;

        $data['customers'] = Customer::whereNull('customer.deleted')
            ->select(['customer.*', 'top.nilai as top_value'])
            ->leftJoin('term_of_payment as top', 'top.id', '=', 'customer.payment_terms')
            ->limit(100)
            ->get();

        $data['vendors'] = Vendor::whereNull('deleted')
            ->when(session('id_company') !== null && session('id_company') !== '', fn($q) => $q->where('company_id', session('id_company')))
            ->orderBy('nama_vendor')
            ->get(['id', 'nama_vendor']);

        $view = view('web.report_stock_customer.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
