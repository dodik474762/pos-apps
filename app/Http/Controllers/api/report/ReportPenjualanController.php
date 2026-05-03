<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use App\Models\Transaction\SalesOrderHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportPenjualanController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function getData()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');

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
                'usr.name as salesman_name',
                'm.status',
                'm.platform',
                'p.code as product_code',
                'p.name as product_name',
                'v.nama_vendor as principal',
                'kec.name as kecamatan',
                'kab.name as kabupaten',
                'dv.cicle_type',
                DB::raw('(sod.qty * sod.unit_price) as total_amount'),
                DB::raw('DAY(m.so_date) as day'),
                DB::raw("ELT(DAYOFWEEK(m.so_date), 'Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') as day_name"),
                DB::raw('MONTH(m.so_date) as month'),
                DB::raw('YEAR(m.so_date) as year'),
                'sih.invoice_number',
                DB::raw("
    (
        SELECT
            CASE uom_count.total_level
                WHEN 4 THEN
                    CONCAT(
                        FLOOR((inner_sod.qty * uom_used.nilai_konversi_terkecil) / uom_l4.nilai_konversi_terkecil), '.',
                        FLOOR(((inner_sod.qty * uom_used.nilai_konversi_terkecil) MOD uom_l4.nilai_konversi_terkecil) / uom_l3.nilai_konversi_terkecil), '.',
                        FLOOR(((inner_sod.qty * uom_used.nilai_konversi_terkecil) MOD uom_l3.nilai_konversi_terkecil) / uom_l2.nilai_konversi_terkecil), '.',
                        FLOOR((inner_sod.qty * uom_used.nilai_konversi_terkecil) MOD uom_l2.nilai_konversi_terkecil)
                    )
                WHEN 3 THEN
                    CONCAT(
                        FLOOR((inner_sod.qty * uom_used.nilai_konversi_terkecil) / uom_l3.nilai_konversi_terkecil), '.',
                        FLOOR(((inner_sod.qty * uom_used.nilai_konversi_terkecil) MOD uom_l3.nilai_konversi_terkecil) / uom_l2.nilai_konversi_terkecil), '.',
                        FLOOR((inner_sod.qty * uom_used.nilai_konversi_terkecil) MOD uom_l2.nilai_konversi_terkecil)
                    )
                WHEN 2 THEN
                    CONCAT(
                        FLOOR((inner_sod.qty * uom_used.nilai_konversi_terkecil) / uom_l2.nilai_konversi_terkecil), '.',
                        FLOOR((inner_sod.qty * uom_used.nilai_konversi_terkecil) MOD uom_l2.nilai_konversi_terkecil)
                    )
                ELSE
                    CAST(FLOOR(inner_sod.qty * uom_used.nilai_konversi_terkecil) AS CHAR)
            END
        FROM sales_order_details inner_sod
        JOIN product_uom uom_used
            ON uom_used.unit_tujuan = inner_sod.unit
            AND uom_used.product = inner_sod.product_id
            AND uom_used.deleted IS NULL
        JOIN (
            SELECT product, COUNT(*) as total_level
            FROM product_uom
            WHERE deleted IS NULL
            GROUP BY product
        ) uom_count ON uom_count.product = inner_sod.product_id
        JOIN product_uom uom_l1 ON uom_l1.product = inner_sod.product_id AND uom_l1.level = 1 AND uom_l1.deleted IS NULL
        LEFT JOIN product_uom uom_l2 ON uom_l2.product = inner_sod.product_id AND uom_l2.level = 2 AND uom_l2.deleted IS NULL
        LEFT JOIN product_uom uom_l3 ON uom_l3.product = inner_sod.product_id AND uom_l3.level = 3 AND uom_l3.deleted IS NULL
        LEFT JOIN product_uom uom_l4 ON uom_l4.product = inner_sod.product_id AND uom_l4.level = 4 AND uom_l4.deleted IS NULL
        WHERE inner_sod.id = sod.id
        LIMIT 1
    ) as qty_sold
"),
                'ppi.beban',
                'sop.discount_amount',                                               // 👈 total discount dari promo
                DB::raw('IFNULL(sop.discount_amount / NULLIF((SELECT COUNT(sod2.qty) FROM sales_order_details sod2 WHERE sod2.sales_order_id = m.id AND sod2.deleted IS NULL), 0), 0) as prorate_discount'),
            ])
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->join('sales_order_details as sod', function ($q) {
                return $q->on('sod.sales_order_id', 'm.id')
                    ->whereNull('sod.deleted');
            })
            ->join('product as p', 'p.id', 'sod.product_id')
            ->join('sales_invoice_detail as sid', function ($q) {
                return $q->on('sid.so_detail_id', 'sod.id')
                    ->whereNull('sid.deleted');
            })
            ->join('sales_invoice_header as sih', 'sih.id', 'sid.invoice_id')
            ->leftJoin('vendor as v', 'v.id', 'p.vendor')
            ->leftJoin('users as usr', 'usr.id', 'm.salesman')
            ->leftJoin('region as kec', 'kec.id', 'c.kecamatan')
            ->leftJoin('region as kab', 'kab.id', 'c.kota')
            ->leftJoin('daily_visit as dv', function ($q) {
                return $q->on('dv.date_visit', 'm.so_date')
                    ->on('dv.users', 'm.salesman')
                    ->whereNull('dv.deleted');
            })
            ->leftJoin('sales_order_promo as sop', 'sop.sales_order_id', 'm.id')
            ->leftJoin('product_promo_item as ppi', 'ppi.id', 'sop.promo')
            ->whereDate('m.so_date', '=', $tanggal)
            // ->where('m.id', '1588')
            ->whereNull('m.deleted')
            ->where('m.total_amount', '>', 0)
            ->orderBy('m.salesman', 'asc')
            ->orderBy('m.so_number', 'asc');

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
                        ->orWhere('usr.name', 'LIKE', '%' . $keyword . '%');
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
                        $datadb->orderBy('c.nama_customer', $_POST['order'][0]['dir']);
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
        $data['draw'] = $_POST['draw'];

        $query = DB::getQueryLog();
        return json_encode($data);
    }

    public function getDataPenjualanPerProduct(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');

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
                'usr.name as salesman_name',
                'm.status',
                'm.platform',
                'p.code as product_code',
                'p.name as product_name',
                'p.category as category_product',
                'p.sku_name',
                'v.nama_vendor as principal',
                'kec.name as kecamatan',
                'kab.name as kabupaten',
                'dv.cicle_type',
                DB::raw('(sod.qty * sod.unit_price) as total_amount'),
                DB::raw('DAY(m.so_date) as day'),
                DB::raw("ELT(DAYOFWEEK(m.so_date), 'Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') as day_name"),
                DB::raw('MONTH(m.so_date) as month'),
                DB::raw('YEAR(m.so_date) as year'),
                'sih.invoice_number',
                'u.name as unit_jual',
                'sih.invoice_date',
                'sid.qty',
                'sid.price',
                'sid.subtotal'
            ])
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->join('sales_order_details as sod', function ($q) {
                return $q->on('sod.sales_order_id', 'm.id')
                    ->whereNull('sod.deleted');
            })
            ->join('product as p', 'p.id', 'sod.product_id')
            ->join('sales_invoice_detail as sid', function ($q) {
                return $q->on('sid.so_detail_id', 'sod.id')
                    ->whereNull('sid.deleted');
            })
            ->join('sales_invoice_header as sih', 'sih.id', 'sid.invoice_id')
            ->join('unit as u', 'u.id', 'sod.unit')
            ->leftJoin('vendor as v', 'v.id', 'p.vendor')
            ->leftJoin('users as usr', 'usr.id', 'm.salesman')
            ->leftJoin('region as kec', 'kec.id', 'c.kecamatan')
            ->leftJoin('region as kab', 'kab.id', 'c.kota')
            ->leftJoin('daily_visit as dv', function ($q) {
                return $q->on('dv.date_visit', 'm.so_date')
                    ->on('dv.users', 'm.salesman')
                    ->whereNull('dv.deleted');
            })
            ->leftJoin('sales_order_promo as sop', 'sop.sales_order_id', 'm.id')
            ->leftJoin('product_promo_item as ppi', 'ppi.id', 'sop.promo')
            ->whereDate('sih.invoice_date', '=', $tanggal)
            // ->where('m.id', '1588')
            ->whereNull('m.deleted')
            ->where('m.total_amount', '>', 0)
            ->orderBy('m.salesman', 'asc')
            ->orderBy('sih.invoice_date', 'asc');

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
                        ->orWhere('usr.name', 'LIKE', '%' . $keyword . '%');
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
                        $datadb->orderBy('c.nama_customer', $_POST['order'][0]['dir']);
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
        $data['draw'] = $_POST['draw'];

        $query = DB::getQueryLog();
        return json_encode($data);
    }
}
