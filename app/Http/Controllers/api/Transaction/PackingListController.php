<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\Currency;
use App\Models\Transaction\DeliveryOrderDtl;
use App\Models\Transaction\DeliveryOrderHeader;
use App\Models\Transaction\PackingList;
use App\Models\Transaction\PackingListDo;
use App\Models\Transaction\PackingListDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PackingListController extends Controller
{
    public function getTableName()
    {
        return 'packing_list';
    }

    public function getData()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName().' as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.packing_list_no', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.packing_date', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.vehicle_no', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.driver_name', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.expedition_name', 'LIKE', '%'.$keyword.'%');
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

    public function getDataDO(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();
        $do_choose = isset($data['data_do_chooce']) ? $data['data_do_chooce'] : [];
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table('delivery_order_header as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'cc.nama_customer',
                'cc.code as customer_code',
                'c.code as currency_code',
                'soh.so_number',
                'soh.so_date',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('sales_order_headers as soh', 'soh.id', 'm.so_id')
            ->join('currency as c', 'c.id', 'soh.currency')
            ->whereNull('m.deleted')
            ->whereIn('m.status', ['CONFIRMED'])
            ->orderBy('m.id', 'asc');
        if(!empty($do_choose)){
            $datadb->whereNotIn('m.id', $do_choose);
        }
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('soh.so_number', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('soh.so_date', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.do_number', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.do_date', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.status', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('cc.nama_customer', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('cc.code', 'LIKE', '%'.$keyword.'%');
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

    public function getDataProductPoDetail(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();

        $exceptPoDetailId = [];
        if (! empty($data['itemsChoose'])) {
            $exceptPoDetailId = collect($data['itemsChoose'])->pluck('purchase_order_detail_id')->toArray();
        }
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table('purchase_order_detail as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'v.nama_vendor',
                'c.code as currency_code',
                'uom.name as unit_name',
                'p.name as product_name',
                'p.code as product_code',
                'po.code as po_code',
            ])
            ->join('purchase_order as po', 'po.id', 'm.purchase_order')
            ->join('users as u', 'u.id', 'po.created_by')
            ->join('vendor as v', 'v.id', 'po.vendor')
            ->join('currency as c', 'c.id', 'po.currency')
            ->join('unit as uom', 'uom.id', 'm.unit')
            ->join('product as p', 'p.id', 'm.product')
            ->whereNull('m.deleted')
            ->whereNull('po.deleted')
            ->whereNotIn('m.status', ['invoiced', 'paid', 'cancelled'])
            ->where('po.vendor', $data['vendor'])
            ->orderBy('m.id', 'desc');

        if (! empty($exceptPoDetailId)) {
            $datadb->whereNotIn('m.id', $exceptPoDetailId);
        }
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('po.code', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('po.po_date', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('po.status', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('v.nama_vendor', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.status', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('uom.name', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('p.name', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('p.code', 'LIKE', '%'.$keyword.'%');
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
        $userId = session('user_id');
        $result = ['is_valid' => false];

        // echo '<pre>';
        // print_r($data);die;


        DB::beginTransaction();
        try {

            $header = empty($data['id'])
                ? new PackingList()
                : PackingList::find($data['id']);

            if (empty($data['id'])) {
                $header->packing_list_no = generateNoPL(); // misal helper
                $header->created_by = $userId;
            }

            $header->packing_date = $data['packing_date'];
            $header->vehicle_no = $data['vehicle_no'];
            $header->driver_name = $data['driver_name'];
            $header->expedition_name = $data['expedition_name'];
            $header->remarks = $data['remarks'];
            $header->save();

            $hdrId = $header->id;

            // === DETAIL DO===
            $details = empty($data['details']) ?  [] : collect($data['details']);
            if(empty($details)){
                DB::rollBack();
                $result['is_valid'] = false;
                $result['message'] = 'Detail DO tidak boleh kosong';
                return response()->json($result);
            }

            foreach ($data['do_list'] as $key=>$value) {
                // Skip baris yang ditandai untuk dihapus
                if (!empty($value['remove']) && $value['remove'] == 1) {
                    if (!empty($value['id'])) {
                        $exist = PackingListDo::find($value['id']);
                        if ($exist) {
                            $exist->delete();

                            //DO kembali ke status confirm
                            $do = DeliveryOrderHeader::find($value['delivery_order_id']);
                            $do->status = 'CONFIRMED';
                            $do->save();

                            $details = PackingListDtl::where('packing_list_id', $hdrId)->where('delivery_order_id', $value['delivery_order_id'])->get();
                            foreach ($details as $key2 => $value2) {
                                $value2->delete();
                            }
                        }
                    }
                    continue;
                }

                $detail = empty($value['id'])
                    ? new PackingListDo()
                    : PackingListDo::find($value['id']);

                $detail->packing_list_id = $hdrId;
                $detail->delivery_order_id = $value['delivery_order_id'];
                $detail->save();

                $do = DeliveryOrderHeader::find($value['delivery_order_id']);
                $do->status = 'PACKED';
                $do->save();


                $details_do = $details->where('do_id', $value['delivery_order_id'])->toArray();
                if(empty($details_do)){
                    DB::rollBack();
                    $result['is_valid'] = false;
                    $result['message'] = 'Detail DO '.$value['do_number'].' tidak boleh kosong';
                    return response()->json($result);
                }

                foreach ($details_do as $key2 => $value2) {
                    $detailDo = new PackingListDtl();
                    $detailDo->packing_list_id = $hdrId;
                    $detailDo->delivery_order_id = $value['delivery_order_id'];
                    $detailDo->product_id = $value2['product_id'];
                    $detailDo->qty_do = $value2['qty_do'];
                    $detailDo->qty_packed = $value2['qty_packed'];
                    $detailDo->remark = $value2['remark'];
                    $detailDo->delivery_detail_id = $value2['id'];
                    $detailDo->save();
                }
            }


            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Sales Payment berhasil disimpan';
            $result['so_id'] = $hdrId;
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['is_valid'] = false;
            $result['message'] = $th->getMessage();
        }

        return response()->json($result);
    }

    public function confirmDelete(Request $request)
    {
        $data = $request->all();
        $id = $data['id'];
        DB::beginTransaction();

        try {
            $userId = session('user_id');

            // ====== HEADER ======
            $header = PackingList::find($id);

            if (! $header) {
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            // ====== UPDATE HEADER ======
            $header->deleted = now();
            $header->deleted_by = $userId;
            $header->save();

            $doDtl = PackingListDo::where('packing_list_id', $id)->get();
            foreach ($doDtl as $key => $value) {
                $do = DeliveryOrderHeader::find($value->delivery_order_id);
                $do->status = 'CONFIRMED';
                $do->save();
            }

            DB::commit();

            return response()->json([
                'is_valid' => true,
                'message' => 'Packing List berhasil dibatalkan'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'is_valid' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function confirmCancel(Request $request)
    {
        $data = $request->all();
        $id = $data['id'];
        DB::beginTransaction();

        try {
            $userId = session('user_id');

            // ====== HEADER ======
            $header = PackingListDo::find($id);

            if (! $header) {
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            // ====== UPDATE HEADER ======
            $header->status = 'CANCEL';
            $header->save();

            DB::commit();

            return response()->json([
                'is_valid' => true,
                'message' => 'Packing List berhasil dibatalkan'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'is_valid' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getDetailData($id)
    {
        DB::enableQueryLog();
        $datadb = DB::table($this->getTableName().' as m')
            ->select([
                'm.*',
            ])
            ->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();

        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();

        return view('web.packing_list.modal.confirmdelete', $data);
    }

    public function cancelPl(Request $request)
    {
        $data = $request->all();

        return view('web.packing_list.modal.confirmbatal', $data);
    }

    public function showModalDO(Request $request)
    {
        $data = $request->all();

        return view('web.packing_list.modal.datado', $data);
    }

    public function getDOConfirmed(Request $request){
        $data = $request->all();
        $do_id = isset($data['do_id']) ? $data['do_id'] : '';
        try {
            //code...
            $datadb = DeliveryOrderHeader::where('delivery_order_header.id', $do_id)
            ->select([
                'delivery_order_header.*',
                'c.code as customer_code',
                'c.nama_customer'
            ])
            ->join('customer as c', 'c.id', 'delivery_order_header.customer_id');

            $datadb->where('delivery_order_header.id', $do_id);
            $datadb = $datadb->get();
        } catch (\Throwable $th) {
            echo $th->getMessage();die;
        }

        $data['data'] = $datadb;

        return view('web.packing_list.datadooutstanding', $data);
    }

    public function getDODetailConfirmed(Request $request){
        $data = $request->all();
        $do_id = isset($data['do_id']) ? $data['do_id'] : '';
        try {
            //code...
            $datadb = DeliveryOrderDtl::where('delivery_order_detail.do_id', $do_id)
            ->select([
                'delivery_order_detail.*',
                'p.code as product_code',
                'p.name as product_name',
            ])
            ->join('product as p', 'p.id', 'delivery_order_detail.product_id')
            ->whereNull('delivery_order_detail.deleted');
            $datadb = $datadb->get();
        } catch (\Throwable $th) {
            echo $th->getMessage();die;
        }

        $data['data'] = $datadb;

        return view('web.packing_list.datadetaildo', $data);
    }

    public function getDataPackingList(Request $request){
        $data = $request->all();
        date_default_timezone_set('Asia/Jakarta');

        $packing_date = date('Y-m-d');
        $result['is_valid'] = true;

        $datadb = DB::table($this->getTableName().' as m')
            ->select([
                'pld.id',
                'm.packing_list_no',
                'u.name as created_by_name',
                'doh.do_number',
                'doh.do_date',
                'c.code as customer_code',
                'c.id as customer_id',
                'c.nama_customer',
                'doh.total_item',
                'pld.confirm_date'
            ])
            ->join('packing_list_do as pld', 'pld.packing_list_id', 'm.id')
            ->join('delivery_order_header as doh', 'doh.id', 'pld.delivery_order_id')
            ->join('customer as c', 'c.id', 'doh.customer_id')
            ->join('users as u', 'u.id', 'm.created_by')
            // ->where('m.packing_date', $packing_date)
            ->whereNull('m.deleted')
            ->where(function($q){
                return $q->whereIn('m.status', ['PARTIAL', 'NOT DELIVERED'])->orWhereNull('m.status');
            })
            ->where(function($q){
                return $q->whereNull('pld.status')->orWhere('pld.status', 'NOT DELIVERED');
            })
            ->orderBy('c.nama_customer')
            ->orderBy('doh.id', 'asc');
        $datadb = $datadb->get()->toArray();

        $result['data'] = $datadb;
        $result['date'] = $packing_date;
        $result['message'] = 'Data berhasil diambil';

        return response()->json($result);
    }

    public function confirmDeliver(Request $request){
        $data = json_decode($request->input('data'), true);
        $users_id = $data['user_id'];
        // echo '<pre>';
        // print_r($data);die;
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {

            $roles = PackingListDo::where('id', $data['id'])->first();
            if(empty($roles)){
                $result['message'] = 'Data tidak ditemukan';
                return response()->json($result);
            }

            $periode = Carbon::parse($data['confirm_date'])->setTimezone('Asia/Jakarta');
            $confirm_date = $periode->format('Y-m-d H:i:s');
            $roles->confirm_date = $confirm_date;
            $roles->latitude = $data['latitude'];
            $roles->longitude = $data['longitude'];
            $roles->platform = 'mobile';
            $roles->remarks = $data['remarks'];
            $roles->status = $data['state'] == 'delivered' ? 'CONFIRMED' : 'NOT DELIVERED';
            $roles->confirm_by = $users_id;
            $roles->save();

            $allDetailDo = PackingListDo::where('packing_list_id', $roles->packing_list_id)->get()->toArray();
            $delivered = 0;
            foreach ($allDetailDo as $key => $value) {
                if($value['status'] == 'CONFIRMED'){
                    $delivered++;
                }
            }
            if($delivered == 0){
                $plHeader = PackingList::find($roles->packing_list_id);
                $plHeader->status = 'NOT DELIVERED';
                $plHeader->save();
            }

            if($delivered == count($allDetailDo)){
                $plHeader = PackingList::find($roles->packing_list_id);
                $plHeader->status = 'CONFIRMED';
                $plHeader->save();
            }else{
                $plHeader = PackingList::find($roles->packing_list_id);
                $plHeader->status = 'PARTIAL';
                $plHeader->save();
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
}
