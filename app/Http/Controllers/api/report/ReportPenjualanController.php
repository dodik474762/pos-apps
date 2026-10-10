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

        $date_start = $_POST['date_start'] ?? date('Y-m-d');
        $date_end   = $_POST['date_end']   ?? date('Y-m-d');

        // ============================================================
        // RETUR (tidak berubah)
        // ============================================================
        $salesReturn = DB::table('sales_return as sr')
            ->join('sales_return_detail as srd', function ($q) {
                return $q->on('srd.return_id', '=', 'sr.id')->whereNull('srd.deleted');
            })
            ->select([
                'srd.product_id',
                'srd.unit_price as return_unit_price',
                'srd.qty_return',
                'sr.invoice_id',
                'srd.invoice_detail_id',
                'sr.return_number',
                'sr.return_date',
                DB::raw("
            (
                SELECT
                    CASE uom_count.total_level
                        WHEN 4 THEN
                            CONCAT(
                                FLOOR(srd.qty_return / uom_l4.nilai_konversi_terkecil), '.',
                                FLOOR((srd.qty_return MOD uom_l4.nilai_konversi_terkecil) / uom_l3.nilai_konversi_terkecil), '.',
                                FLOOR((srd.qty_return MOD uom_l3.nilai_konversi_terkecil) / uom_l2.nilai_konversi_terkecil), '.',
                                FLOOR(srd.qty_return MOD uom_l2.nilai_konversi_terkecil)
                            )
                        WHEN 3 THEN
                            CONCAT(
                                FLOOR(srd.qty_return / uom_l3.nilai_konversi_terkecil), '.',
                                FLOOR((srd.qty_return MOD uom_l3.nilai_konversi_terkecil) / uom_l2.nilai_konversi_terkecil), '.',
                                FLOOR(srd.qty_return MOD uom_l2.nilai_konversi_terkecil)
                            )
                        WHEN 2 THEN
                            CONCAT(
                                FLOOR(srd.qty_return / uom_l2.nilai_konversi_terkecil), '.',
                                FLOOR(srd.qty_return MOD uom_l2.nilai_konversi_terkecil)
                            )
                        ELSE
                            CAST(FLOOR(srd.qty_return) AS CHAR)
                    END
                FROM (
                    SELECT product, COUNT(*) as total_level
                    FROM product_uom
                    WHERE deleted IS NULL
                    GROUP BY product
                ) uom_count
                JOIN product_uom uom_l1 ON uom_l1.product = srd.product_id AND uom_l1.level = 1 AND uom_l1.deleted IS NULL
                LEFT JOIN product_uom uom_l2 ON uom_l2.product = srd.product_id AND uom_l2.level = 2 AND uom_l2.deleted IS NULL
                LEFT JOIN product_uom uom_l3 ON uom_l3.product = srd.product_id AND uom_l3.level = 3 AND uom_l3.deleted IS NULL
                LEFT JOIN product_uom uom_l4 ON uom_l4.product = srd.product_id AND uom_l4.level = 4 AND uom_l4.deleted IS NULL
                WHERE uom_count.product = srd.product_id
                LIMIT 1
            ) as qty_return_formatted
        "),
            ])
            ->whereNull('sr.deleted')
            ->where('sr.types', 'good')
            ->where('sr.return_type', 'RETURN');

        // ============================================================
        // GABUNG BARIS FAKTUR: 1 baris per faktur + produk + satuan
        // ============================================================
        $lines = DB::table('sales_invoice_detail as sid')
            ->join('sales_order_details as sod', function ($q) {
                return $q->on('sod.id', 'sid.so_detail_id')->whereNull('sod.deleted');
            })
            ->whereNull('sid.deleted')
            ->whereNull('sid.flag_cancel')
            ->groupBy('sid.invoice_id', 'sod.sales_order_id', 'sod.product_id', 'sod.unit')
            ->select([
                'sid.invoice_id',
                'sod.sales_order_id',
                'sod.product_id',
                'sod.unit',
                DB::raw('SUM(sod.qty) as qty'),
                DB::raw('SUM(sod.qty * sod.unit_price) as total_amount'),
                DB::raw('SUM(sid.qty * sid.price) as gross_amount'),
                DB::raw('SUM(sid.discount) as discount_per_product'),
                DB::raw('GROUP_CONCAT(DISTINCT sod.id) as sod_ids'),
                DB::raw('GROUP_CONCAT(sid.id) as sid_ids'),
            ]);

        // ============================================================
        // DERIVED TABLE PENGGANTI SUBQUERY KORELASI KE "g"
        // (semua dihitung per produk / per SO, lalu di-JOIN biasa)
        // ============================================================

        // Info level satuan per produk (pengganti uom_count + uom_l1..l4)
        $uomFormat = DB::table('product_uom')
            ->whereNull('deleted')
            ->groupBy('product')
            ->select([
                'product',
                DB::raw('COUNT(*) as total_level'),
                DB::raw('MAX(CASE WHEN level = 1 THEN 1 END) as has_l1'),
                DB::raw('MAX(CASE WHEN level = 2 THEN nilai_konversi_terkecil END) as l2'),
                DB::raw('MAX(CASE WHEN level = 3 THEN nilai_konversi_terkecil END) as l3'),
                DB::raw('MAX(CASE WHEN level = 4 THEN nilai_konversi_terkecil END) as l4'),
            ]);

        // Nilai konversi satuan yang dipakai (pengganti uom_used), 1 baris per produk + satuan
        $uomUsed = DB::table('product_uom')
            ->whereNull('deleted')
            ->groupBy('product', 'unit_tujuan')
            ->select([
                'product',
                'unit_tujuan',
                DB::raw('MAX(nilai_konversi_terkecil) as conv'),
            ]);

        // Id detail SO terkecil yang kena promo, per SO
        $promoMin = DB::table('sales_order_details as sod_inner')
            ->join('sales_order_promo as sop_inner', 'sop_inner.sales_order_id', 'sod_inner.sales_order_id')
            ->join('product_promo_item_detail as ppid_inner', function ($j) {
                $j->on('ppid_inner.product_promo_item', 'sop_inner.promo')
                    ->on('ppid_inner.product', 'sod_inner.product_id');
            })
            ->whereNull('sod_inner.deleted')
            ->groupBy('sod_inner.sales_order_id')
            ->select([
                'sod_inner.sales_order_id',
                DB::raw('MIN(sod_inner.id) as min_sod_id'),
            ]);

        // Total diskon promo per SO
        $promoTotal = DB::table('sales_order_promo')
            ->groupBy('sales_order_id')
            ->select([
                'sales_order_id',
                DB::raw('SUM(discount_amount) as total_discount'),
            ]);

        // Ekspresi qty (dalam satuan terkecil) dari baris yang sudah digabung
        $q = '(g.qty * uomu.conv)';

        $qtySoldSql = "
            CASE
                WHEN uomu.conv IS NULL OR uomf.has_l1 IS NULL THEN NULL
                ELSE
                    CASE uomf.total_level
                        WHEN 4 THEN
                            CONCAT(
                                FLOOR($q / uomf.l4), '.',
                                FLOOR(($q MOD uomf.l4) / uomf.l3), '.',
                                FLOOR(($q MOD uomf.l3) / uomf.l2), '.',
                                FLOOR($q MOD uomf.l2)
                            )
                        WHEN 3 THEN
                            CONCAT(
                                FLOOR($q / uomf.l3), '.',
                                FLOOR(($q MOD uomf.l3) / uomf.l2), '.',
                                FLOOR($q MOD uomf.l2)
                            )
                        WHEN 2 THEN
                            CONCAT(
                                FLOOR($q / uomf.l2), '.',
                                FLOOR($q MOD uomf.l2)
                            )
                        ELSE
                            CAST(FLOOR($q) AS CHAR)
                    END
            END as qty_sold
        ";

        $prorateSql = "
            IFNULL(
                CASE
                    WHEN FIND_IN_SET(pmin.min_sod_id, g.sod_ids) > 0
                    THEN ptot.total_discount
                    ELSE 0
                END
            , 0) as prorate_discount
        ";

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
                'p.code as product_code',
                'p.name as product_name',
                'principal.nama_vendor as principal',
                'p.category',
                'p.sku_name as brand',
                'p.sub_brand',
                'v.nama_vendor',
                'kec.name as kecamatan',
                'kab.name as kabupaten',
                'kel.name as kelurahan',
                'c.address as alamat',
                'dv.cicle_type',
                'g.total_amount',
                DB::raw('DAY(sih.invoice_date) as day'),
                DB::raw("ELT(DAYOFWEEK(sih.invoice_date), 'Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') as day_name"),
                DB::raw('MONTH(sih.invoice_date) as month'),
                DB::raw('YEAR(sih.invoice_date) as year'),
                'sih.invoice_number',
                DB::raw($qtySoldSql),
                'ppi.beban',
                DB::raw($prorateSql),
                'sih.invoice_date',
                'sih.amount_paid',
                'g.discount_per_product',
                DB::raw('(sih.total_amount - sih.amount_paid) AS outstanding_amount'),
                'g.gross_amount',
                'sr_data.return_number',
                'sr_data.qty_return',
                'sr_data.return_unit_price',
                'sr_data.return_date',
                'sr_data.qty_return_formatted'
            ])
            ->distinct()
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->joinSub($lines, 'g', function ($j) {
                $j->on('g.sales_order_id', 'm.id');
            })
            ->join('product as p', 'p.id', 'g.product_id')
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.id', 'g.invoice_id')->whereNull('sih.deleted');
            })
            // pengganti subquery qty_sold
            ->leftJoinSub($uomUsed, 'uomu', function ($j) {
                $j->on('uomu.product', 'g.product_id')
                    ->on('uomu.unit_tujuan', 'g.unit');
            })
            ->leftJoinSub($uomFormat, 'uomf', function ($j) {
                $j->on('uomf.product', 'g.product_id');
            })
            // pengganti subquery prorate_discount
            ->leftJoinSub($promoMin, 'pmin', function ($j) {
                $j->on('pmin.sales_order_id', 'm.id');
            })
            ->leftJoinSub($promoTotal, 'ptot', function ($j) {
                $j->on('ptot.sales_order_id', 'm.id');
            })
            ->leftJoin('vendor as v', 'v.id', 'p.vendor')
            ->leftJoin('vendor as principal', 'principal.id', 'p.principal')
            ->leftJoin('users as usr', 'usr.id', 'm.salesman')
            ->leftJoin('karyawan as k', 'k.nik', 'usr.nik')
            ->leftJoin('region as kec', 'kec.id', 'c.kecamatan')
            ->leftJoin('region as kab', 'kab.id', 'c.kota')
            ->leftJoin('region as kel', 'kel.id', 'c.kelurahan')
            ->leftJoin('daily_visit as dv', function ($q) {
                return $q->on('dv.date_visit', 'm.so_date')
                    ->on('dv.users', 'm.salesman')
                    ->whereNull('dv.deleted');
            })
            ->leftJoin('sales_order_promo as sop', 'sop.sales_order_id', 'm.id')
            ->leftJoin('product_promo_item as ppi', 'ppi.id', 'sop.promo')
            ->leftJoinSub($salesReturn, 'sr_data', function ($join) {
                $join->whereRaw('(
                    FIND_IN_SET(sr_data.invoice_detail_id, g.sid_ids) > 0
                    OR (sr_data.invoice_id = g.invoice_id AND sr_data.product_id = g.product_id)
                )');
            })
            ->whereBetween('sih.invoice_date', [$date_start, $date_end])
            ->whereNull('m.deleted')
            ->whereNull('sih.deleted')
            ->where('m.total_amount', '>', 0);

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();

            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.salesman', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.so_date', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('sih.invoice_number', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('c.code', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('c.channel_outlet', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.remarks', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.check_in_time', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.check_out_time', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('usr.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('principal.nama_vendor', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('v.nama_vendor', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('kec.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('kab.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('kel.name', 'LIKE', '%' . $keyword . '%');
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
            $value = (array) $value;
            $value['is_return'] = 0;
            $resultdb[] = $value;

            if (!empty($value['return_number'])) {
                $returnRow = $value;
                $returnRow['is_return']      = 1;
                $returnRow['invoice_number'] = $value['return_number'];
                $returnRow['qty_sold']       = !empty($value['qty_return_formatted'])
                    ? '-' . $value['qty_return_formatted']
                    : 0;
                $returnRow['gross_amount']   = isset($value['return_unit_price'], $value['qty_return'])
                    ? -1 * ($value['return_unit_price'] * $value['qty_return'])
                    : 0;
                $returnRow['prorate_discount']     = 0;
                $returnRow['discount_per_product'] = 0;

                $resultdb[] = $returnRow;
            }
        }

        $data['data'] = $resultdb;
        $data['draw'] = isset($_POST['draw']) ? $_POST['draw'] : '';

        $query = DB::getQueryLog();
        return json_encode($data);
    }

    public function getDataPenjualanPerProduct(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();
        $filter_satuan = $_POST['filter_satuan'] ?? 'default';
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $date_start = $_POST['date_start'] ?? date('Y-m-d');
        $date_end   = $_POST['date_end']   ?? date('Y-m-d');

        // Detail SO yang punya promo item (distinct supaya join tidak menggandakan baris)
        $promoDetail = DB::table('sales_order_promo_item')
            ->select('sales_order_detail_id')
            ->distinct();

        // ============================================================
        // GABUNG BARIS FAKTUR: 1 baris per faktur + produk + satuan
        // ============================================================
        $lines = DB::table('sales_invoice_detail as sid')
            ->join('sales_order_details as sod', function ($q) {
                return $q->on('sod.id', 'sid.so_detail_id')->whereNull('sod.deleted');
            })
            ->leftJoinSub($promoDetail, 'sopi', function ($j) {
                $j->on('sopi.sales_order_detail_id', 'sod.id');
            })
            ->whereNull('sid.deleted')
            ->whereNull('sid.flag_cancel')
            ->where('sid.qty', '>', 0)
            ->groupBy('sid.invoice_id', 'sod.sales_order_id', 'sod.product_id', 'sod.unit')
            ->select([
                'sid.invoice_id',
                'sod.sales_order_id',
                'sod.product_id',
                'sod.unit',
                DB::raw('SUM(sid.qty) as qty'),
                // harga rata-rata tertimbang (sama dengan harga asli kalau harganya sama)
                DB::raw('ROUND(SUM(sid.qty * sid.price) / NULLIF(SUM(sid.qty), 0), 2) as price'),
                DB::raw('SUM(sid.subtotal + sid.discount) as subtotal'),
                DB::raw('SUM(sod.qty * sod.unit_price) as total_amount'),
                DB::raw('GROUP_CONCAT(DISTINCT sod.id) as sod_ids'),
                // pengganti sopi.sales_order_detail_id (1 nilai per grup)
                DB::raw('MAX(sopi.sales_order_detail_id) as sales_order_detail_id'),
            ]);

        // ============================================================
        // DERIVED TABLE PENGGANTI SUBQUERY KORELASI KE "g"
        // ============================================================

        // Total diskon baris faktur per faktur (pengganti SUM(inner_sid.discount))
        $invDiscount = DB::table('sales_invoice_detail')
            ->whereNull('deleted')
            ->groupBy('invoice_id')
            ->select([
                'invoice_id',
                DB::raw('SUM(discount) as total_discount'),
            ]);

        // Jumlah promo per SO + produk (pengganti subquery is_promo)
        $promoCount = DB::table('sales_order_promo as sp')
            ->join('product_promo_item_detail as ppid', 'ppid.product_promo_item', 'sp.promo')
            ->groupBy('sp.sales_order_id', 'ppid.product')
            ->select([
                'sp.sales_order_id',
                'ppid.product',
                DB::raw('COUNT(*) as cnt'),
            ]);

        // Qty satuan terkecil per faktur + produk (pengganti subquery qty_terkecil)
        $qtyKecil = DB::table('sales_invoice_detail as inner_sid')
            ->join('sales_order_details as inner_sod', function ($j) {
                $j->on('inner_sod.id', 'inner_sid.so_detail_id')
                    ->whereNull('inner_sod.deleted');
            })
            ->join('product_uom as uom_used', function ($j) {
                $j->on('uom_used.unit_tujuan', 'inner_sod.unit')
                    ->on('uom_used.product', 'inner_sod.product_id')
                    ->whereNull('uom_used.deleted');
            })
            ->whereNull('inner_sid.deleted')
            ->whereNull('inner_sid.flag_cancel')
            ->where('inner_sid.qty', '>', 0)
            ->groupBy('inner_sid.invoice_id', 'inner_sod.product_id')
            ->select([
                'inner_sid.invoice_id',
                'inner_sod.product_id',
                DB::raw('SUM(
                    CASE
                        WHEN uom_used.level = 1 THEN FLOOR(inner_sod.qty)
                        ELSE FLOOR(inner_sod.qty * uom_used.nilai_konversi_terkecil)
                    END
                ) as qty_terkecil'),
            ]);

        // Nilai konversi satuan terbesar per produk (pengganti subquery MAX(level))
        $maxLevel = DB::table('product_uom')
            ->whereNull('deleted')
            ->groupBy('product')
            ->select([
                'product',
                DB::raw('MAX(level) as max_level'),
            ]);

        $uomLargest = DB::table('product_uom as pu')
            ->joinSub($maxLevel, 'mx', function ($j) {
                $j->on('mx.product', 'pu.product')
                    ->on('mx.max_level', 'pu.level');
            })
            ->whereNull('pu.deleted')
            ->groupBy('pu.product')
            ->select([
                'pu.product',
                DB::raw('MAX(pu.nilai_konversi_terkecil) as conv'),
            ]);

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
                'p.code as product_code',
                'p.name as product_name',
                'p.category as category_product',
                'p.sku_name',
                'v.nama_vendor as principal',
                'kec.name as kecamatan',
                'kab.name as kabupaten',
                'kel.name as kelurahan',
                'c.address as alamat',
                'dv.cicle_type',
                DB::raw('(m.discount_amount + invd.total_discount) as discount_amount'),
                DB::raw('IFNULL(pc.cnt, 0) as is_promo'),
                'g.total_amount',
                DB::raw('DAY(m.so_date) as day'),
                DB::raw("ELT(DAYOFWEEK(m.so_date), 'Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') as day_name"),
                DB::raw('MONTH(m.so_date) as month'),
                DB::raw('YEAR(m.so_date) as year'),
                'sih.invoice_number',
                'u.name as unit_jual',
                'sih.invoice_date',
                'g.qty',
                'g.price',
                'g.subtotal',
                'qk.qty_terkecil',
                DB::raw('(qk.qty_terkecil / ul.conv) as qty_terbesar'),
                'unit_terkecil.name as unit_terkecil',
                'unit_terbesar.name as unit_terbesar',
                'price_terkecil.price as price_terkecil',
                'price_terbesar.price as price_terbesar',
                'g.sales_order_detail_id',
            ])
            ->distinct()
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->joinSub($lines, 'g', function ($j) {
                $j->on('g.sales_order_id', 'm.id');
            })
            ->join('product as p', 'p.id', 'g.product_id')
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.id', 'g.invoice_id')->whereNull('sih.deleted');
            })
            ->join('unit as u', 'u.id', 'g.unit')
            // pengganti subquery discount_amount
            ->leftJoinSub($invDiscount, 'invd', function ($j) {
                $j->on('invd.invoice_id', 'g.invoice_id');
            })
            // pengganti subquery is_promo
            ->leftJoinSub($promoCount, 'pc', function ($j) {
                $j->on('pc.sales_order_id', 'm.id')
                    ->on('pc.product', 'g.product_id');
            })
            // pengganti subquery qty_terkecil & qty_terbesar
            ->leftJoinSub($qtyKecil, 'qk', function ($j) {
                $j->on('qk.invoice_id', 'g.invoice_id')
                    ->on('qk.product_id', 'g.product_id');
            })
            ->leftJoinSub($uomLargest, 'ul', function ($j) {
                $j->on('ul.product', 'g.product_id');
            })
            ->leftJoin('vendor as v', 'v.id', 'p.vendor')
            ->leftJoin('users as usr', 'usr.id', 'm.salesman')
            ->leftJoin('karyawan as k', 'k.nik', 'usr.nik')
            ->leftJoin('region as kec', 'kec.id', 'c.kecamatan')
            ->leftJoin('region as kab', 'kab.id', 'c.kota')
            ->leftJoin('region as kel', 'kel.id', 'c.kelurahan')
            ->leftJoin('daily_visit as dv', function ($q) {
                return $q->on('dv.date_visit', 'm.so_date')
                    ->on('dv.users', 'm.salesman')
                    ->whereNull('dv.deleted');
            })
            ->leftJoin('product_uom as pou', function ($q) {
                $q->on('pou.product', 'g.product_id')
                    ->where('pou.state', 'large')
                    ->whereNull('pou.deleted');
            })
            ->leftJoin('product_uom as pou_terkecil', function ($q) {
                $q->on('pou_terkecil.product', 'g.product_id')
                    ->where('pou_terkecil.level', '1')
                    ->whereNull('pou_terkecil.deleted');
            })
            ->leftJoin('product_uom_price as price_terkecil', function ($q) {
                $q->on('price_terkecil.product', 'g.product_id')
                    ->on('price_terkecil.unit', 'pou_terkecil.unit_tujuan')
                    ->whereNull('price_terkecil.deleted')
                    ->where('price_terkecil.channel', 'RETAIL UMUM');
            })
            ->leftJoin('product_uom_price as price_terbesar', function ($q) {
                $q->on('price_terbesar.product', 'g.product_id')
                    ->on('price_terbesar.unit', 'pou.unit_tujuan')
                    ->whereNull('price_terbesar.deleted')
                    ->where('price_terbesar.channel', 'RETAIL UMUM');
            })
            ->leftJoin('unit as unit_terkecil', 'unit_terkecil.id', 'pou_terkecil.unit_tujuan')
            ->leftJoin('unit as unit_terbesar', 'unit_terbesar.id', 'pou.unit_tujuan')
            ->whereBetween('sih.invoice_date', [$date_start, $date_end])
            ->whereNull('sih.deleted')
            ->whereNull('m.deleted')
            ->where('m.total_amount', '>', 0)
            ->orderBy('sih.invoice_number', 'asc')
            ->orderBy('m.salesman', 'asc')
            ->orderByRaw('is_promo DESC')
            ->orderBy('g.sales_order_detail_id', 'desc')
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
                        ->orWhere('usr.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('p.code', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('p.name', 'LIKE', '%' . $keyword . '%');
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
