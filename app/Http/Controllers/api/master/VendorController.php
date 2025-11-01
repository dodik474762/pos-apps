<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\Region;
use App\Models\Master\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
     public function getTableName(){
        return "vendor";
    }

    public function getData(){
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $company = session('id_company');
        $akses = session('akses');

        $datadb = DB::table($this->getTableName().' as m')
        ->select([
            'm.*',
        ])
        ->whereNull('m.deleted');

        if(isset($_POST)){
            $data['recordsTotal'] = $datadb->get()->count();
            if(isset($_POST['search']['value'])){
                $keyword = $_POST['search']['value'];
                $datadb->where(function($query) use ($keyword){
                    $query->where('m.nama_vendor', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.pic', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.address', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.email', 'LIKE', '%'.$keyword.'%');
                });
            }
            if(isset($_POST['order'][0]['column'])){
                $datadb->orderBy('m.id', $_POST['order'][0]['dir']);
            }
            $data['recordsFiltered'] = $datadb->get()->count();

            if(isset($_POST['length'])){
                $datadb->limit($_POST['length']);
            }
            if(isset($_POST['start'])){
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

    public function submit(Request $request){
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $roles = $data['id'] == '' ? new Vendor() : Vendor::find($data['id']);
            $roles->nama_vendor = $data['nama_vendor'];
            $roles->pic = $data['pic'];
            $roles->remarks = $data['remarks'];
            $roles->phone = $data['phone'];
            $roles->mobile_phone = $data['mobile_phone'];
            $roles->email = $data['email'];
            $roles->address = $data['address'];
            $roles->city = $data['city'];
            $roles->region = $data['region'];
            $roles->save();
            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function confirmDelete(Request $request){
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $menu = Vendor::find($data['id']);
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

    public function getDetailData($id){
        DB::enableQueryLog();
        $datadb = DB::table($this->getTableName().' as m')
        ->select([
            'm.*',
            'r.name as region_name',
            'c.name as city_name'
        ])
        ->join('region as r', 'r.id', 'm.region')
        ->join('region as c', 'c.id', 'm.city')
        ->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();
        return response()->json($data);
    }

    public function delete(Request $request){
        $data = $request->all();
        return view('web.vendor.modal.confirmdelete', $data);
    }

     public function getCity(Request $request)
    {
        $data = $request->all();
        $datadb = Region::where('type', 'KOTA')
            ->where('parent', $data['province'])
            ->whereNull('deleted')->get()->toArray();

        $result['is_valid'] = true;
        $result['data'] = $datadb;
        return response()->json($result);
    }
}
