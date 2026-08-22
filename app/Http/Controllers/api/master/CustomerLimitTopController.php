<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\Customer;
use App\Models\Master\CustomerLimitTop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerLimitTopController extends Controller
{
    public function getTableName()
    {
        return "customer_limit_top";
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
                'c.code as customer_code',
                'c.nama_customer as customer_name',
                'c.credit_limit as customer_credit_limit',
                'top_new.remarks as new_top_name',
                'top_cur.remarks as current_top_name',
            ])
            ->join('customer as c', 'c.id', 'm.customer')
            ->leftJoin('term_of_payment as top_new', 'top_new.id', 'm.new_payment_terms')
            ->leftJoin('term_of_payment as top_cur', 'top_cur.id', 'm.current_payment_terms')
            ->whereNull('m.deleted');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.type_pengajuan', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('c.code', 'LIKE', '%' . $keyword . '%');
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
        return json_encode($data);
    }

    public function getDataAcc()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'c.code as customer_code',
                'c.nama_customer as customer_name',
                'top_new.remarks as new_top_name',
                'top_cur.remarks as current_top_name',
            ])
            ->join('customer as c', 'c.id', 'm.customer')
            ->leftJoin('term_of_payment as top_new', 'top_new.id', 'm.new_payment_terms')
            ->leftJoin('term_of_payment as top_cur', 'top_cur.id', 'm.current_payment_terms')
            ->where(function ($q) {
                return $q->whereNull('m.spv_sales_date')
                    ->orWhereNull('m.admin_sales_date')
                    ->orWhereNull('m.om_date');
            })
            ->whereNull('m.deleted');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.type_pengajuan', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('c.code', 'LIKE', '%' . $keyword . '%');
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
        return json_encode($data);
    }

    public function getDataAccHistory()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'c.code as customer_code',
                'c.nama_customer as customer_name',
                'top_new.remarks as new_top_name',
                'top_cur.remarks as current_top_name',
            ])
            ->join('customer as c', 'c.id', 'm.customer')
            ->leftJoin('term_of_payment as top_new', 'top_new.id', 'm.new_payment_terms')
            ->leftJoin('term_of_payment as top_cur', 'top_cur.id', 'm.current_payment_terms')
            ->whereNull('m.deleted')
            ->whereIn('m.status', ['APPROVED', 'REJECTED']);

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.type_pengajuan', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('c.code', 'LIKE', '%' . $keyword . '%');
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
        return json_encode($data);
    }

    public function submit(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $customer = Customer::find($data['customer']);

            $roles = $data['id'] == '' ? new CustomerLimitTop() : CustomerLimitTop::find($data['id']);
            if ($data['id'] == '') {
                $roles->code = generateCodePengajuanLimitTop();
                $roles->current_credit_limit = $customer->credit_limit;
                $roles->current_payment_terms = $customer->payment_terms;
                $roles->submitted_by = session('user_id');
                $roles->submitted_date = date('Y-m-d H:i:s');
                $roles->status = 'PENDING';
            } else {
                if ($roles->status == 'REJECTED' || $roles->om_date != null) {
                    $result['message'] = 'Pengajuan Sudah Diproses';
                    return response()->json($result);
                }
            }

            $roles->customer = $data['customer'];
            $roles->type_pengajuan = $data['type_pengajuan'];
            if (in_array($data['type_pengajuan'], ['CREDIT_LIMIT', 'CREDIT_LIMIT_DAN_TOP'])) {
                $roles->new_credit_limit = $data['new_credit_limit'];
            } else {
                $roles->new_credit_limit = null;
            }
            if (in_array($data['type_pengajuan'], ['TERM_OF_PAYMENT', 'CREDIT_LIMIT_DAN_TOP'])) {
                $roles->new_payment_terms = $data['new_payment_terms'];
            } else {
                $roles->new_payment_terms = null;
            }
            $roles->reason = isset($data['reason']) ? $data['reason'] : '';
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

    public function approve(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $akses = session('akses');
            $roles = CustomerLimitTop::find($data['id']);

            if ($roles->status == 'APPROVED' || $roles->status == 'REJECTED') {
                $result['message'] = 'Pengajuan Sudah Diproses';
                return response()->json($result);
            }

            $roles->remarks = isset($data['remarks']) ? $data['remarks'] : '';

            if ($data['status'] == 'rej') {
                $roles->status = 'REJECTED';
                $roles->approved_by = session('user_id');
                $roles->approved_date = date('Y-m-d H:i:s');
            } else {
                if ($akses == 'supervisor sales') {
                    $roles->spv_sales_by = session('user_id');
                    $roles->spv_sales_date = date('Y-m-d H:i:s');
                } elseif ($akses == 'admin supervisor') {
                    $roles->admin_sales_by = session('user_id');
                    $roles->admin_sales_date = date('Y-m-d H:i:s');
                } elseif ($akses == 'operational manager' || $akses == 'superadmin') {
                    $roles->om_by = session('user_id');
                    $roles->om_date = date('Y-m-d H:i:s');
                    $roles->status = 'APPROVED';
                    $roles->approved_by = session('user_id');
                    $roles->approved_date = date('Y-m-d H:i:s');

                    /* apply ke master customer */
                    $customer = Customer::find($roles->customer);
                    if ($roles->new_credit_limit !== null) {
                        $customer->credit_limit = $roles->new_credit_limit;
                    }
                    if ($roles->new_payment_terms !== null) {
                        $customer->payment_terms = $roles->new_payment_terms;
                    }
                    $customer->save();
                }
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

    public function confirmDelete(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $menu = CustomerLimitTop::find($data['id']);
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
                'c.code as customer_code',
                'c.nama_customer as customer_name',
                'top_new.remarks as new_top_name',
                'top_cur.remarks as current_top_name',
            ])
            ->join('customer as c', 'c.id', 'm.customer')
            ->leftJoin('term_of_payment as top_new', 'top_new.id', 'm.new_payment_terms')
            ->leftJoin('term_of_payment as top_cur', 'top_cur.id', 'm.current_payment_terms')
            ->where('m.id', $id);
        $data = $datadb->first();
        return response()->json($data);
    }

    public function getDetailCustomer(Request $request)
    {
        $data = $request->all();
        $datadb = DB::table('customer as c')
            ->select([
                'c.*',
                'top.remarks as current_top_name',
            ])
            ->leftJoin('term_of_payment as top', 'top.id', 'c.payment_terms')
            ->where('c.id', $data['customer'])
            ->first();

        $result['is_valid'] = true;
        $result['data'] = $datadb;
        return response()->json($result);
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        return view('web.customer_limit_top.modal.confirmdelete', $data);
    }
}
