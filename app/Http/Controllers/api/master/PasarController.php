<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\Pasar;
use App\Models\Master\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PasarController extends Controller
{
    public function getTableName()
    {
        return "pasar";
    }

    public function getData()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $company = session('id_company');
        $akses = session('akses');

        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'r.name as city_name',
                'k.name as kecamatan_name',
            ])
            ->leftJoin('region as r', 'r.id', '=', 'm.kota')
            ->leftJoin('region as k', 'k.id', '=', 'm.kecamatan')
            ->whereNull('m.deleted');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.nama_pasar', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('r.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('k.name', 'LIKE', '%' . $keyword . '%');
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

    public function submit(Request $request)
    {
        $data = $request->all();
        // echo '<pre>';
        // print_r($data);die;
        $files_outlet = $request->file('photo_path');
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $roles = $data['id'] == '' ? new Pasar() : Pasar::find($data['id']);
            if ($data['id'] == '') {
                $roles->users = session('user_id');
            }
            $roles->nama_pasar = $data['nama_pasar'];
            $roles->kota = $data['kota'];
            $roles->provinsi = $data['provinsi'];
            $roles->kecamatan = $data['kecamatan'];
            $roles->latitude = $data['latitude'];
            $roles->longitude = $data['longitude'];
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

    public function confirmDelete(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $menu = Pasar::find($data['id']);
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
                'r.name as city_name',
                'k.name as kecamatan_name',
            ])
            ->leftJoin('region as r', 'r.id', '=', 'm.kota')
            ->leftJoin('region as k', 'k.id', '=', 'm.kecamatan')
            ->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();
        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        return view('web.pasar.modal.confirmdelete', $data);
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

    public function getKecamatan(Request $request)
    {
        $data = $request->all();
        $datadb = Region::where('type', 'KECAMATAN')
            ->where('parent', $data['kota'])
            ->whereNull('deleted')->get()->toArray();

        $result['is_valid'] = true;
        $result['data'] = $datadb;
        return response()->json($result);
    }

    public function getProvinsi(Request $request)
    {
        $data = $request->all();
        $datadb = Region::where('type', 'PROVINSI')
            ->whereNull('deleted')->get()->toArray();

        $result['is_valid'] = true;
        $result['data'] = $datadb;
        return response()->json($result);
    }
}
