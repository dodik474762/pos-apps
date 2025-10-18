<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Http\Controllers\web\master\ProductController as MasterProductController;
use App\Models\Master\Product;
use App\Models\Master\ProductCatalog;
use App\Models\Master\ProductLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
            ])
            ->join('product_type as pt', 'pt.id', 'm.product_type')
            ->join('unit as u', 'u.id', 'm.unit')
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
            if($value['files'] != ''){
                $files = explode('.', $value['files']);
                $typeFle = end($files);
                if($typeFle != "pdf"){
                    $value['img'] = url('/').$value['path_files'].$value['files'];
                }
            }
            $resultdb [] = $value;
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
            if($value['files'] != ''){
                $files = explode('.', $value['files']);
                $typeFle = end($files);
                if($typeFle != "pdf"){
                    $value['img'] = url('/').$value['path_files'].$value['files'];
                }
            }
            $resultdb [] = $value;
        }

        $result['data'] = $resultdb;
        $result['total'] = $totalRows;
        $result['total_data'] = count($datadb);

        return response()->json($result);
    }

    public function submit(Request $request)
    {
        $data = $request->all();
        // $data = json_decode($post['data'], true);
        // echo '<pre>';
        // print_r($data);die;
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            // New file directory
            $dir = 'berkas/document/product_catalog/';
            $dir .= date('Y') . '/' . date('m');
            $pathlamp = public_path() . '/' . $dir . '/';
            // Create the directory if it doesn't exist
            if (!File::isDirectory($pathlamp)) {
                File::makeDirectory($pathlamp, 0777, true, true);
            }
            /*file leave */
            // $file = $data['file'];
            // Gunakan nama file yang diposting
            $fileName = empty($data['file']) ? '' : $data['file']->getClientOriginalName();

            // if ($data['file'] != '') {
            //     uploadFileFromBlobString($pathlamp, $fileName, $file);
            // } else {
            //     if ($file != '') {
            //         File::put($pathlamp . $fileName, base64_decode($file));
            //     }
            // }

            if(!empty($data['file'])){
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
            $roles->unit = $data['unit'];
            $roles->remarks = $data['remarks'];
            $roles->purchase_price = str_replace(',', '.', str_replace('.', '', $data['purchase_price']));
            $roles->selling_price = str_replace(',', '.', str_replace('.', '', $data['selling_price']));
            $roles->files = !empty($data['file']) ? $fileName : $roles->files;
            $roles->path_files = !empty($data['file']) ? $dbpathlamp : $roles->path_files;
            $roles->save();

            if ($data['id'] != '') {
                $roles = new ProductLog();
                $roles->product = $data['id'];
                $roles->name = $data['name'];
                $roles->model_number = $data['model_number'];
                $roles->product_type = $data['product_type'];
                $roles->unit = $data['unit'];
                $roles->remarks = $data['remarks'];
                $roles->purchase_price = str_replace(',', '.', str_replace('.', '', $data['purchase_price']));
                $roles->selling_price = str_replace(',', '.', str_replace('.', '', $data['selling_price']));
                $roles->files = isset($fileName) ? $fileName : $roles->files;
                $roles->path_files = isset($dbpathlamp) ? $dbpathlamp : $roles->path_files;
                $roles->creator = session('user_id');
                $roles->save();
            }
            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Data berhasil disimpan';
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        if($result['is_valid']){
            return redirect()->action([MasterProductController::class, 'index'], ['success' => $result['message']]);
        }else{
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
}
