<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use App\Models\Transaction\SalesOrderHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportPiutangController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function getData(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
        $date_start = $_POST['date_start'] ?? date('Y-m-d');
        $date_end = $_POST['date_end'] ?? date('Y-m-d');
        $principal = $_POST['principal'] ?? '';
        $session_vendor_id = session('vendor_id');
        if (!empty($session_vendor_id)) {
            $principal = $session_vendor_id;
        }

        $datadb = SalesOrderHeader::from('sales_order_headers as m')
            ->select([
                'm.id',
                'm.salesman',
                'm.so_date',
                'c.nama_customer',
                'c.code as customer_code',
                'c.channel_outlet',
                'm.remarks',
                'm.check_in_time',
                'm.check_out_time',
                'k.nama_lengkap as salesman_name',
                'usr.name as salesman_nik',
                'm.status',
                'm.platform',
                'kec.name as kecamatan',
                'kab.name as kabupaten',
                'dv.cicle_type',
                DB::raw('(sih.total_amount - sih.amount_paid) AS outstanding_amount'),
                DB::raw('DATEDIFF(CURDATE(), sih.invoice_date) AS umur_faktur'),
                DB::raw('DATEDIFF(CURDATE(), sih.due_date) AS overdue'),
                DB::raw('
                    CASE
                        WHEN DATEDIFF(CURDATE(), sih.due_date) <= 0 THEN "Belum Jatuh Tempo"
                        WHEN DATEDIFF(CURDATE(), sih.due_date) BETWEEN 1 AND 3 THEN "0-3 Hari"
                        WHEN DATEDIFF(CURDATE(), sih.due_date) BETWEEN 4 AND 6 THEN "4-6 Hari"
                        WHEN DATEDIFF(CURDATE(), sih.due_date) BETWEEN 7 AND 14 THEN "7-14 Hari"
                        WHEN DATEDIFF(CURDATE(), sih.due_date) BETWEEN 15 AND 20 THEN "15-20 Hari"
                        ELSE "21 Hari ke Atas"
                    END AS cluster_overdue
                '),
                'sih.invoice_date',
                'sih.due_date',
                'sih.total_amount',
                'sih.amount_paid',
                'sih.invoice_number',
                'top.remarks as payment_terms',
            ])
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.sales_order', 'm.id')->whereNull('sih.deleted');
            })
            ->leftJoin('customer as cc', 'cc.id', 'sih.customer_id')
            ->leftJoin('term_of_payment as top', 'top.id', 'cc.payment_terms')
            ->leftJoin('users as usr', 'usr.id', 'm.salesman')
            ->leftJoin('karyawan as k', 'k.nik', 'usr.nik')
            ->leftJoin('region as kec', 'kec.id', 'c.kecamatan')
            ->leftJoin('region as kab', 'kab.id', 'c.kota')
            ->leftJoin('daily_visit as dv', function ($q) {
                return $q->on('dv.date_visit', 'm.so_date')
                    ->on('dv.users', 'm.salesman')
                    ->whereNull('dv.deleted');
            })
            // ->where('m.id', '1588')
            // ->where('usr.name', 'SLS-005')
            // ->where('sih.invoice_number', 'SI06260132')
            ->whereNull('m.deleted')
            ->whereNull('sih.deleted');

        if ($principal !== '') {
            $datadb->whereExists(function ($q) use ($principal) {
                $q->select(DB::raw(1))
                    ->from('sales_invoice_detail as sid')
                    ->join('product as pr2', 'pr2.id', '=', 'sid.product_id')
                    ->whereColumn('sid.invoice_id', 'sih.id')
                    ->where('pr2.vendor', $principal)
                    ->whereNull('sid.deleted');
            });
        }

        if (isset($data['types']) && $data['types'] == 'per-penjual') {
            $datadb->where('m.total_amount', '>', 0);
        } else {
            $filter_status = $_POST['filter_status'] ?? 'outstanding';
            if ($filter_status !== 'all') {
                $datadb->having('outstanding_amount', '>', 0);
            }
        }

        if (isset($data['types'])) {
            if ($data['types'] == 'per-penjual') {
                $datadb->whereBetween('sih.invoice_date', [$date_start, $date_end])
                    ->whereNotNull('usr.name')
                    ->orderBy('usr.name', 'asc');
            } else {
                $datadb->whereDate('sih.invoice_date', '=', $tanggal);
            }
        } else {
            $datadb->whereDate('sih.invoice_date', '=', $tanggal);
        }

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();

            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.salesman', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.so_date', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('c.code', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('c.channel_outlet', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.remarks', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.check_in_time', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.check_out_time', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('sih.invoice_number', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('usr.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('top.remarks', 'LIKE', '%' . $keyword . '%');
                });
            }

            if (isset($_POST['order'][0]['column'])) {
                switch ($_POST['order'][0]['column']) {
                    case 0:
                        $datadb->orderBy('m.salesman', $_POST['order'][0]['dir']);
                        break;
                    case 1:
                        $datadb->orderBy('m.so_date', $_POST['order'][0]['dir']);
                        break;
                    case 2:
                        if (isset($_POST['types']) && $_POST['types'] == 'per-penjual') {
                            $datadb->orderBy('usr.name', $_POST['order'][0]['dir']);
                        } else {
                            $datadb->orderBy('sih.invoice_date', $_POST['order'][0]['dir']);
                        }
                        break;
                    case 3:
                        $datadb->orderBy('c.code', $_POST['order'][0]['dir']);
                        break;
                    case 4:
                        $datadb->orderBy('c.channel_outlet', $_POST['order'][0]['dir']);
                        break;
                    case 5:
                        $datadb->orderByRaw('m.check_in_time ' . $_POST['order'][0]['dir']);
                        break;
                    case 6:
                        $datadb->orderByRaw('m.check_out_time ' . $_POST['order'][0]['dir']);
                        break;
                    case 7:
                        $datadb->orderByRaw('m.salesman ' . $_POST['order'][0]['dir']);
                        break;
                    default:
                        $datadb->orderBy('m.salesman', 'asc');
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

        foreach ($datadb as $value) {
            $resultdb[] = $value;
        }
        $data['data'] = $resultdb;
        $data['draw'] = isset($_POST['draw']) ? $_POST['draw'] : '';

        $query = DB::getQueryLog();
        return json_encode($data);
    }

    public function getDataPerProduct(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
        $principal = $_POST['principal'] ?? '';
        $session_vendor_id = session('vendor_id');
        if (!empty($session_vendor_id)) {
            $principal = $session_vendor_id;
        }

        $invoiceTotal = DB::table('sales_invoice_detail')
            ->select('invoice_id', DB::raw('SUM(subtotal) as invoice_subtotal'))
            ->whereNull('deleted')
            ->whereNull('flag_cancel')
            ->groupBy('invoice_id');

        $datadb = SalesOrderHeader::from('sales_order_headers as m')
            ->select([
                'm.id',
                'c.nama_customer',
                'c.code as customer_code',
                'c.channel_outlet',
                'k.nama_lengkap as salesman_name',
                'usr.name as salesman_nik',
                'kec.name as kecamatan',
                'kab.name as kabupaten',
                DB::raw('(sih.total_amount - sih.amount_paid) AS outstanding_amount'),
                DB::raw('DATEDIFF(CURDATE(), sih.invoice_date) AS umur_faktur'),
                DB::raw('DATEDIFF(CURDATE(), sih.due_date) AS overdue'),
                DB::raw('
                    CASE
                        WHEN DATEDIFF(CURDATE(), sih.due_date) <= 0 THEN "Belum Jatuh Tempo"
                        WHEN DATEDIFF(CURDATE(), sih.due_date) BETWEEN 1 AND 3 THEN "0-3 Hari"
                        WHEN DATEDIFF(CURDATE(), sih.due_date) BETWEEN 4 AND 6 THEN "4-6 Hari"
                        WHEN DATEDIFF(CURDATE(), sih.due_date) BETWEEN 7 AND 14 THEN "7-14 Hari"
                        WHEN DATEDIFF(CURDATE(), sih.due_date) BETWEEN 15 AND 20 THEN "15-20 Hari"
                        ELSE "21 Hari ke Atas"
                    END AS cluster_overdue
                '),
                'sih.invoice_date',
                'sih.due_date',
                'sih.total_amount',
                'sih.amount_paid',
                'sih.invoice_number',
                'top.remarks as payment_terms',
                'v.nama_vendor as principal',
                'p.code as product_code',
                'p.name as product_name',
                'p.category as category_product',
                'sid.qty',
                'u.name as unit_name',
                'sid.price',
                'sid.discount',
                'sid.subtotal',
                DB::raw('ROUND(CASE WHEN IFNULL(it.invoice_subtotal, 0) > 0 THEN (sih.total_amount * sid.subtotal / it.invoice_subtotal) ELSE 0 END, 2) as product_total_amount'),
                DB::raw('ROUND(CASE WHEN IFNULL(it.invoice_subtotal, 0) > 0 THEN (sih.amount_paid * sid.subtotal / it.invoice_subtotal) ELSE 0 END, 2) as product_amount_paid'),
                DB::raw('ROUND(CASE WHEN IFNULL(it.invoice_subtotal, 0) > 0 THEN ((sih.total_amount - sih.amount_paid) * sid.subtotal / it.invoice_subtotal) ELSE 0 END, 2) as product_outstanding_amount'),
            ])
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.sales_order', 'm.id')->whereNull('sih.deleted');
            })
            ->join('sales_invoice_detail as sid', function ($q) {
                return $q->on('sid.invoice_id', 'sih.id')
                    ->whereNull('sid.deleted')
                    ->whereNull('sid.flag_cancel');
            })
            ->join('product as p', 'p.id', 'sid.product_id')
            ->leftJoin('vendor as v', 'v.id', 'p.vendor')
            ->leftJoin('sales_order_details as sod', function ($q) {
                return $q->on('sod.id', 'sid.so_detail_id')->whereNull('sod.deleted');
            })
            ->leftJoin('unit as u', 'u.id', 'sod.unit')
            ->leftJoin('customer as cc', 'cc.id', 'sih.customer_id')
            ->leftJoin('term_of_payment as top', 'top.id', 'cc.payment_terms')
            ->leftJoin('users as usr', 'usr.id', 'm.salesman')
            ->leftJoin('karyawan as k', 'k.nik', 'usr.nik')
            ->leftJoin('region as kec', 'kec.id', 'c.kecamatan')
            ->leftJoin('region as kab', 'kab.id', 'c.kota')
            ->leftJoinSub($invoiceTotal, 'it', function ($q) {
                return $q->on('it.invoice_id', 'sih.id');
            })
            ->whereNull('m.deleted')
            ->whereNull('sih.deleted')
            ->whereRaw('(sih.total_amount - sih.amount_paid) > 0');

        if ($principal !== '') {
            $datadb->where('p.vendor', $principal);
        }

        $datadb->whereDate('sih.invoice_date', '=', $tanggal);

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();

            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('c.nama_customer', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('c.code', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('c.channel_outlet', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('sih.invoice_number', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('usr.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('top.remarks', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('p.code', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('p.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('p.category', 'LIKE', '%' . $keyword . '%');
                });
            }

            if (isset($_POST['order'][0]['column'])) {
                switch ($_POST['order'][0]['column']) {
                    case 1:
                        $datadb->orderBy('sih.invoice_number', $_POST['order'][0]['dir']);
                        break;
                    case 2:
                        $datadb->orderBy('sih.invoice_date', $_POST['order'][0]['dir']);
                        break;
                    case 3:
                        $datadb->orderBy('c.code', $_POST['order'][0]['dir']);
                        break;
                    case 4:
                        $datadb->orderBy('c.nama_customer', $_POST['order'][0]['dir']);
                        break;
                    case 5:
                        $datadb->orderBy('top.remarks', $_POST['order'][0]['dir']);
                        break;
                    case 6:
                        $datadb->orderBy('kec.name', $_POST['order'][0]['dir']);
                        break;
                    case 7:
                        $datadb->orderBy('kab.name', $_POST['order'][0]['dir']);
                        break;
                    case 8:
                        $datadb->orderBy('c.channel_outlet', $_POST['order'][0]['dir']);
                        break;
                    case 9:
                        $datadb->orderBy('usr.name', $_POST['order'][0]['dir']);
                        break;
                    case 10:
                        $datadb->orderBy('k.nama_lengkap', $_POST['order'][0]['dir']);
                        break;
                    case 11:
                        $datadb->orderBy('sih.due_date', $_POST['order'][0]['dir']);
                        break;
                    case 12:
                        $datadb->orderBy('umur_faktur', $_POST['order'][0]['dir']);
                        break;
                    case 13:
                        $datadb->orderBy('v.nama_vendor', $_POST['order'][0]['dir']);
                        break;
                    case 14:
                        $datadb->orderBy('p.code', $_POST['order'][0]['dir']);
                        break;
                    case 15:
                        $datadb->orderBy('p.name', $_POST['order'][0]['dir']);
                        break;
                    case 16:
                        $datadb->orderBy('p.category', $_POST['order'][0]['dir']);
                        break;
                    case 17:
                        $datadb->orderBy('sid.qty', $_POST['order'][0]['dir']);
                        break;
                    case 18:
                        $datadb->orderBy('u.name', $_POST['order'][0]['dir']);
                        break;
                    case 19:
                        $datadb->orderBy('sid.price', $_POST['order'][0]['dir']);
                        break;
                    case 20:
                        $datadb->orderBy('sid.discount', $_POST['order'][0]['dir']);
                        break;
                    case 21:
                        $datadb->orderBy('sid.subtotal', $_POST['order'][0]['dir']);
                        break;
                    case 22:
                        $datadb->orderBy('product_total_amount', $_POST['order'][0]['dir']);
                        break;
                    case 23:
                        $datadb->orderBy('product_amount_paid', $_POST['order'][0]['dir']);
                        break;
                    case 24:
                        $datadb->orderBy('product_outstanding_amount', $_POST['order'][0]['dir']);
                        break;
                    case 25:
                        $datadb->orderBy('overdue', $_POST['order'][0]['dir']);
                        break;
                    case 26:
                        $datadb->orderBy('cluster_overdue', $_POST['order'][0]['dir']);
                        break;
                    default:
                        $datadb->orderBy('sih.invoice_date', 'asc');
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

        foreach ($datadb as $value) {
            $resultdb[] = $value;
        }
        $data['data'] = $resultdb;
        $data['draw'] = isset($_POST['draw']) ? $_POST['draw'] : '';

        $query = DB::getQueryLog();
        return json_encode($data);
    }
}
