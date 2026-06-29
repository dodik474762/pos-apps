<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\SalesPlanController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Karyawan;
use App\Models\Transaction\SalesInvoiceTagihan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PLTagihanController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/pl_tagihan.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Tagihan';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Packing List';
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);
        // die;
        $routeplan = $this->getRoutePlanSales($data);
        $customers = empty($routeplan) ? [] : collect($routeplan)->pluck('customer_id')->unique()->toArray();
        if (isset($data['salesman'])) {
            $salesman = User::where('id', $data['salesman'])->first();
            $tagihanOther = SalesInvoiceTagihan::where('tgl_tagih', $data['tanggal'])->where('salesman_id', $salesman->id)->get();
            $customers = array_merge($customers, $tagihanOther->pluck('customer_id')->unique()->toArray());
        }

        $invoices = $this->getAllInvoiceCetak($customers);
        $data['invoices'] = $invoices;
        $data['salesmans'] = User::whereNull('deleted')->whereIn('user_group', [6, 4])->get(['id', 'nik', 'name']);
        $view = view('web.pl_tagihan.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getRoutePlanSales($data)
    {
        $month = date('m');
        $year = date('Y');
        if (isset($data['tanggal'])) {
            list($year, $month, $day) = explode('-', $data['tanggal']);
        } else {
            $data['tanggal'] = date('Y-m-d');
        }
        $salesman = isset($data['salesman']) ? $data['salesman'] : 0;

        $today = Carbon::parse($data['tanggal']);

        $salesPlan = new SalesPlanController();
        $dailyVisit = $salesPlan->getDailyVisits($salesman, $today);
        // echo '<pre>';
        // print_r($dailyVisit);
        // die;

        return $dailyVisit;
    }

    public function getRoutePlanSalesAll($data)
    {
        $month = date('m');
        $year = date('Y');
        if (isset($data['tanggal'])) {
            list($year, $month, $day) = explode('-', $data['tanggal']);
        } else {
            $data['tanggal'] = date('Y-m-d');
        }
        $salesman = isset($data['salesman']) ? $data['salesman'] : 0;

        $today = Carbon::parse($data['tanggal']);

        $salesPlan = new SalesPlanController();
        $dailyVisit = $salesPlan->getDailyVisits('all', $today);
        // echo '<pre>';
        // print_r($dailyVisit);
        // die;

        return $dailyVisit;
    }

    public function getAllInvoiceCetak($customers = [], $date = null)
    {
        $date = $date ?? date('Y-m-d');
        $datadb = empty($customers) ? [] : DB::table('sales_invoice_header as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'cc.nama_customer',
                'cc.code as customer_code',
                'do.do_number',
                'do.do_date',
                'dohs.do_number as dohs_number',
                'dohs.do_date as dohs_date',
                'w.name as warehouse_name',
                'soh.so_number',
                'tp.remarks as top_name',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->leftJoin('delivery_order_header as do', 'do.id', 'm.do_id')
            ->leftJoin('sales_order_headers as soh', function ($q) {
                return $q->on('soh.id', 'do.id')->orOn('soh.id', 'm.sales_order');
            })
            ->leftJoin('delivery_order_header as dohs', 'dohs.so_id', 'soh.id')
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            ->join('term_of_payment as tp', 'tp.id', 'cc.payment_terms')
            // ->where('m.invoice_date', $date)
            ->whereNull('m.deleted')
            ->whereIn('m.status', ['POSTED', 'PARTIAL PAID', 'PACKED', 'DRAFT'])
            ->whereIn('cc.id', $customers)
            // ->where('m.invoice_date', '>=', $date)
            ->orderBy('m.id', 'desc');

        $datadb = empty($customers) ?  [] : $datadb->get();

        return $datadb;
    }

    public function cetak(Request $request)
    {
        $data = $request->all();
        $company = CompanyModel::where('id', session('id_company'))->first();
        $routeplan = $this->getRoutePlanSales($data);
        $customers = empty($routeplan) ? [] : collect($routeplan)->pluck('customer_id')->unique()->toArray();
        $salesman = User::where('id', $data['salesman'])->first();
        $salesman_name = ! empty($salesman) ? $salesman->name : '-';
        $qr = '';
        if (isset($data['salesman'])) {
            $tagihanOther = SalesInvoiceTagihan::where('tgl_tagih', $data['tanggal'])->where('salesman_id', $salesman->id)->get();
            $customers = array_merge($customers, $tagihanOther->pluck('customer_id')->unique()->toArray());
        }
        $invoices = $this->getAllInvoiceCetak($customers);

        if (isset($data['ids'])) {
            $invoices = $invoices->whereIn('id', explode(',', $data['ids']));
        }

        $invoices = $invoices->values();


        $tanggal_rute = $data['tanggal'];
        $pdf = Pdf::loadView('web.pl_tagihan.print.po-print', compact('invoices', 'routeplan', 'company', 'qr', 'salesman', 'salesman_name', 'tanggal_rute'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('PL-' . $salesman_name . '.pdf');
    }
}
