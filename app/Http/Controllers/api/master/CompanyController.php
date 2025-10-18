<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CompanyController extends Controller
{
    public function getTableName(){
        return "company";
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
        ->whereNull('m.deleted');
        if(isset($_POST)){
            $data['recordsTotal'] = $datadb->get()->count();
            if(isset($_POST['search']['value'])){
                $keyword = $_POST['search']['value'];
                $datadb->where(function($query) use ($keyword){
                    $query->where('m.nama_company', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.alamat', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.type', 'LIKE', '%'.$keyword.'%');
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
            $roles = $data['id'] == '' ? new CompanyModel() : CompanyModel::find($data['id']);
            $roles->nama_company = $data['nama'];
            $roles->alamat = $data['alamat'];
            $roles->alamat_pengiriman = $data['alamat_pengiriman'];
            $roles->email = $data['email'];
            $roles->no_hp = $data['no_hp'];
            $roles->akun_bank = $data['akun_bank'];
            $roles->akun_bank_name = $data['akun_bank_name'];
            $roles->akun_bank_number = $data['akun_bank_number'];
            $roles->branch_bank = $data['branch_bank'];
            $roles->status = 'APPROVED';
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
            $menu = CompanyModel::find($data['id']);
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
        return view('web.company.modal.confirmdelete', $data);
    }

    public function uploadLogo(Request $request)
    {
        $data = $request->all();
        // echo '<pre>';
        // print_r($data);die;
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            // New file directory
            $dir = 'berkas/company/logo/';
            $dir .= date('Y') . '/' . date('m');
            $pathlamp = public_path() . '/' . $dir . '/';
            // Create the directory if it doesn't exist
            if (!File::isDirectory($pathlamp)) {
                File::makeDirectory($pathlamp, 0777, true, true);
            }
            // Gunakan nama file yang diposting
            $fileName = empty($data['logo-files']) ? '' : $data['logo-files']->getClientOriginalName();

            if (!empty($data['logo-files'])) {
                $files = $data['logo-files'];
                $files->move($pathlamp, $fileName);
            }

            $dbpathlamp = '/' . $dir . '/';

            $items_line = CompanyModel::where('id', $data['id_company'])->first();
            $items_line->files = $fileName;
            $items_line->path_files = $dbpathlamp;
            $items_line->save();

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Data ' . $fileName . ' berhasil disimpan';
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        if ($result['is_valid']) {
            return redirect()->action('web\master\CompanyController@ubah', ['success' => $result['message'], 'id' => $data['id_company']]);
        } else {
            return redirect()->action('web\master\CompanyController@ubah', ['error' => $result['message'], 'id' => $data['id_company']]);
        }
    }
}
