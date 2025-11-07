<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\ProductController as MasterProductController;
use App\Http\Controllers\Controller;
use App\Models\Master\CustomerCategory;
use App\Models\Master\ProductCatalog;
use App\Models\Master\ProductDisc;
use App\Models\Master\ProductFreeGood;
use App\Models\Master\ProductLog;
use App\Models\Master\ProductType;
use App\Models\Master\ProductUom;
use App\Models\Master\ProductUomPrice;
use App\Models\Master\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductController extends Controller
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
            'js-1' => asset('assets/js/lib/number-divider.min.js'),
            'js-2' => asset('assets/js/controllers/master/product.js'),
            'js-3' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Master";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Product";
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.product.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function getStock($product = 0){
        $datadb = DB::table('product_stock as ps')
        ->select(['ps.*', 'u.name as unit_name', 'w.name as warehouse_name'])
        ->join('unit as u', 'u.id', 'ps.unit')
        ->join('warehouse as w', 'w.id', 'ps.warehouse')
        ->where('ps.product', $product)
        ->get();
        return $datadb;
    }

    public function getCostProduct($product = 0){
        $datadb = DB::table('product_uom_cost as ps')
        ->select(['ps.*', 'u.name as unit_name', 'v.nama_vendor'])
        ->join('unit as u', 'u.id', 'ps.unit_id')
        ->join('vendor as v', 'v.id', 'ps.vendor')
        ->where('ps.product', $product)
        ->get();
        return $datadb;
    }

    public function add()
    {
        $data['data'] = [];
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['product_type'] = ProductType::whereNull('deleted')->get()->toArray();
        $data['product_unit'] = Unit::whereNull('deleted')->get()->toArray();
        $data['product_logs'] = [];
        $data['product_stocks'] = [];
        $view = view('web.product.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function getProductLog($product)
    {
        $data = ProductLog::where('product_log.product', $product)
            ->select([
                'product_log.*',
                'usr.username'
            ])
            ->join('users as usr', 'usr.id', 'product_log.creator')
            ->get()->toArray();
        return $data;
    }

    public function getListProductUom($product)
    {
        $data = ProductUom::where('product', $product)
            ->orderBy('level')
            ->get();

        return $data;
    }

    public function getListProductUomPrice($product)
    {
        $data = ProductUomPrice::where('product', $product)
            ->orderBy('id')
            ->get();

        return $data;
    }

    public function getListProductDiscStrata($product)
    {
        $data = ProductDisc::where('product', $product)
            ->orderBy('id')
            ->get();

        return $data;
    }

    public function getListProductDiscFree($product)
    {
        $data = ProductFreeGood::where('product', $product)
            ->orderBy('id')
            ->get();

        return $data;
    }


    public function getListPriceList()
    {
        $datadb = DB::table('price_list as pl')->whereNull('deleted')->get();
        return $datadb;
    }

    public function getListSatuanUom($product)
    {
        $product_uoms = ProductUom::where('product', $product)
            ->select(['u.name as unit_dasar_name', 'ut.name as unit_tujuan_name', 'product_uom.*'])
            ->join('unit as u', 'u.id', 'product_uom.unit_dasar')
            ->join('unit as ut', 'ut.id', 'product_uom.unit_tujuan')
            ->get();

        $data_satuan = [];
        foreach ($product_uoms as $key => $value) {
            $data_satuan[] = $value->unit_dasar . ' // ' . $value->unit_dasar_name;
            $data_satuan[] = $value->unit_tujuan . ' // ' . $value->unit_tujuan_name;
        }
        $data_satuan = collect($data_satuan)->unique()->values()->all();
        $result_satuan = [];
        foreach ($data_satuan as $key => $value) {
            list($id, $name) = explode('//', $value);
            $result_satuan[] = [
                'id' => trim($id),
                'name' => trim($name)
            ];
        }
        return $result_satuan;
    }

    public function ubah(Request $request)
    {
        $api = new MasterProductController();
        $data = $request->all();

        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['product_type'] = ProductType::whereNull('deleted')->get()->toArray();
        $data['product_unit'] = Unit::whereNull('deleted')->get()->toArray();

        $data['data_satuan'] = Unit::whereNull('deleted')->get();
        $data['data_satuan_uom'] = $this->getListSatuanUom($data['id']);
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['product_logs'] = $this->getProductLog($data['id']);
        $data['product_uoms'] = $this->getListProductUom($data['id']);
        $data['tipe_price'] = $this->getListPriceList();
        $data['product_prices'] = $this->getListProductUomPrice($data['id']);
        $data['data_customer_category'] = CustomerCategory::whereNull('deleted')->get();
        $data['data_disc_tipe'] = ['percent', 'nominal'];
        $data['product_disc_strata'] = $this->getListProductDiscStrata($data['id']);
        $data['product_disc_free'] = $this->getListProductDiscFree($data['id']);
        $data['product_stocks'] = $this->getStock($data['id']);
        $data['product_costs'] = $this->getCostProduct($data['id']);
        $view = view('web.product.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
