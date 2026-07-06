<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\ProductAdjustmentStockController as TransactionProductAdjustmentStockController;
use App\Http\Controllers\Controller;
use App\Models\Master\Dictionary;
use App\Models\Master\Menu;
use App\Models\Master\RoutingPermission;
use App\Models\Master\RoutingReminder;
use Illuminate\Http\Request;
use App\Models\Master\Warehouse;
use App\Models\Transaction\ProductAdjustmentStockDtl;
use Illuminate\Support\Facades\DB;

class ProductAdjustmentStockController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/adjustment_stock.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Transaksi";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Adjustment Stock";
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.adjustment_stock.index', $data);
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
        $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['akses_session'] = strtolower(session('akses'));
        $view = view('web.adjustment_stock.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionProductAdjustmentStockController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['akses_session'] = strtolower(session('akses'));
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses_session'] = strtolower(session('akses'));
        $data['list_approval'] = Dictionary::whereNull('deleted')->where('context', 'ROUTE_MODULE')->get()->toArray();
        $data['list_module'] = Menu::whereNull('deleted')->whereNotNull('parent')->where('routing', 1)->whereNull('deleted')->get()->toArray();
        $data['groups'] = Dictionary::where('context', 'GROUP')->whereNull('deleted')->get()->toArray();
        $data['list_items'] = ProductAdjustmentStockDtl::where('product_adjustment_stock_dtl.header_id', $data['id'])
            ->select([
                'product_adjustment_stock_dtl.*',
                'p.name as product_name',
                DB::raw('(product_adjustment_stock_dtl.qty - product_adjustment_stock_dtl.qty_current) as qty_adjustment')
            ])
            ->join('product as p', 'p.id', 'product_adjustment_stock_dtl.product')
            ->get()->toArray();
        $data['routing_item'] = RoutingPermission::where('routing_permission.routing_header', $data['id'])
            ->select(['routing_permission.*', 'k.nama_lengkap as name_user'])
            ->join('users as u', 'u.id', 'routing_permission.users')
            ->join('karyawan as k', 'k.nik', 'u.nik')
            ->whereNull('routing_permission.deleted')
            ->orderBy('routing_permission.id', 'asc')
            ->get()->toArray();
        $data['routing_reminder_item'] = RoutingReminder::where('routing_reminder.routing_header', $data['id'])
            ->select(['routing_reminder.*', 'k.nama_lengkap as name_user'])
            ->join('users as u', 'u.id', 'routing_reminder.users')
            ->join('karyawan as k', 'k.nik', 'u.nik')
            ->whereNull('routing_reminder.deleted')->get()->toArray();
        $view = view('web.adjustment_stock.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
