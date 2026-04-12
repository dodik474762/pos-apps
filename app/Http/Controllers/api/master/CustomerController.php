<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\Customer;
use App\Models\Master\Region;
use App\Models\Master\ProductUom;
use App\Models\Master\ProductUomPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
                'cc.category as customer_category_name',
                'r.name as city_name',
                'k.name as kecamatan_name',
                'kl.name as kelurahan_name',
            ])
            ->join('customer_category as cc', 'cc.id', 'm.customer_category')
            ->leftJoin('region as r', 'r.id', '=', 'm.kota')
            ->leftJoin('region as k', 'k.id', '=', 'm.kecamatan')
            ->leftJoin('region as kl', 'kl.id', '=', 'm.kelurahan')
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
                    $query->orWhere('m.channel_outlet', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.sub_channel_outlet', 'LIKE', '%' . $keyword . '%');
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

    public function getDataAcc()
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
                'cc.category as customer_category_name',
                'r.name as city_name',
                'k.name as kecamatan_name',
                'kl.name as kelurahan_name',
            ])
            ->join('customer_category as cc', 'cc.id', 'm.customer_category')
            ->leftJoin('region as r', 'r.id', '=', 'm.kota')
            ->leftJoin('region as k', 'k.id', '=', 'm.kecamatan')
            ->leftJoin('region as kl', 'kl.id', '=', 'm.kelurahan')
            ->whereNull('m.deleted')
            ->where('m.platform', 'mobile');

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
        $items_price = isset($data['items_price']) ? json_decode($data['items_price']) : [];

        $files_outlet = $request->file('photo_path');
        $files_ktp = $request->file('foto_ktp_path');
        $files_npwp = $request->file('foto_npwp_path');
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $roles = $data['id'] == '' ? new Customer() : Customer::find($data['id']);
            if ($data['id'] == '') {
                $roles->code = generateCodeCustomer();
                $roles->users = session('user_id');
            }

            $dir = 'berkas/document/outlet/';
            $dir .= date('Y') . '/' . date('m');
            $pathlamp = public_path() . '/' . $dir . '/';
            // Create the directory if it doesn't exist
            if (! File::isDirectory($pathlamp)) {
                File::makeDirectory($pathlamp, 0777, true, true);
            }

            if (isset($files_outlet)) {
                $fileOutletName = 'outlet_noo' . time() . '.jpg';

                $path = $files_outlet->move(public_path($dir), $fileOutletName);
                $dbpathlampOutlet = '/' . $dir . '/';
                $roles->photo_path = $dbpathlampOutlet . $fileOutletName;
            }

            if (isset($files_ktp)) {
                $fileOutletName = 'files_ktp' . time() . '.jpg';

                $path = $files_ktp->move(public_path($dir), $fileOutletName);
                $dbpathlampOutlet = '/' . $dir . '/';
                $roles->foto_ktp_path = $dbpathlampOutlet . $fileOutletName;
            }

            if (isset($files_npwp)) {
                $fileOutletName = 'files_npwp' . time() . '.jpg';

                $path = $files_npwp->move(public_path($dir), $fileOutletName);
                $dbpathlampOutlet = '/' . $dir . '/';
                $roles->foto_npwp_path = $dbpathlampOutlet . $fileOutletName;
            }
            // $roles->branch = $data['branch'];
            $roles->pic = $data['pic'];
            $roles->no_ktp = $data['no_ktp'];
            $roles->price_list = $data['price_list'];
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
            $roles->payment_terms = $data['payment_terms'];
            $roles->credit_limit = $data['credit_limit'];
            $roles->kecamatan = $data['kecamatan'];
            $roles->kelurahan = $data['kelurahan'];
            $roles->reference_number = $data['reference_number'];
            $roles->max_retur = $data['max_retur'];
            $roles->latitude = $data['latitude'];
            $roles->longitude = $data['longitude'];
            $roles->pasar = $data['pasar'];
            $roles->channel_outlet = $data['channel_outlet'];
            $roles->sub_channel_outlet = $data['sub_channel_outlet'];
            $roles->min_invoice = $data['min_invoice'];
            $roles->branch = 'YOGYAKARTA';
            $roles->save();
            $id_cust = $roles->id;
            $name_cust = $data['nama_customer'];


            foreach ($items_price as $key => $value) {
                list($product, $product_name) = explode('//', $value->product);
                list($unit, $unit_name) = explode('-', $value->uom);
                $product_uom_price = $value->id != '' ? ProductUomPrice::find($value->id) : new ProductUomPrice();
                $product_uom_price->product = $product;
                $product_uom_price->unit = $unit;
                $product_uom_price->price_list = $value->type_price;
                $product_uom_price->price = $value->price;
                $product_uom_price->date_start = $value->date_start;
                $product_uom_price->min_qty = $value->min_qty;
                $product_uom_price->max_qty = $value->max_qty;
                $product_uom_price->customer = $id_cust;
                $product_uom_price->customer_name = $name_cust;
                $product_uom_price->save();
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

    public function approve(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $data = $request->all();
        // echo '<pre>';
        // print_r($data);die;
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $roles = Customer::find($data['id']);
            // $roles->branch = $data['branch'];
            $roles->status = $data['status'] == 'acc' ? 'APPROVED' : 'REJECTED';
            $roles->approved_date = date('Y-m-d H:i:s');
            $roles->approved_by = session('user_id');
            $roles->remarks = isset($data['remarks']) ? $data['remarks'] : '';
            $roles->office_contact = $data['office_contact'];
            $roles->reference_number = $data['reference_number'];
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

    public function submitNoo(Request $request)
    {
        $data = json_decode($request->input('data'), true);
        $files_outlet = $request->file('files_outlet');
        $files_npwp = $request->file('files_npwp');
        $files_ktp = $request->file('files_ktp');
        $users_id = $data['user_id'];
        // echo '<pre>';
        // print_r($data);die;
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {

            $dir = 'berkas/document/outlet/';
            $dir .= date('Y') . '/' . date('m');
            $pathlamp = public_path() . '/' . $dir . '/';
            // Create the directory if it doesn't exist
            if (! File::isDirectory($pathlamp)) {
                File::makeDirectory($pathlamp, 0777, true, true);
            }

            $fileOutletName = 'outlet_noo' . time() . '.jpg';

            // $path = $files_outlet->move(public_path($dir), $fileOutletName);
            // $dbpathlampOutlet = '/'.$dir.'/';          

            $roles = new Customer();
            $roles->code = generateCodeCustomer();

            if (isset($files_outlet)) {
                $fileOutletName = 'outlet_noo' . time() . '.jpg';

                $path = $files_outlet->move(public_path($dir), $fileOutletName);
                $dbpathlampOutlet = '/' . $dir . '/';
                $roles->photo_path = $dbpathlampOutlet . $fileOutletName;
            }

            if (isset($files_ktp)) {
                $fileOutletName = 'files_ktp' . time() . '.jpg';

                $path = $files_ktp->move(public_path($dir), $fileOutletName);
                $dbpathlampOutlet = '/' . $dir . '/';
                $roles->foto_ktp_path = $dbpathlampOutlet . $fileOutletName;
            }

            if (isset($files_npwp)) {
                $fileOutletName = 'files_npwp' . time() . '.jpg';

                $path = $files_npwp->move(public_path($dir), $fileOutletName);
                $dbpathlampOutlet = '/' . $dir . '/';
                $roles->foto_npwp_path = $dbpathlampOutlet . $fileOutletName;
            }

            $roles->pic = $data['pic'];
            $roles->no_ktp = $data['no_ktp'];
            $roles->nama_customer = $data['nama_customer'];
            $roles->phone = $data['phone'];
            $roles->email = $data['email'];
            $roles->address = $data['address'];
            $roles->kota = $data['kota'];
            $roles->provinsi = $data['provinsi'];
            if (isset($data['kecamatan'])) {
                $roles->kecamatan = $data['kecamatan'];
                $roles->kelurahan = $data['kelurahan'];
            }
            $roles->npwp = $data['npwp'];
            $roles->customer_category = 2; //kandidat
            $roles->latitude = $data['latitude'];
            $roles->longitude = $data['longitude'];
            $roles->platform = 'mobile';
            $roles->users = $users_id;
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
                'r.name as city_name',
                'k.name as kecamatan_name',
                'kl.name as kelurahan_name',
            ])
            ->leftJoin('region as r', 'r.id', '=', 'm.kota')
            ->leftJoin('region as k', 'k.id', '=', 'm.kecamatan')
            ->leftJoin('region as kl', 'kl.id', '=', 'm.kelurahan')
            ->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();
        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        return view('web.customer.modal.confirmdelete', $data);
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

    public function getKelurahan(Request $request)
    {
        $data = $request->all();
        $datadb = Region::where('type', 'KELURAHAN')
            ->where('parent', $data['kecamatan'])
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

    public function getListPriceList()
    {
        $datadb = DB::table('price_list as pl')->whereNull('deleted')->get();
        return $datadb;
    }

    public function addItemPrice(Request $request)
    {
        $data = $request->all();
        $product_uoms = ProductUom::where('product', $data['id'])
            ->select(['u.name as unit_dasar_name', 'ut.name as unit_tujuan_name', 'product_uom.*'])
            ->join('unit as u', 'u.id', 'product_uom.unit_dasar')
            ->join('unit as ut', 'ut.id', 'product_uom.unit_tujuan')
            ->get();

        $data_satuan = [];
        foreach ($product_uoms as $key => $value) {
            $data_satuan[] = $value->unit_dasar . ' // ' . $value->unit_dasar_name;
            $data_satuan[] = $value->unit_tujuan . ' // ' . $value->unit_tujuan_name;
        }
        $data_satuan = collect($data_satuan)->unique()->values()->all();
        $result_satuan = [];
        foreach ($data_satuan as $key => $value) {
            list($id, $name) = explode('//', $value);
            $result_satuan[] = [
                'id' => trim($id),
                'name' => trim($name)
            ];
        }
        $data['data_satuan'] = $result_satuan;
        $data['tipe_price'] = $this->getListPriceList();
        return view('web.customer.product-item-price', $data);
    }

    public function showDataProduct(Request $request)
    {
        $data = $request->all();
        return view('web.product.modal.dataproductchooce', $data);
    }

    public function getDataProduct()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table('product as m')
            ->select([
                'm.*',
                'pt.type',
                'u.name as unit_name',
                'uo.name as unit_tujuan_name',
                'uo.id as unit_tujuan_id',
                'pu.id as id_uom',
                'puc.cost as product_cost',
                'puc.date_start as product_cost_date_start',
            ])
            ->join('product_type as pt', 'pt.id', 'm.product_type')
            ->join('product_uom as pu', 'pu.product', 'm.id')
            ->join('unit as uo', 'uo.id', 'pu.unit_tujuan')
            ->join('unit as u', 'u.id', 'm.unit')
            ->leftJoin('product_uom_cost  as puc', function ($q) {
                return $q->on('puc.product_uom', 'pu.id')
                    ->where('puc.is_active', '1');
            })
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.remarks', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.model_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('pt.type', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('uo.name', 'LIKE', '%' . $keyword . '%');
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

    public function validateCustomer(Request $request)
    {
        $data = json_decode($request->input('data'), true);
        $users_id = $data['user_id'];

        $result['is_valid'] = false;
        $result['message'] = '';
        DB::beginTransaction();
        try {

            $dir = 'berkas/document/customer_validation/';
            $dir .= date('Y') . '/' . date('m');
            $pathlamp = public_path() . '/' . $dir . '/';
            // Create the directory if it doesn't exist
            if (!File::isDirectory($pathlamp)) {
                File::makeDirectory($pathlamp, 0777, true, true);
            }

            $dbpathlampNpwp = null;
            $fileNpWpName = null;

            if ($request->hasFile('files_npwp')) {
                $files_npwp = $request->file('files_npwp');

                $fileNpWpName = 'files_npwp_' . time() . '.' . $files_npwp->getClientOriginalExtension();
                $files_npwp->move(public_path($dir), $fileNpWpName);

                $dbpathlampNpwp = '/' . $dir . '/';
            }

            $dbpathlampKtp = null;
            $fileKtpName = null;

            if ($request->hasFile('files_ktp')) {
                $files_ktp = $request->file('files_ktp');

                $fileKtpName = 'files_ktp_' . time() . '.' . $files_ktp->getClientOriginalExtension();
                $files_ktp->move(public_path($dir), $fileKtpName);

                $dbpathlampKtp = '/' . $dir . '/';
            }

            $detail = Customer::find($data['id']);
            $detail->npwp = $data['npwp'] ?? $detail->npwp;
            $detail->no_ktp = $data['no_ktp'] ?? $detail->no_ktp;
            $detail->validate_time = date('Y-m-d H:i:s');
            $detail->validate_by = $users_id;
            // $detail->foto_path = $dbpathlampOutlet . $fileOutletName;
            $detail->foto_ktp_path = $fileKtpName ? $dbpathlampKtp . $fileKtpName : $detail->foto_ktp_path;
            $detail->foto_npwp_path = $fileNpWpName ? $dbpathlampNpwp . $fileNpWpName : $detail->foto_npwp_path;
            $detail->save();
            DB::commit();
            $result['message'] = 'Success';
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $result['message'] = $th->getMessage();
        }

        return response()->json($result);
    }
}
