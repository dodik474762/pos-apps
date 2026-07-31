<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\SalesPlanController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Karyawan;
use App\Models\Transaction\PackingListSalesman;
use App\Models\Transaction\PackingListSalesmanInvoice;
use App\Models\Transaction\SalesInvoiceHeader;
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
        }

        // echo '<pre>';
        // print_r($salesman);
        // die;
        $invoices = $this->getAllInvoiceCetak($customers, null, $salesman->id ?? null);
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

    public function getAllInvoiceCetak($customers = [], $date = null, $salesman = null)
    {
        $date = $date ?? date('Y-m-d');
        $tagihanOther = SalesInvoiceTagihan::where('tgl_tagih', $date)->where('salesman_id', $salesman)->get();
        $invoices = $tagihanOther->pluck('invoice_id')->unique()->toArray();

        // query tetap dijalankan kalau ada customers ATAU ada invoices dari tagihan
        $datadb = (empty($customers) && empty($invoices)) ? [] : DB::table('sales_invoice_header as m')
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
                'usr.name as salesman_name',
                'kec.name as kecamatan_name'
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
            ->whereNull('pld.id')
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            ->join('term_of_payment as tp', 'tp.id', 'cc.payment_terms')
            ->leftJoin('users as usr', 'usr.id', 'soh.salesman')
            ->leftJoin('region as kec', 'kec.id', 'cc.kecamatan')
            ->whereNull('m.deleted')
            ->whereRaw('(m.total_amount - m.amount_paid) > 0')
            // ==== bagian yang diubah ====
            ->where(function ($q) use ($customers, $salesman, $invoices) {
                // kondisi normal: customer harus in $customers, dan kalau ada filter salesman, harus cocok
                $q->where(function ($q2) use ($customers, $salesman) {
                    $q2->whereIn('cc.id', $customers)
                        ->when($salesman != '', function ($q3) use ($salesman) {
                            return $q3->where('soh.salesman', $salesman);
                        });
                });

                // kondisi tambahan: kalau invoice sudah pernah ditagihkan (ada di $invoices),
                // tampilkan tanpa peduli customer_id maupun soh.salesman
                $q->when($invoices != [], function ($qOr) use ($invoices) {
                    return $qOr->orWhereIn('m.id', $invoices);
                });
            })
            // ==== selesai bagian yang diubah ====
            ->orderBy('m.id', 'desc');

        $datadb = (empty($customers) && empty($invoices)) ? [] : $datadb->get();

        // echo '<pre>';
        // print_r($datadb);
        // die;
        return $datadb;
    }

    public function getAllInvoiceCetakByIds($ids = [], $date = null)
    {
        $date = $date ?? date('Y-m-d');
        $datadb = empty($ids) ? [] : DB::table('sales_invoice_header as m')
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
                'usr.name as salesman_name',
                'kec.name as kecamatan_name'
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
            ->leftJoin(DB::raw('(
    SELECT so_id, do_number, do_date
    FROM delivery_order_header
    WHERE id IN (
        SELECT MAX(id) FROM delivery_order_header GROUP BY so_id
    )
) as dohs'), 'dohs.so_id', 'soh.id')
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            ->join('term_of_payment as tp', 'tp.id', 'cc.payment_terms')
            ->leftJoin('users as usr', 'usr.id', 'soh.salesman')
            ->leftJoin('region as kec', 'kec.id', 'cc.kecamatan')
            // ->where('m.invoice_date', $date)
            ->whereNull('m.deleted')
            ->whereIn('m.status', ['POSTED', 'PARTIAL PAID', 'PACKED', 'DRAFT', 'PAID'])
            ->whereIn('m.id', $ids)
            // ->where('m.invoice_date', '>=', $date)
            ->orderBy('m.id', 'desc');

        $datadb = empty($ids) ?  [] : $datadb->get();

        // echo '<pre>';
        // print_r($datadb);
        // die;

        return $datadb;
    }


    public function cetak(Request $request)
    {
        $data = $request->all();
        $plTagihan = PackingListSalesman::where('id', $data['id'])->first();
        $data['salesman'] = $plTagihan->salesman;
        $data['tanggal'] = $plTagihan->packing_date;
        $company = CompanyModel::where('id', session('id_company'))->first();
        // $routeplan = $this->getRoutePlanSales($data);
        // $customers = empty($routeplan) ? [] : collect($routeplan)->pluck('customer_id')->unique()->toArray();
        $salesman = User::where('id', $data['salesman'])->first();
        $salesman_name = ! empty($salesman) ? $salesman->name : '-';
        $qr = '';
        // if (isset($data['salesman'])) {
        //     $tagihanOther = SalesInvoiceTagihan::where('tgl_tagih', $data['tanggal'])->where('salesman_id', $salesman->id)->get();
        //     $customers = array_merge($customers, $tagihanOther->pluck('customer_id')->unique()->toArray());
        // }
        // $invoices = $this->getAllInvoiceCetak($customers, null, $salesman->id);
        // foreach ($invoices as $key => $value) {
        //     $inv_update = SalesInvoiceHeader::where('id', $value->id)->first();
        //     $inv_update->pl_date = $data['tanggal'];
        //     $inv_update->pl_by = $salesman->id;
        //     $inv_update->save();
        // }

        $invoices = PackingListSalesmanInvoice::where('packing_list_salesman_invoice.packing_list_id', $data['id'])
            ->select([
                'sih.total_amount',
                'sih.amount_paid',
                'doh.do_date',
                'doh.do_date as dohs_date',
                'doh.do_number',
                'sih.invoice_number',
                'sih.invoice_date',
                'sih.due_date',
                'cc.nama_customer',
                'cc.code as customer_code',
                'sih.status',
                'tp.remarks as top_name'
            ])
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.id', 'packing_list_salesman_invoice.invoice_id')
                    ->whereNull('sih.deleted');
            })
            ->join('customer as cc', 'cc.id', 'sih.customer_id')
            ->join('term_of_payment as tp', 'tp.id', 'cc.payment_terms')
            ->leftJoin('delivery_order_header as doh', function ($q) {
                return $q->on('doh.so_id', 'sih.sales_order')
                    ->whereNull('doh.deleted');
            })
            ->get();
        // echo '<pre>';
        // print_r($invoices);
        // die;

        // if (isset($data['ids'])) {
        //     $invoices = $invoices->whereIn('id', explode(',', $data['ids']));
        // }

        $invoices = $invoices->values();


        $tanggal_rute = $data['tanggal'];
        $pdf = Pdf::loadView('web.pl_tagihan.print.po-print', compact('invoices', 'plTagihan',  'company', 'qr', 'salesman', 'salesman_name', 'tanggal_rute'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('PL-' . $salesman_name . '.pdf');
    }
}
