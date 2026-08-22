<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\CustomerLimitTopController as MasterCustomerLimitTopController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerLimitTopController extends Controller
{
    //
    public $akses_menu = [];
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/js/controllers/master/customer_limit_top.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Transaksi";
    }

    public function getTitleParentAcc()
    {
        return "Approval";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Pengajuan Limit & TOP";
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $data['akses_session'] = session('akses');
        // echo '<pre>';
        // print_r(session()->all());die;
        $view = view('web.customer_limit_top.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function index_acc()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParentAcc();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r(session()->all());die;
        $view = view('web.customer_limit_top.index_acc', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParentAcc();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function getTerms()
    {
        $datadb = DB::table('term_of_payment')->whereNull('deleted')
            ->orderBy('nilai', 'asc')
            ->get();

        return $datadb;
    }

    public function getListCustomer()
    {
        $datadb = DB::table('customer as c')->whereNull('deleted')
            ->select(['c.id', 'c.code', 'c.nama_customer'])
            ->orderBy('c.nama_customer', 'asc')
            ->get();

        return $datadb;
    }

    public function add()
    {
        $api = new MasterCustomerLimitTopController();
        $data['data'] = [];
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = session('akses');
        $data['tops'] = $this->getTerms();
        $data['customers'] = $this->getListCustomer();

        $view = view('web.customer_limit_top.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new MasterCustomerLimitTopController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['akses'] = session('akses');
        $data['tops'] = $this->getTerms();
        $data['customers'] = $this->getListCustomer();
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.customer_limit_top.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function detail(Request $request)
    {
        $api = new MasterCustomerLimitTopController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['akses'] = strtolower(session('akses'));
        $data['tops'] = $this->getTerms();
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParentAcc();
        $data['view_detail'] = 'detail';
        $view = view('web.customer_limit_top.formdetail', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParentAcc();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
