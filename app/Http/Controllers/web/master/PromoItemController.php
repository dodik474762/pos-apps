<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\PromoItemController as MasterPromoItemController;
use App\Http\Controllers\Controller;
use App\Models\Master\Dictionary;
use App\Models\Master\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromoItemController extends Controller
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
            'js-1' => asset('assets/js/controllers/master/promo_item.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
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
        return "Promo Item";
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.promo_item.index', $data);
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
        $data['list_approval'] = Dictionary::whereNull('deleted')->where('context', 'ROUTE_MODULE')->get()->toArray();
        $data['list_module'] = Menu::whereNull('deleted')->whereNotNull('parent')->where('routing', 1)->whereNull('deleted')->get()->toArray();
        $data['groups'] = Dictionary::where('context', 'GROUP')->whereNull('deleted')->get()->toArray();

        $data['data_disc_tipe'] = ['percent', 'nominal'];
        $data['routing_item'] = [];
        $data['routing_reminder_item'] = [];
        $view = view('web.promo_item.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new MasterPromoItemController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;

        $data['data_disc_tipe'] = ['percent', 'nominal'];
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['list_approval'] = Dictionary::whereNull('deleted')->where('context', 'ROUTE_MODULE')->get()->toArray();
        $data['list_module'] = Menu::whereNull('deleted')->whereNotNull('parent')->where('routing', 1)->whereNull('deleted')->get()->toArray();
        $data['groups'] = Dictionary::where('context', 'GROUP')->whereNull('deleted')->get()->toArray();
        $data['promo_item'] = DB::table('product_promo_item_detail')->where('product_promo_item_detail.product_promo_item', $data['id'])
            ->select(['product_promo_item_detail.*', 'p.name as product_name', 'u.name as unit_name'])
            ->join('product as p', 'p.id', '=', 'product_promo_item_detail.product')
            ->join('unit as u', 'u.id', '=', 'product_promo_item_detail.unit')
            ->orderBy('product_promo_item_detail.id', 'asc')
            ->get()->toArray();
        // echo '<pre>';
        // print_r($data['promo_item']);die;
        $data['product_free'] = DB::table('product_promo_item_detail_free')->where('product_promo_item_detail_free.product_promo_item', $data['id'])
            ->select(['product_promo_item_detail_free.*'])->get()->toArray();
        $view = view('web.promo_item.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
