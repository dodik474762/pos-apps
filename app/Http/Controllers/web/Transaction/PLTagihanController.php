<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Karyawan;
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
        // print_r($data);die;
        $routeplan = $this->getRoutePlanSales($data);
        $customers = empty($routeplan) ? [] : collect($routeplan)->pluck('customer_id')->unique()->toArray();
        $invoices = $this->getAllInvoiceCetak($customers);
        $data['invoices'] = $invoices;
        $data['salesmans'] = Karyawan::whereNull('deleted')->get();
        $view = view('web.pl_tagihan.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getRoutePlanSales($data){
        $month = date('m');
        $year = date('Y');
        if(isset($data['tanggal'])){
            list($year, $month, $day) = explode('-', $data['tanggal']);
        }else{
            $data['tanggal'] = date('Y-m-d');
        }
        $salesman = isset($data['salesman']) ? $data['salesman'] : 0;

        $today = Carbon::parse($data['tanggal']);

        // Nama hari (Monday, Tuesday, ...)
        $dayName = $today->format('l');

        // echo $dayName;die;die;
        // Minggu ke berapa dalam bulan
        $weekOfMonth = $today->weekOfMonth;

        // Tentukan ganjil / genap
        $weekType = ($weekOfMonth % 2 === 0) ? 'EVEN' : 'ODD';

        DB::enableQueryLog();
         $datadb = DB::table('sales_plan_header as h')
            ->join('sales_plan_detail as d', 'd.header_id', '=', 'h.id')
            ->join('customer as c', 'c.id', '=', 'd.customer_id')
            ->join('customer_category as cc', 'cc.id', '=', 'c.customer_category')
            ->leftJoin('region as pr', 'pr.id', '=', 'c.provinsi')
            ->leftJoin('region as kt', 'kt.id', '=', 'c.kota')
            ->leftJoin('region as kc', 'kc.id', '=', 'c.kecamatan')
            ->leftJoin('region as kl', 'kl.id', '=', 'c.kelurahan')
            ->leftJoin('product as p', 'p.id', '=', 'd.product_id')
            ->select(
                'h.*',
                'd.*',
                'c.code as customer_code',
                'c.nama_customer',
                'pr.name as nama_provinsi',
                'kt.name as nama_kota',
                'kc.name as nama_kecamatan',
                'kl.name as nama_kelurahan',
                'c.address',
                'p.name as product_name',
                'p.code as product_code',
                'cc.category'
            )
            ->whereNull('h.deleted')
            ->where('h.period_year', $year)
            ->where('h.period_month', $month)
            ->where('h.salesman', $salesman)
             // =======================
            // FILTER HARI & MINGGU
            // =======================
            ->where('d.week_number', $weekOfMonth)
            ->where('d.week_type', $weekType)
            ->where('d.day_of_week', $dayName)

            ->orderBy('h.id')
            ->orderBy('d.week_number')
            ->get();
        // echo '<pre>';
        // print_r(DB::getQueryLog());die;

        return $datadb;
    }

    public function getAllInvoiceCetak($customers = [])
    {
        $datadb = empty($customers) ? [] : DB::table('sales_invoice_header as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'cc.nama_customer',
                'cc.code as customer_code',
                'do.do_number',
                'do.do_date',
                'w.name as warehouse_name',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('delivery_order_header as do', 'do.id', 'm.do_id')
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            // ->where('m.invoice_date', $date)
            ->whereNull('m.deleted')
            ->whereIn('m.status', ['POSTED', 'PARTIAL PAID'])
            ->whereIn('cc.id', $customers)
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
        $invoices = $this->getAllInvoiceCetak($customers);
        $salesman = Karyawan::where('id', $data['salesman'])->first();
        $salesman_name = ! empty($salesman) ? $salesman->nama_lengkap : '-';
        $qr = '';

        // echo '<pre>';
        // print_r($invoices);die;

        $pdf = Pdf::loadView('web.pl_tagihan.print.po-print', compact('invoices', 'routeplan', 'company', 'qr', 'salesman', 'salesman_name'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('PL-'.$salesman_name.'.pdf');
    }
}
