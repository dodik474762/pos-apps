<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\Accounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountsController extends Controller
{
     public function getTableName(){
        return "accounts";
    }

    public function getData(){
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName().' as m')
        ->select([
            'm.*',
            'at.name as account_type_name',
            'p.code as parent_code',
            'p.name as parent_name',
        ])
        ->join('account_types as at', 'm.account_type_id', '=', 'at.id')
        ->leftJoin('accounts as p', 'm.parent_id', '=', 'p.id')
        ->whereNull('m.deleted_at')
        ->orderBy('m.code', 'asc');
        if(isset($_POST)){
            $data['recordsTotal'] = $datadb->get()->count();
            if(isset($_POST['search']['value'])){
                $keyword = $_POST['search']['value'];
                $datadb->where(function($query) use ($keyword){
                    $query->where('m.code', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.name', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('at.name', 'LIKE', '%'.$keyword.'%');
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
                $exist = Accounts::where('code', $data['code'])
                ->whereNull('deleted_at')
                ->first();
                if(!empty($exist)){
                    DB::rollBack();
                    $result['message'] = 'Kode akun sudah ada';
                    return response()->json($result);
                }
            }
            $roles = $data['id'] == '' ? new Accounts() : Accounts::find($data['id']);
            $roles->code = $data['code'];
            $roles->name = $data['name'];
            $roles->account_type_id = $data['account_type_id'];
            $roles->parent_id = $data['parent_id'] == '' ? null : $data['parent_id'];
            $roles->level = $data['parent_id'] == '' ? 1 : (int) DB::table($this->getTableName())
                ->where('id', $data['parent_id'])
                ->value('level') + 1;
            $roles->normal_balance = $data['normal_balance'];
            $roles->is_header = $data['is_header'];
            $roles->is_control_account = $data['is_control_account'] ?? 0;
            $roles->is_active = $data['is_active'];
            $roles->description = $data['description'];
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
            $menu = Accounts::find($data['id']);
            $menu->deleted_at = date('Y-m-d H:i:s');
            $menu->save();

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
        return view('web.accounts.modal.confirmdelete', $data);
    }
}
