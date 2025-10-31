<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Roles;
use App\Models\Master\RoutingHeader;
use App\Models\Master\RoutingPermission;
use App\Models\Master\RoutingReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoutingApprovalController extends Controller
{

    public function getTableName(){
        return "routing_header";
    }

    public function getData(){
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName().' as m')
        ->select([
            'm.*',
            'mm.nama as menu_name',
            'd.keterangan as group_name'
        ])
        ->join('menu as mm', 'mm.id', 'm.menu')
        ->leftJoin('dictionary as d', 'd.term_id', 'm.group')
        ->whereNull('m.deleted')
        ->orderBy('m.id', 'desc');
        if(isset($_POST)){
            $data['recordsTotal'] = $datadb->get()->count();
            if(isset($_POST['search']['value'])){
                $keyword = $_POST['search']['value'];
                $datadb->where(function($query) use ($keyword){
                    $query->where('m.menu', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('mm.nama', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('d.keterangan', 'LIKE', '%'.$keyword.'%');
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
            $company = CompanyModel::where('type', 'HO')->whereNull('deleted')->first();

            $aksesData = Roles::where('group', session('akses'))->first();
            $roles = $data['id'] == '' ? new RoutingHeader() : RoutingHeader::find($data['id']);
            $roles->user_group = $aksesData->id;
            $roles->company = $company->id;
            $roles->menu = $data['menu'];
            $roles->group = $data['group'];
            $roles->remarks = $data['remarks'];
            $roles->save();
            $headerId = $roles->id;

            foreach ($data['routing'] as $key => $value) {
                if ($value['remove'] != '1') {
                    list($users, $usersName) = explode('//', $value['users']);
                    $items = $value['id'] == '' ? new RoutingPermission() : RoutingPermission::find($value['id']);
                    $items->routing_header = $headerId;
                    $items->menu = $data['menu'];
                    $items->group = $data['group'];
                    $items->prev_state = $key == 0 ? null : $data['routing'][$key - 1]['routing'];
                    $items->state = $value['routing'];
                    $items->users = $users;
                    $items->is_active = 1;
                    $items->save();
                } else {
                    if ($value['id'] != '') {
                        RoutingPermission::where('id', $value['id'])->delete();
                    }
                }
            }

            foreach ($data['reminders'] as $key => $value) {
                if ($value['remove'] != '1') {
                    if($value['users'] != ''){
                        list($users, $usersName) = explode('//', $value['users']);
                        $items = $value['id'] == '' ? new RoutingReminder() : RoutingReminder::find($value['id']);
                        $items->routing_header = $headerId;
                        $items->menu = $data['menu'];
                        $items->users = $users;
                        $items->save();
                    }
                } else {
                    if ($value['id'] != '') {
                        RoutingReminder::where('id', $value['id'])->delete();
                    }
                }
            }

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
            $menu = RoutingHeader::find($data['id']);
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
        ])->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();
        return response()->json($data);
    }

    public function delete(Request $request){
        $data = $request->all();
        return view('web.routing_approval.modal.confirmdelete', $data);
    }

    public function showDataUsers(Request $request){
        $data = $request->all();
        return view('web.routing_approval.modal.datausers', $data);
    }
}
