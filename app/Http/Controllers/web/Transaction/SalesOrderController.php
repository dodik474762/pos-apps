<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\SalesOrderController as TransactionSalesOrderController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Currency;
use App\Models\Master\Customer;
use App\Models\Master\Tax;
use App\Models\Transaction\SalesOrderDetail;
use App\Models\Transaction\SalesOrderHeader;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SalesOrderController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/sales_order.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Penjualan';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Sales Order';
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.sales_order.index', $data);
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
        $data['customers'] = isset($data['salesman']) ? $this->getCustomer($data['salesman']) : Customer::whereNull('deleted')->get();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['salesmen'] = User::where('user_group', '1')->whereNull('deleted')->get(['id', 'name']);
        $data['currencies'] = Currency::whereNull('deleted')->get();
        $data['data_item'] = [];
        $view = view('web.sales_order.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionSalesOrderController;
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['salesman'] = isset($data['salesman']) ? $data['salesman'] : $data['data']->salesman;
        $data['customers'] = $data['customers'] = $data['salesman'] != '' ? $this->getCustomer($data['salesman']) : Customer::whereNull('deleted')->get();;
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['data_item'] = SalesOrderDetail::where('sales_order_details.sales_order_id', $data['id'])
            ->select([
                'sales_order_details.*',
                'p.id as product_id',
                'p.name as product_name',
                'u.name as unit_name',
            ])
            ->join('product as p', 'p.id', 'sales_order_details.product_id')
            ->join('unit as u', 'u.id', 'sales_order_details.unit')
            ->orderBy('sales_order_details.id')
            ->get();

        $data['salesmen'] = User::where('user_group', '1')->whereNull('deleted')->get(['id', 'name']);
        $data['currencies'] = Currency::whereNull('deleted')->get();
        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.sales_order.formadd', $data);
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
        $data = SalesOrderHeader::with(['customers', 'items.products', 'items.units'])->findOrFail($data['id']);
        $qr = base64_encode(QrCode::format('png')->size(80)->generate($data->code));
        // $qr = '';
        // echo '<pre>';
        // print_r($data);
        // die;

        // Kalkulasi total, subtotal, dsb bisa disiapkan di sini
        $total = $data->items->sum('subtotal');

        $pdf = Pdf::loadView('web.sales_order.print.po-print', compact('data', 'total', 'company', 'qr'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('SO-'.$data->code.'.pdf');
    }
}
