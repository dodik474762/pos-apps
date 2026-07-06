<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\CustomerController as MasterCustomerController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\CustomerCategory;
use App\Models\Master\ProductUomPrice;
use App\Models\Master\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
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
            'js-1' => asset('assets/js/controllers/master/customer.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Data";
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
        return "Customer";
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
        $view = view('web.customer.index', $data);
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
        $view = view('web.customer.index_acc', $data);
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

    public function getChannel()
    {
        $datadb = DB::table('dictionary')->whereNull('deleted')
            ->where('context', 'CHANNEL_OUTLET')
            ->get();

        return $datadb;
    }

    public function getSubChannel()
    {
        $datadb = DB::table('dictionary')->whereNull('deleted')
            ->where('context', 'SUB_CHANNEL_OUTLET')
            ->get();

        return $datadb;
    }

    public function getPasar()
    {
        $datadb = DB::table('pasar')->whereNull('deleted')
            ->get();

        return $datadb;
    }

    public function add()
    {
        $data['data'] = [];
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['data_category'] = CustomerCategory::whereNull('deleted')->get()->toArray();
        $data['akses'] = session('akses');
        $data['company'] = session('id_company');
        $data['stock_customer'] = [];
        $data['data_province'] = Region::whereNull('parent')->whereNull('deleted')->get()->toArray();
        $data['data_price_list'] = $this->getListPriceList();
        $data['tops'] = $this->getTerms();
        $data['channels'] = $this->getChannel();
        $data['sub_channels'] = $this->getSubChannel();
        $data['pasars'] = $this->getPasar();
        $data['product_prices'] = [];
        $view = view('web.customer.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function getListProductUomPrice($customer)
    {
        $data = ProductUomPrice::where('product_uom_price.customer', $customer)
            ->select(['product_uom_price.*', 'p.name as product_name', 'u.name as unit_name'])
            ->join('product as p', 'p.id', 'product_uom_price.product')
            ->join('unit as u', 'u.id', 'product_uom_price.unit')
            ->orderBy('product_uom_price.id')
            ->get();

        return $data;
    }

    public function getListProductStockKunjungan($customer)
    {
        $data = DB::table('stock_customer as sc')->where('sc.customer', $customer)
            ->select(['sc.*', 'p.name as product_name', 'u.name as unit_name', 'p.code as product_code'])
            ->join('product as p', 'p.id', 'sc.product_id')
            ->join('unit as u', 'u.id', 'sc.unit')
            ->orderBy('sc.id', 'desc')
            ->limit(100)
            ->get();

        return $data;
    }

    public function ubah(Request $request)
    {
        $api = new MasterCustomerController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['akses'] = session('akses');
        $data['company'] = session('id_company');
        $data['data_category'] = CustomerCategory::whereNull('deleted')->get()->toArray();
        $data['pasars'] = $this->getPasar();

        $data['stock_customer'] = $this->getListProductStockKunjungan($data['id']);
        $data['product_prices'] = $this->getListProductUomPrice($data['id']);
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['data_province'] = Region::whereNull('parent')->whereNull('deleted')->get()->toArray();
        $data['data_price_list'] = $this->getListPriceList();
        $data['channels'] = $this->getChannel();
        $data['sub_channels'] = $this->getSubChannel();
        $data['tops'] = $this->getTerms();
        $view = view('web.customer.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function detail(Request $request)
    {
        $api = new MasterCustomerController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['akses'] = strtolower(session('akses'));
        $data['company'] = session('id_company');
        $data['data_category'] = CustomerCategory::whereNull('deleted')->get()->toArray();
        $data['pasars'] = $this->getPasar();
        // echo "<pre>";
        // print_r($data);
        // die;
        $data['stock_customer'] = $this->getListProductStockKunjungan($data['id']);
        $data['product_prices'] = $this->getListProductUomPrice($data['id']);
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['data_province'] = Region::whereNull('parent')->whereNull('deleted')->get()->toArray();
        $data['data_price_list'] = $this->getListPriceList();
        $data['channels'] = $this->getChannel();
        $data['sub_channels'] = $this->getSubChannel();
        $data['tops'] = $this->getTerms();
        $data['view_detail'] = 'detail';
        $view = view('web.customer.formdetail', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function getListPriceList()
    {
        $datadb = DB::table('price_list as pl')->whereNull('deleted')->get();
        return $datadb;
    }
}
