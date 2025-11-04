<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\Currency;
use App\Models\Transaction\GoodReceipt;
use App\Models\Transaction\GoodReceiptDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodReceiptController extends Controller
{
     public function getTableName()
    {
        return "goods_receipt_header";
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
                'v.nama_vendor',
                'c.code as currency_code'
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('vendor as v', 'v.id', 'm.vendor')
            ->join('currency as c', 'c.id', 'm.currency')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.gr_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.received_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('v.nama_vendor', 'LIKE', '%' . $keyword . '%');
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
        $users = session('user_id');
        // echo '<pre>';
        // print_r($data);die;
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $currency = Currency::where('code', 'IDR')->first();
            if(empty($currency)){
                DB::rollBack();
                $result['message'] = 'Currency IDR tidak ditemukan';
                return response()->json($result);
            }

            $roles = $data['id'] == '' ? new GoodReceipt() : GoodReceipt::find($data['id']);
            if ($data['id'] == '') {
                $roles->code = generateNoPO();
                $roles->created_by = $users;
            }
            $roles->po_date = $data['po_date'];
            $roles->remarks = $data['remarks'];
            $roles->vendor = $data['vendor'];
            $roles->warehouse = $data['warehouse'];
            $roles->status = 'DRAFT';
            $roles->est_received_date = $data['est_received_date'];
            $roles->currency = $currency->id;
            $roles->save();
            $hdrId = $roles->id;

            $grand_total = 0;
            foreach ($data['items'] as $key => $value) {
                if($value['remove'] == '1'){
                    $items = GoodReceiptDtl::find($value['id']);
                    if($items->status != 'open'){
                        DB::rollBack();
                        $result['message'] = 'Tidak dapat dihapus karena status sudah tidak open';
                        return response()->json($result);
                    }
                    $items->deleted = now();
                    $items->save();
                }else{
                    list($product_uom, $product, $product_name) = explode('//', $value['product']);
                    $items = $value['id'] == '' ? new GoodReceiptDtl() : GoodReceiptDtl::find($value['id']);
                    $items->purchase_order = $hdrId;
                    $items->product = $product;
                    $items->unit = $value['unit'];
                    $items->qty = $value['qty'];
                    $items->purchase_price = $value['price'];
                    $items->diskon_persen = $value['disc_persen'];
                    $items->diskon_nominal = $value['disc_nominal'];
                    $items->est_received_date = $data['est_received_date'];
                    $items->product_uom = $product_uom;
                    $items->subtotal = $value['subtotal'];
                    $items->tax = $value['tax'];
                    $items->tax_rate = $value['tax_rate'];
                    $items->tax_amount = $value['tax_amount'];
                    if($value['id'] == ''){
                        $items->status = 'open';
                        $items->qty_received = 0;
                    }
                    $items->save();

                    if($value['id'] != ''){
                        if($items->status != 'open'){
                            DB::rollBack();
                            $result['message'] = 'Tidak dapat diubah karena status sudah tidak open';
                            return response()->json($result);
                        }
                    }

                    $grand_total += $value['subtotal'];
                }
            }

            $update = GoodReceipt::find($hdrId);
            $update->grand_total = $grand_total;
            $update->save();

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
            $menu = GoodReceipt::find($data['id']);
            if($menu->status != 'DRAFT'){
                DB::rollBack();
                $result['message'] = 'Tidak dapat dihapus karena status sudah tidak draft';
                return response()->json($result);
            }
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
        return view('web.good_receipt.modal.confirmdelete', $data);
    }

    public function showDataPOItem(Request $request)
    {
        $data = $request->all();
        return view('web.good_receipt.modal.dataproductpo', $data);
    }
   
    public function getListItemOutstandingPO(Request $request)
    {
        $data = $request->all();

        try {
            $data['po_details_outstanding'] = DB::table('purchase_order_detail as pod')
                ->join('purchase_order as po', 'po.id', '=', 'pod.purchase_order')
                ->join('product as p', 'p.id', '=', 'pod.product') // opsional: kalau ada tabel products
                ->join('unit as u', 'u.id', '=', 'pod.unit')       // opsional: kalau ada tabel satuan
                ->select(
                    'pod.id',
                    'pod.purchase_order',
                    'po.code as po_code',
                    'pod.product',
                    'p.name as product_name',
                    'p.code as product_code',
                    'pod.unit',
                    'u.name as unit_name',
                    'pod.qty',
                    'pod.qty_received',
                    DB::raw('(pod.qty - IFNULL(pod.qty_received, 0)) as outstanding_qty'),
                    'pod.purchase_price',
                    'pod.subtotal',
                    'pod.status'
                )
                ->where('po.status', '!=', 'cancelled')
                ->where('pod.status', '!=', 'received')
                ->whereRaw('(pod.qty - IFNULL(pod.qty_received, 0)) > 0')
                ->where('po.id', $data['po'])
                ->orderBy('po.id', 'desc')
                ->get();
        } catch (\Throwable $th) {
            echo $th->getMessage();die;
        }

        return view('web.good_receipt.dataproductpo', $data);
    }
}
