<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
     public function getTableName()
    {
        return 'purchase_invoice_header';
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
                'v.nama_vendor',
                'c.code as currency_code',
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
                    $query->where('m.invoice_number', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.invoice_date', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.status', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('v.nama_vendor', 'LIKE', '%'.$keyword.'%');
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
        $users = session('user_id');
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            // code...
            $currency = Currency::where('code', 'IDR')->first();
            $po = PurchaseOrder::find($data['purchase_order']);
            $warehouse = $po->warehouse;

            if (empty($currency)) {
                DB::rollBack();
                $result['message'] = 'Currency IDR tidak ditemukan';

                return response()->json($result);
            }

            $roles = $data['id'] == '' ? new GoodReceipt : GoodReceipt::find($data['id']);
            if ($data['id'] == '') {
                $roles->gr_number = generateGrNumber();
                $roles->created_by = $users;
            }
            $roles->received_date = $data['received_date'];
            $roles->purchase_order = $data['purchase_order'];
            $roles->remarks = $data['remarks'];
            $roles->vendor = $data['vendor'];
            $roles->received_by = $data['received_by'];
            $roles->status = 'open';
            $roles->total_qty = $data['total_qty'];
            $roles->currency = $currency->id;
            $roles->save();
            $hdrId = $roles->id;

            $grand_total = 0;
            $totalFullyReceived = 0;
            $totalPartlyReceived = 0;
            $disc_total = 0;
            $ppnTotal = 0;
            $poGrandTotal = 0;
            foreach ($data['items'] as $key => $value) {
                if ($value['remove'] == '1') {
                    $items = GoodReceiptDtl::find($value['id_detail']);
                    if ($items->status != 'open') {
                        DB::rollBack();
                        $result['message'] = 'Tidak dapat dihapus karena status sudah tidak open';

                        return response()->json($result);
                    }
                    $items->deleted = now();
                    $items->save();
                } else {
                    $items = $value['id_detail'] == '' ? new GoodReceiptDtl : GoodReceiptDtl::find($value['id_detail']);
                    $status = $value['id_detail'] == '' ? '' : $items->status;

                     $update = PurchaseOrderDetail::where('id', $value['id'])->first();
                    if (empty($update)) {
                        DB::rollBack();
                        $result['message'] = 'Data PO Item '.$value['product'].' tidak ditemukan';

                        return response()->json($result);
                    }

                    if ($data['id'] != '') {
                        // Cek data lama di detail GR
                        $oldDetail = GoodReceiptDtl::find($value['id_detail']);
                        if ($oldDetail) {
                            // Hitung qty dalam base unit
                            $qtyBaseUnitOld = getSmallestUnit($oldDetail->product, $oldDetail->unit, $oldDetail->qty_received);
                            $qtyBaseUnitOld = $qtyBaseUnitOld['qty_in_base_unit'];

                            // Rollback stok lama
                            $valueRollback = [
                                'product' => $oldDetail->product,
                                'price' => $update->purchase_price ?? 0,
                            ];

                            $productUomLevel1 = ProductUom::where('product', $oldDetail->product)->where('level', '1')->first();

                            // rollback stok lama (kebalikan dari add)
                            stockRollback(
                                $hdrId,
                                $warehouse,
                                $oldDetail->product,
                                $productUomLevel1->unit_tujuan,
                                $qtyBaseUnitOld,
                                $valueRollback,
                                'add' // karena sebelumnya 'add', rollback-nya kebalikannya
                            );
                        }
                    }

                    $purchase_price = $update->purchase_price;
                    $subtotal = $purchase_price * $value['qty_received'];
                    $diskon_persentase = ($update->diskon_persen / 100) * $subtotal;
                    $diskon_nominal = $update->diskon_nominal;
                    $tax_amount = $update->tax_rate / 100 * ($subtotal - $diskon_persentase - $diskon_nominal);
                    $value['subtotal'] = $subtotal - $diskon_persentase - $diskon_nominal + $tax_amount;

                    $disc_total += ($diskon_persentase + $diskon_nominal);
                    $ppnTotal += $tax_amount;
                    $grandTotalPo = $purchase_price * $update->qty;
                    $poGrandTotal += $grandTotalPo;

                    $items->goods_receipt_header = $hdrId;
                    $items->purchase_order_detail = $value['id'];
                    $items->product = $value['product'];
                    $items->unit = $value['unit'];
                    $items->unit_name = $value['unit_name'];
                    $items->qty_received = $value['qty_received'];
                    $items->lot_number = $value['lot_number'];
                    $items->expired_date = $value['expired_date'];
                    $items->subtotal = $subtotal;
                    $items->status = 'open';
                    $items->save();

                    if ($value['id_detail'] != '') {
                        if ($status != 'open') {
                            DB::rollBack();
                            $result['message'] = 'Tidak dapat diubah karena status sudah tidak open';

                            return response()->json($result);
                        }
                    }

                    $total_oustanding_qty_po = $data['id'] == '' ? intval($value['qty_outstanding']) - intval($value['qty_received']) :
                        intval($update->qty) - intval($value['qty_received']) ;

                    if ($total_oustanding_qty_po < 0) {
                        DB::rollBack();
                        $result['message'] = 'Qty outstanding tidak mencukupi';

                        return response()->json($result);
                    }


                    $update->qty_received = $data['id'] == '' ? $update->qty_received + $value['qty_received'] : $value['qty_received'];

                    if ($total_oustanding_qty_po == 0) {
                        $update->status = 'received';
                        $totalFullyReceived += 1;
                    }

                    if ($total_oustanding_qty_po > 0) {
                        $update->status = 'partial-received';
                        $totalPartlyReceived += 1;
                    }
                    $update->save();

                    $qtyBaseUnit = getSmallestUnit($value['product'], $value['unit'], $value['qty_received']);
                    $productUomLevel1 = ProductUom::where('product', $value['product'])->where('level', '1')->first();
                    $qtyBaseUnit = $qtyBaseUnit['qty_in_base_unit'];

                    $value['price'] = $purchase_price;
                    stockUpdate($hdrId, $warehouse, $value['product'], $productUomLevel1->unit_tujuan, $qtyBaseUnit, $value, 'add', 'good_receipt');
                    $grand_total += $subtotal;
                }
            }

            if ($totalFullyReceived == count($data['items'])) {
                $po->status = 'received';
            }
            if ($totalPartlyReceived == count($data['items'])) {
                $po->status = 'partial-received';
            }
            $po->save();

            $update = GoodReceipt::find($hdrId);
            $update->total_amount = $grand_total;
            $update->save();

            $inventoryAccount = AccountMapping::where('module', 'GOOD_RECEIPT')
                ->where('account_type', 'inventory')
                ->with('account') // kalau kamu pakai relasi
                ->first();

            $grirAccount = AccountMapping::where('module', 'GOOD_RECEIPT')
                ->where('account_type', 'grir')
                ->with('account')
                ->first();

            // $ppnMasukanAccount = AccountMapping::where('module', 'GOOD_RECEIPT')
            //     ->where('account_type', 'ppn masukan')
            //     ->with('account')
            //     ->first();

            // $discAccount = AccountMapping::where('module', 'GOOD_RECEIPT')
            //     ->where('account_type', 'diskon pembelian')
            //     ->with('account')
            //     ->first();

            if (! $inventoryAccount || ! $grirAccount) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Konfigurasi akun untuk Good Receipt belum lengkap.',
                ]);
            }

            postingGL($update->gr_number, $inventoryAccount->account_id, $inventoryAccount->account->account_name, $inventoryAccount->cd, $grand_total, $update->currency);
            postingGL($update->gr_number, $grirAccount->account_id, $grirAccount->account->account_name, $grirAccount->cd, ($grand_total), $update->currency);

            // if ($ppnMasukanAccount) {
            //     $partialRatio = $grand_total / $poGrandTotal;

            //     $ppnAmount = $ppnTotal * $partialRatio; // proporsional PPN
            //     postingGL($update->gr_number, $ppnMasukanAccount->account_id, $ppnMasukanAccount->account->account_name, $ppnMasukanAccount->cd, $ppnAmount, $update->currency);
            // }

            // if ($discAccount) {
            //     $partialRatio = $grand_total / $poGrandTotal;
            //     $ppnAmount = $ppnTotal * $partialRatio; // proporsional PPN
            //     $discountAmount = $disc_total * $partialRatio; // proporsional diskon
            //     postingGL($update->gr_number, $discAccount->account_id, $discAccount->account->account_name, $discAccount->cd, $discountAmount, $update->currency);
            // }

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            // throw $th;
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
            // code...
            $menu = GoodReceipt::find($data['id']);
            if ($menu->status != 'open') {
                DB::rollBack();
                $result['message'] = 'Tidak dapat dihapus karena status sudah tidak open';

                return response()->json($result);
            }
            $menu->deleted = date('Y-m-d H:i:s');
            $menu->deleted_by = session('user_id');
            $menu->save();

            $items = GoodReceiptDtl::where('goods_receipt_header', $data['id'])->get();
            $po = PurchaseOrder::find($menu->purchase_order);
            $warehouse = $po->warehouse;

            $totalFullyReceived = 0;
            $totalPartlyReceived = 0;
            $totalOpen = 0;
            foreach ($items as $value) {
                // Cek data lama di detail GR
                $oldDetail = GoodReceiptDtl::find($value->id);
                if ($oldDetail) {
                    // Hitung qty dalam base unit
                    $qtyBaseUnitOld = getSmallestUnit($oldDetail->product, $oldDetail->unit, $oldDetail->qty_received);
                    $qtyBaseUnitOld = $qtyBaseUnitOld['qty_in_base_unit'];

                    // Rollback stok lama
                    $valueRollback = [
                        'product' => $oldDetail->product,
                        'price' => $update->purchase_price ?? 0,
                    ];

                    $productUomLevel1 = ProductUom::where('product', $oldDetail->product)->where('level', '1')->first();

                    // rollback stok lama (kebalikan dari add)
                    stockRollback(
                        $menu->id,
                        $warehouse,
                        $oldDetail->product,
                        $productUomLevel1->unit_tujuan,
                        $qtyBaseUnitOld,
                        $valueRollback,
                        'add' // karena sebelumnya 'add', rollback-nya kebalikannya
                    );
                }


                $value->deleted = date('Y-m-d H:i:s');
                $value->deleted_by = session('user_id');
                $value->save();

                $update = PurchaseOrderDetail::where('id', $value->purchase_order_detail)->first();
                $update->qty_received = $update->qty_received - $value->qty_received;

                if($update->qty - $update->qty_received == 0){
                    $update->status = 'received';
                    $totalFullyReceived += 1;
                }

                if($update->qty - $update->qty_received > 0){
                    $update->status = 'partial-received';
                    $totalPartlyReceived += 1;
                }
                if($update->qty_received == 0){
                    $update->status = 'open';
                    $totalOpen += 1;
                }
                $update->save();
            }


            $po = PurchaseOrder::find($menu->purchase_order);
            if ($totalFullyReceived == count($items->toArray())) {
                $po->status = 'received';
            }
            if ($totalPartlyReceived == count($items->toArray())) {
                $po->status = 'partial-received';
            }
            if ($totalOpen == count($items->toArray())) {
                $po->status = 'draft';
            }
            $po->save();

            $inventoryAccount = AccountMapping::where('module', 'GOOD_RECEIPT')
                ->where('account_type', 'inventory')
                ->with('account') // kalau kamu pakai relasi
                ->first();

            $grirAccount = AccountMapping::where('module', 'GOOD_RECEIPT')
                ->where('account_type', 'grir')
                ->with('account')
                ->first();

            cancelGL($menu->gr_number, $inventoryAccount->account_id, $inventoryAccount->account->account_name, $inventoryAccount->cd, $menu->total_amount, $menu->currency);
            cancelGL($menu->gr_number, $grirAccount->account_id, $grirAccount->account->account_name, $grirAccount->cd, $menu->total_amount, $menu->currency);

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
                'po.code as po_code',
                'po.vendor',
                'v.nama_vendor',
                'po.status as status_po'
            ])
            ->join('purchase_order as po', 'po.id', 'm.purchase_order')
            ->join('vendor as v', 'v.id', 'po.vendor')
            ->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();

        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();

        return view('web.purchase_invoice.modal.confirmdelete', $data);
    }

    public function showDataPOItem(Request $request)
    {
        $data = $request->all();

        return view('web.purchase_invoice.modal.dataproductpo', $data);
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
            echo $th->getMessage();
            exit;
        }

        return view('web.purchase_invoice.dataproductpo', $data);
    }
}
