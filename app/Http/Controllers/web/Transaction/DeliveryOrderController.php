<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\DeliveryOrderController as TransactionDeliveryOrderController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Warehouse;
use App\Models\Transaction\DeliveryOrderDtl;
use App\Models\Transaction\DeliveryOrderHeader;
use App\Models\Transaction\SalesOrderHeader;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DeliveryOrderController extends Controller
{
    public $akses_menu = [];

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
    }

    public function getHeaderCss()
    {
        return [
            'js-1' => asset('assets/js/controllers/transaction/delivery_order.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Pengiriman';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Delivery Order';
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.delivery_order.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function add(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['code'] = generateNoPO();
        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['details'] = [];
        $view = view('web.delivery_order.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionDeliveryOrderController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['details'] = DeliveryOrderDtl::where('delivery_order_detail.do_id', $data['id'])
            ->select([
                'delivery_order_detail.*',
                'p.id as product_id',
                'p.name as product_name',
                'u.name as unit_name',
            ])
            ->join('product as p', 'p.id', 'delivery_order_detail.product_id')
            ->join('unit as u', 'u.id', 'delivery_order_detail.uom')
            ->whereNull('delivery_order_detail.deleted')
            ->orderBy('delivery_order_detail.id')
            ->get();
        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.delivery_order.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getCustomer($salesmanId){
        $periodYear = intval(date('Y'));  // misal dari form input
        $periodMonth = intval(date('m'));   // misal dari form input

        $customers = DB::table('sales_plan_detail as d')
            ->join('sales_plan_header as h', 'h.id', '=', 'd.header_id')
            ->join('customer as c', 'c.id', '=', 'd.customer_id')
            ->where('h.salesman', $salesmanId)
            ->where('h.period_year', $periodYear)
            ->where('h.period_month', $periodMonth)
            ->whereNull('h.deleted')
            ->select('d.customer_id as id', 'c.nama_customer')
            ->distinct()
            ->get();

            return $customers;
    }

    public function cetak(Request $request)
    {
        $data = $request->all();
        $company = CompanyModel::where('id', session('id_company'))->first();
        $data = DeliveryOrderHeader::with(['customers', 'warehouses','items.products', 'items.units'])->findOrFail($data['id']);
        $qr = base64_encode(QrCode::format('png')->size(80)->generate($data->do_number));
        // $qr = '';
        // echo '<pre>';
        // print_r($data);
        // die;

        // Kalkulasi total, subtotal, dsb bisa disiapkan di sini

        $pdf = Pdf::loadView('web.delivery_order.print.po-print', compact('data',  'company', 'qr'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('DO-'.$data->do_number.'.pdf');
    }
}
