<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
     public function getTableName(){
        return "tax";
    }

    public function getData(){
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName().' as m')
        ->select([
            'm.*',
            'c.account_name',
            'c.account_code',
        ])
        ->join('coa as c', 'm.coa_id', '=', 'c.id')
        ->whereNull('m.deleted')
        ->orderBy('m.tax_code', 'asc');
        if(isset($_POST)){
            $data['recordsTotal'] = $datadb->get()->count();
            if(isset($_POST['search']['value'])){
                $keyword = $_POST['search']['value'];
                $datadb->where(function($query) use ($keyword){
                    $query->where('m.tax_code', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.tax_name', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.tax_type', 'LIKE', '%'.$keyword.'%');
                });
            }
            if(isset($_POST['order'][0]['column'])){
                $datadb->orderBy('m.tax_code', $_POST['order'][0]['dir']);
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
            if($data['id'] == ''){
                $exist = Tax::where('tax_code', $data['tax_code'])
                ->whereNull('deleted')
                ->first();
                if(!empty($exist)){
                    DB::rollBack();
                    $result['message'] = 'Kode akun sudah ada';
                    return response()->json($result);
                }
            }
            $roles = $data['id'] == '' ? new Tax() : Tax::find($data['id']);
            $roles->tax_code = $data['tax_code'];
            $roles->tax_name = $data['tax_name'];
            $roles->tax_type = $data['tax_type'];
            $roles->rate = $data['rate'];
            $roles->coa_id = $data['coa_id'];
            $roles->description = $data['description'];
            $roles->is_active = $data['is_active'];
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
            $menu = Tax::find($data['id']);
            $menu->deleted = date('Y-m-d H:i:s');
            $menu->deleted_by = session('user_id');
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
        ])->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();
        return response()->json($data);
    }

    public function delete(Request $request){
        $data = $request->all();
        return view('web.tax.modal.confirmdelete', $data);
    }
}
