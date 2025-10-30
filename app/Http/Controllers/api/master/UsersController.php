<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\Users;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsersController extends Controller
{
    public function getTableName(){
        return "users";
    }

    public function getData(){
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName().' as m')
        ->select([
            'm.*',
            'ug.group',
            'p.nik',
            'p.nama_lengkap'
        ])
        ->join('users_group as ug', 'ug.id', 'm.user_group')
        ->join('karyawan as p', 'p.nik', 'm.nik')
        ->whereNull('m.deleted');
        if(isset($_POST)){
            $data['recordsTotal'] = $datadb->get()->count();
            if(isset($_POST['search']['value'])){
                $keyword = $_POST['search']['value'];
                $datadb->where(function($query) use ($keyword){
                    $query->where('ug.group', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('p.nik', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('p.nama_lengkap', 'LIKE', '%'.$keyword.'%');
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
            list($nik, $name) = explode('//', $data['nik']);
            $roles = $data['id'] == '' ? new Users() : Users::find($data['id']);
            $roles->user_group = $data['roles'];
            $roles->username = $data['username'];
            $roles->password = Hash::make($request->get('password'));
            $roles->nik = trim($nik);
            $roles->save();
            $user = User::create([
                'name' => $request->get('name'),
                'username' => $request->get('username'),
                'password' => Hash::make($request->get('password')),
            ]);
            $token = JWTAuth::fromUser($user);

            Users::whereNull('nik')->delete();

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
            $menu = Users::find($data['id']);
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
        return view('web.users.modal.confirmdelete', $data);
    }

    public function showDataKaryawan(Request $request){
        $data = $request->all();
        return view('web.users.modal.datakaryawan', $data);
    }
}
