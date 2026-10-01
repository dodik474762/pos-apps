<?php

namespace App\Http\Controllers\web;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Master\Region;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // status invoice yang dihitung sebagai penjualan aktif
    const STATUS_INVOICE = ['PACKED', 'POSTED', 'PARTIAL PAID', 'PAID', 'DRAFT'];

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
        // print_r($data['summary_so']);
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

        // total pesanan = SO yang benar-benar sudah terbit jadi invoice.
        // status 'correction' tidak dihitung, dan SO yang belum jadi invoice diabaikan
        // supaya basisnya sama dengan card Total Penjualan.
        $totalSales = DB::table('sales_order_headers')
            ->whereYear('so_date', $year)
            ->where('total_amount', '>', 0)
            ->whereNull('deleted')
            ->whereIn('status', ['confirmed', 'draft'])
            ->whereExists(function ($q) use ($year) {
                $q->select(DB::raw(1))
                    ->from('sales_invoice_header as sih')
                    ->whereColumn('sih.sales_order', 'sales_order_headers.id')
                    ->whereNull('sih.deleted')
                    ->whereYear('sih.invoice_date', $year);
            });

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

        $invoiceBase = DB::table('sales_invoice_header')
            ->whereNull('deleted')
            // ->where('invoice_number', 'SI06260204')
            ->whereYear('invoice_date', $year)
            ->whereIn('status', self::STATUS_INVOICE);

        // total tagihan outstanding: hanya invoice yang masih punya sisa tagihan
        $outstandingReceivable = (clone $invoiceBase)
            ->whereRaw('(total_amount - amount_paid) > 0');

        $summary = $outstandingReceivable->selectRaw('SUM(total_amount - amount_paid) as outstanding')
            ->value('outstanding');
        $jumlah_outstanding = $outstandingReceivable->count();

        $summary_netto = $invoiceBase->sum('total_amount');
        $jumlah = $invoiceBase->count();
        $summary_gross = $invoiceBase->sum('subtotal');

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
                    ->on('pu_con.unit_tujuan', '=', 'sod.unit')
                    ->whereNull('pu_con.deleted');
            })
            // ->where('sih.invoice_number', 'SI06260204')
            ->whereNull('sih.deleted')
            ->whereNull('sid.deleted')
            ->whereNull('sid.flag_cancel')
            // ->where('sid.product_id', 120)
            ->whereYear('sih.invoice_date', $year)
            ->whereIn('sih.status', self::STATUS_INVOICE)
            ->select(
                DB::raw("
                       SUM(
        sid.qty 
        * CASE 
            WHEN sod.unit = pu.unit_tujuan THEN 1
            ELSE pu_con.nilai_konversi_terkecil / pu.nilai_konversi_terkecil
          END
        * COALESCE((
            SELECT puc.cost FROM product_uom_cost puc 
            WHERE puc.product = sid.product_id AND puc.date_start <= sih.invoice_date 
            ORDER BY puc.date_start DESC LIMIT 1
          ), 0)
    ) as total_cogs
                            "),
                // 'sid.product_id',
                // 'sid.qty',
                // 'pu.nilai_konversi_terkecil',
                // 'sih.invoice_date',
                // 'sid.so_detail_id',
                // 'sod.unit',
                // 'pu_con.nilai_konversi_terkecil as nilai_konversi_terkecil_con'
            )
            // ->groupBy(
            //     'sid.product_id',
            //     'sid.qty',
            //     'pu.nilai_konversi_terkecil',
            //     'sih.invoice_date',
            //     'sid.so_detail_id',
            //     'sod.unit',
            //     'pu_con.nilai_konversi_terkecil'
            // )
            // ->get();
            ->value('total_cogs');
        // echo '<pre>';
        // print_r($cogsQuery);
        // die;

        return [
            'summary' => $summary ?: 0,
            'jumlah' => $jumlah,
            'jumlah_outstanding' => $jumlah_outstanding,
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
        // daftar invoice outstanding: basisnya sama dengan card Total Tagihan Outstanding
        $outstandingReceivable = DB::table('sales_invoice_header as sih')
            ->whereNull('sih.deleted')
            ->whereYear('sih.invoice_date', $year)
            ->whereIn('sih.status', self::STATUS_INVOICE)
            ->whereRaw('(sih.total_amount - sih.amount_paid) > 0');


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

        // penjualan valid per bulan, basisnya sama dengan card Total Penjualan
        $validPerBulan = DB::table('sales_invoice_header')
            ->whereNull('deleted')
            ->whereIn('status', self::STATUS_INVOICE)
            ->whereYear('invoice_date', $year)
            ->selectRaw('MONTH(invoice_date) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        // penjualan batal: invoice dihapus atau berstatus CANCELED
        $batalPerBulan = DB::table('sales_invoice_header')
            ->where(function ($q) {
                $q->whereNotNull('deleted')->orWhere('status', 'CANCELED');
            })
            ->whereYear('invoice_date', $year)
            ->selectRaw('MONTH(invoice_date) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $resultStatusSo = [];
        $resultsStatusSoCancel = [];
        for ($i = 1; $i <= 12; $i++) {
            $resultStatusSo[] = (int) ($validPerBulan[$i] ?? 0);
            $resultsStatusSoCancel[] = (int) ($batalPerBulan[$i] ?? 0);
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
            $datadb->where('soh.so_date', date('Y-m-d'));
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
