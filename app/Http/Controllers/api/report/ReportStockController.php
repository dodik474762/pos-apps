<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use App\Models\Transaction\ProductStockMove;
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

    public function getDataStock()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
        $tiga_bulan_lalu = date('Y-m-d', strtotime('-3 months', strtotime($tanggal)));

        $datadb = ProductStockMove::from('product_stock_move as m')
            ->select([
                'm.product',
                'm.warehouse',
                'p.code as product_code',
                'p.name as product_name',
                'w.name as warehouse_name',
                'u_large.name as unit_large_name',
                'u_small.name as unit_small_name',
                'pu_large.nilai_konversi_terkecil as konversi_large',
                'pu_small.nilai_konversi_terkecil as konversi_small',
                'v.nama_vendor as principal',

                // total_masuk dalam satuan LARGE (CTN)
                DB::raw('FLOOR(
            SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_in ELSE 0 END)
            / NULLIF(pu_large.nilai_konversi_terkecil, 0)
        ) as total_masuk_ctn'),

                // sisa total_masuk dalam satuan SMALL (PCS) — sisanya setelah dibagi CTN
                DB::raw('MOD(
            SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_in ELSE 0 END),
            NULLIF(pu_large.nilai_konversi_terkecil, 0)
        ) / NULLIF(pu_small.nilai_konversi_terkecil, 0) as total_masuk_pcs'),

                // total_keluar dalam satuan LARGE (CTN)
                DB::raw('FLOOR(
            SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_out ELSE 0 END)
            / NULLIF(pu_large.nilai_konversi_terkecil, 0)
        ) as total_keluar_ctn'),

                // sisa total_keluar dalam satuan SMALL (PCS)
                DB::raw('MOD(
            SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_out ELSE 0 END),
            NULLIF(pu_large.nilai_konversi_terkecil, 0)
        ) / NULLIF(pu_small.nilai_konversi_terkecil, 0) as total_keluar_pcs'),

                // stok_tersedia murni dalam PCS (satuan terkecil)
                DB::raw('ROUND(
            SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_in - m.qty_out ELSE 0 END)
            / NULLIF(pu_small.nilai_konversi_terkecil, 0)
        ) as stok_tersedia_pcs'),
            ])
            ->with(['products.uomFromLarge.units'])
            ->join('product as p', 'p.id', 'm.product')
            ->join('warehouse as w', 'w.id', 'm.warehouse')
            ->join('unit as u', 'u.id', 'm.unit')
            ->leftJoin('vendor as v', 'p.vendor', 'v.id')

            // Join LARGE
            ->join('product_uom as pu_large', function ($q) {
                return $q->on('pu_large.product', 'm.product')
                    ->where('pu_large.state', 'large')
                    ->whereNull('pu_large.deleted');
            })
            ->join('unit as u_large', 'u_large.id', 'pu_large.unit_tujuan')

            // Join SMALL
            ->join('product_uom as pu_small', function ($q) {
                return $q->on('pu_small.product', 'm.product')
                    ->where('pu_small.state', 'small')
                    ->whereNull('pu_small.deleted');
            })
            ->join('unit as u_small', 'u_small.id', 'pu_small.unit_tujuan')

            ->whereDate('m.created_at', '<=', $tanggal)
            ->groupBy(
                'm.product',
                'm.warehouse',
                'p.code',
                'p.name',
                'w.name',
                'v.nama_vendor',
                'pu_large.nilai_konversi_terkecil',
                'u_large.name',
                'pu_small.nilai_konversi_terkecil',
                'u_small.name'
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

        foreach ($datadb as &$value) {
            // Format: "5 CTN 3 PCS"
            $value['total_masuk_display']  = $value['total_masuk_ctn'] . ' ' . $value['unit_large_name']
                . ' ' . $value['total_masuk_pcs'] . ' ' . $value['unit_small_name'];

            $value['total_keluar_display'] = $value['total_keluar_ctn'] . ' ' . $value['unit_large_name']
                . ' ' . $value['total_keluar_pcs'] . ' ' . $value['unit_small_name'];

            // Stok tersedia murni PCS
            $value['stok_tersedia'] = number_format($value['stok_tersedia_pcs'], 0, ',', '.')
                . ' ' . $value['unit_small_name'];

            // UOM string
            $uoms = collect($value['products']['uom_from_large'] ?? [])
                ->sortByDesc('level')
                ->pluck('units.name')
                ->filter()
                ->values();
            $value['uom_product'] = $uoms->implode('-');

            $resultdb[] = $value;
        }

        $data['data'] = $resultdb;
        $data['draw'] = $_POST['draw'];

        $query = DB::getQueryLog();
        return json_encode($data);
    }

    public function getDataStockDetail(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');

        $datadb = ProductStockMove::from('product_stock_move as m')
            ->select([
                'm.product',
                'm.warehouse',
                'p.code as product_code',
                'p.name as product_name',
                'w.name as warehouse_name',
                'v.nama_vendor as principal',

                // Nama satuan per level
                'u_ctn.name as unit_ctn_name', // level 4
                'u_pck.name as unit_pck_name', // level 3
                'u_rtg.name as unit_rtg_name', // level 2
                'u_pcs.name as unit_pcs_name', // level 1

                // Nilai konversi ke satuan terkecil per level
                'pu_ctn.nilai_konversi_terkecil as konversi_ctn', // level 4
                'pu_pck.nilai_konversi_terkecil as konversi_pck', // level 3
                'pu_rtg.nilai_konversi_terkecil as konversi_rtg', // level 2
                'pu_pcs.nilai_konversi_terkecil as konversi_pcs', // level 1

                // Stok tersedia dalam satuan terkecil (raw)
                DB::raw('ROUND(
                SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_in - m.qty_out ELSE 0 END)
            ) as stok_tersedia_raw'),
            ])
            ->with(['products.uomFromLarge.units'])
            ->join('product as p', 'p.id', 'm.product')
            ->join('warehouse as w', 'w.id', 'm.warehouse')
            ->join('unit as u', 'u.id', 'm.unit')
            ->leftJoin('vendor as v', 'p.vendor', 'v.id')

            // Join level 4 = CTN
            ->leftJoin('product_uom as pu_ctn', function ($q) {
                $q->on('pu_ctn.product', 'm.product')
                    ->where('pu_ctn.level', 4)
                    ->whereNull('pu_ctn.deleted');
            })
            ->leftJoin('unit as u_ctn', 'u_ctn.id', 'pu_ctn.unit_tujuan')

            // Join level 3 = PCK
            ->leftJoin('product_uom as pu_pck', function ($q) {
                $q->on('pu_pck.product', 'm.product')
                    ->where('pu_pck.level', 3)
                    ->whereNull('pu_pck.deleted');
            })
            ->leftJoin('unit as u_pck', 'u_pck.id', 'pu_pck.unit_tujuan')

            // Join level 2 = RTG
            ->leftJoin('product_uom as pu_rtg', function ($q) {
                $q->on('pu_rtg.product', 'm.product')
                    ->where('pu_rtg.level', 2)
                    ->whereNull('pu_rtg.deleted');
            })
            ->leftJoin('unit as u_rtg', 'u_rtg.id', 'pu_rtg.unit_tujuan')

            // Join level 1 = PCS
            ->leftJoin('product_uom as pu_pcs', function ($q) {
                $q->on('pu_pcs.product', 'm.product')
                    ->where('pu_pcs.level', 1)
                    ->whereNull('pu_pcs.deleted');
            })
            ->leftJoin('unit as u_pcs', 'u_pcs.id', 'pu_pcs.unit_tujuan')

            ->whereDate('m.created_at', '<=', $tanggal)
            // ->where('p.id', '699')
            ->groupBy(
                'm.product',
                'm.warehouse',
                'p.code',
                'p.name',
                'w.name',
                'v.nama_vendor',
                'pu_ctn.nilai_konversi_terkecil',
                'u_ctn.name',
                'pu_pck.nilai_konversi_terkecil',
                'u_pck.name',
                'pu_rtg.nilai_konversi_terkecil',
                'u_rtg.name',
                'pu_pcs.nilai_konversi_terkecil',
                'u_pcs.name',
            )
            ->orderBy('p.name');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();

            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('p.code', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('p.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('v.nama_vendor', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('w.name', 'LIKE', '%' . $keyword . '%');
                });
            }

            if (isset($_POST['order'][0]['column'])) {
                switch ($_POST['order'][0]['column']) {
                    case 1:
                        $datadb->orderBy('p.code', $_POST['order'][0]['dir']);
                        break;
                    case 2:
                        $datadb->orderBy('p.name', $_POST['order'][0]['dir']);
                        break;
                    case 3:
                        $datadb->orderBy('v.nama_vendor', $_POST['order'][0]['dir']);
                        break;
                    default:
                        $datadb->orderBy('p.name', 'asc');
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
        // echo '<pre>';
        // print_r($datadb);
        // die;

        foreach ($datadb as &$value) {
            $stok_raw = (int) ($value['stok_tersedia_raw'] ?? 0);

            $konversi_ctn = (int) ($value['konversi_ctn'] ?? 0); // level 4
            $konversi_pck = (int) ($value['konversi_pck'] ?? 0); // level 3
            $konversi_rtg = (int) ($value['konversi_rtg'] ?? 0); // level 2
            // level 1 (PCS) tidak perlu konversi, langsung sisa akhir

            // Cek keberadaan tiap level
            $has_ctn = $konversi_ctn > 0;
            $has_rtg = $konversi_rtg > 0;
            $has_pck = $konversi_pck > 0;

            // Reset semua qty
            $value['qty_ctn'] = 0;
            $value['qty_rtg'] = 0;
            $value['qty_pck'] = 0;
            $value['qty_pcs'] = 0;

            $sisa = $stok_raw;

            if ($has_ctn && $has_rtg && $has_pck) {
                $value['qty_ctn'] = intdiv($sisa, $konversi_ctn);
                $sisa = $sisa % $konversi_ctn;

                $value['qty_rtg'] = intdiv($sisa, $konversi_rtg);
                $sisa = $sisa % $konversi_rtg;

                $value['qty_pck'] = intdiv($sisa, $konversi_pck);
                $sisa = $sisa % $konversi_pck;

                $value['qty_pcs'] = $sisa;
            } elseif ($has_ctn && $has_rtg && !$has_pck) {
                $value['qty_ctn'] = intdiv($sisa, $konversi_ctn);
                $sisa = $sisa % $konversi_ctn;

                $value['qty_rtg'] = intdiv($sisa, $konversi_rtg);
                $sisa = $sisa % $konversi_rtg;

                $value['qty_pcs'] = $sisa;
            } elseif ($has_ctn && $has_pck && !$has_rtg) {
                $value['qty_ctn'] = intdiv($sisa, $konversi_ctn);
                $sisa = $sisa % $konversi_ctn;

                $value['qty_pck'] = intdiv($sisa, $konversi_pck);
                $sisa = $sisa % $konversi_pck;

                $value['qty_pcs'] = $sisa;
            } elseif ($has_ctn && !$has_rtg && !$has_pck) {
                $value['qty_ctn'] = intdiv($sisa, $konversi_ctn);
                $sisa = $sisa % $konversi_ctn;

                $value['qty_pcs'] = $sisa;
            } elseif (!$has_ctn && $has_rtg && !$has_pck) {
                $value['qty_ctn'] = intdiv($sisa, $konversi_rtg);
                $sisa = $sisa % $konversi_rtg;

                $value['qty_pcs'] = $sisa;
            } else {
                // Hanya PCS
                $value['qty_pcs'] = $sisa;
            }

            // UOM string: urut dari terbesar ke terkecil
            $uoms = collect($value['products']['uom_from_large'] ?? [])
                ->sortByDesc('level')
                ->pluck('units.name')
                ->filter()
                ->values();
            $value['uom_product'] = $uoms->implode('-');

            $resultdb[] = $value;
        }

        $data['data'] = $resultdb;
        $data['draw'] = $_POST['draw'];

        $query = DB::getQueryLog();
        return json_encode($data);
    }
}
