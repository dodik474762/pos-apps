<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\SalesPaymentController as TransactionSalesPaymentController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Tax;
use App\Models\Transaction\PackingList;
use App\Models\Transaction\PackingListDo;
use App\Models\Transaction\SalesInvoiceHeader;
use App\Models\Transaction\SalesInvoiceTagihan;
use App\Models\Transaction\SalesPaymentDtl;
use App\Models\Transaction\SalesPaymentHeader;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SalesPaymentController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/sales_payment.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Pembayaran';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Sales Payment';
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $data['akses_roles'] = session('akses');
        $data['salesmans'] = User::whereNull('deleted')->whereIn('user_group', [6, 4, 5])->get(['id', 'nik', 'name']);
        $view = view('web.sales_payment.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getListKasBank($payment_method = 'CASH')
    {
        $datadb = DB::table('coa')->where('is_active', 1)
            ->where('parent_code', '1100')
            ->whereNull('deleted')
            ->where('payment_method', $payment_method)
            ->get();
        return $datadb;
    }

    public function add(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['code'] = generateNoPO();
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        // $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['details'] = [];
        $data['general_ledgers'] = [];
        $payment_method = $data['payment_method'] ?? 'CASH';
        $data['akses'] = session('akses');
        $data['cashBankAccounts'] = $this->getListKasBank($payment_method);
        $view = view('web.sales_payment.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getListCustomer()
    {
        $datadb = SalesInvoiceHeader::whereIn('sales_invoice_header.status', ['POSTED', 'PARTIAL PAID', 'DRAFT', 'PACKED'])
            ->select([
                'c.id as id',
                'c.nama_customer',
                'c.code as customer_code',
                'pt.remarks as payment_terms'
            ])
            ->distinct()
            ->join('customer as c', 'c.id', 'sales_invoice_header.customer_id')
            ->join('term_of_payment as pt', 'pt.id', 'c.payment_terms')
            ->whereNull('sales_invoice_header.deleted')
            ->get()
            ->toArray();

        return $datadb;
    }

    public function addAll(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['code'] = generateNoPO();
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        // $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['details'] = [];
        $data['general_ledgers'] = [];
        $payment_method = $data['payment_method'] ?? 'CASH';
        $data['cashBankAccounts'] = $this->getListKasBank($payment_method);
        $data['data_customer'] = $this->getListCustomer();
        $data['akses'] = session('akses');
        $data['packing_list'] = $this->getListPackingListInvoice();
        $view = view('web.sales_payment.formaddbulk', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getListPackingListInvoice()
    {
        $datadb = PackingListDo::join('packing_list as pl', 'pl.id', 'packing_list_do.packing_list_id')
            ->select([
                'pl.packing_list_no',
                'pl.id',
                'pl.vehicle_no',
                'pl.driver_name',
                'pl.packing_date',
                'pl.id'
            ])
            ->join('delivery_order_header as doh', 'doh.id', 'packing_list_do.delivery_order_id')
            ->join('sales_order_headers as soh', 'soh.id', 'doh.so_id')
            ->join('sales_invoice_header as sih', 'sih.sales_order', 'soh.id')
            ->whereIn('sih.status', ['POSTED', 'PARTIAL PAID', 'DRAFT', 'PACKED'])
            ->distinct()
            ->whereNull('pl.deleted')
            ->get();

        // echo '<pre>';
        // print_r($datadb);
        // die;
        return $datadb;
    }

    public function ubah(Request $request)
    {
        $api = new TransactionSalesPaymentController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['cashBankAccounts'] = $this->getListKasBank();
        $data['details'] = SalesPaymentDtl::where('sales_payment_detail.payment_id', $data['id'])
            ->select([
                'sales_payment_detail.*',
                'sih.invoice_number',
                'sih.invoice_date',
                'sih.total_amount as total_amount_invoice',
                'sih.discount_amount',
                'sih.subtotal',
                'sih.amount_paid',
                'c.code as customer_code',
                'c.nama_customer',
                'c.id as customer_id'
            ])
            ->join('sales_invoice_header as sih', 'sih.id', 'sales_payment_detail.invoice_id')
            ->join('customer as c', 'sih.customer_id', 'c.id')
            ->whereNull('sales_payment_detail.deleted')
            ->orderBy('sales_payment_detail.id')
            ->get();

        $data['payment_method'] = $data['data']->payment_method;
        $data['general_ledgers'] = getGeneralLedger($data['data']->payment_code);
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = session('akses');
        $view = view('web.sales_payment.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function detail(Request $request)
    {
        $api = new TransactionSalesPaymentController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['cashBankAccounts'] = $this->getListKasBank();
        $data['details'] = SalesPaymentDtl::where('sales_payment_detail.payment_id', $data['id'])
            ->select([
                'sales_payment_detail.*',
                'sih.invoice_number',
                'sih.invoice_date',
                'sih.total_amount as total_amount_invoice',
                'sih.discount_amount',
                'sih.subtotal',
                'sih.amount_paid',
                'c.code as customer_code',
                'c.nama_customer',
                'c.id as customer_id'
            ])
            ->join('sales_invoice_header as sih', 'sih.id', 'sales_payment_detail.invoice_id')
            ->join('customer as c', 'sih.customer_id', 'c.id')
            ->whereNull('sales_payment_detail.deleted')
            ->orderBy('sales_payment_detail.id')
            ->get();

        $data['payment_method'] = $data['data']->payment_method;
        $data['general_ledgers'] = getGeneralLedger($data['data']->payment_code);
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = session('akses');
        $data['view_akses'] = 'detail';
        $view = view('web.sales_payment.formadd', $data);
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
        $data = SalesPaymentHeader::with(['customers', 'items.invoice'])->findOrFail($data['id']);
        // $qr = base64_encode(QrCode::format('png')->size(80)->generate($data->payment_code));
        $qr = '';
        // echo '<pre>';
        // print_r($data->items);
        // die;

        // Kalkulasi total, subtotal, dsb bisa disiapkan di sini

        $pdf = Pdf::loadView('web.sales_payment.print.po-print', compact('data',  'company', 'qr'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('SP-' . $data->payment_code . '.pdf');
    }

    public function getListRekapanSalesman($customers, $tanggal, $salesman = '')
    {
        $rpdSub = DB::table('receive_payment_detail as rpd')
            ->select('rpd.invoice_id', 'rpd.receive_id', DB::raw('SUM(rpd.amount_paid) as amount_paid'))
            ->join('receive_payment_header as rph', function ($q) {
                return $q->on('rph.id', 'rpd.receive_id')->whereNull('rph.deleted');
            })
            ->whereRaw('CAST(rph.visit_date as date) = ?', [$tanggal])
            ->where('rpd.amount_paid', '>', 0)
            ->groupBy('rpd.invoice_id', 'rpd.receive_id');

        $datadb = SalesPaymentDtl::select([
            'sales_payment_detail.id',
            'cc.nama_customer',
            'cc.code as customer_code',
            'sih.invoice_number',
            'usr.name as salesman_name',
            'kec.name as kecamatan_name',
            'tp.remarks as top_name',
            'sih.invoice_date',
            'sih.due_date',
            'sih.status',
            'sih.total_amount',
            // 'sih.amount_paid',
            'sales_payment_detail.allocated_amount as amount_paid',
            'rpd.amount_paid as total_terbayar_rph',
            'rph.status as status_received',
            'sales_payment_detail.invoice_id',
            'sph.payment_method'
        ])
            ->distinct()
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.id', 'sales_payment_detail.invoice_id')
                    ->whereNull('sih.deleted');
            })
            ->join('sales_order_headers as soh', function ($q) {
                return $q->on('soh.id', 'sih.sales_order')->whereNull('soh.deleted');
            })
            ->leftJoin('users as usr', 'usr.id', 'soh.salesman')
            ->join('customer as cc', 'cc.id', 'sih.customer_id')
            ->leftJoin('region as kec', 'kec.id', 'cc.kecamatan')
            ->join('term_of_payment as tp', 'tp.id', 'cc.payment_terms')
            ->join('sales_payment_header as sph', 'sph.id', 'sales_payment_detail.payment_id')
            ->leftJoinSub($rpdSub, 'rpd', function ($q) {
                return $q->on('rpd.invoice_id', '=', 'sih.id');
            })
            ->leftJoin('receive_payment_header as rph', function ($q) use ($tanggal) {
                return $q->on('rph.id', 'rpd.receive_id')
                    ->where('rph.visit_date', $tanggal)
                    ->whereNull('rph.deleted');
            })
            ->whereIn('sih.customer_id', $customers)
            ->whereNull('sales_payment_detail.deleted')
            ->whereNull('sph.deleted')
            ->where(function ($q) use ($tanggal) {
                return $q->where('sph.payment_date', $tanggal);
            })
            ->when($salesman != '', function ($q) use ($salesman) {
                return $q->where('soh.salesman', $salesman);
            })
            ->get();
        return $datadb;
    }

    public function getListRekapanDriver($nik, $tanggal)
    {
        $tanggalPackingDate = date('Y-m-d', strtotime($tanggal . ' -1 day'));
        $rpdSub = DB::table('receive_payment_detail as rpd')
            ->select('rpd.invoice_id', 'rpd.receive_id', DB::raw('SUM(rpd.amount_paid) as amount_paid'))
            ->join('receive_payment_header as rph', function ($q) {
                return $q->on('rph.id', 'rpd.receive_id')->whereNull('rph.deleted');
            })
            ->whereRaw('CAST(rph.visit_date as date) = ?', [$tanggal])
            ->where('rpd.amount_paid', '>', 0)
            ->groupBy('rpd.invoice_id', 'rpd.receive_id');

        $datadb = SalesPaymentDtl::select([
            'sales_payment_detail.id',
            'cc.nama_customer',
            'cc.code as customer_code',
            'sih.invoice_number',
            'usr.name as salesman_name',
            'kec.name as kecamatan_name',
            'tp.remarks as top_name',
            'sih.invoice_date',
            'sih.due_date',
            'sih.status',
            'sih.total_amount',
            // 'sih.amount_paid',
            'sales_payment_detail.allocated_amount as amount_paid',
            'rpd.amount_paid as total_terbayar_rph',
            'rph.status as status_received',
            'sales_payment_detail.invoice_id',
            'sph.payment_method'
        ])
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.id', 'sales_payment_detail.invoice_id')
                    ->whereNull('sih.deleted');
            })
            ->join('sales_order_headers as soh', function ($q) {
                return $q->on('soh.id', 'sih.sales_order')->whereNull('soh.deleted');
            })
            ->join('delivery_order_header as doh', function ($q) {
                return $q->on('doh.so_id', 'soh.id')->whereNull('doh.deleted');
            })
            ->join('packing_list_do as pld', 'pld.delivery_order_id', 'doh.id')
            ->join('packing_list as pl', function ($q) {
                return $q->on('pl.id', 'pld.packing_list_id')->whereNull('pl.deleted');
            })
            ->leftJoinSub($rpdSub, 'rpd', function ($q) {
                return $q->on('rpd.invoice_id', '=', 'sih.id');
            })
            ->leftJoin('receive_payment_header as rph', function ($q) use ($tanggal) {
                return $q->on('rph.id', 'rpd.receive_id')
                    ->where('rph.visit_date', $tanggal);
            })
            ->leftJoin('users as usr', 'usr.id', 'soh.salesman')
            ->join('customer as cc', 'cc.id', 'sih.customer_id')
            ->leftJoin('region as kec', 'kec.id', 'cc.kecamatan')
            ->join('term_of_payment as tp', 'tp.id', 'cc.payment_terms')
            ->join('sales_payment_header as sph', 'sph.id', 'sales_payment_detail.payment_id')
            ->whereNull('sales_payment_detail.deleted')
            // ->where('pl.packing_date', '>', '2026-06-29')
            ->where('pl.packing_date', $tanggalPackingDate)
            ->where('pl.driver_name', $nik)
            ->whereNull('sph.deleted')
            ->where(function ($q) use ($tanggal, $tanggalPackingDate) {
                return $q->where('sph.payment_date', $tanggal);
            })
            ->orderBy('usr.nik')
            ->orderBy('cc.nama_customer')
            ->get();
        // echo '<pre>';
        // print_r($datadb->toSql());
        // die;
        return $datadb;
    }

    public function cetakRekapan(Request $request)
    {
        $data = $request->all();
        $company = CompanyModel::where('id', session('id_company'))->first();
        $qr = '';

        // echo '<pre>';
        // print_r($data['data_payment']);
        // die;
        $salesmans = isset($data['salesman']) ? $data['salesman'] : '';
        $user_group = '';
        if ($salesmans != '') {
            $salesmans = User::find($salesmans);
            $user_group = $salesmans->user_group;
        }

        // Filter salesman sebelum get()
        $data['data_payment'] = [];
        if ($salesmans != '' && $user_group != 5) {
            $plTagihan = new PLTagihanController();
            $data['tanggal'] = $data['date'] ?? date('Y-m-d');
            $routeplan = $plTagihan->getRoutePlanSales($data);
            $customers = empty($routeplan) ? [] : collect($routeplan)->pluck('customer_id')->unique()->toArray();
            if (isset($data['salesman'])) {
                $salesman = User::where('id', $data['salesman'])->first();
                $tagihanOther = SalesInvoiceTagihan::where('tgl_tagih', $data['date'])->where('salesman_id', $salesman->id)->get();
                $customers = array_merge($customers, $tagihanOther->pluck('customer_id')->unique()->toArray());
            }
            $salesPayment = $this->getListRekapanSalesman($customers, $data['tanggal'], $salesmans->id);
            // echo '<pre>';
            // print_r($salesPayment);
            // die;
            $data['data_payment'] = $salesPayment;
        }

        if ($salesmans == '') {
            $plTagihan = new PLTagihanController();
            $data['tanggal'] = $data['date'] ?? date('Y-m-d');
            $routeplan = $plTagihan->getRoutePlanSalesAll($data);
            $customers = empty($routeplan) ? [] : collect($routeplan)->pluck('customer_id')->unique()->toArray();
            if (isset($data['salesman'])) {
                $salesman = User::where('id', $data['salesman'])->first();
                $tagihanOther = SalesInvoiceTagihan::where('tgl_tagih', $data['date'])->where('salesman_id', $salesman->id)->get();
                $customers = array_merge($customers, $tagihanOther->pluck('customer_id')->unique()->toArray());
            }
            $salesPayment = $this->getListRekapanSalesman($customers, $data['tanggal']);
            $data['data_payment'] = $salesPayment;
        }

        if ($salesmans != '' && $user_group == 5) {
            $data['tanggal'] = $data['date'] ?? date('Y-m-d');
            $salesPayment = $this->getListRekapanDriver($salesmans->nik, $data['tanggal']);
            $data['data_payment'] = $salesPayment;
        }

        // echo '<pre>';
        // print_r($data['data_payment']);
        // die;
        $pdf = Pdf::loadView('web.sales_payment.print.print-rekapan-sp', compact('data',  'company', 'qr', 'salesmans', 'user_group'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('REKAPAN-SP-' . $data['date'] . '.pdf');
    }

    public function confirmPayment(Request $request)
    {
        $data = $request->all();
        $company = CompanyModel::where('id', session('id_company'))->first();
        $data['data_payment'] = SalesPaymentHeader::with([
            'customers',
            'customers.kecamatans',
            'items.invoice',
            'items.invoice.do',
            'items.invoice.do.so.salesmans',
            'items.invoice.so.salesmans'
        ])
            ->select(['sales_payment_header.*', 'c.nama_customer', 'c.code as customer_code'])
            ->join('customer as c', 'c.id', 'sales_payment_header.customer_id')
            ->where('sales_payment_header.status', 'PENDING')
            ->whereNotNull('verificator_id')
            ->whereNull('sales_payment_header.deleted');
        if (isset($data['date'])) {
            if ($data['date'] != '') {
                $data['data_payment'] = $data['data_payment']->where('payment_date', $data['date']);
            }
        }
        $data['data_payment'] = $data['data_payment']->get();
        $qr = '';

        // echo '<pre>';
        // print_r($data['data_payment']);
        // die;
        $salesmans = isset($data['salesman']) ? $data['salesman'] : '';
        if ($salesmans != '') {
            $salesmans = User::find($salesmans);
        }
        $data['salesmans'] = $salesmans;

        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses_roles'] = session('akses');

        $view = view('web.sales_payment.confirm-invoice', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }
}
