<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function getTableName()
    {
        return "customer";
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
                'cc.category as customer_category_name'
            ])
            ->join('customer_category as cc', 'cc.id', 'm.customer_category')
            ->whereNull('m.deleted');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.nama_customer', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.pic', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.address', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.email', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.numbering_code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.kota', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.category', 'LIKE', '%' . $keyword . '%');
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
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $roles = $data['id'] == '' ? new Customer() : Customer::find($data['id']);
            if ($data['id'] == '') {
                $roles->code = generateCodeCustomer();
            }
            // $roles->branch = $data['branch'];
            $roles->pic = $data['pic'];
            $roles->numbering_code = $data['numbering_code'];
            $roles->nama_customer = $data['nama_customer'];
            $roles->pic = $data['pic'];
            $roles->phone = $data['phone'];
            $roles->office_contact = $data['office_contact'];
            $roles->email = $data['email'];
            $roles->address = $data['address'];
            $roles->kota = $data['kota'];
            $roles->provinsi = $data['provinsi'];
            $roles->npwp = $data['npwp'];
            $roles->currency = $data['currency'];
            $roles->customer_category = $data['customer_category'];
            $roles->save();

            // $nik_upt = new KaryawanHasUpt();
            // $nik_upt->nik = $data['nik'];
            // $nik_upt->nama = $data['nama'];
            // $nik_upt->upt = $data['upt'];
            // $nik_upt->save();

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
            $menu = Customer::find($data['id']);
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
            ])->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();
        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        return view('web.customer.modal.confirmdelete', $data);
    }
}
