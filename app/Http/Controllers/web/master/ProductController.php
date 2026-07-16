<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\ProductController as MasterProductController;
use App\Http\Controllers\Controller;
use App\Models\Master\CustomerCategory;
use App\Models\Master\ProductCatalog;
use App\Models\Master\ProductDisc;
use App\Models\Master\ProductFreeGood;
use App\Models\Master\ProductLog;
use App\Models\Master\ProductType;
use App\Models\Master\ProductUom;
use App\Models\Master\ProductUomPrice;
use App\Models\Master\Tax;
use App\Models\Master\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductController extends Controller
{
    public $akses_menu = [];
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/js/lib/number-divider.min.js'),
            'js-2' => asset('assets/js/controllers/master/product.js'),
            'js-3' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Master";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Product";
    }

    public function index(Request $request)
    {
        $data = $request->all();
        // $this->generateStock();
        // die;
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.product.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function getStock($product = 0)
    {
        $datadb = DB::table('product_stock as ps')
            ->select(['ps.*', 'u.name as unit_name', 'w.name as warehouse_name'])
            ->join('unit as u', 'u.id', 'ps.unit')
            ->join('warehouse as w', 'w.id', 'ps.warehouse')
            ->where('ps.product', $product)
            ->get();

        $result = [];
        foreach ($datadb as $key => $value) {
            $units_large = getLargestUnit($product, $value->unit, $value->qty);
            $value->unit_large = $units_large['largest_unit_name'];
            $value->qty_large = $units_large['qty_in_largest_unit'];
            $result[] = $value;
        }
        return $result;
    }

    public function getCostProduct($product = 0)
    {
        $datadb = DB::table('product_uom_cost as ps')
            ->select(['ps.*', 'u.name as unit_name', 'v.nama_vendor'])
            ->join('unit as u', 'u.id', 'ps.unit_id')
            ->join('vendor as v', 'v.id', 'ps.vendor')
            ->where('ps.product', $product)
            ->get();
        return $datadb;
    }

    public function getListVendor()
    {
        $datadb = DB::table('vendor')->whereNull('deleted')->get();
        return $datadb;
    }

    public function add()
    {
        $data['data'] = [];
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['product_type'] = ProductType::whereNull('deleted')->get()->toArray();
        $data['product_unit'] = Unit::whereNull('deleted')->get()->toArray();
        $data['taxs'] = Tax::whereNull('deleted')->where('tax_type', 'Output')->get()->toArray();
        // $data['tax_type'] = ['include', 'exclude', 'non-taxable'];
        $data['tax_type'] = ['include'];
        $data['retur_type'] = ['NON RETUR', 'RETUR'];
        $data['vendors'] = $this->getListVendor();
        $data['channels'] = $this->getChannel();
        $data['sub_channels'] = $this->getSubChannel();
        $data['product_logs'] = [];
        $data['product_stocks'] = [];
        $data['product_uoms'] = [];
        $data['product_prices'] = [];
        $data['akses'] = session('akses');
        $view = view('web.product.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function getProductLog($product)
    {
        $data = ProductLog::where('product_log.product', $product)
            ->select([
                'product_log.*',
                'usr.username'
            ])
            ->join('users as usr', 'usr.id', 'product_log.creator')
            ->get()->toArray();
        return $data;
    }

    public function getListProductUom($product)
    {
        $data = ProductUom::where('product', $product)
            ->orderBy('level')
            ->get();

        return $data;
    }

    public function getListProductUomPrice($product)
    {
        $data = ProductUomPrice::where('product', $product)
            ->orderBy('id')
            ->get();

        return $data;
    }

    public function getListProductDiscStrata($product)
    {
        $data = ProductDisc::where('product', $product)
            ->orderBy('id')
            ->get();

        return $data;
    }

    public function getListProductDiscFree($product)
    {
        $data = ProductFreeGood::where('product', $product)
            ->orderBy('id')
            ->get();

        return $data;
    }


    public function getListPriceList()
    {
        $datadb = DB::table('price_list as pl')->whereNull('deleted')->get();
        return $datadb;
    }

    public function getListSatuanUom($product)
    {
        $product_uoms = ProductUom::where('product', $product)
            ->select(['u.name as unit_dasar_name', 'ut.name as unit_tujuan_name', 'product_uom.*'])
            ->join('unit as u', 'u.id', 'product_uom.unit_dasar')
            ->join('unit as ut', 'ut.id', 'product_uom.unit_tujuan')
            ->get();

        $data_satuan = [];
        foreach ($product_uoms as $key => $value) {
            $data_satuan[] = $value->unit_dasar . ' // ' . $value->unit_dasar_name;
            $data_satuan[] = $value->unit_tujuan . ' // ' . $value->unit_tujuan_name;
        }
        $data_satuan = collect($data_satuan)->unique()->values()->all();
        $result_satuan = [];
        foreach ($data_satuan as $key => $value) {
            list($id, $name) = explode('//', $value);
            $result_satuan[] = [
                'id' => trim($id),
                'name' => trim($name)
            ];
        }
        return $result_satuan;
    }

    public function getChannel()
    {
        $datadb = DB::table('dictionary')->whereNull('deleted')
            ->where('context', 'CHANNEL_OUTLET')
            ->get();

        return $datadb;
    }

    public function getSubChannel()
    {
        $datadb = DB::table('dictionary')->whereNull('deleted')
            ->where('context', 'SUB_CHANNEL_OUTLET')
            ->get();

        return $datadb;
    }

    public function ubah(Request $request)
    {
        $api = new MasterProductController();
        $data = $request->all();

        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['product_type'] = ProductType::whereNull('deleted')->get()->toArray();
        $data['product_unit'] = Unit::whereNull('deleted')->get()->toArray();
        $data['taxs'] = Tax::whereNull('deleted')->where('tax_type', 'Output')->get()->toArray();

        $data['retur_type'] = ['NON RETUR', 'RETUR'];
        // $data['tax_type'] = ['include', 'exclude', 'non-taxable'];
        $data['tax_type'] = ['include'];
        $data['data_satuan'] = Unit::whereNull('deleted')->get();
        $data['vendors'] = $this->getListVendor();
        $data['data_satuan_uom'] = $this->getListSatuanUom($data['id']);
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['product_logs'] = $this->getProductLog($data['id']);
        $data['product_uoms'] = $this->getListProductUom($data['id']);
        $data['tipe_price'] = $this->getListPriceList();
        $data['product_prices'] = $this->getListProductUomPrice($data['id']);
        $data['prices_retails'] = collect($data['product_prices'])->where('channel', 'RETAIL UMUM');
        $data['data_customer_category'] = CustomerCategory::whereNull('deleted')->get();
        $data['data_disc_tipe'] = ['percent', 'nominal'];
        $data['channels'] = $this->getChannel();
        $data['sub_channels'] = $this->getSubChannel();
        $data['product_disc_strata'] = $this->getListProductDiscStrata($data['id']);
        $data['product_disc_free'] = $this->getListProductDiscFree($data['id']);
        $data['product_stocks'] = $this->getStock($data['id']);
        $data['product_costs'] = $this->getCostProduct($data['id']);
        $data['akses'] = session('akses');
        $view = view('web.product.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function detail(Request $request)
    {
        $api = new MasterProductController();
        $data = $request->all();

        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['product_type'] = ProductType::whereNull('deleted')->get()->toArray();
        $data['product_unit'] = Unit::whereNull('deleted')->get()->toArray();
        $data['taxs'] = Tax::whereNull('deleted')->where('tax_type', 'Output')->get()->toArray();

        $data['retur_type'] = ['NON RETUR', 'RETUR'];
        // $data['tax_type'] = ['include', 'exclude', 'non-taxable'];
        $data['tax_type'] = ['include'];
        $data['data_satuan'] = Unit::whereNull('deleted')->get();
        $data['vendors'] = $this->getListVendor();
        $data['data_satuan_uom'] = $this->getListSatuanUom($data['id']);
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['product_logs'] = $this->getProductLog($data['id']);
        $data['product_uoms'] = $this->getListProductUom($data['id']);
        $data['tipe_price'] = $this->getListPriceList();
        $data['product_prices'] = $this->getListProductUomPrice($data['id']);
        $data['prices_retails'] = collect($data['product_prices'])->where('channel', 'RETAIL UMUM');
        $data['data_customer_category'] = CustomerCategory::whereNull('deleted')->get();
        $data['data_disc_tipe'] = ['percent', 'nominal'];
        $data['channels'] = $this->getChannel();
        $data['sub_channels'] = $this->getSubChannel();
        $data['product_disc_strata'] = $this->getListProductDiscStrata($data['id']);
        $data['product_disc_free'] = $this->getListProductDiscFree($data['id']);
        $data['product_stocks'] = $this->getStock($data['id']);
        $data['product_costs'] = $this->getCostProduct($data['id']);
        $data['akses'] = session('akses');
        $data['view_detail'] = 'detail';
        $view = view('web.product.formdetail', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function import(Request $request)
    {
        $api = new MasterProductController();
        $data = $request->all();

        $data['data'] = [];
        $data['title_parent'] = $this->getTitleParent();
        $data['title'] = 'Form Import ' . $this->getTitle();
        $view = view('web.product.form_import', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function generateStock()
    {
        $excludedIds = [
            58,
            132,
            1303,
            1327,
            1325,
            165,
            168,
            170,
            1215,
            1214,
            1337,
            1336,
            1338,
            1339,
            1319,
            1317,
            1328,
            1318,
            1320,
            1288,
            1285,
            1295,
            1293,
            1294,
            1284,
            1287,
            81,
            82,
            84,
            1324,
            162,
            163,
            164,
            87,
            86,
            1283,
            725,
            716,
            720,
            719,
            718,
            717,
            699,
            701,
            703,
            710,
            709,
            711,
            738,
            739,
            733,
            735,
            730,
            743,
            745,
            747,
            749,
            96,
            97,
            1040,
            1323,
            1332,
            1333,
            1334,
            1331,
            51,
            52,
            53,
            18,
            20,
            14,
            22,
            16,
            27,
            23,
            17,
            25,
            26,
            21,
            15,
            24,
            1335,
            89,
            91,
            90,
            83,
            697,
            59,
            60,
            45,
            46,
            47,
            48,
            49,
            50,
            1271,
            1273,
            1272,
            1274,
            1276,
            1275,
            119,
            1329,
            124,
            121,
            120,
            125,
            123,
            122,
            142,
            147,
            1309,
            2,
            3,
            10,
            4,
            6,
            9,
            8,
            7,
            11,
            1330,
            1268,
            1263,
            1261,
            1035,
            1034,
            1186,
            1187,
            1036,
            1037,
            1038,
            637,
            828,
            1177,
            160,
            161,
            72,
            74,
            75,
            1306,
            109,
            648,
            650,
            658,
            666,
            664,
            651,
            652,
            646,
            662,
            723,
            679,
            680,
            645,
            644,
            643,
            642,
            573,
            805,
            800,
            809,
            795,
            639,
            640,
            641,
            638,
            678,
            676,
            675,
            636,
            630,
            629,
            624,
            625,
            859,
            861,
            683,
            681,
            724,
            599,
            600,
            669,
            671,
            670,
            687,
            618,
            596,
            598,
            595,
            594,
            597,
            590,
            589,
            586,
            593,
            591,
            592,
            585,
            583,
            584,
            581,
            620,
            619,
            622,
            621,
            603,
            606,
            607,
            608,
            605,
            690,
            1039,
            76,
            79,
            80,
            1340,
            135,
            137,
            171,
            172,
            173,
            174,
            1305,
            1322,
            131,
            1245,
            1249,
            1250,
            1199,
            158,
            1269,
            1321,
            1270,
            63,
            1032,
            1033,
            31,
            32,
            33,
            34,
            44,
            39,
            43,
            40,
            41,
            42,
            88,
            126,
            127,
            128,
            129,
            116,
            166,
            167,
            107,
            1299,
            95,
            93,
            94,
            1031,
            149,
            144,
            176,
            175,
            177,
            111,
            133,
            61,
            62,
            12,
            1143,
            1167,
            1153,
            1310,
        ];

        $products = DB::table('product as p')
            ->join('product_type as pt', 'pt.id', '=', 'p.product_type')
            ->select('p.*')
            ->whereNotIn('p.id', $excludedIds)
            ->whereNull('p.deleted')
            ->get();

        foreach ($products as $key => $product) {
            # code...
            $product_uom_small = ProductUom::whereNull('deleted')
                ->where('product', $product->id)
                ->where('state', 'small')
                ->first();

            $unit = $product_uom_small->unit_tujuan ?? 9;
            $warehouse = 1;
            $productId = $product->id;
            $item['product'] = $productId;

            stockUpdate(
                0,
                $warehouse,
                $productId,
                $unit,
                0,
                $item,
                'add',
                'generate stock'
            );

            recalculateFrom('0', date('Y-m-d'), date('Y-m-d'), 0, 0, $productId);
        }

        echo '<pre>';
        print_r("done");
        echo '</pre>';
        die;
    }

    public function updateProduct()
    {
        // Ambil semua produk beserta UOM-nya sekaligus
        $products = DB::table('product as p')
            ->whereNull('p.deleted')
            ->select([
                'p.id as product_id',
                'pu.id as uom_id',
                'pu.unit_dasar',
                'pu.unit_tujuan',
                'pu.nilai_konversi',
                'pu.level',
                'puo.price as harga_jual'
            ])
            ->join('product_uom as pu', 'pu.product', 'p.id')
            ->join('product_uom_price as puo', function ($q) {
                return $q->on('puo.product', 'p.id')
                    ->on('puo.unit', 'pu.unit_tujuan');
            })
            ->whereNull('pu.deleted')
            ->where('p.id', '>=', 180)
            ->orderBy('p.id')
            ->orderBy('pu.level', 'desc') // level terbesar dulu
            ->get()
            ->groupBy('product_id'); // group per produk

        foreach ($products as $productId => $uoms) {
            $hargaJual = $uoms->first()->harga_jual ?? 0;

            // Unit terbesar = level tertinggi (sudah urut desc)
            $unitTerbesar = $uoms->first();

            // Hitung multiplier unit terbesar ke terkecil
            // Kalikan semua nilai_konversi dari level terbesar ke terkecil
            // Karena sudah urut desc, tinggal akumulasi
            $multiplierTerbesar = 1;
            foreach ($uoms as $uom) {
                if ($uom->level > 1) { // skip level 1 (unit terkecil)
                    $multiplierTerbesar *= $uom->nilai_konversi;
                }
            }

            // Sekarang hitung per unit tanpa query tambahan
            $updates = [];
            $runningMultiplier = $multiplierTerbesar;

            foreach ($uoms as $uom) {
                // nilai_konversi_terkecil = multiplier dari unit ini ke unit terkecil
                $nilaiKonversiTerkecil = $runningMultiplier;

                // Harga per unit ini = hargaJual / (multiplierTerbesar / runningMultiplier)
                $hargaPerUnit = $multiplierTerbesar > 0
                    ? $hargaJual / ($multiplierTerbesar / $runningMultiplier)
                    : 0;

                echo "Product: {$productId} | Level: {$uom->level} | Unit: {$uom->unit_tujuan} | ";
                echo "nilai_konversi_terkecil: {$nilaiKonversiTerkecil} | Harga: {$hargaPerUnit}\n";

                $updates[] = [
                    'uom_id'                  => $uom->uom_id,
                    'nilai_konversi_terkecil' => $nilaiKonversiTerkecil,
                    'harga_per_unit'          => $hargaPerUnit,
                    'unit_tujuan'             => $uom->unit_tujuan
                ];

                // Kurangi running multiplier untuk unit berikutnya (level lebih kecil)
                if ($uom->level > 1) {
                    $runningMultiplier /= $uom->nilai_konversi;
                }
            }

            // Batch update — 1 query per uom_id, tapi dalam 1 loop produk
            foreach ($updates as $upd) {
                DB::table('product_uom')
                    ->where('id', $upd['uom_id'])
                    ->update([
                        'nilai_konversi_terkecil' => $upd['nilai_konversi_terkecil'],
                        'updated_at'              => now(),
                    ]);
                DB::table('product_uom_price')
                    ->where('product', $productId)
                    ->where('unit', $upd['unit_tujuan'])
                    ->update([
                        'price'          => $upd['harga_per_unit'],
                        'updated_at'              => now(),
                    ]);
            }

            echo "--- Product {$productId} selesai ---\n";
        }

        echo "Update selesai.";
        die;
    }
}
