<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\AccountingPeriodsController as MasterAccountingPeriodsController;
use App\Http\Controllers\Controller;
use App\Services\Accounting\AccountingPeriodService;
use Illuminate\Http\Request;

class AccountingPeriodsController extends Controller
{
    public $akses_menu = [];
    protected $periodService;

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
        $this->periodService = new AccountingPeriodService();
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/js/controllers/master/accounting_periods.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Accounting";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Accounting Periods";
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $view = view('web.accounting_periods.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function add()
    {
        $data['data'] = [];
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['status_list'] = $this->periodService->getStatusList();
        $data['month_list'] = $this->periodService->getMonthList();
        $view = view('web.accounting_periods.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new MasterAccountingPeriodsController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['status_list'] = $this->periodService->getStatusList();
        $data['month_list'] = $this->periodService->getMonthList();
        $view = view('web.accounting_periods.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
