<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Controllers\api\Transaction\SalesPlanController;
use App\Models\Master\CompanyModel;

class TerimaUangController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/terima_uang.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Transaksi';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Penerimaan Uang Kasir';
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $data['akses_user'] = session('akses');

        $usersdb = isset($data['salesman']) ? User::where('id', $data['salesman'])->first() : null;
        $akses = 6;
        if (!empty($usersdb)) {
            $akses = $usersdb->user_group;
        }
        $routeplan = $akses == 6 ? $this->getRoutePlanSales($data) : $this->getRoutePlanDelivery($data);
        $customers = empty($routeplan) ? [] : collect($routeplan)->pluck('customer_id')->unique()->toArray();
        $invoices = $this->getAllInvoiceCetak($customers, $akses == 5 ? 'delivery' : 'salesman');
        $data['invoices'] = $invoices;
        $data['salesmans'] = User::whereNull('deleted')->whereIn('user_group', [6, 4, 5])->get(['id', 'nik', 'name']);
        $view = view('web.terima_uang.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getRoutePlanDelivery($data)
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

        if ($salesman == 0) {
            return [];
        }

        $dailyVisit = $this->getPackingList($today, $salesman);

        return $dailyVisit;
    }

    public function getPackingList($date, $users = 0)
    {
        $packing_date = $date->format('Y-m-d');

        $datadb = DB::table('packing_list as m')
            ->select([
                'pld.id',
                'm.packing_list_no',
                'u.name as created_by_name',
                'doh.do_number',
                'doh.do_date',
                'c.code as customer_code',
                'c.id as customer_id',
                'c.nama_customer',
                'doh.total_item',
                'pld.confirm_date',
                'c.address',
                'c.latitude',
                'c.longitude',
                'top.code as top_code',
                'top.nilai as top_nilai'
            ])
            ->join('packing_list_do as pld', 'pld.packing_list_id', 'm.id')
            ->join('delivery_order_header as doh', 'doh.id', 'pld.delivery_order_id')
            ->join('customer as c', 'c.id', 'doh.customer_id')
            ->join('term_of_payment as top', 'c.payment_terms', '=', 'top.id')
            ->join('users as u', 'u.id', 'm.created_by')
            ->whereNull('m.deleted')
            ->where(function ($q) {
                return $q->whereIn('m.status', ['PARTIAL', 'NOT DELIVERED'])->orWhereNull('m.status');
            })
            ->where(function ($q) {
                return $q->whereNull('pld.status')->orWhere('pld.status', 'NOT DELIVERED');
            })
            ->where('pld.confirm_date', $packing_date)
            ->orderBy('c.nama_customer')
            ->orderBy('doh.id', 'asc');
        $datadb->where('m.driver', $users);
        $datadb = $datadb->get()->toArray();

        return $datadb;
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

        return $dailyVisit;
    }

    public function getAllInvoiceCetak($customers = [], $type = 'salesman', $date = null)
    {
        $date = $date ?? date('Y-m-d');
        if (empty($customers)) {
            return [];
        }
        $datadb = DB::table('sales_invoice_header as m')
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
                'spd.allocated_amount as total_terbayar',
                'rpd.amount_paid as total_terbayar_rph',
                'top.remarks as top_customer',
                'rph.status as status_received'
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->leftJoin('term_of_payment as top', 'top.id', 'cc.payment_terms')
            ->leftJoin('delivery_order_header as do', 'do.id', 'm.do_id')
            ->leftJoin('sales_order_headers as soh', function ($q) {
                return $q->on('soh.id', 'do.id')->orOn('soh.id', 'm.sales_order');
            })
            ->leftJoin('delivery_order_header as dohs', 'dohs.so_id', 'soh.id')
            ->leftJoin('sales_payment_detail as spd', function ($q) use ($date) {
                return $q->on('spd.invoice_id', 'm.id')->whereNull('spd.deleted')
                    ->whereRaw('CAST(spd.created_at as date) = ?', [$date]);
            })
            ->leftJoin('receive_payment_detail as rpd', 'rpd.invoice_id', 'm.id')
            ->leftJoin('receive_payment_header as rph', function ($q) use ($date) {
                return $q->on('rph.id', 'rpd.receive_id')
                    ->where('rph.visit_date', $date);
            })
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            // ->where('m.invoice_date', $date)
            ->whereNull('m.deleted')
            ->whereIn('m.status', ['POSTED', 'PARTIAL PAID', 'PACKED'])
            ->whereIn('cc.id', $customers)
            // ->where('m.invoice_date', '>=', $date)
            ->orderBy('m.id', 'desc');
        if ($type == 'salesman') {
            $datadb->where('top.code', '!=', 'CASH');
        }
        if ($type == 'delivery') {
            $datadb->where('top.code', '=', 'CASH');
        }

        $datadb = $datadb->get();

        return $datadb;
    }

    public function cetak(Request $request)
    {
        $data = $request->all();
        $company = CompanyModel::where('id', session('id_company'))->first();
        $routeplan = $this->getRoutePlanSales($data);
        $customers = empty($routeplan) ? [] : collect($routeplan)->pluck('customer_id')->unique()->toArray();
        $invoices = $this->getAllInvoiceCetak($customers);
        $salesman = User::where('id', $data['salesman'])->first();
        $salesman_name = ! empty($salesman) ? $salesman->name : '-';
        $qr = '';

        // echo '<pre>';
        // print_r($invoices);die;

        $tanggal_rute = $data['tanggal'];
        $pdf = Pdf::loadView('web.pl_tagihan.print.po-print', compact('invoices', 'routeplan', 'company', 'qr', 'salesman', 'salesman_name', 'tanggal_rute'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('PL-' . $salesman_name . '.pdf');
    }
}
