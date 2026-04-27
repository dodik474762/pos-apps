<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportStockController extends Controller
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
        $tiga_bulan_lalu = date('Y-m-d', strtotime('-3 months', strtotime($tanggal)));

        $datadb = DB::table('product_stock_move as m')
            ->select([
                'm.product',
                'm.warehouse',
                // 'm.unit',
                'p.code as product_code',
                'p.name as product_name',
                'w.name as warehouse_name',
                'u_large.name as unit_large_name',       // tampilkan satuan large
                'pu_large.nilai_konversi_terkecil as konversi_large',

                // QTY PO DRAFT → qty di PO sudah satuan terkecil? 
                // Jika PO masih pakai satuan asli, tetap perlu join pu_pod
                DB::raw('(
                SELECT COALESCE(SUM(pod.qty * pu_pod.nilai_konversi_terkecil), 0)
                    / NULLIF(pu_large.nilai_konversi_terkecil, 0)
                FROM purchase_order_detail pod
                JOIN purchase_order po ON po.id = pod.purchase_order
                JOIN product_uom pu_pod ON pu_pod.product = pod.product
                    AND pu_pod.unit_tujuan = pod.unit
                    AND pu_pod.deleted IS NULL
                WHERE pod.product = m.product
                AND po.warehouse = m.warehouse
                AND po.status = "draft"
                AND po.deleted IS NULL
                AND pod.deleted IS NULL
                AND po.po_date <= "' . $tanggal . '"
            ) as qty_po_draft'),

                // qty_in/qty_out sudah satuan terkecil → langsung bagi konversi_large
                DB::raw('SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_in ELSE 0 END)
                / NULLIF(pu_large.nilai_konversi_terkecil, 0)
            as total_masuk'),

                DB::raw('SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_out ELSE 0 END)
                / NULLIF(pu_large.nilai_konversi_terkecil, 0)
            as total_keluar'),

                DB::raw('ROUND(SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_in - m.qty_out ELSE 0 END)
                / NULLIF(pu_large.nilai_konversi_terkecil, 0))
            as stok_tersedia'),

                DB::raw('SUM(CASE WHEN DATE(m.created_at) BETWEEN "' . $tiga_bulan_lalu . '" AND "' . $tanggal . '" THEN m.qty_in ELSE 0 END)
                / NULLIF(pu_large.nilai_konversi_terkecil, 0)
            as masuk_3bln'),

                DB::raw('SUM(CASE WHEN DATE(m.created_at) BETWEEN "' . $tiga_bulan_lalu . '" AND "' . $tanggal . '" THEN m.qty_out ELSE 0 END)
                / NULLIF(pu_large.nilai_konversi_terkecil, 0)
            as keluar_3bln'),

                DB::raw('SUM(CASE WHEN DATE(m.created_at) BETWEEN "' . $tiga_bulan_lalu . '" AND "' . $tanggal . '" THEN m.qty_in - m.qty_out ELSE 0 END)
                / NULLIF(pu_large.nilai_konversi_terkecil, 0)
            as stok_3bln'),

                // stock_future = qty_po_draft (duplikasi subquery karena alias tidak bisa direferensikan)
                DB::raw('ROUND((
    SELECT COALESCE(SUM(pod.qty * pu_pod.nilai_konversi_terkecil), 0)
        / NULLIF(pu_large.nilai_konversi_terkecil, 0)
    FROM purchase_order_detail pod
    JOIN purchase_order po ON po.id = pod.purchase_order
    JOIN product_uom pu_pod ON pu_pod.product = pod.product
        AND pu_pod.unit_tujuan = pod.unit
        AND pu_pod.deleted IS NULL
    WHERE pod.product = m.product
    AND po.warehouse = m.warehouse
    AND po.status = "draft"
    AND po.deleted IS NULL
    AND pod.deleted IS NULL
    AND po.po_date <= "' . $tanggal . '"
), 2) as stock_future'),

                // stock_and_intransit = stok_tersedia + stock_future
                DB::raw('ROUND(
                (
                    SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" 
                        THEN m.qty_in - m.qty_out ELSE 0 END)
                    / NULLIF(pu_large.nilai_konversi_terkecil, 0)
                    + (
                        SELECT COALESCE(SUM(pod.qty * pu_pod.nilai_konversi_terkecil), 0)
                            / NULLIF(pu_large.nilai_konversi_terkecil, 0)
                        FROM purchase_order_detail pod
                        JOIN purchase_order po ON po.id = pod.purchase_order
                        JOIN product_uom pu_pod ON pu_pod.product = pod.product
                            AND pu_pod.unit_tujuan = pod.unit
                            AND pu_pod.deleted IS NULL
                        WHERE pod.product = m.product
                        AND po.warehouse = m.warehouse
                        AND po.status = "draft"
                        AND po.deleted IS NULL
                        AND pod.deleted IS NULL
                        AND po.po_date <= "' . $tanggal . '"
                    )
                )
            , 2) as stock_and_intransit'),

                // avg_omset = stok_3bln (satuan large) / 25 hari kerja
                DB::raw('SUM(CASE WHEN DATE(m.created_at) BETWEEN "' . $tiga_bulan_lalu . '" AND "' . $tanggal . '" THEN m.qty_in - m.qty_out ELSE 0 END)
                / NULLIF(pu_large.nilai_konversi_terkecil, 0)
                / 25
            as avg_omset'),

                // stock_scd = stok_tersedia / avg_omset
                DB::raw('(
                SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_in - m.qty_out ELSE 0 END)
                / NULLIF(pu_large.nilai_konversi_terkecil, 0)
            ) / NULLIF(
                SUM(CASE WHEN DATE(m.created_at) BETWEEN "' . $tiga_bulan_lalu . '" AND "' . $tanggal . '" THEN m.qty_in - m.qty_out ELSE 0 END)
                / NULLIF(pu_large.nilai_konversi_terkecil, 0)
                / 25
            , 0)
            as stock_scd'),

                // stock_scd_and_intransit = stock_and_intransit / avg_omset
                DB::raw('(
                SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_in - m.qty_out ELSE 0 END)
                / NULLIF(pu_large.nilai_konversi_terkecil, 0)
                + (
                    SELECT COALESCE(SUM(pod.qty * pu_pod.nilai_konversi_terkecil), 0)
                        / NULLIF(pu_large.nilai_konversi_terkecil, 0)
                    FROM purchase_order_detail pod
                    JOIN purchase_order po ON po.id = pod.purchase_order
                    JOIN product_uom pu_pod ON pu_pod.product = pod.product
                        AND pu_pod.unit_tujuan = pod.unit
                        AND pu_pod.deleted IS NULL
                    WHERE pod.product = m.product
                    AND po.warehouse = m.warehouse
                    AND po.status = "draft"
                    AND po.deleted IS NULL
                    AND pod.deleted IS NULL
                    AND po.po_date <= "' . $tanggal . '"
                )
            ) / NULLIF(
                SUM(CASE WHEN DATE(m.created_at) BETWEEN "' . $tiga_bulan_lalu . '" AND "' . $tanggal . '" THEN m.qty_in - m.qty_out ELSE 0 END)
                / NULLIF(pu_large.nilai_konversi_terkecil, 0)
                / 25
            , 0)
            as stock_scd_and_intransit'),
            ])
            ->join('product as p', 'p.id', 'm.product')
            ->join('warehouse as w', 'w.id', 'm.warehouse')
            ->join('unit as u', 'u.id', 'm.unit')
            // Join product_uom LARGE saja — tidak perlu pu untuk unit transaksi
            ->join('product_uom as pu_large', function ($q) {
                return $q->on('pu_large.product', 'm.product')
                    ->where('pu_large.state', 'large')
                    ->whereNull('pu_large.deleted');
            })
            ->join('unit as u_large', 'u_large.id', 'pu_large.unit_tujuan')
            ->whereDate('m.created_at', '<=', $tanggal)
            // ->where('p.id', '657')
            ->groupBy(
                'm.product',
                'm.warehouse',
                // 'm.unit',
                'p.code',
                'p.name',
                'w.name',
                // 'u.name',
                'pu_large.nilai_konversi_terkecil',
                'u_large.name'
            )
            ->orderBy('p.name')
            ->orderBy('u_large.name');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();

            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.product', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.warehouse', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.move_type', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('p.code', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('p.name', 'LIKE', '%' . $keyword . '%');
                });
            }

            if (isset($_POST['order'][0]['column'])) {
                switch ($_POST['order'][0]['column']) {
                    case 0:
                        $datadb->orderBy('m.product', $_POST['order'][0]['dir']);
                        break;
                    case 1:
                        $datadb->orderBy('m.warehouse', $_POST['order'][0]['dir']);
                        break;
                    case 2:
                        $datadb->orderBy('m.unit', $_POST['order'][0]['dir']);
                        break;
                    case 3:
                        $datadb->orderByRaw('total_masuk ' . $_POST['order'][0]['dir']);
                        break;
                    case 4:
                        $datadb->orderByRaw('total_keluar ' . $_POST['order'][0]['dir']);
                        break;
                    case 5:
                        $datadb->orderByRaw('stok_tersedia ' . $_POST['order'][0]['dir']);
                        break;
                    default:
                        $datadb->orderBy('m.product', 'asc');
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
            $value->stok_tersedia           = number_format($value->stok_tersedia, 2, ',', '.');
            $value->total_masuk             = number_format($value->total_masuk, 2, ',', '.');
            $value->total_keluar            = number_format($value->total_keluar, 2, ',', '.');
            $value->stok_3bln               = number_format($value->stok_3bln, 2, ',', '.');
            $value->avg_omset               = number_format($value->avg_omset, 2, ',', '.');
            $value->stock_scd               = number_format($value->stock_scd, 2, ',', '.');
            $value->stock_scd_and_intransit = number_format($value->stock_scd_and_intransit, 2, ',', '.');
            $value->hari_kerja              = 25;
            $resultdb[] = $value;
        }

        $data['data'] = $resultdb;
        $data['draw'] = $_POST['draw'];

        $query = DB::getQueryLog();
        return json_encode($data);
    }
}
