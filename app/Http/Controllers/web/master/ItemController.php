<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\ItemController as MasterItemController;
use App\Http\Controllers\Controller;
use App\Models\Master\ItemLog;
use App\Models\Master\ProductType;
use App\Models\Master\Unit;
use Illuminate\Http\Request;

class ItemController extends Controller
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
            'js-2' => asset('assets/js/controllers/master/item.js'),
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
        return "Item";
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $view = view('web.item.index', $data);
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
        $data['product_type'] = ProductType::whereNull('deleted')->get()->toArray();
        $data['product_unit'] = Unit::whereNull('deleted')->get()->toArray();
        $data['product_logs'] = [];
        $view = view('web.item.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function getProductLog($product)
    {
        $data = ItemLog::where('items_log.item', $product)
            ->select([
                'items_log.*',
                'usr.username'
            ])
            ->join('users as usr', 'usr.id', 'items_log.creator')
            ->get()->toArray();
        return $data;
    }

    public function ubah(Request $request)
    {
        $api = new MasterItemController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['product_type'] = ProductType::whereNull('deleted')->get()->toArray();
        $data['product_unit'] = Unit::whereNull('deleted')->get()->toArray();

        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['product_logs'] = $this->getProductLog($data['id']);
        $view = view('web.item.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
