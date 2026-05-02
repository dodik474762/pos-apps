<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\Coa;
use App\Models\Master\Currency;
use App\Models\Transaction\DeliveryOrderDtl;
use App\Models\Transaction\DeliveryOrderHeader;
use App\Models\Transaction\PackingList;
use App\Models\Transaction\PackingListDo;
use App\Models\Transaction\PackingListDtl;
use App\Models\Transaction\PackingListReturn;
use App\Models\Transaction\PackingListReturnDtl;
use App\Models\Transaction\SalesReturnDtl;
use App\Models\Transaction\SalesReturnHdr;
use App\Models\Master\AccountMapping;
use App\Models\Master\ProductUom;
use App\Models\Transaction\SalesInvoiceHeader;
use App\Models\Transaction\SalesInvoiceDtl;
use App\Models\Transaction\SalesOrderDetail;
use App\Models\Transaction\SalesOrderHeader;
use App\Models\Transaction\SalesPaymentDtl;
use App\Models\Transaction\SalesPaymentHeader;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

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
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->whereNull('m.deleted')
            ->where('m.type_transaction', 'PL')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.packing_list_no', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.packing_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.vehicle_no', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.driver_name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.expedition_name', 'LIKE', '%' . $keyword . '%');
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

    public function getDataSr()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->whereNull('m.deleted')
            ->where('m.type_transaction', 'SR')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.packing_list_no', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.packing_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.vehicle_no', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.driver_name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.expedition_name', 'LIKE', '%' . $keyword . '%');
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
                'cc.address',
                'cc.code as customer_code',
                'c.code as currency_code',
                'soh.so_number',
                'soh.so_date',
                'sih.invoice_number',
                'sih.invoice_date',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('sales_order_headers as soh', 'soh.id', 'm.so_id')
            ->join('currency as c', 'c.id', 'soh.currency')
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.do_id', 'm.id')
                    ->orOn('sih.sales_order', 'soh.id');
            })
            ->whereNull('m.deleted')
            ->where('soh.total_amount', '>', 0)
            ->whereIn('m.status', ['CONFIRMED', 'DRAFT'])
            ->orderBy('m.id', 'asc');
        if (!empty($do_choose)) {
            $datadb->whereNotIn('m.id', $do_choose);
        }
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('soh.so_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('soh.so_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.do_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.do_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.nama_customer', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.code', 'LIKE', '%' . $keyword . '%');
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

    public function getDataSalesRetur(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();
        $do_choose = isset($data['data_do_chooce']) ? $data['data_do_chooce'] : [];
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $datadb = DB::table('sales_return as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'cc.nama_customer',
                'cc.code as customer_code',
                'i.invoice_number',
            ])
            ->where('m.status', 'POSTED')
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->leftJoin('sales_invoice_header as i', 'i.id', 'm.invoice_id')
            ->whereNull('m.deleted');
        if (!empty($do_choose)) {
            $datadb->whereNotIn('m.id', $do_choose);
        }

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.return_type', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.return_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.return_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('i.invoice_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.nama_customer', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.code', 'LIKE', '%' . $keyword . '%');
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
        if (!empty($data['itemsChoose'])) {
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

        if (!empty($exceptPoDetailId)) {
            $datadb->whereNotIn('m.id', $exceptPoDetailId);
        }
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('po.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('po.po_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('po.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('v.nama_vendor', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('uom.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('p.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('p.code', 'LIKE', '%' . $keyword . '%');
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

            $users = User::where('id', $data['driver'])->first();

            $header->packing_date = $data['packing_date'];
            $header->vehicle_no = $data['vehicle_no'];
            $header->driver = $users->id;
            $header->driver_name = $users->name;
            $header->expedition_name = $data['expedition_name'];
            $header->remarks = $data['remarks'];
            $header->type_transaction = 'PL';
            $header->save();

            $hdrId = $header->id;

            // === DETAIL DO===
            $details = empty($data['details']) ? [] : collect($data['details']);
            if (empty($details)) {
                DB::rollBack();
                $result['is_valid'] = false;
                $result['message'] = 'Detail DO tidak boleh kosong';
                return response()->json($result);
            }

            foreach ($data['do_list'] as $key => $value) {
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

                $so = SalesInvoiceHeader::where('do_id', $do->id)->orWhere('sales_order', $do->so_id)->first();
                $so->status = 'PACKED';
                $so->save();


                $details_do = $details->where('do_id', $value['delivery_order_id'])->toArray();
                if (empty($details_do)) {
                    DB::rollBack();
                    $result['is_valid'] = false;
                    $result['message'] = 'Detail DO ' . $value['do_number'] . ' tidak boleh kosong';
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
            $result['message'] = 'Packing List berhasil disimpan';
            $result['so_id'] = $hdrId;
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['is_valid'] = false;
            $result['message'] = $th->getMessage();
        }

        return response()->json($result);
    }

    public function submitSr(Request $request)
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

            $users = User::where('id', $data['driver'])->first();

            $header->packing_date = $data['packing_date'];
            $header->vehicle_no = $data['vehicle_no'];
            $header->driver = $users->id;
            $header->driver_name = $users->name;
            $header->expedition_name = $data['expedition_name'];
            $header->remarks = $data['remarks'];
            $header->type_transaction = 'SR';
            $header->save();

            $hdrId = $header->id;

            // === DETAIL DO===
            $details = empty($data['details']) ? [] : collect($data['details']);
            if (empty($details)) {
                DB::rollBack();
                $result['is_valid'] = false;
                $result['message'] = 'Detail SR tidak boleh kosong';
                return response()->json($result);
            }

            foreach ($data['do_list'] as $key => $value) {
                // Skip baris yang ditandai untuk dihapus
                if (!empty($value['remove']) && $value['remove'] == 1) {
                    if (!empty($value['id'])) {
                        $exist = PackingListReturn::find($value['id']);
                        if ($exist) {
                            $exist->delete();

                            //DO kembali ke status confirm
                            $do = SalesReturnHdr::find($value['delivery_order_id']);
                            $do->status = 'POSTED';
                            $do->save();

                            $details = PackingListReturnDtl::where('packing_list_id', $hdrId)->where('sales_return_id', $value['delivery_order_id'])->get();
                            foreach ($details as $key2 => $value2) {
                                $value2->delete();
                            }
                        }
                    }
                    continue;
                }

                $detail = empty($value['id'])
                    ? new PackingListReturn()
                    : PackingListReturn::find($value['id']);

                $detail->packing_list_id = $hdrId;
                $detail->sales_return_id = $value['delivery_order_id'];
                $detail->save();

                $do = SalesReturnHdr::find($value['delivery_order_id']);
                $do->status = 'PACKED';
                $do->save();


                $details_do = $details->where('do_id', $value['delivery_order_id'])->toArray();
                if (empty($details_do)) {
                    DB::rollBack();
                    $result['is_valid'] = false;
                    $result['message'] = 'Detail SR ' . $value['do_number'] . ' tidak boleh kosong';
                    return response()->json($result);
                }

                foreach ($details_do as $key2 => $value2) {
                    $detailDo = new PackingListReturnDtl();
                    $detailDo->packing_list_id = $hdrId;
                    $detailDo->sales_return_id = $value['delivery_order_id'];
                    $detailDo->product_id = $value2['product_id'];
                    $detailDo->qty_do = $value2['qty_do'];
                    $detailDo->qty_packed = $value2['qty_packed'];
                    $detailDo->remark = $value2['remark'];
                    $detailDo->sales_return_detail_id = $value2['id'];
                    $detailDo->save();
                }
            }


            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Packing List berhasil disimpan';
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

            if (!$header) {
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

            if (!$header) {
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
        $datadb = DB::table($this->getTableName() . ' as m')
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

    public function showModalSR(Request $request)
    {
        $data = $request->all();
        // echo '<pre>';
        // print_r($data);die;

        return view('web.packing_list.modal.datasr', $data);
    }

    public function getSRConfirmed(Request $request)
    {
        $data = $request->all();
        $do_id = isset($data['do_id']) ? $data['do_id'] : '';
        try {
            //code...
            $datadb = SalesReturnHdr::where('sales_return.id', $do_id)
                ->select([
                    'sales_return.*',
                    'c.code as customer_code',
                    'c.nama_customer',
                    'sales_return.return_number as do_number',
                    'sales_return.return_date as do_date'
                ])
                ->join('customer as c', 'c.id', 'sales_return.customer_id');

            $datadb->where('sales_return.id', $do_id);
            $datadb = $datadb->get();
        } catch (\Throwable $th) {
            echo $th->getMessage();
            die;
        }

        $data['data'] = $datadb;

        return view('web.packing_list.datadooutstanding', $data);
    }

    public function getDOConfirmed(Request $request)
    {
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
            echo $th->getMessage();
            die;
        }

        $data['data'] = $datadb;

        return view('web.packing_list.datadooutstanding', $data);
    }

    public function getDODetailConfirmed(Request $request)
    {
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
            echo $th->getMessage();
            die;
        }

        $data['data'] = $datadb;

        return view('web.packing_list.datadetaildo', $data);
    }

    public function getSRDetailConfirmed(Request $request)
    {
        $data = $request->all();
        $do_id = isset($data['do_id']) ? $data['do_id'] : '';
        try {
            //code...
            $datadb = SalesReturnDtl::where('sales_return_detail.return_id', $do_id)
                ->select([
                    'sales_return_detail.*',
                    'p.code as product_code',
                    'p.name as product_name',
                    'sales_return_detail.return_id as do_id',
                    'sales_return_detail.qty_return as qty'
                ])
                ->join('product as p', 'p.id', 'sales_return_detail.product_id')
                ->whereNull('sales_return_detail.deleted');
            $datadb = $datadb->get();
        } catch (\Throwable $th) {
            echo $th->getMessage();
            die;
        }

        $data['data'] = $datadb;

        return view('web.packing_list.datadetaildo', $data);
    }

    public function getDataPackingList(Request $request)
    {
        $data = $request->all();
        date_default_timezone_set('Asia/Jakarta');

        $packing_date = date('Y-m-d');
        $result['is_valid'] = true;

        $datadb = DB::table($this->getTableName() . ' as m')
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
                'pld.confirm_date',
                'c.address',
                'c.latitude',
                'c.longitude',
                'top.code as top_code',
                'top.nilai as top_nilai'
            ])
            ->join('packing_list_do as pld', 'pld.packing_list_id', 'm.id')
            ->join('delivery_order_header as doh', 'doh.id', 'pld.delivery_order_id')
            ->join('customer as c', 'c.id', 'doh.customer_id')
            ->join('term_of_payment as top', 'c.payment_terms', '=', 'top.id')
            ->join('users as u', 'u.id', 'm.created_by')
            // ->where('m.packing_date', $packing_date)
            // ->where('m.driver', $data['users'])
            ->whereNull('m.deleted')
            ->where(function ($q) {
                return $q->whereIn('m.status', ['PARTIAL', 'NOT DELIVERED'])->orWhereNull('m.status');
            })
            ->where(function ($q) {
                return $q->whereNull('pld.status')->orWhere('pld.status', 'NOT DELIVERED');
            })
            ->orderBy('c.nama_customer')
            ->orderBy('doh.id', 'asc');
        $users = User::where('id', $data['users'])->first();
        if ($users->user_group == '5') { //driver
            $datadb->where('m.driver', $data['users']);
        }
        $datadb = $datadb->get()->toArray();

        $result['data'] = $datadb;
        $result['date'] = $packing_date;
        $result['message'] = 'Data berhasil diambil';

        return response()->json($result);
    }

    public function getDataPackingListPickup(Request $request)
    {
        $data = $request->all();
        date_default_timezone_set('Asia/Jakarta');

        $packing_date = date('Y-m-d');
        $result['is_valid'] = true;

        try {
            $datadb = DB::table($this->getTableName() . ' as m')
                ->select([
                    'psr.id',
                    'm.packing_list_no',
                    'u.name as created_by_name',
                    'sr.return_number as do_number',
                    'sr.return_date as do_date',
                    'c.code as customer_code',
                    'c.id as customer_id',
                    'c.nama_customer',
                    DB::raw('1 as total_item'),
                    'psr.confirm_date'
                ])
                ->join('packing_list_sales_return as psr', 'psr.packing_list_id', 'm.id')
                ->join('sales_return as sr', 'sr.id', 'psr.sales_return_id')
                ->join('customer as c', 'c.id', 'sr.customer_id')
                ->join('users as u', 'u.id', 'm.created_by')
                // ->where('m.packing_date', $packing_date)
                ->whereNull('m.deleted')
                ->where(function ($q) {
                    return $q->whereIn('m.status', ['PARTIAL', 'NOT DELIVERED'])->orWhereNull('m.status');
                })
                ->where(function ($q) {
                    return $q->whereNull('psr.status')->orWhere('psr.status', 'NOT DELIVERED');
                })
                ->orderBy('c.nama_customer')
                ->orderBy('sr.id', 'asc');
            $datadb = $datadb->get()->toArray();
            $result['message'] = 'Data berhasil diambil';
        } catch (\Throwable $th) {
            $result['is_valid'] = false;
            //throw $th;
            $result['message'] = $th->getMessage();
        }

        $result['data'] = $datadb;
        $result['date'] = $packing_date;

        return response()->json($result);
    }

    public function confirmDeliver(Request $request)
    {
        $data = json_decode($request->input('data'), true);
        $files_outlet = $request->file('files_outlet');
        $users_id = $data['user_id'];
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {

            $fileOutletName = '';
            $dbpathlampOutlet = '';

            if ($files_outlet) {
                $dir = 'berkas/document/delivery/';
                $dir .= date('Y') . '/' . date('m');
                $pathlamp = public_path() . '/' . $dir . '/';
                if (!File::isDirectory($pathlamp)) {
                    File::makeDirectory($pathlamp, 0777, true, true);
                }
                $fileOutletName = $users_id . 'confirm_delivery_' . time() . '.jpg';
                $files_outlet->move(public_path($dir), $fileOutletName);
                $dbpathlampOutlet = '/' . $dir . '/';
            }

            $roles = PackingListDo::where('id', $data['id'])->first();
            if (empty($roles)) {
                $result['message'] = 'Data tidak ditemukan';
                return response()->json($result);
            }

            $periode = Carbon::parse($data['confirm_date'])->setTimezone('Asia/Jakarta');
            $confirm_date = $periode->format('Y-m-d H:i:s');
            $roles->confirm_date = $confirm_date;
            $roles->latitude     = $data['latitude'];
            $roles->longitude    = $data['longitude'];
            $roles->platform     = 'mobile';
            $roles->remarks      = $data['remarks'];
            $roles->status       = $data['state'] == 'delivered' ? 'CONFIRMED' : 'NOT DELIVERED';
            $roles->confirm_by   = $users_id;
            $roles->photo_path   = $dbpathlampOutlet . $fileOutletName;
            $roles->save();

            $allDetailDo = PackingListDo::where('packing_list_id', $roles->packing_list_id)->get()->toArray();
            $delivered = 0;
            foreach ($allDetailDo as $value) {
                if ($value['status'] == 'CONFIRMED') $delivered++;
            }

            $plHeader = PackingList::find($roles->packing_list_id);
            if ($delivered == 0) {
                $plHeader->status = 'NOT DELIVERED';
            } elseif ($delivered == count($allDetailDo)) {
                $plHeader->status = 'CONFIRMED';
            } else {
                $plHeader->status = 'PARTIAL';
            }
            $plHeader->save();

            // ====== CORET FAKTUR ======
            if ($data['state'] == 'delivered') {

                $invoice     = null;
                $invoiceId   = null;
                $idDtlCancel = [];
                $glCancelled = false;
                $currency    = null;
                $currencyId  = null;
                $customer_id = null;
                $warehouseId = null;

                if ($data['customer_id'] != '') {
                    if (trim($data['invoice_number']) != '') {

                        $invoice = SalesInvoiceHeader::where('invoice_number', trim($data['invoice_number']))->first();
                        if (empty($invoice)) {
                            DB::rollBack();
                            return response()->json([
                                'is_valid' => false,
                                'message'  => 'Invoice ' . $data['invoice_number'] . ' Tidak Ditemukan',
                            ]);
                        }

                        $invoiceId  = $invoice->id;
                        $currency   = Currency::where('code', 'IDR')->first();
                        $currencyId = $currency->id;
                        $reference  = trim($data['invoice_number']);
                        $warehouseId = $invoice->warehouse_id;

                        $parts = array_map('trim', explode('/', $data['customer_id']));
                        list($customer_id, $customer_code, $customer_name, $outstanding_amount, $invoice_number) = $parts;

                        // ====== CANCELLED ITEMS ======
                        if (!empty($data['cancelled_items'])) {

                            $piutangUsaha   = AccountMapping::where('module', 'SALES_VOID')->where('account_type', 'piutang usaha')->with('account')->first();
                            $penjualanBrg   = AccountMapping::where('module', 'SALES_VOID')->where('account_type', 'penjualan barang')->with('account')->first();
                            $ppnKeluaranAcc = AccountMapping::where('module', 'SALES_VOID')->where('account_type', 'ppn keluaran')->with('account')->first();
                            $discAcc        = AccountMapping::where('module', 'SALES_VOID')->where('account_type', 'diskon penjualan')->with('account')->first();

                            if (!$piutangUsaha || !$ppnKeluaranAcc || !$discAcc || !$penjualanBrg) {
                                DB::rollBack();
                                return response()->json(['is_valid' => false, 'message' => 'Konfigurasi akun untuk Sales Void belum lengkap.']);
                            }

                            $totalAmount = 0;
                            $disc_total  = 0;
                            $net_total   = 0;
                            $tax_total   = 0;

                            // ✅ Siapkan item untuk auto return cancelled
                            $cancelReturnItems = [];

                            foreach ($data['cancelled_items'] as $value) {
                                $invUpdate = SalesInvoiceDtl::find($value['id']);
                                if (empty($invUpdate)) continue;

                                $invUpdate->flag_cancel     = 1;
                                $invUpdate->packing_list_id = $roles->packing_list_id;
                                $invUpdate->save();

                                $disc_total  += $invUpdate->discount;
                                $tax_total   += $invUpdate->tax_amount;
                                $totalAmount += ($invUpdate->price * $invUpdate->qty);
                                $net_total   += (($invUpdate->price * $invUpdate->qty) - $invUpdate->discount + $invUpdate->tax_amount);

                                $so_detail = SalesOrderDetail::find($invUpdate->so_detail_id);
                                if ($so_detail) {
                                    $qtyBaseUnit      = getSmallestUnit($invUpdate->product_id, $so_detail->unit, $invUpdate->qty);
                                    $productUomLevel1 = ProductUom::where('product', $invUpdate->product_id)->where('level', '1')->first();
                                    if ($productUomLevel1) {
                                        stockUpdate($invoiceId, $invoice->warehouse_id, $invUpdate->product_id, $productUomLevel1->unit_tujuan, $qtyBaseUnit['qty_in_base_unit'], $value, 'add', 'sales_void');
                                    }
                                }

                                $idDtlCancel[] = $value['id'];

                                // ✅ Kumpulkan untuk auto return
                                $cancelReturnItems[] = [
                                    'product' => $invUpdate->product_id,
                                    'invoice_detail_id' => $invUpdate->id,
                                    'qty_return'        => (float)$invUpdate->qty,
                                    'warehouse_id'      => $warehouseId,
                                ];
                            }

                            // Recalculate invoice setelah cancel
                            $dataInvoiceDtl    = SalesInvoiceDtl::where('invoice_id', $invoiceId)->whereNotIn('id', $idDtlCancel)->get();
                            $totalAmountUpdate = 0;
                            $disc_total_update = 0;
                            $net_total_update  = 0;
                            $tax_total_update  = 0;

                            foreach ($dataInvoiceDtl as $v) {
                                $disc_total_update  += $v->discount;
                                $tax_total_update   += $v->tax_amount;
                                $totalAmountUpdate  += ($v->price * $v->qty);
                                $net_total_update   += (($v->price * $v->qty) - $v->discount + $v->tax_amount);
                            }

                            $invoiceUpdate                 = SalesInvoiceHeader::find($invoiceId);
                            $invoiceUpdate->subtotal        = $totalAmountUpdate - $disc_total_update;
                            $invoiceUpdate->discount_amount = $disc_total_update;
                            $invoiceUpdate->tax_amount      = $tax_total_update;
                            $invoiceUpdate->total_amount    = $net_total_update;
                            $invoiceUpdate->save();

                            cancelAllGL($reference);
                            $glCancelled = true;

                            postingGL($reference, $piutangUsaha->account_id, $piutangUsaha->account->account_name, $piutangUsaha->cd, $net_total_update, $currencyId);
                            postingGL($reference, $discAcc->account_id, $discAcc->account->account_name, $discAcc->cd, $disc_total_update, $currencyId);
                            postingGL($reference, $penjualanBrg->account_id, $penjualanBrg->account->account_name, $penjualanBrg->cd, $totalAmountUpdate, $currencyId);

                            // ✅ Auto return untuk cancelled items
                            if (!empty($cancelReturnItems)) {
                                createAutoReturn($invoiceId, $cancelReturnItems, 'REFUND', $users_id, $customer_id);
                            }
                        }

                        // ====== EDITED ITEMS ======
                        if (!empty($data['edited_items'])) {

                            // ✅ Siapkan item untuk auto return selisih qty
                            $editReturnItems = [];

                            foreach ($data['edited_items'] as $editedItem) {
                                $invDtl = SalesInvoiceDtl::find($editedItem['id']);
                                if (empty($invDtl)) continue;

                                $newQty      = (float)$editedItem['qty'];
                                $originalQty = (float)$editedItem['original_qty'];

                                // ✅ Simpan original dari DB hanya sekali
                                if (empty($invDtl->original_qty)) {
                                    $invDtl->original_qty      = $invDtl->qty;
                                    $invDtl->original_price    = $invDtl->price;
                                    $invDtl->original_subtotal = $invDtl->subtotal;
                                }

                                $dbOriginalQty = (float)$invDtl->original_qty;
                                if (!empty($invDtl->original_qty)) {
                                    $dbOriginalQty = (float)$invDtl->original_qty - (float)$invDtl->return_qty;
                                }

                                $invDtl->qty             = $newQty;
                                $invDtl->flag_correction = 1;
                                $invDtl->packing_list_id = $roles->packing_list_id;

                                $grossAmount = $invDtl->price * $newQty;
                                $discAmount  = !empty($invDtl->discount_per_unit)
                                    ? ($invDtl->discount_per_unit * $newQty)
                                    : (($dbOriginalQty > 0) ? round($invDtl->discount / $dbOriginalQty * $newQty) : 0);
                                $subtotal    = $grossAmount - $discAmount;

                                $invDtl->discount = $discAmount;
                                $invDtl->subtotal = $subtotal;
                                $invDtl->save();

                                // ✅ Hitung selisih untuk auto return
                                $selisih = $dbOriginalQty - $newQty;
                                if ($selisih > 0) {
                                    $editReturnItems[] = [
                                        'product' => $invDtl->product_id,
                                        'invoice_detail_id' => $invDtl->id,
                                        'qty_return'        => $selisih,
                                        'warehouse_id'      => $warehouseId,
                                    ];
                                }
                            }

                            // Recalculate invoice header
                            $allActiveDtl = SalesInvoiceDtl::where('invoice_id', $invoiceId)
                                ->whereNotIn('id', $idDtlCancel)
                                ->where(function ($q) {
                                    $q->where('flag_cancel', 0)->orWhereNull('flag_cancel');
                                })
                                ->get();

                            $totalAmountRecalc = 0;
                            $discTotalRecalc   = 0;
                            $taxTotalRecalc    = 0;
                            $netTotalRecalc    = 0;

                            foreach ($allActiveDtl as $dtl) {
                                $totalAmountRecalc += $dtl->price * $dtl->qty;
                                $discTotalRecalc   += $dtl->discount;
                                $taxTotalRecalc    += $dtl->tax_amount;
                                $netTotalRecalc    += $dtl->subtotal;
                            }

                            $invoiceRecalc = SalesInvoiceHeader::find($invoiceId);
                            if ($invoiceRecalc) {
                                $invoiceRecalc->subtotal        = $totalAmountRecalc - $discTotalRecalc;
                                $invoiceRecalc->discount_amount = $discTotalRecalc;
                                $invoiceRecalc->tax_amount      = $taxTotalRecalc;
                                $invoiceRecalc->total_amount    = $netTotalRecalc;
                                $invoiceRecalc->save();
                            }

                            // Reposting GL
                            $piutangUsahaEdit = AccountMapping::where('module', 'SALES_VOID')->where('account_type', 'piutang usaha')->with('account')->first();
                            $penjualanBrgEdit = AccountMapping::where('module', 'SALES_VOID')->where('account_type', 'penjualan barang')->with('account')->first();
                            $discEdit         = AccountMapping::where('module', 'SALES_VOID')->where('account_type', 'diskon penjualan')->with('account')->first();

                            if ($piutangUsahaEdit && $penjualanBrgEdit && $discEdit) {
                                if (!$glCancelled) {
                                    cancelAllGL($reference);
                                    $glCancelled = true;
                                }
                                postingGL($reference, $piutangUsahaEdit->account_id, $piutangUsahaEdit->account->account_name, $piutangUsahaEdit->cd, $netTotalRecalc, $currencyId);
                                postingGL($reference, $discEdit->account_id, $discEdit->account->account_name, $discEdit->cd, $discTotalRecalc, $currencyId);
                                postingGL($reference, $penjualanBrgEdit->account_id, $penjualanBrgEdit->account->account_name, $penjualanBrgEdit->cd, $totalAmountRecalc, $currencyId);
                            }

                            // ✅ Auto return untuk selisih qty edited items
                            if (!empty($editReturnItems)) {
                                createAutoReturn($invoiceId, $editReturnItems, 'REFUND', $users_id, $customer_id);
                            }
                        }
                    }
                }

                // ====== PAYMENT ======
                if ($data['customer_id'] != '' && $data['total_amount'] != '') {
                    if ((float)$data['total_amount'] > 0) {
                        $payment_date = $periode->format('Y-m-d');
                        $parts = array_map('trim', explode('/', $data['customer_id']));
                        list($customer_id, $customer_code, $customer_name, $outstanding_amount, $invoice_number) = $parts;

                        $piutangAcc   = AccountMapping::where('module', 'SALES_PAYMENT')->where('account_type', 'piutang usaha')->with('account')->first();
                        $discBayarAcc = AccountMapping::where('module', 'SALES_PAYMENT')->where('account_type', 'diskon bayar')->with('account')->first();

                        if (!$piutangAcc || !$discBayarAcc) {
                            DB::rollBack();
                            return response()->json(['is_valid' => false, 'message' => 'Konfigurasi akun untuk Sales Payment belum lengkap.']);
                        }

                        $data['account_id'] = 3;
                        $kasAccount = Coa::find($data['account_id']);

                        $paymentHeader                 = new SalesPaymentHeader();
                        $paymentHeader->payment_code   = generateNoSP();
                        $paymentHeader->created_by     = $users_id;
                        $paymentHeader->status         = 'PENDING';
                        $paymentHeader->payment_date   = $payment_date;
                        $paymentHeader->customer_id    = $customer_id;
                        $paymentHeader->payment_method = 'CASH';
                        $paymentHeader->total_amount   = 0;
                        $paymentHeader->discount_amount = 0;
                        $paymentHeader->net_amount     = 0;
                        $paymentHeader->reference_no   = $data['id'];
                        $paymentHeader->remarks        = '-';
                        $paymentHeader->coa_kas        = $data['account_id'];
                        $paymentHeader->bulk           = 0;
                        $paymentHeader->platform       = 'mobile';
                        $paymentHeader->save();

                        $hdrId     = $paymentHeader->id;
                        $reference = $paymentHeader->payment_code;

                        $totalAmount = 0;
                        $disc_total  = 0;
                        $net_total   = 0;

                        $invoicePayment = SalesInvoiceHeader::where('invoice_number', trim($invoice_number))->first();
                        if (empty($invoicePayment)) {
                            DB::rollBack();
                            return response()->json(['is_valid' => false, 'message' => 'Invoice tidak ditemukan ' . $invoice_number]);
                        }

                        $invoicePaymentId = $invoicePayment->id;
                        $discount_amount  = $invoicePayment->discount_amount;

                        $jumlahInvoicePayment = SalesPaymentDtl::where('invoice_id', $invoicePaymentId)->count();
                        $disc_amount = 0;
                        if ($jumlahInvoicePayment == 0 || $jumlahInvoicePayment == 1) {
                            $disc_amount = $discount_amount;
                            $disc_total += $disc_amount;
                        }

                        if ((float)$data['total_amount'] > 0) {
                            $net_total += ((float)$data['total_amount'] - $disc_amount);
                        }

                        if ((float)$data['total_amount'] < $disc_amount) {
                            DB::rollBack();
                            return response()->json(['is_valid' => false, 'message' => 'Allocated amount tidak boleh lebih kecil dari Discount Amount ' . $disc_amount]);
                        }

                        $totalAmount += (float)$data['total_amount'];

                        $detail                    = new SalesPaymentDtl();
                        $detail->payment_id        = $hdrId;
                        $detail->invoice_id        = $invoicePaymentId;
                        $detail->allocated_amount  = (float)$data['total_amount'];
                        $detail->outstanding_amount = $outstanding_amount;
                        $detail->line_no           = 1;
                        $detail->save();

                        $total_paid                  = (float)($invoicePayment->amount_paid ?? 0) + (float)$data['total_amount'];
                        $invoicePayment->amount_paid = $total_paid;
                        $newOutstanding              = (float)($invoicePayment->total_amount) - $total_paid;
                        $invoicePayment->status      = $newOutstanding <= 0 ? 'PAID' : 'PARTIAL PAID';
                        $invoicePayment->save();

                        $currency   = $currency ?? Currency::where('code', 'IDR')->first();
                        $currencyId = $currency->id;

                        $update                  = SalesPaymentHeader::find($hdrId);
                        $update->total_amount    = $totalAmount;
                        $update->discount_amount = $disc_total;
                        $update->net_amount      = $net_total;
                        $update->save();

                        postingGL($reference, $piutangAcc->account_id, $piutangAcc->account->account_name, $piutangAcc->cd, $totalAmount, $currencyId, '', $users_id);
                        $kasAccount->cd = $kasAccount->normal_balance == 'Debit' ? 'D' : 'C';
                        postingGL($reference, $kasAccount->id, $kasAccount->account_name, $kasAccount->cd, $net_total, $currencyId, '', $users_id);
                        if ($disc_total > 0) {
                            postingGL($reference, $discBayarAcc->account_id, $discBayarAcc->account->account_name, $discBayarAcc->cd, $disc_total, $currencyId, '', $users_id);
                        }
                    }
                }
            }

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function confirmDeliverPickup(Request $request)
    {
        $data = json_decode($request->input('data'), true);
        $users_id = $data['user_id'];
        // echo '<pre>';
        // print_r($data);die;
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {

            $roles = PackingListReturn::where('id', $data['id'])->first();
            if (empty($roles)) {
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

            $allDetailDo = PackingListReturn::where('packing_list_id', $roles->packing_list_id)->get()->toArray();
            $delivered = 0;
            foreach ($allDetailDo as $key => $value) {
                if ($value['status'] == 'CONFIRMED') {
                    $delivered++;
                }
            }
            if ($delivered == 0) {
                $plHeader = PackingList::find($roles->packing_list_id);
                $plHeader->status = 'NOT DELIVERED';
                $plHeader->save();
            }

            if ($delivered == count($allDetailDo)) {
                $plHeader = PackingList::find($roles->packing_list_id);
                $plHeader->status = 'CONFIRMED';
                $plHeader->save();
            } else {
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
