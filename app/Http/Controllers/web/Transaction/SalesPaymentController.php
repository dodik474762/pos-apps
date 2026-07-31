<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\SalesPaymentController as TransactionSalesPaymentController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Tax;
use App\Models\Transaction\PackingList;
use App\Models\Transaction\PackingListDo;
use App\Models\Transaction\PackingListSalesman;
use App\Models\Transaction\SalesInvoiceHeader;
use App\Models\Transaction\SalesInvoiceTagihan;
use App\Models\Transaction\SalesPaymentDtl;
use App\Models\Transaction\SalesPaymentHeader;
use App\Models\Transaction\SalesReturnHdr;
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

    public function index(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $data['tanggal'] = $data['tanggal'] ?? date('Y-m-d');
        $data['packing_list'] = isset($data['packing_list']) ? $data['packing_list'] : '';
        // echo '<pre>';
        // print_r($data);die;
        $data['akses_roles'] = session('akses');
        $data['salesmans'] = User::whereNull('deleted')->whereIn('user_group', [6, 4, 5])->get(['id', 'nik', 'name']);
        $data['packing_lists'] = $this->getListPackingList($data);
        $view = view('web.sales_payment.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getListPackingList($params)
    {
        $start = date('Y-m-d', strtotime($params['tanggal'] . ' -2 days'));
        $end = date('Y-m-d', strtotime($params['tanggal']));
        $packingLists = PackingList::whereBetween('packing_date', [$start, $end])
            ->select([
                'packing_list_no',
                'driver_name',
                DB::raw("'delivery' as pl_type")
            ])
            ->where('status', '!=', 'CANCELED')
            ->whereNull('deleted')
            ->get()->toArray();

        $packingListSales = PackingListSalesman::whereBetween('packing_list_salesman.packing_date', [$start, $end])
            ->select([
                'packing_list_salesman.packing_list_no',
                'usr.name as driver_name',
                DB::raw("'sales' as pl_type")
            ])
            ->join('users as usr', 'usr.id', 'packing_list_salesman.salesman')
            ->whereNull('packing_list_salesman.deleted')
            ->get()->toArray();

        $plMerge = array_merge($packingLists, $packingListSales);
        // echo '<pre>';
        // print_r($plMerge);
        // die;
        return $plMerge;
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
        $datadb = SalesInvoiceHeader::whereNotIn('sales_invoice_header.status', ['CANCELED'])
            ->select([
                'c.id as id',
                'c.nama_customer',
                'c.code as customer_code',
                'pt.remarks as payment_terms'
            ])
            ->distinct()
            ->join('customer as c', 'c.id', 'sales_invoice_header.customer_id')
            ->join('term_of_payment as pt', 'pt.id', 'c.payment_terms')
            ->whereRaw('(sales_invoice_header.total_amount - sales_invoice_header.amount_paid) > 0')
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
        // echo '<pre>';
        // print_r($data['details']);
        // die;

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

        // echo '<pre>';
        // print_r($customers);
        // die;

        $datadb = DB::table('sales_invoice_header as m')
            ->select([
                'sales_payment_detail.id',
                'cc.nama_customer',
                'cc.code as customer_code',
                'm.invoice_number',
                'usr.name as salesman_name',
                'kec.name as kecamatan_name',
                'tp.remarks as top_name',
                'm.invoice_date',
                'm.due_date',
                'm.status',
                'm.total_amount',
                'sales_payment_detail.allocated_amount as amount_paid',
                'rpd.amount_paid as total_terbayar_rph',
                'rph.status as status_received',
                'sales_payment_detail.invoice_id',
                'sph.payment_method',
                'do.do_number',
                'dohs.do_number as dohs_number',
                'do.do_date',
                'dohs.do_date as dohs_date',
                'sales_payment_detail.allocated_amount as total_terbayar',
                'rpd.amount_paid as total_terbayar_rph',
                'tp.remarks as top_customer',
                'w.name as warehouse_name',
            ])
            ->distinct()
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->leftJoin('delivery_order_header as do', function ($q) {
                return $q->on('do.id', 'm.do_id')->whereNull('do.deleted');
            })
            ->leftJoin('sales_order_headers as soh', function ($q) {
                return $q->on('soh.id', 'do.id')->orOn('soh.id', 'm.sales_order');
            })
            ->leftJoin('delivery_order_header as dohs', function ($q) {
                return $q->on('dohs.so_id', 'soh.id')->whereNull('dohs.deleted');
            })
            ->leftJoin('packing_list_do as pld', function ($q) {
                return $q->on('pld.delivery_order_id', 'do.id');
            })
            ->leftJoin('sales_payment_detail', function ($q) {
                return $q->on('sales_payment_detail.invoice_id', 'm.id')
                    ->whereNull('sales_payment_detail.deleted');
            })
            ->leftJoin('sales_payment_header as sph', function ($q) use ($tanggal) {
                return $q->on('sph.id', 'sales_payment_detail.payment_id')
                    ->whereNull('sph.deleted')
                    ->where('sph.payment_date', $tanggal);
            })
            ->leftJoinSub($rpdSub, 'rpd', function ($q) {
                return $q->on('rpd.invoice_id', '=', 'm.id');
            })
            ->leftJoin('receive_payment_header as rph', function ($q) use ($tanggal) {
                return $q->on('rph.id', 'rpd.receive_id')
                    ->where('rph.visit_date', $tanggal)
                    ->whereNull('rph.deleted');
            })
            // ->where('m.invoice_number', 'SI07261305')
            ->whereNull('pld.id')
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            ->join('term_of_payment as tp', 'tp.id', 'cc.payment_terms')
            ->leftJoin('users as usr', 'usr.id', 'soh.salesman')
            ->leftJoin('region as kec', 'kec.id', 'cc.kecamatan')
            // ->where('m.invoice_date', $date)
            ->whereNull('m.deleted')
            ->where('m.pl_date', $tanggal)
            ->when($salesman != '', function ($q) use ($salesman) {
                return $q->where('m.pl_by', $salesman);
            })
            // ->whereRaw('(m.total_amount - m.amount_paid) > 0')
            // ->whereIn('m.status', ['POSTED', 'PARTIAL PAID', 'PACKED', 'DRAFT'])
            // ->whereIn('cc.id', $customers)
            ->when($salesman != '', function ($q) use ($salesman) {
                return $q->where('soh.salesman', $salesman);
            });
        // ->where('m.invoice_number', 'SI06261030')
        // ->where('m.invoice_date', '>=', $date)
        // ->orderBy('m.id', 'desc');

        $datadb = $datadb->get();
        // echo '<pre>';
        // print_r($datadb);
        // die;
        return $datadb;
        // $datadb = SalesPaymentDtl::select([
        //     'sales_payment_detail.id',
        //     'cc.nama_customer',
        //     'cc.code as customer_code',
        //     'sih.invoice_number',
        //     'usr.name as salesman_name',
        //     'kec.name as kecamatan_name',
        //     'tp.remarks as top_name',
        //     'sih.invoice_date',
        //     'sih.due_date',
        //     'sih.status',
        //     'sih.total_amount',
        //     // 'sih.amount_paid',
        //     'sales_payment_detail.allocated_amount as amount_paid',
        //     'rpd.amount_paid as total_terbayar_rph',
        //     'rph.status as status_received',
        //     'sales_payment_detail.invoice_id',
        //     'sph.payment_method'
        // ])
        //     ->distinct()
        //     ->join('sales_invoice_header as sih', function ($q) {
        //         return $q->on('sih.id', 'sales_payment_detail.invoice_id')
        //             ->whereNull('sih.deleted');
        //     })
        //     ->join('sales_order_headers as soh', function ($q) {
        //         return $q->on('soh.id', 'sih.sales_order')->whereNull('soh.deleted');
        //     })
        //     ->leftJoin('users as usr', 'usr.id', 'soh.salesman')
        //     ->join('customer as cc', 'cc.id', 'sih.customer_id')
        //     ->leftJoin('region as kec', 'kec.id', 'cc.kecamatan')
        //     ->join('term_of_payment as tp', 'tp.id', 'cc.payment_terms')
        //     ->join('sales_payment_header as sph', 'sph.id', 'sales_payment_detail.payment_id')
        //     ->leftJoinSub($rpdSub, 'rpd', function ($q) {
        //         return $q->on('rpd.invoice_id', '=', 'sih.id');
        //     })
        //     ->leftJoin('receive_payment_header as rph', function ($q) use ($tanggal) {
        //         return $q->on('rph.id', 'rpd.receive_id')
        //             ->where('rph.visit_date', $tanggal)
        //             ->whereNull('rph.deleted');
        //     })
        //     ->join('users as usr_payment', 'usr_payment.id', 'sph.created_by')
        //     ->where('usr_payment.user_group', '!=', '5')
        //     ->whereIn('sih.customer_id', $customers)
        //     ->whereNull('sales_payment_detail.deleted')
        //     ->whereNull('sph.deleted')
        //     ->where(function ($q) use ($tanggal) {
        //         return $q->where('sph.payment_date', $tanggal);
        //     })
        //     ->when($salesman != '', function ($q) use ($salesman) {
        //         return $q->where('soh.salesman', $salesman);
        //     })
        //     ->get();
        // return $datadb;
    }

    public function getListRekapanDriver($nik, $tanggal)
    {
        $isMonday = date('l', strtotime($tanggal)) == 'Monday';
        $tanggalPackingDate = $isMonday
            ? date('Y-m-d', strtotime($tanggal . ' -2 day'))
            : date('Y-m-d', strtotime($tanggal . ' -1 day'));

        $rpdSub = DB::table('receive_payment_detail as rpd')
            ->select('rpd.invoice_id', 'rpd.receive_id', DB::raw('SUM(rpd.amount_paid) as amount_paid'))
            ->join('receive_payment_header as rph', function ($q) {
                return $q->on('rph.id', 'rpd.receive_id')->whereNull('rph.deleted');
            })
            ->whereRaw('CAST(rph.visit_date as date) = ?', [$tanggal])
            ->where('rpd.amount_paid', '>', 0)
            ->groupBy('rpd.invoice_id', 'rpd.receive_id');

        $datadb = PackingList::query()
            ->from('packing_list as pl')
            ->select([
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
                'sales_payment_detail.allocated_amount as amount_paid',
                'rpd.amount_paid as total_terbayar_rph',
                'rph.status as status_received',
                'sales_payment_detail.invoice_id',
                'sph.payment_method',
                'doh.do_number',
                'w.name as warehouse_name',
            ])
            ->join('packing_list_do as pld', 'pld.packing_list_id', 'pl.id')
            ->join('delivery_order_header as doh', function ($q) {
                return $q->on('doh.id', 'pld.delivery_order_id')->whereNull('doh.deleted');
            })
            ->join('sales_order_headers as soh', function ($q) {
                return $q->on('soh.id', 'doh.so_id')->whereNull('soh.deleted');
            })
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.sales_order', 'soh.id')->whereNull('sih.deleted');
            })
            // sekarang leftJoin, biar invoice yang belum dibayar tetap muncul
            ->leftJoin('sales_payment_detail', function ($q) {
                return $q->on('sales_payment_detail.invoice_id', 'sih.id')
                    ->whereNull('sales_payment_detail.deleted');
            })
            ->leftJoin('sales_payment_header as sph', function ($q) use ($tanggal) {
                return $q->on('sph.id', 'sales_payment_detail.payment_id')
                    ->whereNull('sph.deleted')
                    ->where('sph.payment_date', $tanggal); // dipindah ke ON, bukan WHERE
            })
            ->leftJoinSub($rpdSub, 'rpd', function ($q) {
                return $q->on('rpd.invoice_id', '=', 'sih.id');
            })
            ->leftJoin('receive_payment_header as rph', function ($q) use ($tanggal) {
                return $q->on('rph.id', 'rpd.receive_id')
                    ->where('rph.visit_date', $tanggal);
            })
            ->join('warehouse as w', 'w.id', 'sih.warehouse_id')
            ->leftJoin('users as usr', 'usr.id', 'soh.salesman')
            ->join('customer as cc', 'cc.id', 'sih.customer_id')
            ->leftJoin('region as kec', 'kec.id', 'cc.kecamatan')
            ->join('term_of_payment as tp', 'tp.id', 'cc.payment_terms')
            ->leftJoin('users as usr_payment', 'usr_payment.id', 'sph.created_by')
            ->whereNull('pl.deleted')
            // ->where('sih.invoice_number', 'SI07261307')
            ->where('pl.driver_name', $nik)
            ->where(function ($q) use ($tanggalPackingDate, $tanggal) {
                return $q->where('pl.packing_date', $tanggalPackingDate)
                    ->orWhere('pl.packing_date', $tanggal);
            })
            ->orderBy('usr.nik')
            ->orderBy('cc.nama_customer')
            ->get();

        return $datadb;
    }

    public function getListSalesReturn($invoice_ids = [])
    {
        $datadb = SalesReturnHdr::whereNull('sales_return.deleted')
            ->select([
                'sales_return.id',
                'sales_return.return_number',
                'sales_return.invoice_id',
                'sales_return.refund_amount'
            ])
            ->where('sales_return.status', '!=', 'CANCELLED')
            ->where('sales_return.return_type', 'RETURN');
        $datadb = $datadb->get();

        return $datadb;
    }

    public function getListRekapan($params)
    {
        $tanggal = date('Y-m-d', strtotime($params['date']));
        $pl_no = $params['packing_list'];
        $rpdSub = DB::table('receive_payment_detail as rpd')
            ->select('rpd.invoice_id', 'rpd.receive_id', DB::raw('SUM(rpd.amount_paid) as amount_paid'))
            ->join('receive_payment_header as rph', function ($q) {
                return $q->on('rph.id', 'rpd.receive_id')->whereNull('rph.deleted');
            })
            ->whereRaw('CAST(rph.visit_date as date) = ?', [$tanggal])
            ->where('rpd.amount_paid', '>', 0)
            ->groupBy('rpd.invoice_id', 'rpd.receive_id');

        $packingLists = PackingList::where('packing_list.packing_list_no', $pl_no)
            ->select([
                'packing_list.packing_list_no',
                'packing_list.driver_name',
                'pld.delivery_order_id',
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
                'sales_payment_detail.allocated_amount as amount_paid',
                'rpd.amount_paid as total_terbayar_rph',
                'rph.status as status_received',
                'sales_payment_detail.invoice_id',
                'sph.payment_method',
                'doh.do_number',
                'doh.do_date',
                'w.name as warehouse_name',
                'sales_payment_detail.allocated_amount as total_terbayar',
                'tp.remarks as top_customer',
                'sih.id as invoice_id',
                'usr.id as salesman_id'
            ])
            ->join('packing_list_do as pld', 'pld.packing_list_id', 'packing_list.id')
            ->join('delivery_order_header as doh', function ($q) {
                return $q->on('doh.id', 'pld.delivery_order_id')->whereNull('doh.deleted');
            })
            ->join('sales_order_headers as soh', 'soh.id', 'doh.so_id')
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.sales_order', 'doh.so_id')->whereNull('sih.deleted');
            })
            ->leftJoin('sales_payment_detail', function ($q) {
                return $q->on('sales_payment_detail.invoice_id', 'sih.id')
                    ->whereNull('sales_payment_detail.deleted');
            })
            ->leftJoin('sales_payment_header as sph', function ($q) use ($tanggal) {
                return $q->on('sph.id', 'sales_payment_detail.payment_id')
                    ->whereNull('sph.deleted')
                    ->where('sph.payment_date', $tanggal); // dipindah ke ON, bukan WHERE
            })
            ->leftJoinSub($rpdSub, 'rpd', function ($q) {
                return $q->on('rpd.invoice_id', '=', 'sih.id');
            })
            ->leftJoin('receive_payment_header as rph', function ($q) use ($tanggal) {
                return $q->on('rph.id', 'rpd.receive_id')
                    ->where('rph.visit_date', $tanggal);
            })
            ->join('warehouse as w', 'w.id', 'sih.warehouse_id')
            ->leftJoin('users as usr', 'usr.id', 'soh.salesman')
            ->join('customer as cc', 'cc.id', 'sih.customer_id')
            ->leftJoin('region as kec', 'kec.id', 'cc.kecamatan')
            ->join('term_of_payment as tp', 'tp.id', 'cc.payment_terms')
            ->leftJoin('users as usr_payment', 'usr_payment.id', 'sph.created_by')
            ->whereNull('packing_list.deleted')
            ->get()->toArray();

        $packingListSales = PackingListSalesman::where('packing_list_salesman.packing_list_no', $pl_no)
            ->select([
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
                'sales_payment_detail.allocated_amount as amount_paid',
                'rpd.amount_paid as total_terbayar_rph',
                'rph.status as status_received',
                'sales_payment_detail.invoice_id',
                'sph.payment_method',
                'doh.do_number',
                'doh.do_number as dohs_number',
                'doh.do_date',
                'doh.do_date as dohs_date',
                'sales_payment_detail.allocated_amount as total_terbayar',
                'rpd.amount_paid as total_terbayar_rph',
                'tp.remarks as top_customer',
                'w.name as warehouse_name',
                'sih.id as invoice_id',
                'usr.id as salesman_id'
            ])
            ->join('users as usr', 'usr.id', 'packing_list_salesman.salesman')
            ->join('packing_list_salesman_invoice as pld', 'pld.packing_list_id', 'packing_list_salesman.id')
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.id', 'pld.invoice_id')->whereNull('sih.deleted');
            })
            ->join('sales_order_headers as soh', 'soh.id', 'sih.sales_order')
            ->join('delivery_order_header as doh', function ($q) {
                return $q->on('doh.so_id', 'soh.id')->whereNull('doh.deleted');
            })
            ->leftJoin('sales_payment_detail', function ($q) use ($tanggal) {
                return $q->on('sales_payment_detail.invoice_id', 'sih.id')
                    ->whereNull('sales_payment_detail.deleted')
                    ->whereRaw('DATE(sales_payment_detail.created_at) = ?', $tanggal);
            })
            ->leftJoin('sales_payment_header as sph', function ($q) use ($tanggal) {
                return $q->on('sph.id', 'sales_payment_detail.payment_id')
                    ->whereNull('sph.deleted')
                    ->where('sph.payment_date', $tanggal); // dipindah ke ON, bukan WHERE
            })
            ->leftJoinSub($rpdSub, 'rpd', function ($q) {
                return $q->on('rpd.invoice_id', '=', 'sih.id');
            })
            ->leftJoin('receive_payment_header as rph', function ($q) use ($tanggal) {
                return $q->on('rph.id', 'rpd.receive_id')
                    ->where('rph.visit_date', $tanggal);
            })
            ->join('warehouse as w', 'w.id', 'sih.warehouse_id')
            ->join('customer as cc', 'cc.id', 'sih.customer_id')
            ->leftJoin('region as kec', 'kec.id', 'cc.kecamatan')
            ->join('term_of_payment as tp', 'tp.id', 'cc.payment_terms')
            ->leftJoin('users as usr_payment', 'usr_payment.id', 'sph.created_by')
            ->whereNull('packing_list_salesman.deleted')
            ->get()->toArray();

        $plMerge = array_merge($packingLists, $packingListSales);
        // echo '<pre>';
        // print_r($plMerge);
        // die;
        return $plMerge;
    }

    public function cetakRekapan(Request $request)
    {
        $data = $request->all();
        $packing_list = $data['packing_list'];

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
        // if ($salesmans != '' && $user_group != 5) {
        //     $plTagihan = new PLTagihanController();
        //     $data['tanggal'] = $data['date'] ?? date('Y-m-d');
        //     $routeplan = $plTagihan->getRoutePlanSales($data);
        //     $customers = empty($routeplan) ? [] : collect($routeplan)->pluck('customer_id')->unique()->toArray();
        //     if (isset($data['salesman'])) {
        //         $salesman = User::where('id', $data['salesman'])->first();
        //         $tagihanOther = SalesInvoiceTagihan::where('tgl_tagih', $data['date'])->where('salesman_id', $salesman->id)->get();
        //         $customers = array_merge($customers, $tagihanOther->pluck('customer_id')->unique()->toArray());
        //     }
        //     $salesPayment = $this->getListRekapanSalesman($customers, $data['tanggal'], $salesmans->id);
        //     // echo '<pre>';
        //     // print_r($salesPayment);
        //     // die;
        //     $invoice_ids = collect($salesPayment)->pluck('invoice_id')->unique()->toArray();
        //     $salesReturns = $this->getListSalesReturn($invoice_ids);
        //     $data['data_payment'] = $salesPayment;
        //     $data['data_return'] = $salesReturns;
        // }

        // if ($salesmans == '') {
        $plTagihan = new PLTagihanController();
        $data['tanggal'] = $data['date'] ?? date('Y-m-d');
        // $routeplan = $plTagihan->getRoutePlanSalesAll($data);
        // $customers = empty($routeplan) ? [] : collect($routeplan)->pluck('customer_id')->unique()->toArray();
        // if (isset($data['salesman'])) {
        //     $salesman = User::where('id', $data['salesman'])->first();
        //     $tagihanOther = SalesInvoiceTagihan::where('tgl_tagih', $data['date'])->where('salesman_id', $salesman->id)->get();
        //     $customers = array_merge($customers, $tagihanOther->pluck('customer_id')->unique()->toArray());
        // }
        $salesPayment = $this->getListRekapan($data);
        $data['data_payment'] = $salesPayment;
        $invoice_ids = collect($salesPayment)->pluck('invoice_id')->unique()->toArray();
        $salesReturns = $this->getListSalesReturn($invoice_ids);
        $data['data_return'] = $salesReturns;
        // }

        // if ($salesmans != '' && $user_group == 5) {
        //     $data['tanggal'] = $data['date'] ?? date('Y-m-d');
        //     $salesPayment = $this->getListRekapanDriver($salesmans->nik, $data['tanggal']);
        //     $data['data_payment'] = $salesPayment;
        //     $invoice_ids = collect($salesPayment)->pluck('invoice_id')->unique()->toArray();
        //     $salesReturns = $this->getListSalesReturn($invoice_ids);
        //     // echo '<pre>';
        //     // print_r($salesReturns);
        //     // die;
        //     $data['data_return'] = $salesReturns;
        // }

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
