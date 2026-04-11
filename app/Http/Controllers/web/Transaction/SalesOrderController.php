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
use App\Http\Controllers\api\Transaction\SalesPlanController;

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
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['customers'] = isset($data['salesman']) ? $this->getCustomer($data['salesman']) : Customer::whereNull('customer.deleted')
            ->select(['customer.*', 'top.nilai as top_value'])
            ->leftJoin('term_of_payment as top', 'top.id', '=', 'customer.payment_terms')
            ->get();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['salesmen'] = User::whereNull('deleted')->whereIn('user_group', [6, 4])->get(['id', 'name']);
        $data['currencies'] = Currency::whereNull('deleted')->get();
        $data['data_item'] = [];
        $view = view('web.sales_order.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
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
        // $data['customers'] = $data['customers'] = $data['salesman'] != '' ? $this->getCustomer($data['salesman']) : Customer::whereNull('customer.deleted')
        //     ->select(['customer.*', 'top.nilai as top_value'])
        //     ->leftJoin('term_of_payment as top', 'top.id', '=', 'customer.payment_terms')
        //     ->get();
        $data['customers'] = Customer::whereNull('customer.deleted')
            ->select(['customer.*', 'top.nilai as top_value'])
            ->leftJoin('term_of_payment as top', 'top.id', '=', 'customer.payment_terms')
            ->get();


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
            ->whereNull('sales_order_details.deleted')
            ->orderBy('sales_order_details.id')
            ->get();
        // echo '<pre>';
        // print_r($data['data_item']);die;

        $data['salesmen'] = User::whereNull('deleted')->whereIn('user_group', [6, 4])->get(['id', 'name']);
        $data['currencies'] = Currency::whereNull('deleted')->get();
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.sales_order.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getCustomer($salesmanId)
    {
        $periodYear = intval(date('Y'));  // misal dari form input
        $periodMonth = intval(date('m'));   // misal dari form input

        $salesPlan = new SalesPlanController();
        $dataPlan = $salesPlan->getDailyVisits($salesmanId);
        // echo '<pre>';
        // print_r($dataPlan);
        // die;
        $planDtlId = !empty($dataPlan) ? collect($dataPlan)->pluck('id')->toArray() : [];


        $customers = DB::table('sales_plan_detail_route as d')
            ->join('sales_plan_header as h', 'h.id', '=', 'd.header_id')
            ->join('customer as c', 'c.id', '=', 'd.customer_id')
            ->leftJoin('term_of_payment as top', 'top.id', '=', 'c.payment_terms')
            ->where('h.salesman', $salesmanId)
            ->whereIn('d.id', $planDtlId)
            // ->where('h.period_year', $periodYear)
            // ->where('h.period_month', $periodMonth)
            ->whereNull('h.deleted')
            ->select('d.customer_id as id', 'c.nama_customer', 'top.nilai as top_value', 'c.channel_outlet', 'c.sub_channel_outlet', 'c.code')
            ->distinct()
            ->get();

        return $customers;
    }

    public function cetak(Request $request)
    {
        $data = $request->all();
        $company = CompanyModel::where('id', session('id_company'))->first();
        $data = SalesOrderHeader::with(['customers', 'items.products', 'items.units'])->findOrFail($data['id']);
        $promo_item = DB::table('sales_order_promo_item as sopi')
            ->where('sales_order_id', $data['id'])
            ->get();

        // $rawQr = QrCode::format('png')->size(80)->generate($data->so_number);
        // $qr = 'data:image/png;base64,'.base64_encode($rawQr);
        $qr = '';

        $promo = DB::table('sales_order_promo as sop')
            ->select([
                'sop.promo_name',
                DB::raw('MAX(sop.discount_percent) as discount_percent'),
                DB::raw('SUM(sop.discount_amount) as total_potongan')
            ])
            ->where('sop.sales_order_id', $data['id'])
            ->groupBy('sop.promo_name')
            ->get();

        // $promoString = $promo
        //     ->map(fn($p) => $p->promo_name . ' : ' . $p->total_potongan)
        //     ->implode('<br/>');
        // echo '<pre>';
        // print_r($promoString);die;

        // $qr = '';

        // Kalkulasi total, subtotal, dsb bisa disiapkan di sini
        $total = $data->items->sum('subtotal');

        $pdf = Pdf::loadView('web.sales_order.print.po-print', compact('data', 'total', 'company', 'qr', 'promo', 'promo_item'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('SO-' . $data->so_number . '.pdf');
    }

    public function getAllSalesNotInvoice($date = '', $state = '')
    {
        $date = $date == '' ? date('Y-m-d') : date('Y-m-d', strtotime($date));
        $datadb = DB::table('sales_order_headers as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'cc.nama_customer',
                'cy.code as currency_code'
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('currency as cy', 'cy.id', 'm.currency')
            ->leftJoin('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.sales_order', 'm.id')
                    ->whereNull('sih.deleted');
            })
            ->whereIn('m.status', ['draft', 'submited'])
            ->whereNull('sih.id')
            ->where('m.total_amount', '>', 0)
            ->where('m.so_date', $date)
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if ($state == '') {
        }

        $datadb = $datadb->get();

        return $datadb;
    }

    public function generateAll(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $data['sales_orders'] = $this->getAllSalesNotInvoice($data['tanggal'], $data['state']);
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.sales_order.list-sales-order', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }
}
