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
use App\Models\Master\Unit;
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
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'pt.type',
                'u.name as unit_name',
                'v.nama_vendor',
                'ps.qty as stock'
            ])
            ->join('product_type as pt', 'pt.id', 'm.product_type')
            ->leftJoin('vendor as v', 'v.id', 'm.vendor')
            ->leftJoin('product_stock as ps', 'ps.product', 'm.id')
            ->leftJoin('unit as u', 'u.id', 'm.unit')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.remarks', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.model_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('v.nama_vendor', 'LIKE', '%' . $keyword . '%');
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
            ->leftJoin('product_uom_cost  as puc', function($q){
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

    public function getDataProductMobile()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.id as product_id',
                'm.code as product_code',
                'm.name as product_name',
                'u.name as product_unit',
                'pup.price as product_price',
                'u.id as product_unit_id',
                'ps.qty as stock_product',
                'pup.id as product_uom_price_id'
            ])
            ->join('product_type as pt', 'pt.id', 'm.product_type')
            ->join('product_uom_price as pup',function($q){
                return $q->on('pup.product', 'm.id')->whereNull('pup.deleted');
            })
            ->join('product_stock as ps', 'ps.product', 'm.id')
            ->join('unit as u', 'u.id', 'pup.unit')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc')
            ->orderBy('pup.id', 'asc');
        $data = $datadb->get()->toArray();
        // echo '<pre>';
        // print_r($query);die;
        $result['is_valid'] = empty($data) ? false : true;
        $result['data'] = $data;
        return response()->json($result);
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

    public function submit_import(Request $request){
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

        $groupByIdProduct = collect($rows)
            // ->where('kode_produk_new_sistem', '63')
            // ->where('kode_produk_principle', 'KPQ-003')
            ->groupBy('kode_produk_new_sistem')
            ->map(function ($items) {

                $totalItem = $items->count(); // panjang array

                return [
                    'total_item' => $totalItem,
                    'items'      => $items->reverse()->values(),
                ];
            })
            ->toArray();
        // echo '<pre>';
        // print_r($groupByIdProduct);die;

        $vendors = DB::table('vendor')->whereNull('deleted')->get()->toArray();
        $vendorGrouped = collect($vendors)->groupBy('nama_vendor')->toArray();
        // echo '<pre>';
        // print_r($vendorGrouped);die;

        $result['is_valid'] = false;
        $result['message'] = 'Error';
        DB::beginTransaction();
        try {
            $productRowsImport = 0;
            foreach ($groupByIdProduct as $key => $group) {
                // proses setiap group
                $productId = $key;
                $total_item = $group['total_item'];
                $items = $group['items'];
                if($total_item == 4){
                    //satuan terkecil, satuan menengah, satuan besar
                    $satuan_terkecil = $items[0];
                    $satuan_renceng = $items[1];
                    $satuan_menengah = $items[2];
                    $satuan_besar = $items[3];

                    $satuan_kecil_ke_renceng = $satuan_terkecil['isi_satuan']/$satuan_renceng['isi_satuan'];
                    $satuan_tengah_ke_renceng = $satuan_renceng['isi_satuan']/$satuan_menengah['isi_satuan'];

                    $harga_beli_non_ppn_besar = ceil(str_replace(',', '', $satuan_besar['harga_beli_non_ppn']));
                    $harga_jual_non_ppn_kecil = ceil(str_replace(',', '', $satuan_terkecil['harga_jual_non_ppn']));
                    $harga_jual_non_ppn_renceng = ceil(str_replace(',', '', $satuan_renceng['harga_jual_non_ppn']));
                    $harga_jual_non_ppn_tengah = ceil(str_replace(',', '', $satuan_menengah['harga_jual_non_ppn']));
                    $harga_jual_non_ppn_besar = ceil(str_replace(',', '', $satuan_besar['harga_jual_non_ppn']));

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
                    $product_satuan->nilai_konversi_terkecil = $satuan_kecil_ke_renceng;
                    $product_satuan->save();

                    $productPrice = new ProductUomPrice();
                    $productPrice->product = $productId;
                    $productPrice->unit = $satuan_renceng['id_satuan'];
                    $productPrice->price_list = 2;
                    $productPrice->price = $harga_jual_non_ppn_renceng;
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
                    $product_satuan->nilai_konversi_terkecil = $satuan_tengah_ke_renceng * $satuan_kecil_ke_renceng;
                    $product_satuan->save();

                    $productPrice = new ProductUomPrice();
                    $productPrice->product = $productId;
                    $productPrice->unit = $satuan_menengah['id_satuan'];
                    $productPrice->price_list = 2;
                    $productPrice->price = $harga_jual_non_ppn_tengah;
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
                    if(empty($existVendor)){
                        //throw error vendor not found
                        DB::rollBack();
                        $result['is_valid'] = false;
                        $result['message'] = 'Error Vendor '.trim($satuan_terkecil['nama_vendordistributor']).' not found';
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
                }

                if($total_item == 3){
                    //satuan terkecil, satuan menengah, satuan besar
                    $satuan_terkecil = $items[0];
                    $satuan_menengah = $items[1];
                    $satuan_besar = $items[2];
                    $satuan_menengah_ke_kecil = $satuan_terkecil['isi_satuan']/$satuan_menengah['isi_satuan'];

                    $harga_beli_non_ppn_besar = str_replace(',', '', $satuan_besar['harga_beli_non_ppn']);
                    $harga_jual_non_ppn_kecil = str_replace(',', '', $satuan_terkecil['harga_jual_non_ppn']);
                    $harga_jual_non_ppn_tengah = str_replace(',', '', $satuan_menengah['harga_jual_non_ppn']);
                    $harga_jual_non_ppn_besar = str_replace(',', '', $satuan_besar['harga_jual_non_ppn']);

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
                    $productPrice->date_start = '2026-01-01';
                    $productPrice->min_qty = 1;
                    $productPrice->max_qty = 99999;
                    $productPrice->save();

                    //insert harga beli
                    $existVendor = isset($vendorGrouped[trim($satuan_terkecil['nama_vendordistributor'])]) ? $vendorGrouped[trim($satuan_terkecil['nama_vendordistributor'])][0] : null;
                    if(empty($existVendor)){
                        //throw error vendor not found
                        DB::rollBack();
                        $result['is_valid'] = false;
                        $result['message'] = 'Error Vendor '.trim($satuan_terkecil['nama_vendordistributor']).' not found';
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
                }

                if($total_item == 2){
                    //satuan terkecil, satuan menengah, satuan besar
                    $satuan_terkecil = $items[0];
                    $satuan_besar = $items[1];

                    $harga_beli_non_ppn_besar = str_replace(',', '', $satuan_besar['harga_beli_non_ppn']);
                    $harga_jual_non_ppn_kecil = str_replace(',', '', $satuan_terkecil['harga_jual_non_ppn']);
                    $harga_jual_non_ppn_besar = str_replace(',', '', $satuan_besar['harga_jual_non_ppn']);
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
                    $productPrice->date_start = '2026-01-01';
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
                    $productPrice->date_start = '2026-01-01';
                    $productPrice->min_qty = 1;
                    $productPrice->max_qty = 99999;
                    $productPrice->save();

                    //insert harga beli
                    $existVendor = isset($vendorGrouped[trim($satuan_terkecil['nama_vendordistributor'])]) ? $vendorGrouped[trim($satuan_terkecil['nama_vendordistributor'])][0] : null;
                    if(empty($existVendor)){
                        //throw error vendor not found
                        DB::rollBack();
                        $result['is_valid'] = false;
                        $result['message'] = 'Error Vendor '.trim($satuan_terkecil['nama_vendordistributor']).' not found';
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
                }

                if($total_item == 1){
                    //satuan terkecil, satuan menengah, satuan besar
                    $satuan_besar = $items[0];
                    $harga_beli_non_ppn_besar = ceil(str_replace(',', '', $satuan_besar['harga_beli_non_ppn']));
                    $harga_jual_non_ppn_besar = ceil(str_replace(',', '', $satuan_besar['harga_jual_non_ppn']));
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
                    if(empty($existVendor)){
                        //throw error vendor not found
                        DB::rollBack();
                        $result['is_valid'] = false;
                        $result['message'] = 'Error Vendor '.trim($satuan_besar['nama_vendordistributor']).' not found';
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
                }

                $productRowsImport++;
            }
            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Success '.$productRowsImport.' Imported';
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['message'] = 'Error '.$th->getMessage();
        }


        return response()->json($result);
    }

    public function submit(Request $request)
    {
        $data = $request->all();
        // echo '<pre>';
        // print_r($data);die;
        $user = session()->all();

        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            // New file directory
            $dir = 'berkas/document/product/';
            $dir .= date('Y') . '/' . date('m');
            $pathlamp = public_path() . '/' . $dir . '/';
            // Create the directory if it doesn't exist
            if (!File::isDirectory($pathlamp)) {
                File::makeDirectory($pathlamp, 0777, true, true);
            }
            /*file leave */
            // Gunakan nama file yang diposting
            $fileName = empty($data['file']) ? '' : $data['file']->getClientOriginalName();
            if (!empty($data['file'])) {
                $files = $data['file'];
                $files->move($pathlamp, $fileName);
            }

            $dbpathlamp = '/' . $dir . '/';

            $roles = $data['id'] == '' ? new Product() : Product::find($data['id']);
            if ($data['id'] == '') {
                $roles->code = generateCodeProduct();
                $roles->creator = session('user_id');
            }
            $roles->name = $data['name'];
            $roles->model_number = $data['model_number'];
            $roles->product_type = $data['product_type'];
            $roles->remarks = $data['remarks'];
            $roles->vendor = $data['vendor'];
            $roles->tax_sale = $data['tax_id'];
            $roles->type_tax = $data['type_tax'];
            $roles->type_retur = $data['type_retur'];
            $roles->files = !empty($data['file']) ? $fileName : $roles->files;
            $roles->path_files = !empty($data['file']) ? $dbpathlamp : $roles->path_files;
            $roles->save();

            if ($data['id'] != '') {
                $roles = new ProductLog();
                $roles->product = $data['id'];
                $roles->name = $data['name'];
                $roles->model_number = $data['model_number'];
                $roles->product_type = $data['product_type'];
                $roles->remarks = $data['remarks'];
                $roles->files = isset($fileName) ? $fileName : $roles->files;
                $roles->path_files = isset($dbpathlamp) ? $dbpathlamp : $roles->path_files;
                $roles->creator = session('user_id');
                $roles->save();
            }

            $unit_dasar_id = 0;
            if (isset($data['unit_dasar'])) {
                if (!empty($data['unit_dasar'])) {
                    for ($i = 0; $i < count($data['unit_dasar']); $i++) {
                        if($i == 0){
                            $unit_dasar_id = $data['unit_dasar'][$i];
                        }
                        $product_uom = isset($data['level_id'][$i]) ? ProductUom::find($data['level_id'][$i]) : new ProductUom();
                        $product_uom->product = $data['id'];
                        $product_uom->unit_dasar = $data['unit_dasar'][$i];
                        $product_uom->unit_tujuan = $data['unit_tujuan'][$i];
                        $product_uom->nilai_konversi = $data['nilai_konversi'][$i];
                        $product_uom->nilai_konversi_terkecil = $data['nilai_konversi_terkecil'][$i];
                        $product_uom->level = $i + 1;
                        if($i == 0){
                            $product_uom->state = 'small';
                        }
                        if($i == count($data['unit_dasar']) - 1){
                            $product_uom->state = 'large';
                        }
                        $product_uom->save();
                    }
                }
            }

            if($unit_dasar_id != 0){
                $update = Product::find($data['id']);
                $update->unit = $unit_dasar_id;
                $update->save();
            }

            if (isset($data['uom_id'])) {
                if (!empty($data['uom_id'])) {
                    for ($i = 0; $i < count($data['uom_id']); $i++) {
                        $product_uom_price = isset($data['price_uom'][$i]) ? ProductUomPrice::find($data['price_uom'][$i]) : new ProductUomPrice();
                        $product_uom_price->product = $data['id'];
                        $product_uom_price->unit = $data['uom_id'][$i];
                        $product_uom_price->price_list = $data['type_price'][$i];
                        $product_uom_price->price = $data['price'][$i];
                        $product_uom_price->date_start = $data['date_start'][$i];
                        $product_uom_price->min_qty = $data['min_qty'][$i];
                        $product_uom_price->max_qty = $data['max_qty'][$i];
                        if ($data['customer'][$i] != '') {
                            list($id_cust, $name_cust) = explode('//', $data['customer'][$i]);
                            $product_uom_price->customer = $id_cust;
                            $product_uom_price->customer_name = $name_cust;

                            /*cek customer sudah setup pricel level */
                            $cust = Customer::find($id_cust);
                            if($cust->price_list != ''){
                                $result['message'] = 'Customer sudah setup pricelist';
                                return response()->json($result);
                            }
                            /*cek customer sudah setup pricel level */
                        }
                        $product_uom_price->save();
                    }
                }
            }

            if (isset($data['uom_disc_id'])) {
                if (!empty($data['uom_disc_id'])) {
                    for ($i = 0; $i < count($data['uom_disc_id']); $i++) {
                        $product_disc_strata = isset($data['disc_strata_id'][$i]) ? ProductDisc::find($data['disc_strata_id'][$i]) : new ProductDisc();
                        $product_disc_strata->product = $data['id'];
                        $product_disc_strata->unit = $data['uom_disc_id'][$i];
                        $product_disc_strata->min_qty = $data['min_disc_qty'][$i];
                        $product_disc_strata->max_qty = $data['max_disc_qty'][$i];
                        $product_disc_strata->discount_type = $data['disc_type'][$i];
                        $product_disc_strata->discount_value = $data['disc_value'][$i];
                        $product_disc_strata->date_start = $data['date_start_disc'][$i];
                        if ($data['customer_disc'][$i] != '') {
                            list($id_cust, $name_cust) = explode('//', $data['customer_disc'][$i]);
                            $product_disc_strata->customer = $id_cust;
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
            }

            if (isset($data['uom_disc_free_id'])) {
                if (!empty($data['uom_disc_free_id'])) {
                    for ($i = 0; $i < count($data['uom_disc_free_id']); $i++) {
                        list($product_uom, $product, $product_name) = explode('//', $data['product_free'][$i]);
                        list($unit, $unit_name) = explode('//', $data['product_free_unit'][$i]);
                        $product_disc_free = isset($data['disc_free_id'][$i]) ? ProductFreeGood::find($data['disc_free_id'][$i]) : new ProductFreeGood();
                        $product_disc_free->product = $data['id'];
                        $product_disc_free->unit = $data['uom_disc_free_id'][$i];
                        $product_disc_free->min_qty = $data['min_free_qty'][$i];
                        $product_disc_free->max_qty = $data['max_free_qty'][$i];
                        $product_disc_free->product_uom = $product_uom;
                        $product_disc_free->free_product = $product;
                        $product_disc_free->product_name = $product_name;
                        $product_disc_free->free_unit = $unit;
                        $product_disc_free->free_qty = $data['free_qty'][$i];
                        $product_disc_free->unit_name = $unit_name;
                        $product_disc_free->date_start = $data['date_start_free'][$i];
                        if ($data['customer_disc_free'][$i] != '') {
                            list($id_cust, $name_cust) = explode('//', $data['customer_disc_free'][$i]);
                            $product_disc_free->customer = $id_cust;
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
            }

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Data berhasil disimpan';
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        if ($result['is_valid']) {
            return redirect()->action([MasterProductController::class, 'index'], ['success' => $result['message']]);
        } else {
            return redirect()->action([MasterProductController::class, 'index'], ['error' => $result['message']]);
        }
        // return response()->json($result);
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

    public function addItemPrice(Request $request)
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
}
