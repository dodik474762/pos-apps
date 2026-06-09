<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Http\Controllers\web\master\ProductController as MasterProductController;
use App\Imports\ProductImport;
use App\Models\Master\Customer;
use App\Models\Master\CustomerCategory;
use App\Models\Master\Product;
use App\Models\Master\ProductCatalog;
use App\Models\Master\ProductDisc;
use App\Models\Master\ProductFreeGood;
use App\Models\Master\ProductLog;
use App\Models\Master\ProductUom;
use App\Models\Master\ProductUomPrice;
use App\Models\Master\ProductUomPriceLog;
use App\Models\Master\Unit;
use App\Models\Transaction\ProductAdjustmentStock;
use App\Models\Transaction\ProductAdjustmentStockDtl;
use App\Models\Transaction\ProductUomCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductController extends Controller
{
    public function getTableName()
    {
        return "product";
    }

    public function getData()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $stock = DB::table('product_stock')
            ->select('product', DB::raw('SUM(qty) as stock'))
            ->groupBy('product');

        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'pt.type',
                'u.name as unit_name',
                'v.nama_vendor',
                'prin.nama_vendor as nama_principal',
                DB::raw('COALESCE(ps.stock,0) as stock')
            ])
            ->join('product_type as pt', 'pt.id', '=', 'm.product_type')
            ->leftJoin('vendor as v', 'v.id', '=', 'm.vendor')
            // ->leftJoin('vendor as prin', 'prin.id', '=', 'v.parent')
            ->leftJoin('vendor as prin', 'prin.id', '=', 'm.principal')
            ->leftJoinSub($stock, 'ps', function ($join) {
                $join->on('ps.product', '=', 'm.id');
            })
            ->leftJoin('unit as u', 'u.id', '=', 'm.unit')
            ->whereNull('m.deleted');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.remarks', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.model_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.sku_name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.category', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('v.nama_vendor', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('prin.nama_vendor', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.sub_brand', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('pt.type', 'LIKE', '%' . $keyword . '%');
                });
            }
            if (isset($_POST['order'][0]['column'])) {
                $datadb->orderBy('m.id', $_POST['order'][0]['dir']);
            }
            $data['recordsFiltered'] = $datadb->get()->count();

            if (isset($_POST['length'])) {
                $datadb->limit($_POST['length']);
            }
            if (isset($_POST['start'])) {
                $datadb->offset($_POST['start']);
            }
        }
        $data['data'] = $datadb->get()->toArray();
        $data['draw'] = $_POST['draw'];
        $query = DB::getQueryLog();
        // echo '<pre>';
        // print_r($query);die;
        return json_encode($data);
    }

    public function getDataProduct()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'pt.type',
                'u.name as unit_name',
                'uo.name as unit_tujuan_name',
                'uo.id as unit_tujuan_id',
                'pu.id as id_uom',
                'puc.cost as product_cost',
                'puc.date_start as product_cost_date_start',
            ])
            ->join('product_type as pt', 'pt.id', 'm.product_type')
            ->join('product_uom as pu', 'pu.product', 'm.id')
            ->join('unit as uo', 'uo.id', 'pu.unit_tujuan')
            ->join('unit as u', 'u.id', 'm.unit')
            ->leftJoin('product_uom_cost  as puc', function ($q) {
                return $q->on('puc.product_uom', 'pu.id')
                    ->where('puc.is_active', '1');
            })
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.remarks', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.model_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('pt.type', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('uo.name', 'LIKE', '%' . $keyword . '%');
                });
            }
            if (isset($_POST['order'][0]['column'])) {
                $datadb->orderBy('m.id', $_POST['order'][0]['dir']);
            }
            $data['recordsFiltered'] = $datadb->get()->count();

            if (isset($_POST['length'])) {
                $datadb->limit($_POST['length']);
            }
            if (isset($_POST['start'])) {
                $datadb->offset($_POST['start']);
            }
        }
        $data['data'] = $datadb->get()->toArray();
        $data['draw'] = $_POST['draw'];
        $query = DB::getQueryLog();
        // echo '<pre>';
        // print_r($query);die;
        return json_encode($data);
    }

    public function getDataProductMobile(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        try {
            $productIds = [];
            if (isset($data['user_id'])) {
                $users = DB::table('users')->where('id', $data['user_id'])->first();
                $karyawan = DB::table('karyawan')->where('nik', $users->nik)->first();
                $karyawanId = $karyawan->id;
                $productIds = DB::table('karyawan_has_product')
                    ->where('karyawan', $karyawanId)
                    ->pluck('product')
                    ->toArray();
            }

            $datadb = DB::table($this->getTableName() . ' as m')
                ->select([
                    'm.id as product_id',
                    'm.code as product_code',
                    'm.name as product_name',
                    // DB::raw("TRIM(CONCAT(COALESCE(v.nama_vendor, ''), ' ', m.name)) AS product_name"),
                    'u.name as product_unit',
                    'pup.price as product_price',
                    'u.id as product_unit_id',
                    // 'ps.qty as stock_product',
                    DB::raw("COALESCE(ps.qty, 0) as stock_product"),
                    'pup.id as product_uom_price_id',
                    'us.name as stock_unit',
                    'tx.rate as tax_rate',
                    'm.tax_sale',
                    'm.type_tax'
                ])
                ->leftJoin('vendor as v', 'v.id', 'm.vendor')
                ->join('product_type as pt', 'pt.id', 'm.product_type')
                ->join('product_uom_price as pup', function ($q) {
                    return $q->on('pup.product', 'm.id')->whereNull('pup.deleted');
                })
                ->leftJoin('product_stock as ps', 'ps.product', 'm.id')
                ->leftJoin('product_uom as pu', function ($q) {
                    return $q->on('pu.product', 'm.id')
                        ->where('pu.level', '1');
                })
                ->leftJoin('unit as us', 'us.id', 'pu.unit_tujuan')
                ->leftJoin('tax as tx', 'tx.id', 'm.tax_sale')
                ->join('unit as u', 'u.id', 'pup.unit')
                ->whereNull('m.deleted')
                // ->where('ps.qty', '>', 0)
                ->orderBy('m.id', 'desc')
                ->orderBy('pup.id', 'asc');

            if (!empty($productIds)) {
                $datadb->whereIn('m.id', $productIds);
            }
            $data = $datadb->get()->toArray();
            // echo '<pre>';
            // print_r($query);die;
            $result['is_valid'] = empty($data) ? false : true;
            $result['data'] = $data;
            return response()->json($result);
        } catch (\Throwable $th) {
            return response()->json([
                'is_valid' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function getProductCatalog(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = true;


        $totalRows = Product::whereNull('deleted')->count();
        $datadb = Product::whereNull('product.deleted')
            ->where('product.id', '>', $data['last_id'])
            ->limit($data['length'])
            ->orderBy('product.id', 'asc');
        if (isset($data['keyword'])) {
            $keyword = $data['keyword'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('product.name', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('product.remarks', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('product.code', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('product.model_number', 'LIKE', '%' . $keyword . '%');
            });
        }
        $datadb = $datadb->get()->toArray();
        $resultdb = [];
        foreach ($datadb as $key => $value) {
            $value = (array) $value;
            $value['selling_price'] = number_format($value['selling_price'], 0, ',', '.');
            $value['img'] = null;
            if ($value['files'] != '') {
                $files = explode('.', $value['files']);
                $typeFle = end($files);
                if ($typeFle != "pdf") {
                    $value['img'] = url('/') . $value['path_files'] . $value['files'];
                }
            }
            $resultdb[] = $value;
        }

        $result['data'] = $resultdb;
        $result['total'] = $totalRows;
        $result['total_data'] = count($datadb);

        return response()->json($result);
    }

    public function getProductMasterCatalog(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = true;


        $totalRows = ProductCatalog::whereNull('deleted')->count();
        $datadb = ProductCatalog::whereNull('product_catalog.deleted')
            ->where('product_catalog.id', '>', $data['last_id'])
            ->limit($data['length'])
            ->orderBy('product_catalog.id', 'asc');
        if (isset($data['keyword'])) {
            $keyword = $data['keyword'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('product_catalog.files', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('product_catalog.remarks', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('product_catalog.created_at', 'LIKE', '%' . $keyword . '%');
            });
        }
        $datadb = $datadb->get()->toArray();
        $resultdb = [];
        foreach ($datadb as $key => $value) {
            $value = (array) $value;
            $value['selling_price'] = 0;
            $value['img'] = null;
            if ($value['files'] != '') {
                $files = explode('.', $value['files']);
                $typeFle = end($files);
                if ($typeFle != "pdf") {
                    $value['img'] = url('/') . $value['path_files'] . $value['files'];
                }
            }
            $resultdb[] = $value;
        }

        $result['data'] = $resultdb;
        $result['total'] = $totalRows;
        $result['total_data'] = count($datadb);

        return response()->json($result);
    }

    public function submit_import(Request $request)
    {
        $data = $request->all();
        // validasi file
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
                        $fail('File harus berformat CSV, XLSX, atau XLS.');
                    }
                }
            ]
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'File tidak valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ambil file
        $file = $request->file('file');

        // nama file
        // $filename = time() . '_' . $file->getClientOriginalName();

        // // simpan ke storage/app/import
        // $path = $file->storeAs('import', $filename);


        $import = new ProductImport();

        Excel::import($import, $file);

        // ambil data excel (BELUM masuk DB)
        $rows = $import->rows;
        // return $this->submit_stock($rows);
        // die;
        // echo '<pre>';
        // print_r($rows);
        // die;

        // $groupByIdProduct = collect($rows)
        //     // ->where('kode_produk_new_sistem', '63')
        //     ->where('kode_produk_principle', '1017914')
        //     ->groupBy('kode_produk_principle')
        //     ->map(function ($items) {

        //         $totalItem = $items->count(); // panjang array

        //         return [
        //             'total_item' => $totalItem,
        //             'items' => $items->reverse()->values(),
        //         ];
        //     })
        //     ->toArray();

        $groupByIdProduct = collect($rows)
            ->filter(function ($row) {
                return !empty($row['nama_barang']);
            })
            // ->where('kode_produk_principle', '1018261')
            ->groupBy('nama_barang')
            ->map(function ($items) {

                $totalItem = $items->count(); // panjang array

                return [
                    'total_item' => $totalItem,
                    'items' => $items->reverse()->values(),
                ];
            })
            ->toArray();
        // echo '<pre>';
        // print_r($groupByIdProduct);
        // die;

        $vendors = DB::table('vendor')->whereNull('deleted')->get()->toArray();
        $vendorGrouped = collect($vendors)->groupBy('nama_vendor')->toArray();
        // echo '<pre>';
        // print_r($vendorGrouped);
        // die;

        $result['is_valid'] = false;
        $result['message'] = 'Error';
        DB::beginTransaction();
        try {
            $productRowsImport = 0;
            $failImported = 0;
            $counter = 1;
            foreach ($groupByIdProduct as $key => $group) {
                try {
                    // proses setiap group
                    $productId = $key;
                    $total_item = $group['total_item'];
                    $items = $group['items'];

                    $prod = new Product();
                    $prod->code = $group['items'][0]['kode_produk_principle'] == '' ? 'P26MAY-' . ($counter++) : $group['items'][0]['kode_produk_principle'];
                    $prod->name = $group['items'][0]['nama_barang'];
                    $prod->product_type = 1;
                    $prod->unit = $group['items'][0]['id_satuan'];
                    $prod->remarks = 'IMPORTED';
                    $prod->creator = 1;
                    $prod->type_tax = 'include';
                    $prod->type_retur = 'RETUR';
                    $prod->sku_name = $group['items'][0]['sku_name'];
                    $prod->category = $group['items'][0]['kategori_barang_sku'];
                    $prod->sub_brand = $group['items'][0]['sub_brand_name'];
                    $prod->save();
                    $productId = $prod->id;

                    if ($total_item == 4) {
                        //satuan terkecil, satuan menengah, satuan besar
                        $satuan_terkecil = $items[0];
                        $satuan_renceng = $items[1];
                        $satuan_menengah = $items[2];
                        $satuan_besar = $items[3];

                        $satuan_kecil_ke_renceng = $satuan_terkecil['isi_satuan'] / $satuan_renceng['isi_satuan'];
                        $satuan_tengah_ke_renceng = $satuan_renceng['isi_satuan'] / $satuan_menengah['isi_satuan'];

                        // $harga_beli_non_ppn_besar = ceil(str_replace(',', '', $satuan_besar['harga_beli_non_ppn']));
                        // $harga_jual_non_ppn_kecil = ceil(str_replace(',', '', $satuan_terkecil['harga_jual_non_ppn']));
                        // $harga_jual_non_ppn_renceng = ceil(str_replace(',', '', $satuan_renceng['harga_jual_non_ppn']));
                        // $harga_jual_non_ppn_tengah = ceil(str_replace(',', '', $satuan_menengah['harga_jual_non_ppn']));
                        // $harga_jual_non_ppn_besar = ceil(str_replace(',', '', $satuan_besar['harga_jual_non_ppn']));

                        $harga_beli_non_ppn_besar = ceil(str_replace(',', '', $satuan_besar['harga_belippn']));
                        $harga_jual_non_ppn_kecil = ceil(str_replace(',', '', $satuan_besar['harga_jualppn']) / $satuan_terkecil['isi_satuan']);
                        $harga_jual_non_ppn_renceng = ceil(str_replace(',', '', $satuan_besar['harga_jualppn']) / $satuan_kecil_ke_renceng);
                        $harga_jual_non_ppn_tengah = ceil(str_replace(',', '', $satuan_besar['harga_jualppn']) / ($satuan_tengah_ke_renceng * $satuan_kecil_ke_renceng));
                        $harga_jual_non_ppn_besar = ceil(str_replace(',', '', $satuan_besar['harga_jualppn']));

                        // echo '<pre>';
                        // print_r($satuan_terkecil);die;

                        //insert product satuan terkecil
                        $product_satuan = new ProductUom();
                        $product_satuan->product = $productId;
                        $product_satuan->unit_dasar = $satuan_terkecil['id_satuan'];
                        $product_satuan->unit_tujuan = $satuan_terkecil['id_satuan'];
                        $product_satuan->nilai_konversi = 1;
                        $product_satuan->level = 1;
                        $product_satuan->state = 'small';
                        $product_satuan->nilai_konversi_terkecil = 1;
                        $product_satuan->save();

                        $productPrice = new ProductUomPrice();
                        $productPrice->product = $productId;
                        $productPrice->unit = $satuan_terkecil['id_satuan'];
                        $productPrice->price_list = 2;
                        $productPrice->price = $harga_jual_non_ppn_kecil;
                        $productPrice->date_start = '2026-01-01';
                        $productPrice->min_qty = 1;
                        $productPrice->max_qty = 99999;
                        $productPrice->save();

                        //insert product satuan renceng
                        $product_satuan = new ProductUom();
                        $product_satuan->product = $productId;
                        $product_satuan->unit_dasar = $satuan_terkecil['id_satuan'];
                        $product_satuan->unit_tujuan = $satuan_renceng['id_satuan'];
                        $product_satuan->nilai_konversi = $satuan_kecil_ke_renceng;
                        $product_satuan->level = 2;
                        $product_satuan->nilai_konversi_terkecil = $satuan_tengah_ke_renceng * $satuan_kecil_ke_renceng;
                        $product_satuan->save();

                        $productPrice = new ProductUomPrice();
                        $productPrice->product = $productId;
                        $productPrice->unit = $satuan_renceng['id_satuan'];
                        $productPrice->price_list = 2;
                        $productPrice->price = $harga_jual_non_ppn_tengah;
                        $productPrice->date_start = '2026-01-01';
                        $productPrice->min_qty = 1;
                        $productPrice->max_qty = 99999;
                        $productPrice->save();

                        //insert product satuan menengah
                        $product_satuan = new ProductUom();
                        $product_satuan->product = $productId;
                        $product_satuan->unit_dasar = $satuan_renceng['id_satuan'];
                        $product_satuan->unit_tujuan = $satuan_menengah['id_satuan'];
                        $product_satuan->nilai_konversi = $satuan_tengah_ke_renceng;
                        $product_satuan->level = 3;
                        $product_satuan->nilai_konversi_terkecil = $satuan_kecil_ke_renceng;
                        $product_satuan->save();

                        $productPrice = new ProductUomPrice();
                        $productPrice->product = $productId;
                        $productPrice->unit = $satuan_menengah['id_satuan'];
                        $productPrice->price_list = 2;
                        $productPrice->price = $harga_jual_non_ppn_renceng;
                        $productPrice->date_start = '2026-01-01';
                        $productPrice->min_qty = 1;
                        $productPrice->max_qty = 99999;
                        $productPrice->save();

                        //insert product satuan besar
                        $product_satuan = new ProductUom();
                        $product_satuan->product = $productId;
                        $product_satuan->unit_dasar = $satuan_menengah['id_satuan'];
                        $product_satuan->unit_tujuan = $satuan_besar['id_satuan'];
                        $product_satuan->nilai_konversi = $satuan_menengah['isi_satuan'];
                        $product_satuan->level = 4;
                        $product_satuan->state = 'large';
                        $product_satuan->nilai_konversi_terkecil = $satuan_terkecil['isi_satuan'];
                        $product_satuan->save();
                        $productSatuanId = $product_satuan->id;

                        $productPrice = new ProductUomPrice();
                        $productPrice->product = $productId;
                        $productPrice->unit = $satuan_besar['id_satuan'];
                        $productPrice->price_list = 2;
                        $productPrice->price = $harga_jual_non_ppn_besar;
                        $productPrice->date_start = '2026-01-01';
                        $productPrice->min_qty = 1;
                        $productPrice->max_qty = 99999;
                        $productPrice->save();

                        //insert harga beli
                        $existVendor = isset($vendorGrouped[trim($satuan_terkecil['nama_vendordistributor'])]) ? $vendorGrouped[trim($satuan_terkecil['nama_vendordistributor'])][0] : null;
                        if (empty($existVendor)) {
                            //throw error vendor not found
                            DB::rollBack();
                            $result['is_valid'] = false;
                            $result['message'] = 'Error Vendor ' . trim($satuan_terkecil['nama_vendordistributor']) . ' not found';
                            return response()->json($result);
                        } else {
                            $vendorId = $existVendor->id;
                            $productCost = new ProductUomCost();
                            $productCost->product = $productId;
                            $productCost->unit_id = $satuan_besar['id_satuan'];
                            $productCost->cost = $harga_beli_non_ppn_besar;
                            $productCost->vendor = $vendorId;
                            $productCost->date_start = '2026-01-01';
                            $productCost->is_active = 1;
                            $productCost->product_uom = $productSatuanId;
                            $productCost->save();
                        }

                        $productUpdate = Product::find($productId);
                        $productUpdate->vendor = $vendorId;
                        $productUpdate->save();
                    }

                    if ($total_item == 3) {
                        //satuan terkecil, satuan menengah, satuan besar
                        $satuan_terkecil = $items[0];
                        $satuan_menengah = $items[1];
                        $satuan_besar = $items[2];
                        $satuan_menengah_ke_kecil = $satuan_terkecil['isi_satuan'] / $satuan_menengah['isi_satuan'];

                        // $harga_beli_non_ppn_besar = str_replace(',', '', $satuan_besar['harga_beli_non_ppn']);
                        // $harga_jual_non_ppn_kecil = str_replace(',', '', $satuan_terkecil['harga_jual_non_ppn']);
                        // $harga_jual_non_ppn_tengah = str_replace(',', '', $satuan_menengah['harga_jual_non_ppn']);
                        // $harga_jual_non_ppn_besar = str_replace(',', '', $satuan_besar['harga_jual_non_ppn']);

                        $harga_beli_non_ppn_besar = str_replace(',', '', $satuan_besar['harga_belippn']);
                        $harga_jual_non_ppn_kecil = str_replace(',', '', $satuan_besar['harga_jualppn']) / $satuan_terkecil['isi_satuan'];
                        $harga_jual_non_ppn_tengah = str_replace(',', '', $satuan_besar['harga_jualppn']) / $satuan_menengah_ke_kecil;
                        $harga_jual_non_ppn_besar = str_replace(',', '', $satuan_besar['harga_jualppn']);

                        // echo '<pre>';
                        // print_r($satuan_terkecil);die;

                        //insert product satuan terkecil
                        $product_satuan = new ProductUom();
                        $product_satuan->product = $productId;
                        $product_satuan->unit_dasar = $satuan_terkecil['id_satuan'];
                        $product_satuan->unit_tujuan = $satuan_terkecil['id_satuan'];
                        $product_satuan->nilai_konversi = 1;
                        $product_satuan->level = 1;
                        $product_satuan->state = 'small';
                        $product_satuan->nilai_konversi_terkecil = 1;
                        $product_satuan->save();

                        $productPrice = new ProductUomPrice();
                        $productPrice->product = $productId;
                        $productPrice->unit = $satuan_terkecil['id_satuan'];
                        $productPrice->price_list = 2;
                        $productPrice->price = $harga_jual_non_ppn_kecil;
                        $productPrice->date_start = '2026-05-06';
                        $productPrice->min_qty = 1;
                        $productPrice->max_qty = 99999;
                        $productPrice->save();

                        //insert product satuan menengah
                        $product_satuan = new ProductUom();
                        $product_satuan->product = $productId;
                        $product_satuan->unit_dasar = $satuan_terkecil['id_satuan'];
                        $product_satuan->unit_tujuan = $satuan_menengah['id_satuan'];
                        $product_satuan->nilai_konversi = $satuan_menengah_ke_kecil;
                        $product_satuan->level = 2;
                        $product_satuan->nilai_konversi_terkecil = $satuan_menengah_ke_kecil;
                        $product_satuan->save();

                        $productPrice = new ProductUomPrice();
                        $productPrice->product = $productId;
                        $productPrice->unit = $satuan_menengah['id_satuan'];
                        $productPrice->price_list = 2;
                        $productPrice->price = $harga_jual_non_ppn_tengah;
                        $productPrice->date_start = '2026-05-06';
                        $productPrice->min_qty = 1;
                        $productPrice->max_qty = 99999;
                        $productPrice->save();

                        //insert product satuan besar
                        $product_satuan = new ProductUom();
                        $product_satuan->product = $productId;
                        $product_satuan->unit_dasar = $satuan_menengah['id_satuan'];
                        $product_satuan->unit_tujuan = $satuan_besar['id_satuan'];
                        $product_satuan->nilai_konversi = $satuan_menengah['isi_satuan'];
                        $product_satuan->level = 3;
                        $product_satuan->state = 'large';
                        $product_satuan->nilai_konversi_terkecil = $satuan_terkecil['isi_satuan'];
                        $product_satuan->save();
                        $productSatuanId = $product_satuan->id;

                        $productPrice = new ProductUomPrice();
                        $productPrice->product = $productId;
                        $productPrice->unit = $satuan_besar['id_satuan'];
                        $productPrice->price_list = 2;
                        $productPrice->price = $harga_jual_non_ppn_besar;
                        $productPrice->date_start = '2026-05-06';
                        $productPrice->min_qty = 1;
                        $productPrice->max_qty = 99999;
                        $productPrice->save();

                        //insert harga beli
                        $existVendor = isset($vendorGrouped[trim($satuan_terkecil['nama_vendordistributor'])]) ? $vendorGrouped[trim($satuan_terkecil['nama_vendordistributor'])][0] : null;
                        if (empty($existVendor)) {
                            //throw error vendor not found
                            DB::rollBack();
                            $result['is_valid'] = false;
                            $result['message'] = 'Error Vendor ' . trim($satuan_terkecil['nama_vendordistributor']) . ' not found';
                            return response()->json($result);
                        } else {
                            $vendorId = $existVendor->id;
                            $productCost = new ProductUomCost();
                            $productCost->product = $productId;
                            $productCost->unit_id = $satuan_besar['id_satuan'];
                            $productCost->cost = $harga_beli_non_ppn_besar;
                            $productCost->vendor = $vendorId;
                            $productCost->date_start = '2026-05-06';
                            $productCost->is_active = 1;
                            $productCost->product_uom = $productSatuanId;
                            $productCost->save();
                        }

                        $productUpdate = Product::find($productId);
                        $productUpdate->vendor = $vendorId;
                        $productUpdate->save();
                    }

                    if ($total_item == 2) {
                        //satuan terkecil, satuan menengah, satuan besar
                        $satuan_terkecil = $items[0];
                        $satuan_besar = $items[1];

                        // $harga_beli_non_ppn_besar = str_replace(',', '', $satuan_besar['harga_beli_non_ppn']);
                        // $harga_jual_non_ppn_kecil = str_replace(',', '', $satuan_terkecil['harga_jual_non_ppn']);
                        // $harga_jual_non_ppn_besar = str_replace(',', '', $satuan_besar['harga_jual_non_ppn']);

                        $harga_beli_non_ppn_besar = str_replace(',', '', $satuan_besar['harga_belippn']);
                        $harga_jual_non_ppn_kecil = str_replace(',', '', $satuan_besar['harga_jualppn']) / $satuan_terkecil['isi_satuan'];
                        $harga_jual_non_ppn_besar = str_replace(',', '', $satuan_besar['harga_jualppn']);
                        // echo '<pre>';
                        // print_r($satuan_besar_ke_satuan_menengah);die;


                        //insert product satuan terkecil
                        $product_satuan = new ProductUom();
                        $product_satuan->product = $productId;
                        $product_satuan->unit_dasar = $satuan_terkecil['id_satuan'];
                        $product_satuan->unit_tujuan = $satuan_terkecil['id_satuan'];
                        $product_satuan->nilai_konversi = 1;
                        $product_satuan->level = 1;
                        $product_satuan->state = 'small';
                        $product_satuan->nilai_konversi_terkecil = 1;
                        $product_satuan->save();

                        $productPrice = new ProductUomPrice();
                        $productPrice->product = $productId;
                        $productPrice->unit = $satuan_terkecil['id_satuan'];
                        $productPrice->price_list = 2;
                        $productPrice->price = $harga_jual_non_ppn_kecil;
                        $productPrice->date_start = '2026-05-06';
                        $productPrice->min_qty = 1;
                        $productPrice->max_qty = 99999;
                        $productPrice->save();

                        //insert product satuan besar
                        $product_satuan = new ProductUom();
                        $product_satuan->product = $productId;
                        $product_satuan->unit_dasar = $satuan_terkecil['id_satuan'];
                        $product_satuan->unit_tujuan = $satuan_besar['id_satuan'];
                        $product_satuan->nilai_konversi = $satuan_terkecil['isi_satuan'];
                        $product_satuan->level = 2;
                        $product_satuan->state = 'large';
                        $product_satuan->nilai_konversi_terkecil = $satuan_terkecil['isi_satuan'];
                        $product_satuan->save();
                        $productSatuanId = $product_satuan->id;

                        $productPrice = new ProductUomPrice();
                        $productPrice->product = $productId;
                        $productPrice->unit = $satuan_besar['id_satuan'];
                        $productPrice->price_list = 2;
                        $productPrice->price = $harga_jual_non_ppn_besar;
                        $productPrice->date_start = '2026-05-06';
                        $productPrice->min_qty = 1;
                        $productPrice->max_qty = 99999;
                        $productPrice->save();

                        //insert harga beli
                        $existVendor = isset($vendorGrouped[trim($satuan_terkecil['nama_vendordistributor'])]) ? $vendorGrouped[trim($satuan_terkecil['nama_vendordistributor'])][0] : null;
                        if (empty($existVendor)) {
                            //throw error vendor not found
                            DB::rollBack();
                            $result['is_valid'] = false;
                            $result['message'] = 'Error Vendor ' . trim($satuan_terkecil['nama_vendordistributor']) . ' not found';
                            return response()->json($result);
                        } else {
                            $vendorId = $existVendor->id;
                            $productCost = new ProductUomCost();
                            $productCost->product = $productId;
                            $productCost->unit_id = $satuan_besar['id_satuan'];
                            $productCost->cost = $harga_beli_non_ppn_besar;
                            $productCost->vendor = $vendorId;
                            $productCost->date_start = '2026-05-06';
                            $productCost->is_active = 1;
                            $productCost->product_uom = $productSatuanId;
                            $productCost->save();
                        }

                        $productUpdate = Product::find($productId);
                        $productUpdate->vendor = $vendorId;
                        $productUpdate->save();
                    }

                    if ($total_item == 1) {
                        //satuan terkecil, satuan menengah, satuan besar
                        $satuan_besar = $items[0];
                        // $harga_beli_non_ppn_besar = ceil(str_replace(',', '', $satuan_besar['harga_beli_non_ppn']));
                        // $harga_jual_non_ppn_besar = ceil(str_replace(',', '', $satuan_besar['harga_jual_non_ppn']));

                        $harga_beli_non_ppn_besar = ceil(str_replace(',', '', $satuan_besar['harga_belippn']));
                        $harga_jual_non_ppn_besar = ceil(str_replace(',', '', $satuan_besar['harga_jualppn']));
                        // echo '<pre>';
                        // print_r($satuan_besar_ke_satuan_menengah);die;

                        //insert product satuan besar
                        $product_satuan = new ProductUom();
                        $product_satuan->product = $productId;
                        $product_satuan->unit_dasar = $satuan_besar['id_satuan'];
                        $product_satuan->unit_tujuan = $satuan_besar['id_satuan'];
                        $product_satuan->nilai_konversi = $satuan_besar['isi_satuan'];
                        $product_satuan->level = 1;
                        $product_satuan->state = 'large';
                        $product_satuan->nilai_konversi_terkecil = $satuan_besar['isi_satuan'];
                        $product_satuan->save();
                        $productSatuanId = $product_satuan->id;

                        $productPrice = new ProductUomPrice();
                        $productPrice->product = $productId;
                        $productPrice->unit = $satuan_besar['id_satuan'];
                        $productPrice->price_list = 2;
                        $productPrice->price = $harga_jual_non_ppn_besar;
                        $productPrice->date_start = '2026-01-01';
                        $productPrice->min_qty = 1;
                        $productPrice->max_qty = 99999;
                        $productPrice->save();

                        //insert harga beli
                        $existVendor = isset($vendorGrouped[trim($satuan_besar['nama_vendordistributor'])]) ? $vendorGrouped[trim($satuan_besar['nama_vendordistributor'])][0] : null;
                        if (empty($existVendor)) {
                            //throw error vendor not found
                            DB::rollBack();
                            $result['is_valid'] = false;
                            $result['message'] = 'Error Vendor ' . trim($satuan_besar['nama_vendordistributor']) . ' not found';
                            return response()->json($result);
                        } else {
                            $vendorId = $existVendor->id;
                            $productCost = new ProductUomCost();
                            $productCost->product = $productId;
                            $productCost->unit_id = $satuan_besar['id_satuan'];
                            $productCost->cost = $harga_beli_non_ppn_besar;
                            $productCost->vendor = $vendorId;
                            $productCost->date_start = '2026-01-01';
                            $productCost->is_active = 1;
                            $productCost->product_uom = $productSatuanId;
                            $productCost->save();
                        }
                        $productUpdate = Product::find($productId);
                        $productUpdate->vendor = $vendorId;
                        $productUpdate->save();
                    }
                } catch (\Throwable $th) {
                    $failImported += 1;
                    // DB::rollBack();
                    // $result['is_valid'] = false;
                    // $result['data'] = $group;
                    // $result['message'] = 'Error ' . $th->getMessage();
                    // return response()->json($result);
                }

                $productRowsImport++;
            }
            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Success ' . $productRowsImport . ' Imported dan Failed Imported ' . $failImported;
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['message'] = 'Error ' . $th->getMessage();
        }


        return response()->json($result);
    }


    public function submit_stock($items)
    {
        $imported = 0;
        DB::beginTransaction();
        try {
            $adjs = new ProductAdjustmentStock();
            $adjs->code = 'ADJMAY2026';
            $adjs->remarks = 'IMPORTED';
            $adjs->warehouse = 1;
            $adjs->save();
            $adjsId = $adjs->id;

            foreach ($items as $key => $value) {
                $product_code = $value['product_code'];
                $product = Product::where('code', $product_code)->first();
                $konversi_in_pcs = str_replace(',', '', trim($value['konversi_in_pcs']));

                $unit = 9;
                $warehouse = 1;
                $productId = $product->id;
                $item['product'] = $productId;

                $adjDtl = new ProductAdjustmentStockDtl();
                $adjDtl->header_id = $adjsId;
                $adjDtl->product = $productId;
                $adjDtl->unit = $unit;
                $adjDtl->warehouse = $warehouse;
                $adjDtl->qty = $konversi_in_pcs;
                $adjDtl->save();

                stockUpdate(
                    $adjsId,
                    $warehouse,
                    $productId,
                    $unit,
                    $konversi_in_pcs,
                    $item,
                    'add',
                    'adjustment stock'
                );

                $imported += 1;
            }

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Success ' . $imported . ' Imported dan Failed Imported 0';
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $result['message'] = 'Error ' . $imported . ' ' . $th->getMessage();
        }

        return response()->json($result);
    }

    public function submit(Request $request)
    {
        $data = $request->all();
        $user = session()->all();

        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            // ========================
            // FILE UPLOAD
            // ========================
            $dir = 'berkas/document/product/';
            $dir .= date('Y') . '/' . date('m');
            $pathlamp = public_path() . '/' . $dir . '/';
            if (!File::isDirectory($pathlamp)) {
                File::makeDirectory($pathlamp, 0777, true, true);
            }

            $fileName = empty($data['file']) ? '' : $data['file']->getClientOriginalName();
            if (!empty($data['file'])) {
                $files = $data['file'];
                $files->move($pathlamp, $fileName);
            }
            $dbpathlamp = '/' . $dir . '/';

            // ========================
            // SIMPAN PRODUCT
            // ========================
            $roles = $data['id'] == '' ? new Product() : Product::find($data['id']);
            if ($data['id'] == '') {
                $roles->code    = generateCodeProduct();
                $roles->creator = session('user_id');
            }
            $roles->name        = $data['name'];
            $roles->model_number = $data['model_number'];
            $roles->product_type = $data['product_type'];
            $roles->remarks     = $data['remarks'];
            $roles->vendor      = $data['vendor'];
            $roles->tax_sale    = $data['tax_id'];
            $roles->type_tax    = $data['type_tax'];
            $roles->type_retur  = $data['type_retur'];
            $roles->principal  = $data['principal'];
            $roles->sku_name  = $data['sku_name'];
            $roles->category  = $data['category'];
            $roles->sub_brand  = $data['sub_brand'];
            $roles->files       = !empty($data['file']) ? $fileName : $roles->files;
            $roles->path_files  = !empty($data['file']) ? $dbpathlamp : $roles->path_files;
            $roles->save();

            // ========================
            // PRODUCT LOG
            // ========================
            if ($data['id'] != '') {
                $log              = new ProductLog();
                $log->product     = $data['id'];
                $log->name        = $data['name'];
                $log->model_number = $data['model_number'];
                $log->product_type = $data['product_type'];
                $log->remarks     = $data['remarks'];
                $log->files       = isset($fileName) ? $fileName : null;
                $log->path_files  = isset($dbpathlamp) ? $dbpathlamp : null;
                $log->creator     = session('user_id');
                $log->save();
            }

            // ========================
            // SIMPAN PRODUCT UOM + HITUNG HARGA PER UNIT
            // ========================
            $unit_dasar_id   = 0;
            $pricingApply    = [];

            if (isset($data['unit_dasar']) && !empty($data['unit_dasar'])) {
                $totalLevel      = count($data['unit_dasar']);
                $hargaUnitTerbesar = isset($data['harga_satuan_besar']) ? (float) $data['harga_satuan_besar'] : 0;

                // Hitung multiplierTerbesar = perkalian semua nilai_konversi dari level terbesar ke terkecil
                // Contoh: CARTON(6) × PACK(2) × RENCENG(12) = 144
                $multiplierTerbesar = 1;
                for ($i = 0; $i < $totalLevel; $i++) {
                    if (($i + 1) > 1) { // skip level 1 (unit terkecil)
                        $multiplierTerbesar *= (float) $data['nilai_konversi'][$i];
                    }
                }

                // runningMultiplier dimulai dari multiplierTerbesar
                // lalu dibagi nilai_konversi setiap turun level
                $runningMultiplier = $multiplierTerbesar;

                for ($i = 0; $i < $totalLevel; $i++) {
                    if ($i == 0) {
                        $unit_dasar_id = $data['unit_dasar'][$i];
                    }

                    $product_uom = isset($data['level_id'][$i])
                        ? ProductUom::find($data['level_id'][$i])
                        : new ProductUom();

                    $product_uom->product                 = $data['id'];
                    $product_uom->unit_dasar              = $data['unit_dasar'][$i];
                    $product_uom->unit_tujuan             = $data['unit_tujuan'][$i];
                    $product_uom->nilai_konversi          = $data['nilai_konversi'][$i];
                    $product_uom->nilai_konversi_terkecil = $data['nilai_konversi_terkecil'][$i];
                    $product_uom->level                   = $i + 1;
                    $product_uom->state                   = null;

                    if ($i == 0) {
                        $product_uom->state = 'small';
                    }
                    if ($i == $totalLevel - 1) {
                        $product_uom->state = 'large';
                    }

                    // Hitung harga per unit ini berdasarkan runningMultiplier
                    // harga_per_unit = harga_unit_terbesar / (multiplierTerbesar / runningMultiplier)
                    // Contoh CARTON(multiplier=144): 144000 / (144/144) = 144000
                    // Contoh PACK(multiplier=24):    144000 / (144/24)  = 24000
                    // Contoh RENCENG(multiplier=12): 144000 / (144/12)  = 12000
                    // Contoh PCS(multiplier=1):      144000 / (144/1)   = 1000
                    $hargaPerUnit = ($multiplierTerbesar > 0 && $hargaUnitTerbesar > 0)
                        ? $hargaUnitTerbesar / ($multiplierTerbesar / $runningMultiplier)
                        : 0;

                    // $product_uom->harga_per_unit = $hargaPerUnit;
                    $product_uom->save();

                    // Kumpulkan untuk pricingApply (RETAIL)
                    if ($hargaUnitTerbesar > 0) {
                        $pricingApply[] = [
                            'price'      => $hargaPerUnit,
                            'unit_tujuan' => $data['unit_tujuan'][$i],
                            'unit_dasar'  => $data['unit_dasar'][$i],
                        ];
                    }

                    // Turunkan runningMultiplier untuk level berikutnya
                    if (($i + 1) < $totalLevel && (float) $data['nilai_konversi'][$i + 1] > 0) {
                        $runningMultiplier /= (float) $data['nilai_konversi'][$i + 1];
                    }
                }
            }

            $pricingApply = array_reverse($pricingApply);

            // Update unit terkecil di product
            if ($unit_dasar_id != 0) {
                $update       = Product::find($data['id']);
                $update->unit = $unit_dasar_id;
                $update->save();
            }

            // ========================
            // SIMPAN PRODUCT UOM PRICE
            // ========================
            if (isset($data['uom_id']) && !empty($data['uom_id'])) {
                for ($i = 0; $i < count($data['uom_id']); $i++) {
                    $product_uom_price = isset($data['price_uom'][$i])
                        ? ProductUomPrice::find($data['price_uom'][$i])
                        : new ProductUomPrice();

                    if ($product_uom_price->type == 'RETAIL') {
                        // ── RETAIL: pakai pricingApply dari harga_satuan_besar ──
                        $product_uom_price->product    = $data['id'];
                        $product_uom_price->unit       = $data['uom_id'][$i];
                        $product_uom_price->price_list = $data['type_price'][$i];
                        $product_uom_price->price      = $pricingApply[$i]['price'] ?? 0;
                        $product_uom_price->date_start = $data['date_start'][$i];
                        $product_uom_price->min_qty    = $data['min_qty'][$i];
                        $product_uom_price->max_qty    = $data['max_qty'][$i];

                        if ($data['customer'][$i] != '') {
                            list($id_cust, $name_cust) = explode('//', $data['customer'][$i]);
                            $product_uom_price->customer      = $id_cust;
                            $product_uom_price->customer_name = $name_cust;
                            $cust = Customer::find($id_cust);
                            if ($cust->price_list != '') {
                                $result['message'] = 'Customer sudah setup pricelist';
                                return response()->json($result);
                            }
                        }

                        $product_uom_price->save();
                    } else {
                        // ── NON-RETAIL ──
                        if (!isset($data['price_uom'][$i])) {
                            // INSERT BARU — hitung harga semua unit dari harga input
                            // pakai getHargaSemuaUnit supaya konsisten
                            $hargaAllUnit = getHargaSemuaUnit(
                                $data['id'],
                                (float) $data['price'][$i],
                                $data['uom_id'][$i]
                            );

                            // Simpan harga untuk unit yang diinput (unit terbesar)
                            $product_uom_price             = new ProductUomPrice();
                            $product_uom_price->product    = $data['id'];
                            $product_uom_price->unit       = $data['uom_id'][$i];
                            $product_uom_price->price_list = $data['type_price'][$i];
                            $product_uom_price->price      = (float) $data['price'][$i];
                            $product_uom_price->date_start = $data['date_start'][$i];
                            $product_uom_price->min_qty    = $data['min_qty'][$i];
                            $product_uom_price->max_qty    = $data['max_qty'][$i];
                            $product_uom_price->channel    = $data['channel'][$i];
                            $product_uom_price->sub_channel = $data['sub_channel'][$i];

                            if ($data['customer'][$i] != '') {
                                list($id_cust, $name_cust) = explode('//', $data['customer'][$i]);
                                $product_uom_price->customer      = $id_cust;
                                $product_uom_price->customer_name = $name_cust;
                                $cust = Customer::find($id_cust);
                                if ($cust->price_list != '') {
                                    $result['message'] = 'Customer sudah setup pricelist';
                                    return response()->json($result);
                                }
                            }
                            $product_uom_price->save();

                            // Simpan harga untuk unit-unit di bawahnya
                            // getHargaSemuaUnit return dari terbesar ke terkecil
                            // skip index 0 karena sudah disimpan di atas
                            foreach ($hargaAllUnit as $key => $h) {
                                if ($key == 0) continue; // skip unit terbesar, sudah disimpan

                                $sub                   = new ProductUomPrice();
                                $sub->product          = $data['id'];
                                $sub->unit             = $h['unit_id'];
                                $sub->price_list       = $data['type_price'][$i];
                                $sub->price            = $h['harga'];
                                $sub->date_start       = $data['date_start'][$i];
                                $sub->min_qty          = $data['min_qty'][$i];
                                $sub->max_qty          = $data['max_qty'][$i];
                                $sub->channel          = $data['channel'][$i];
                                $sub->sub_channel      = $data['sub_channel'][$i];

                                if ($data['customer'][$i] != '') {
                                    list($id_cust, $name_cust) = explode('//', $data['customer'][$i]);
                                    $sub->customer      = $id_cust;
                                    $sub->customer_name = $name_cust;
                                    $cust = Customer::find($id_cust);
                                    if ($cust->price_list != '') {
                                        $result['message'] = 'Customer sudah setup pricelist';
                                        return response()->json($result);
                                    }
                                }
                                $sub->save();
                            }
                        } else {
                            // UPDATE existing — langsung update harga yang diinput saja
                            $product_uom_price->product     = $data['id'];
                            $product_uom_price->unit        = $data['uom_id'][$i];
                            $product_uom_price->price_list  = $data['type_price'][$i];
                            $product_uom_price->price       = $data['price'][$i];
                            $product_uom_price->date_start  = $data['date_start'][$i];
                            $product_uom_price->min_qty     = $data['min_qty'][$i];
                            $product_uom_price->max_qty     = $data['max_qty'][$i];
                            $product_uom_price->channel     = $data['channel'][$i];
                            $product_uom_price->sub_channel = $data['sub_channel'][$i];

                            if ($data['customer'][$i] != '') {
                                list($id_cust, $name_cust) = explode('//', $data['customer'][$i]);
                                $product_uom_price->customer      = $id_cust;
                                $product_uom_price->customer_name = $name_cust;
                                $cust = Customer::find($id_cust);
                                if ($cust->price_list != '') {
                                    $result['message'] = 'Customer sudah setup pricelist';
                                    return response()->json($result);
                                }
                            }
                            $product_uom_price->save();
                        }
                    }
                }
            } else {
                // Tidak ada uom_id dari input — pakai pricingApply dari harga_satuan_besar (RETAIL)
                if (!empty($pricingApply)) {
                    foreach ($pricingApply as $p) {
                        $product_uom_price              = new ProductUomPrice();
                        $product_uom_price->product     = $data['id'];
                        $product_uom_price->unit        = $p['unit_tujuan'];
                        $product_uom_price->price_list  = 2;
                        $product_uom_price->price       = $p['price'];
                        $product_uom_price->date_start  = date('Y-m-d');
                        $product_uom_price->min_qty     = 1;
                        $product_uom_price->max_qty     = 99999;
                        $product_uom_price->type        = 'RETAIL';
                        $product_uom_price->save();
                    }
                }
            }

            // ========================
            // SIMPAN DISC STRATA
            // ========================
            if (isset($data['uom_disc_id']) && !empty($data['uom_disc_id'])) {
                for ($i = 0; $i < count($data['uom_disc_id']); $i++) {
                    $product_disc_strata                   = isset($data['disc_strata_id'][$i])
                        ? ProductDisc::find($data['disc_strata_id'][$i])
                        : new ProductDisc();
                    $product_disc_strata->product          = $data['id'];
                    $product_disc_strata->unit             = $data['uom_disc_id'][$i];
                    $product_disc_strata->min_qty          = $data['min_disc_qty'][$i];
                    $product_disc_strata->max_qty          = $data['max_disc_qty'][$i];
                    $product_disc_strata->discount_type    = $data['disc_type'][$i];
                    $product_disc_strata->discount_value   = $data['disc_value'][$i];
                    $product_disc_strata->date_start       = $data['date_start_disc'][$i];

                    if ($data['customer_disc'][$i] != '') {
                        list($id_cust, $name_cust) = explode('//', $data['customer_disc'][$i]);
                        $product_disc_strata->customer      = $id_cust;
                        $product_disc_strata->customer_name = $name_cust;
                    }
                    if ($data['customer_category'][$i] != '') {
                        $product_disc_strata->customer_category = $data['customer_category'][$i];
                    }
                    if (!isset($data['disc_strata_id'][$i])) {
                        $product_disc_strata->created_by = $user['user_id'];
                    }
                    $product_disc_strata->save();
                }
            }

            // ========================
            // SIMPAN FREE GOODS
            // ========================
            if (isset($data['uom_disc_free_id']) && !empty($data['uom_disc_free_id'])) {
                for ($i = 0; $i < count($data['uom_disc_free_id']); $i++) {
                    list($product_uom, $product, $product_name) = explode('//', $data['product_free'][$i]);
                    list($unit, $unit_name)                     = explode('//', $data['product_free_unit'][$i]);

                    $product_disc_free              = isset($data['disc_free_id'][$i])
                        ? ProductFreeGood::find($data['disc_free_id'][$i])
                        : new ProductFreeGood();
                    $product_disc_free->product     = $data['id'];
                    $product_disc_free->unit        = $data['uom_disc_free_id'][$i];
                    $product_disc_free->min_qty     = $data['min_free_qty'][$i];
                    $product_disc_free->max_qty     = $data['max_free_qty'][$i];
                    $product_disc_free->product_uom = $product_uom;
                    $product_disc_free->free_product = $product;
                    $product_disc_free->product_name = $product_name;
                    $product_disc_free->free_unit   = $unit;
                    $product_disc_free->free_qty    = $data['free_qty'][$i];
                    $product_disc_free->unit_name   = $unit_name;
                    $product_disc_free->date_start  = $data['date_start_free'][$i];

                    if ($data['customer_disc_free'][$i] != '') {
                        list($id_cust, $name_cust) = explode('//', $data['customer_disc_free'][$i]);
                        $product_disc_free->customer      = $id_cust;
                        $product_disc_free->customer_name = $name_cust;
                    }
                    if ($data['customer_category_free'][$i] != '') {
                        $product_disc_free->customer_category = $data['customer_category_free'][$i];
                    }
                    if (!isset($data['disc_free_id'][$i])) {
                        $product_disc_free->created_by = $user['user_id'];
                    }
                    $product_disc_free->save();
                }
            }

            DB::commit();
            $result['is_valid'] = true;
            $result['message']  = 'Data berhasil disimpan';
        } catch (\Throwable $th) {
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }

        if ($result['is_valid']) {
            return redirect()->action([MasterProductController::class, 'index'], ['success' => $result['message']]);
        } else {
            return redirect()->action([MasterProductController::class, 'index'], ['error' => $result['message']]);
        }
    }

    public function confirmDelete(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $productCost = ProductUomCost::where('product', $data['id'])->get();
            if (!empty($productCost)) {
                DB::rollBack();
                $result['message'] = 'Data tidak bisa dihapus karena masih digunakan di cost list';
                return response()->json($result);
            }

            $menu = Product::find($data['id']);
            $menu->deleted = date('Y-m-d H:i:s');
            $menu->save();

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function removeUom(Request $request)
    {
        $data = $request->all();

        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $product_uom = ProductUom::find($data['id']);
            $product_uom_price = ProductUomPrice::where('unit', $product_uom->unit_dasar)
                ->orWhere('unit', $product_uom->unit_tujuan)->get()->toArray();
            if (!empty($product_uom_price)) {
                DB::rollBack();
                $result['message'] = 'Data tidak bisa dihapus karena masih digunakan di price list';
                return response()->json($result);
            }

            ProductUom::find($data['id'])->delete();
            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function removeUomPrice(Request $request)
    {
        $data = $request->all();
        // echo '<pre>';
        // print_r($data);die;

        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            //harus ada pengecekan ke sales order jika sudah ada maka harga tdak bisa dihapu
            ProductUomPrice::where('id', $data['id'])->delete();
            // $product_uom_price = ProductUomPrice::where('unit', $product_uom->unit_dasar)
            // ->orWhere('unit', $product_uom->unit_tujuan)->get()->toArray();
            // if(!empty($product_uom_price)){
            //     DB::rollBack();
            //     $result['message'] = 'Data tidak bisa dihapus karena masih digunakan di price list';
            //     return response()->json($result);
            // }

            // ProductUom::find($data['id'])->delete();
            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function removeDiscStrata(Request $request)
    {
        $data = $request->all();

        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            ProductDisc::where('id', $data['id'])->delete();
            //harus ada pengecekan ke sales order jika sudah ada maka harga tdak bisa dihapu
            // $product_uom = ProductUom::find($data['id']);
            // $product_uom_price = ProductUomPrice::where('unit', $product_uom->unit_dasar)
            // ->orWhere('unit', $product_uom->unit_tujuan)->get()->toArray();
            // if(!empty($product_uom_price)){
            //     DB::rollBack();
            //     $result['message'] = 'Data tidak bisa dihapus karena masih digunakan di price list';
            //     return response()->json($result);
            // }

            // ProductUom::find($data['id'])->delete();
            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function removeItemDiscFree(Request $request)
    {
        $data = $request->all();

        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            ProductFreeGood::where('id', $data['id'])->delete();
            //harus ada pengecekan ke sales order jika sudah ada maka harga tdak bisa dihapu
            // $product_uom = ProductUom::find($data['id']);
            // $product_uom_price = ProductUomPrice::where('unit', $product_uom->unit_dasar)
            // ->orWhere('unit', $product_uom->unit_tujuan)->get()->toArray();
            // if(!empty($product_uom_price)){
            //     DB::rollBack();
            //     $result['message'] = 'Data tidak bisa dihapus karena masih digunakan di price list';
            //     return response()->json($result);
            // }

            // ProductUom::find($data['id'])->delete();
            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function getDetailData($id)
    {
        DB::enableQueryLog();
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
            ])->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();
        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        return view('web.product.modal.confirmdelete', $data);
    }

    public function addItemLevel(Request $request)
    {
        $data = $request->all();
        $data['data_satuan'] = Unit::whereNull('deleted')->get();
        return view('web.product.product-item-level', $data);
    }

    public function getListPriceList()
    {
        $datadb = DB::table('price_list as pl')->whereNull('deleted')->get();
        return $datadb;
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

    public function addItemPrice(Request $request)
    {
        $data = $request->all();
        $product_uoms = ProductUom::where('product', $data['id'])
            ->select(['u.name as unit_dasar_name', 'ut.name as unit_tujuan_name', 'product_uom.*'])
            ->join('unit as u', 'u.id', 'product_uom.unit_dasar')
            ->join('unit as ut', 'ut.id', 'product_uom.unit_tujuan')
            ->where('product_uom.state', 'large')
            ->get();

        $data_satuan = [];
        foreach ($product_uoms as $key => $value) {
            // $data_satuan[] = $value->unit_dasar . ' // ' . $value->unit_dasar_name;
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
        $data['data_satuan'] = $result_satuan;
        $data['channels'] = $this->getChannel();
        $data['sub_channels'] = $this->getSubChannel();
        $data['tipe_price'] = $this->getListPriceList();
        return view('web.product.product-item-price', $data);
    }

    public function addItemDiscStrata(Request $request)
    {
        $data = $request->all();
        $product_uoms = ProductUom::where('product', $data['id'])
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
        $data['data_satuan'] = $result_satuan;
        $data['data_customer_category'] = CustomerCategory::whereNull('deleted')->get();
        $data['data_disc_tipe'] = ['percent', 'nominal'];
        return view('web.product.product-disc-strata', $data);
    }

    public function addItemDiscFreeGood(Request $request)
    {
        $data = $request->all();
        $product_uoms = ProductUom::where('product', $data['id'])
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
        $data['data_satuan'] = $result_satuan;
        $data['data_customer_category'] = CustomerCategory::whereNull('deleted')->get();
        return view('web.product.product-disc-free', $data);
    }

    public function showDataCustomer(Request $request)
    {
        $data = $request->all();
        return view('web.product.modal.datacustomer', $data);
    }

    public function showDataProduct(Request $request)
    {
        $data = $request->all();
        return view('web.product.modal.dataproductchooce', $data);
    }

    public function updatePrice(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;

        DB::beginTransaction();
        try {
            DB::commit();

            $update = ProductUomPrice::find($data['id']);
            $update->price = $data['price'];
            $update->save();

            $uomPrice = new ProductUomPriceLog();
            $uomPrice->product = $data['id'];
            $uomPrice->unit = $update->unit;
            $uomPrice->channel = $update->channel;
            $uomPrice->sub_channel = $update->sub_channel;
            $uomPrice->price_list = 2;
            $uomPrice->price = $data['price'];
            $uomPrice->date_start = date('Y-m-d');
            $uomPrice->min_qty = $update->min_qty;
            $uomPrice->max_qty = $update->max_qty;
            $uomPrice->type_transaction = $update->type_transaction;
            $uomPrice->created_by = session('user_id');
            $uomPrice->save();


            $result['is_valid'] = true;
            $result['message'] = 'Success';
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $result['message'] = $th->getMessage();
        }

        return response()->json($result);
    }

    public function updateHargaRetail(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;

        $product_uom = ProductUom::whereNull('deleted')
            ->where('product', $data['id'])
            ->orderBy('level')
            ->get();

        DB::beginTransaction();
        try {
            // Ambil nilai_konversi_terkecil dari satuan TERBESAR (level tertinggi)
            $konversi_terbesar = $product_uom->max('nilai_konversi_terkecil'); // = 12

            foreach ($product_uom as $uom) {
                // Harga per satuan = price / konversi_terbesar * konversi_satuan_ini
                $harga_satuan = ($data['price'] / $konversi_terbesar) * $uom->nilai_konversi_terkecil;
                // Carton : (300.000 / 12) * 12 = 300.000 ✅
                // Pcs    : (300.000 / 12) * 1  = 25.000  ✅

                $uomPrice = ProductUomPrice::where('product', $data['id'])
                    ->whereNull('deleted')
                    ->where('unit', $uom->unit_tujuan)
                    ->where('date_start', '<=', date('Y-m-d'))
                    ->where('channel', 'RETAIL UMUM')
                    ->where('sub_channel', 'RT-RETAIL UMUM')
                    ->orderBy('date_start', 'desc')
                    ->first();

                if ($uomPrice) {
                    $uomPrice->price = $harga_satuan;
                    $uomPrice->save();
                } else {
                    $uomPrice = new ProductUomPrice();
                    $uomPrice->product = $data['id'];
                    $uomPrice->unit = $uom->unit_tujuan;
                    $uomPrice->channel = 'RETAIL UMUM';
                    $uomPrice->sub_channel = 'RT-RETAIL UMUM';
                    $uomPrice->price_list = 2;
                    $uomPrice->price = $harga_satuan;
                    $uomPrice->date_start = date('Y-m-d');
                    $uomPrice->min_qty = 1;
                    $uomPrice->max_qty = 99999;
                    $uomPrice->type_transaction = 'qty';
                    $uomPrice->save();
                }

                $uomPrice = new ProductUomPriceLog();
                $uomPrice->product = $data['id'];
                $uomPrice->unit = $uom->unit_tujuan;
                $uomPrice->channel = 'RETAIL UMUM';
                $uomPrice->sub_channel = 'RT-RETAIL UMUM';
                $uomPrice->price_list = 2;
                $uomPrice->price = $harga_satuan;
                $uomPrice->date_start = date('Y-m-d');
                $uomPrice->min_qty = 1;
                $uomPrice->max_qty = 99999;
                $uomPrice->type_transaction = 'qty';
                $uomPrice->created_by = session('user_id');
                $uomPrice->save();
            }

            DB::commit();

            $result['is_valid'] = true;
            $result['message'] = 'Success';
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['message'] = $th->getMessage();
        }

        return response()->json($result);
    }

    public function submit_import_customer(Request $request)
    {
        $data = $request->all();
        // validasi file
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
                        $fail('File harus berformat CSV, XLSX, atau XLS.');
                    }
                }
            ]
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'File tidak valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ambil file
        $file = $request->file('file');

        // nama file
        // $filename = time() . '_' . $file->getClientOriginalName();

        // // simpan ke storage/app/import
        // $path = $file->storeAs('import', $filename);


        $import = new ProductImport();

        Excel::import($import, $file);

        // ambil data excel (BELUM masuk DB)
        $rows = $import->rows;
        // echo '<pre>';
        // print_r($rows);
        // die;

        $result['is_valid'] = false;
        $result['message'] = 'Error';
        DB::beginTransaction();
        try {
            $productRowsImport = 0;
            $failImported = 0;
            $counter = 1;
            foreach ($rows as $key => $group) {
                try {
                    // proses setiap group
                    $group['saldo_awal_per_tanggal'] = empty($group['saldo_awal_per_tanggal']) ? date('d') . '/' . date('m') . '/' . date('Y') : $group['saldo_awal_per_tanggal'];
                    list($day, $month, $year) = explode('/', $group['saldo_awal_per_tanggal']);
                    $group['saldo_awal_per_tanggal'] = $year . '-' . $month . '-' . $day;

                    $customer = new Customer();
                    $customer->nama_customer = $group['nama_custumer'];
                    $customer->pic = '-';
                    $customer->office_contact = $group['handphone'];
                    $customer->phone = $group['handphone'];
                    $customer->email = '-';
                    $customer->address = $group['alamat_2'];
                    $customer->kota = $group['id_kabupatenkota'];
                    $customer->provinsi = 34;
                    $customer->npwp = '-';
                    $customer->currency = $group['mata_uang_utama'];
                    $customer->customer_category = 1;
                    $customer->code = $group['customer_code'];
                    $customer->credit_limit = $group['credit_limit'];
                    $customer->payment_terms = $group['term_of_payment'];
                    $customer->no_ktp = '-';
                    $customer->kecamatan = $group['id_kecamatan'];
                    $customer->kelurahan = $group['id_kelurahan'];
                    $customer->reference_number = 'IMPORT' . date('Ymd');
                    $customer->max_retur = 0;
                    $customer->latitude = str_replace('*', '', $group['latitude']);
                    $customer->longitude = str_replace('*', '', $group['longitude']);
                    $customer->pasar = $group['id_pasar'];
                    $customer->address_penagihan = $group['alamat_penagihan'];
                    $customer->address_pengiriman = $group['alamat_pengiriman'];
                    $customer->branch = 1;
                    $customer->nama_wajib_pajak = $group['nama_wajib_pajak'];
                    $customer->no_pkp = 0;
                    $customer->detail_transaksi = $group['detail_transaksi'];
                    $customer->jenis_dokumen = $group['jenis_dokumen'];
                    $customer->address_pajak = $group['alamat_pajak'];
                    $customer->saldo_awal = $group['saldo_awal'];
                    $customer->saldo_awal_per_tgl = $group['saldo_awal_per_tanggal'];
                    $customer->no_faktur_saldo = $group['no_faktur_saldo'];
                    $customer->cabang_saldo = $group['cabang_saldo'];
                    $customer->status_outlet = $group['status_outlet'];
                    $customer->location_outlet = $group['location_outlet'];
                    $customer->market_segment = $group['market_segment'];
                    $customer->channel_outlet = $group['channel_outlet'];
                    $customer->sub_channel_outlet = $group['sub_channel_outlet'];
                    if ($group['invoice_limit'] > 0) {
                        $customer->min_invoice = $group['invoice_limit'];
                    }
                    $customer->save();
                } catch (\Throwable $th) {
                    $failImported += 1;
                    DB::rollBack();
                    $result['is_valid'] = false;
                    $result['data'] = $group;
                    $result['message'] = 'Error ' . $th->getMessage();
                    return response()->json($result);
                }

                $productRowsImport++;
            }
            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Success ' . $productRowsImport . ' Imported dan Failed Imported ' . $failImported;
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['message'] = 'Error ' . $th->getMessage();
        }


        return response()->json($result);
    }
}
