<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\Currency;
use App\Models\Transaction\ProductUomCost;
use App\Models\Transaction\ProductUomCostHistory;
use App\Models\Transaction\PurchaseOrder;
use App\Models\Transaction\PurchaseOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function getTableName()
    {
        return "purchase_order";
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
                    $query->where('m.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.po_date', 'LIKE', '%' . $keyword . '%');
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

    public function getDataProductPoDetail(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();

        $exceptPoDetailId = [];
        if(!empty($data['itemsChoose'])) {
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
                'po.code as po_code'
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

        if(!empty($exceptPoDetailId)){
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

            $roles = $data['id'] == '' ? new PurchaseOrder() : PurchaseOrder::find($data['id']);
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
                    $items = PurchaseOrderDetail::find($value['id']);
                    if($items->status != 'open'){
                        DB::rollBack();
                        $result['message'] = 'Tidak dapat dihapus karena status sudah tidak open';
                        return response()->json($result);
                    }
                    $items->deleted = now();
                    $items->save();
                }else{
                    list($product_uom, $product, $product_name) = explode('//', $value['product']);
                    $items = $value['id'] == '' ? new PurchaseOrderDetail() : PurchaseOrderDetail::find($value['id']);
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


                    /*uom cost price */
                    $existCost = ProductUomCost::where('product', $product)->where('unit_id', $value['unit'])
                    ->where('vendor', $data['vendor'])
                    ->first();
                    if(!empty($existCost)){
                        $existCost->cost = $value['price'];
                        $existCost->vendor = $data['vendor'];
                        $existCost->product_uom = $product_uom;
                        $existCost->date_start = date('Y-m-d');
                        $existCost->save();
                    }else{
                        $product_cost = new ProductUomCost();
                        $product_cost->cost = $value['price'];
                        $product_cost->vendor = $data['vendor'];
                        $product_cost->product_uom = $product_uom;
                        $product_cost->product = $product;
                        $product_cost->unit_id = $value['unit'];
                        $product_cost->date_start = date('Y-m-d');
                        $product_cost->save();
                    }
                    /*uom cost price */

                    /*uom cost history */
                    $historyCost = new ProductUomCostHistory();
                    $historyCost->cost = $value['price'];
                    $historyCost->vendor = $data['vendor'];
                    $historyCost->product_uom = $product_uom;
                    $historyCost->product = $product;
                    $historyCost->unit_id = $value['unit'];
                    $historyCost->date_start = date('Y-m-d');
                    $historyCost->save();
                    /*uom cost history */

                    $grand_total += $value['subtotal'];
                }
            }

            $update = PurchaseOrder::find($hdrId);
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
            $menu = PurchaseOrder::find($data['id']);
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
        return view('web.purchase_order.modal.confirmdelete', $data);
    }

    public function showDataProduct(Request $request)
    {
        $data = $request->all();
        return view('web.product.modal.dataproduct', $data);
    }
}
