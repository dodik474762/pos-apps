<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\ProductUom;
use App\Models\Transaction\DeliveryOrderDtl;
use App\Models\Transaction\DeliveryOrderHeader;
use App\Models\Transaction\DeliveryOrderStatusLog;
use App\Models\Transaction\SalesOrderDetail;
use App\Models\Transaction\SalesOrderHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryOrderController extends Controller
{
    public function getTableName()
    {
        return 'delivery_order_header';
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
                'cc.nama_customer',
                'so.so_number',
                'w.name as warehouse_name',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('sales_order_headers as so', 'so.id', 'm.so_id')
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.do_number', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.do_date', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.status', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('so.so_number', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('w.name', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('cc.nama_customer', 'LIKE', '%'.$keyword.'%');
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

    public function getDataSo()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table('sales_order_headers as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'cc.nama_customer',
                'c.code as currency_code',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('currency as c', 'c.id', 'm.currency')
            ->whereNull('m.deleted')
            ->whereIn('m.status', ['draft', 'partial'])
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.so_number', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.so_date', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.status', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('cc.nama_customer', 'LIKE', '%'.$keyword.'%');
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

        DB::beginTransaction();
        try {

            // === HEADER ===
            $header = empty($data['id'])
                ? new DeliveryOrderHeader()
                : DeliveryOrderHeader::find($data['id']);

            if (empty($data['id'])) {
                $header->do_number = generateNoDO(); // misal helper
                $header->created_by = $userId;
                $header->status = 'DRAFT';
            }

            list($cust_id, $cust_name) = explode('//', $data['customer_id']);

            $header->do_date = $data['do_date'];
            $header->so_id = $data['so_id'];
            $header->customer_id = $cust_id;
            $header->warehouse_id = $data['warehouse_id'];
            $header->total_item = 0; // akan dihitung ulang di bawah
            $header->total_qty = 0; // akan dihitung ulang di bawah
            $header->save();

            $hdrId = $header->id;

            // === DETAIL ===
            $line_no = 1;
            foreach ($data['items'] as $item) {
                // Skip baris yang ditandai untuk dihapus
                if (!empty($item['remove']) && $item['remove'] == 1) {
                    if (!empty($item['id'])) {
                        $exist = DeliveryOrderDtl::find($item['id']);
                        if ($exist && $exist->status !== 'DRAFT') {
                            DB::rollBack();
                            return response()->json([
                                'is_valid' => false,
                                'message' => 'Tidak dapat dihapus karena status sudah bukan draft'
                            ]);
                        }
                        if ($exist) {
                            $exist->deleted = now();
                            $exist->deleted_by = $userId;
                            $exist->save();
                        }
                    }
                    continue;
                }

                // Item baru atau update
                $detail = empty($item['id'])
                    ? new DeliveryOrderDtl()
                    : DeliveryOrderDtl::find($item['id']);

                $detail->do_id = $hdrId;
                $detail->so_detail_id = $item['so_detail_id'];
                $detail->product_id = $item['product_id'];
                $detail->qty = $item['qty'];
                $detail->uom = $item['uom'];
                $detail->note = $item['note'];
                $detail->line_no = $line_no++;
                $detail->save();

                $qtyBaseUnit = getSmallestUnit($item['product_id'], $item['uom'], $item['qty']);
                $productUomLevel1 = ProductUom::where('product', $item['product_id'])->where('level', '1')->first();
                $qtyBaseUnit = $qtyBaseUnit['qty_in_base_unit'];

                $item['product'] = $item['product_id'];
                stockUpdate($hdrId,
                $data['warehouse_id'],
                $item['product_id'],
                $productUomLevel1->unit_tujuan, $qtyBaseUnit, $item, 'min', 'delivery_order');
            }

            $total_item = collect($data['items'])->where('remove', 0)->count();
            $total_qty = collect($data['items'])->where('remove', 0)->sum('qty');
            // Update total header
            $header->total_item = $total_item;
            $header->total_qty = $total_qty;
            $header->save();

            $so = SalesOrderHeader::find($data['so_id']);
            $so->status = 'confirmed';
            $so->save();

            $dev_status_log = DeliveryOrderStatusLog::where('do_id', $hdrId)->first();
            if (empty($dev_status_log)) {
                $dev_status_log = new DeliveryOrderStatusLog();
                $dev_status_log->do_id = $hdrId;
                $dev_status_log->status_from = 'DRAFT';
                $dev_status_log->status_to = 'DRAFT';
                $dev_status_log->changed_by = $userId;
                $dev_status_log->changed_at = now();
                $dev_status_log->save();
            }

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Delivery Order berhasil disimpan';
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
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            // code...
            $menu = DeliveryOrderHeader::find($data['id']);
            if ($menu->status != 'DRAFT') {
                DB::rollBack();
                $result['message'] = 'Tidak dapat dihapus karena status sudah tidak draft';

                return response()->json($result);
            }
            $menu->deleted = date('Y-m-d H:i:s');
            $menu->deleted_by = session('user_id');
            $menu->save();

            $wh_id = $menu->warehouse_id;

            $delivery_dtl = DeliveryOrderDtl::where('do_id', $data['id'])->get();
            foreach ($delivery_dtl as $item) {
                $item->deleted = date('Y-m-d H:i:s');
                $item->deleted_by = session('user_id');
                $item->save();

                $qtyBaseUnit = getSmallestUnit($item->product_id, $item->uom, $item->qty);
                $productUomLevel1 = ProductUom::where('product', $item->product_id)->where('level', '1')->first();
                $qtyBaseUnit = $qtyBaseUnit['qty_in_base_unit'];

                $value['product'] = $item->product_id;
                stockUpdate($data['id'],
                $wh_id,
                $item->product_id,
                $productUomLevel1->unit_tujuan, $qtyBaseUnit, $value, 'add', 'cancel_delivery_order');
            }

            DeliveryOrderStatusLog::where('do_id', $data['id'])->delete();

            $so = SalesOrderHeader::find($menu->so_id);
            $so->status = 'draft';
            $so->save();

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            // throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }

        return response()->json($result);
    }

    public function getDetailData($id)
    {
        DB::enableQueryLog();
        $datadb = DB::table($this->getTableName().' as m')
            ->select([
                'm.*',
                'so.so_number',
                'c.nama_customer'
            ])
            ->join('sales_order_headers as so', 'so.id', 'm.so_id')
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();

        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();

        return view('web.delivery_order.modal.confirmdelete', $data);
    }

    public function showModalSO(Request $request)
    {
        $data = $request->all();

        return view('web.delivery_order.modal.dataso', $data);
    }

    public function getSoDetail(Request $request){
        $data = $request->all();
        $datadb = SalesOrderDetail::where('sales_order_details.sales_order_id', $data['so_id'])
        ->select([
            'sales_order_details.*',
            'u.name as unit_name',
            'p.code as product_code',
            'p.name as product_name'
        ])
        ->join('product as p', 'p.id', 'sales_order_details.product_id')
        ->join('unit as u', 'u.id', 'sales_order_details.unit')
        ->whereNull('sales_order_details.deleted')
        ->get();

        $data['data'] = $datadb;

        return view('web.delivery_order.datasodetail', $data);
    }
}
