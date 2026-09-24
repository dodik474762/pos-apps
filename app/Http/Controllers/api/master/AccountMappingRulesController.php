<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Transaction\AccountMappingRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountMappingRulesController extends Controller
{
    public function getTableName()
    {
        return "account_mapping_rules";
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
                'a.code as account_code',
                'a.name as account_name',
            ])
            ->leftJoin('accounts as a', 'm.account_id', '=', 'a.id')
            ->whereNull('m.deleted_at')
            ->orderBy('m.transaction_type', 'asc')
            ->orderBy('m.account_role', 'asc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.transaction_type', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.account_role', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('a.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('a.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.description', 'LIKE', '%' . $keyword . '%');
                });
            }
            if (isset($_POST['order'][0]['column'])) {
                $datadb->orderBy('m.transaction_type', $_POST['order'][0]['dir']);
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
        if (isset($_POST['draw'])) {
            $data['draw'] = $_POST['draw'];
        }
        return json_encode($data);
    }

    public function submit(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            $role = $data['id'] == '' ? new AccountMappingRules() : AccountMappingRules::find($data['id']);
            $role->transaction_type = $data['transaction_type'];
            $role->account_role = $data['account_role'];
            $role->account_id = $data['account_id'];
            $role->product_category_id = $data['product_category_id'] == '' ? null : $data['product_category_id'];
            $role->warehouse_id = $data['warehouse_id'] == '' ? null : $data['warehouse_id'];
            $role->company_id = $data['company_id'] == '' ? null : $data['company_id'];
            $role->is_active = $data['is_active'];
            $role->description = $data['description'];
            $role->save();

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function confirmDelete(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            $menu = AccountMappingRules::find($data['id']);
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
        return view('web.account_mapping_rules.modal.confirmdelete', $data);
    }
}
