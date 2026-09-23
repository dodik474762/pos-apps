<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\AccountTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountTypesController extends Controller
{
     public function getTableName(){
        return "account_types";
    }

    public function getData(){
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName().' as m')
        ->select([
            'm.*',
        ])
        ->orderBy('m.code', 'asc');
        if(isset($_POST)){
            $data['recordsTotal'] = $datadb->get()->count();
            if(isset($_POST['search']['value'])){
                $keyword = $_POST['search']['value'];
                $datadb->where(function($query) use ($keyword){
                    $query->where('m.code', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.name', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.normal_balance', 'LIKE', '%'.$keyword.'%');
                });
            }
            if(isset($_POST['order'][0]['column'])){
                $datadb->orderBy('m.code', $_POST['order'][0]['dir']);
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
        return json_encode($data);
    }

    public function submit(Request $request){
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            if($data['id'] == ''){
                $exist = AccountTypes::where('code', $data['code'])
                ->first();
                if(!empty($exist)){
                    DB::rollBack();
                    $result['message'] = 'Kode tipe akun sudah ada';
                    return response()->json($result);
                }
            }
            $roles = $data['id'] == '' ? new AccountTypes() : AccountTypes::find($data['id']);
            $roles->code = $data['code'];
            $roles->name = $data['name'];
            $roles->normal_balance = $data['normal_balance'];
            $roles->description = $data['description'];
            $roles->is_active = $data['is_active'];
            $roles->save();

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
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
            AccountTypes::where('id', $data['id'])->delete();

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
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
        return view('web.account_types.modal.confirmdelete', $data);
    }
}
