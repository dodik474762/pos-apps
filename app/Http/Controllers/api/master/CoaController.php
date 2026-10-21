<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoaController extends Controller
{
    public function getTableName(){
        return "coa";
    }

    public function getData(){
        DB::enableQueryLog();
        $company_id = session('id_company');
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName().' as m')
        ->select([
            'm.*',
        ])
        ->whereNull('m.deleted')
        ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('m.company_id', $company_id))
        ->orderBy('m.account_code', 'asc');
        if(isset($_POST)){
            $data['recordsTotal'] = $datadb->get()->count();
            if(isset($_POST['search']['value'])){
                $keyword = $_POST['search']['value'];
                $datadb->where(function($query) use ($keyword){
                    $query->where('m.account_code', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.account_name', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.category', 'LIKE', '%'.$keyword.'%');
                });
            }
            if(isset($_POST['order'][0]['column'])){
                $datadb->orderBy('m.account_code', $_POST['order'][0]['dir']);
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
        $company_id = session('id_company');
        DB::beginTransaction();
        try {
            //code...
            if($data['id'] == ''){
                $exist = Coa::where('account_code', $data['account_code'])
                ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))
                ->whereNull('deleted')
                ->first();
                if(!empty($exist)){
                    DB::rollBack();
                    $result['message'] = 'Kode akun sudah ada';
                    return response()->json($result);
                }
            }
            $roles = $data['id'] == '' ? new Coa() : Coa::when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))->find($data['id']);
            $roles->account_code = $data['account_code'];
            $roles->account_name = $data['account_name'];
            $roles->parent_code = $data['parent_code'] == '' ? null : $data['parent_code'];
            $roles->account_type = $data['account_type'];
            $roles->category = $data['category'];
            $roles->normal_balance = $data['normal_balance'];
            $roles->description = $data['description'];
            $roles->is_active = $data['is_active'];
            if ($company_id !== null && $company_id !== '') {
                $roles->company_id = $company_id;
            }
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
        $company_id = session('id_company');
        DB::beginTransaction();
        try {
            //code...
            $menu = Coa::when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))->find($data['id']);
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
        $company_id = session('id_company');
        $datadb = DB::table($this->getTableName().' as m')
        ->select([
            'm.*',
        ])
        ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('m.company_id', $company_id))
        ->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();
        return response()->json($data);
    }

    public function delete(Request $request){
        $data = $request->all();
        return view('web.coa.modal.confirmdelete', $data);
    }
}
