<?php

namespace App\Http\Controllers\web;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Master\Region;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $userGroup;
    private $id_user;

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->userGroup = session('akses');
        $this->id_user = session('user_id');
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/libs/leaflet/leaflet.js'),
            'js-2' => asset('assets/js/controllers/dashboard.js'),
            'js-3' => asset('assets/js/controllers/notification.js'),
            'css-1' => asset('assets/libs/leaflet/leaflet.css'),
        );
    }

    public function getTitleParent()
    {
        return "Monitoring";
    }

    public function getTableName()
    {
        return "";
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $year = isset($data['year']) ? $data['year'] : date('Y');
        $data['year'] = $year;
        $data['data'] = [];
        $data['username'] = session('username');
        $data['akses'] = session('akses');
        $data['data_province'] = Region::whereNull('parent')->whereNull('deleted')->get()->toArray();
        $data['data_salesman'] = User::whereNull('deleted')->get(['id', 'name']);
        $data['summary_po'] = $this->getSummaryPO($year);
        $data['summary_so'] = $this->getSummarySO($year);
        $data['summary_invoice'] = $this->getSummaryInvoice($year);
        // echo '<pre>';
        // print_r($data['summary_invoice']);
        // exit;

        // $data['gross_profit'] = $data['summary_so']['summary'] - $data['summary_po']['summary_po'];
        $data['gross_profit'] = $data['summary_invoice']['summary_netto'] - $data['summary_invoice']['total_cogs'];
        $view = view('web.dashboard.index', $data);

        $put['group_karyawan'] = $this->getListGroupKaryawan();
        $put['title_content'] = 'Dashboard';
        $put['title_top'] = 'Dashboard';
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }


    public function getListGroupKaryawan()
    {
        $data = DB::table('karyawan_group as kg')->whereNull('kg.deleted')
            ->select(['kg.*', 'dic.keterangan as group_name'])
            ->join('karyawan as kry', 'kry.id', '=', 'kg.karyawan')
            ->join('dictionary as dic', 'dic.term_id', '=', 'kg.group')
            ->join('users as usr', 'usr.nik', '=', 'kry.nik')
            ->where('usr.id', session('user_id'))
            ->get()->toArray();
        return $data;
    }

    public function getSummaryPO($year = '')
    {
        $year = ($year == '') ? date('Y') : $year;

        $totalPO = DB::table('purchase_order')
            ->whereYear('po_date', $year)
            ->where('is_active', 1)
            ->whereNull('deleted');
        $summaryPO = $totalPO->sum('grand_total');
        $jumlahPO = $totalPO->count();

        return [
            'summary_po' => $summaryPO,
            'jumlah_po' => $jumlahPO
        ];
    }

    public function getSummarySO($year = '')
    {
        $year = ($year == '') ? date('Y') : $year;

        $totalSales = DB::table('sales_order_headers')
            ->whereYear('so_date', $year)
            ->whereNull('deleted');
        // ->whereIn('status', ['confirmed', 'completed', 'partial']);

        $summary = $totalSales->sum('total_amount');
        $jumlah = $totalSales->count();

        return [
            'summary' => $summary,
            'jumlah' => $jumlah
        ];
    }

    public function getSummaryInvoice($year = '')
    {
        $year = ($year == '') ? date('Y') : $year;

        $outstandingReceivable = DB::table('sales_invoice_header')
            ->whereNull('deleted')
            ->where('invoice_number', 'SI06260204')
            ->whereYear('invoice_date', $year)
            ->whereIn('status', ['POSTED', 'PARTIAL PAID', 'DRAFT', 'PAID']);


        $summary = $outstandingReceivable->selectRaw('SUM(total_amount - amount_paid) as outstanding')
            ->value('outstanding');
        $summary_netto = $outstandingReceivable->sum('total_amount');
        $jumlah = $outstandingReceivable->count();
        $summary_gross = $outstandingReceivable->sum('subtotal');

        $cogsQuery = DB::table('sales_invoice_detail as sid')
            ->join('sales_invoice_header as sih', 'sih.id', '=', 'sid.invoice_id')
            ->join('product_uom as pu', function ($join) {
                $join->on('pu.product', '=', 'sid.product_id')
                    ->where('pu.state', '=', 'large')
                    ->whereNull('pu.deleted');
            })
            ->join('sales_order_details as sod', 'sod.id', 'sid.so_detail_id')
            ->join('product_uom as pu_con', function ($join) {
                $join->on('pu_con.product', '=', 'sid.product_id')
                    ->where('pu_con.unit_tujuan', '=', 'sod.unit')
                    ->whereNull('pu.deleted');
            })
            ->where('sih.invoice_number', 'SI06260204')
            ->whereNull('sih.deleted')
            ->where('sid.product_id', 120)
            ->whereYear('sih.invoice_date', $year)
            ->whereIn('sih.status', ['POSTED', 'PARTIAL PAID', 'DRAFT', 'PAID'])
            ->select(
                DB::raw("
                SUM(
                    (sid.qty * pu_con.nilai_konversi_terkecil) 
                    COALESCE((
                        SELECT puc.cost 
                        FROM product_uom_cost puc 
                        WHERE puc.product = sid.product_id 
                          AND puc.date_start <= sih.invoice_date 
                        ORDER BY puc.date_start DESC 
                        LIMIT 1
                    ), 0)
                ) as total_cogs
            "),
                'sid.product_id',
                'sid.qty',
                'pu.nilai_konversi_terkecil',
                'sih.invoice_date'
            )
            ->groupBy('sid.product_id', 'sid.qty', 'pu.nilai_konversi_terkecil', 'sih.invoice_date')
            ->get();
        // ->value('total_cogs');
        echo '<pre>';
        print_r($cogsQuery);
        die;

        return [
            'summary' => $summary,
            'jumlah' => $jumlah,
            'summary_netto' => $summary_netto,
            'summary_gross' => $summary_gross,
            'total_cogs' => $cogsQuery ?: 0
        ];
    }

    public function getInvoiceOutstanding(Request $request)
    {
        $data = $request->all();
        $year = isset($data['year']) ? $data['year'] : date('Y');

        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $outstandingReceivable = DB::table('sales_invoice_header as sih')
            ->whereNull('sih.deleted')
            ->whereYear('sih.invoice_date', $year)
            ->whereIn('sih.status', ['POSTED', 'PARTIAL PAID']);


        $datadb = $outstandingReceivable->select([
            'sih.*',
            DB::raw('(sih.total_amount - sih.amount_paid) as outstanding'),
            'c.code as customer_code',
            'c.nama_customer'
        ])
            ->join('customer as c', 'c.id', '=', 'sih.customer_id');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('sih.invoice_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('sih.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('c.code', 'LIKE', '%' . $keyword . '%');
                });
            }
            if (isset($_POST['order'][0]['column'])) {
                switch ($_POST['order'][0]['column']) {
                    case 0:
                        $datadb->orderBy('sih.id', $_POST['order'][0]['dir']);
                        break;
                    default:
                        break;
                }
            }
            $data['recordsFiltered'] = $datadb->get()->count();

            if (isset($_POST['length'])) {
                $datadb->limit($_POST['length']);
            }
            if (isset($_POST['start'])) {
                $datadb->offset($_POST['start']);
            }
        }
        $resultdb = [];
        $datadb = $datadb->get()->toArray();
        foreach ($datadb as $key => $value) {
            $value->akses = session('akses');
            $resultdb[] = $value;
        }
        $data['data'] = $resultdb;
        $data['draw'] = $_POST['draw'];
        $query = DB::getQueryLog();

        return response()->json($data);
    }

    public function getGrafikPenjualan(Request $request)
    {
        $data = $request->all();
        $year = isset($data['year']) ? $data['year'] : date('Y');
        $resultStatusSo = [];
        for ($i = 1; $i < 13; $i++) {
            $month = $i < 10 ? '0' . $i : $i;
            $total = DB::table('sales_order_headers')->whereNull('sales_order_headers.deleted')
                ->where('sales_order_headers.status', '!=', 'CANCELLED')
                ->where(function ($q) use ($year, $month) {
                    return $q->where('sales_order_headers.created_at', 'like', '%' . $year . '-' . $month . '%');
                })
                ->count();
            $resultStatusSo[] = $total;
        }

        $resultsStatusSoCancel = [];
        for ($i = 1; $i < 13; $i++) {
            $month = $i < 10 ? '0' . $i : $i;
            $total = DB::table('sales_order_headers')
                ->whereNotNull('sales_order_headers.deleted')
                ->where(function ($q) use ($year, $month) {
                    return $q->where('sales_order_headers.created_at', 'like', '%' . $year . '-' . $month . '%');
                })
                ->count();
            $resultsStatusSoCancel[] = $total;
        }

        $result['is_valid'] = true;
        $result['so_cancel'] = $resultsStatusSoCancel;
        $result['so_ok'] = $resultStatusSo;

        return response()->json($result);
    }

    public function getMapVisit(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();
        $date_visit = $data['date_visit'];
        $salesman = $data['salesman'];

        $result['is_valid'] = true;
        $datadb = DB::table('sales_order_headers as soh')
            ->select([
                'soh.*',
                'c.code as customer_code',
                'c.nama_customer',
            ])
            ->join('customer as c', 'c.id', '=', 'soh.customer_id')
            ->where('soh.platform', 'mobile')
            ->whereNull('soh.deleted');
        if ($salesman != '') {
            $datadb->where('soh.salesman', $salesman);
        }
        if ($date_visit != '') {
            $datadb->where('soh.so_date', $date_visit);
        } else {
            $datadb->whereYear('soh.so_date', date('Y'));
        }

        $datadb = $datadb->get()->toArray();
        $resultdb = [];
        foreach ($datadb as $key => $value) {
            $resultdb[] = $value;
        }

        $result['data'] = $resultdb;
        $result['total_rows'] = count($resultdb);
        $result['query'] = DB::getQueryLog();
        return response()->json($result);
    }
}
