<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\AccountMapping;
use App\Models\Master\Currency;
use App\Models\Master\ProductUom;
use App\Models\Master\ProductUomPrice;
use App\Models\Transaction\ReturnOfConsigment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnConsigmentController extends Controller
{
    public function getTableName()
    {
        return 'sales_retur_of_consigment';
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
                'p.code as product_code',
                'p.name as product_name',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('vendor as v', 'v.id', 'm.vendor_id')
            ->join('product as p', 'p.id', 'm.product_id')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.return_number', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.return_date', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.remarks', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.status', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('p.code', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('p.name', 'LIKE', '%'.$keyword.'%');
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
        $userId = session('user_id');
        $result = ['is_valid' => false];

        // echo '<pre>';
        // print_r($data);die;

        DB::beginTransaction();
        try {

            $inventoryAcc = AccountMapping::where('module', $data['status_supply'] == 'good' ? 'RETURN_GOOD' : 'RETURN_BAD')
                ->where('account_type', 'inventory')
                ->with('account') // kalau kamu pakai relasi
                ->first();

            $otherAcc = AccountMapping::where('module', $data['status_supply'] == 'good' ? 'RETURN_GOOD' : 'RETURN_BAD')
                ->where('account_type', $data['status_supply'] == 'good' ? 'retur penjualan' :  'barang rusak')
                ->with('account')
                ->first();

            if (! $inventoryAcc || ! $otherAcc) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Konfigurasi akun untuk Retur belum lengkap.',
                ]);
            }

            // === HEADER ===
            $header = empty($data['id'])
                ? new ReturnOfConsigment
                : ReturnOfConsigment::find($data['id']);

            if (empty($data['id'])) {
                $header->return_number = generateNoReturOther(); // misal helper
                $header->created_by = $userId;
                $header->status = 'DRAFT';
            }

            $header->return_date = $data['return_date'];
            $header->vendor_id = $data['vendor_id'];
            $header->status_supply = $data['status_supply'];
            $header->product_id = $data['product_id'];
            $header->price_id = $data['price_id'];
            $header->price = $data['price'];
            $header->remarks = $data['remarks'];
            $header->qty = $data['qty'];
            $header->loss = $data['qty'] * $data['price'];
            $header->save();

            $hdrId = $header->id;

            $reference = $header->return_number;
            if ($data['id'] != '') {
                cancelAllGL($reference);
            }

            $currency = Currency::where('code', 'IDR')->first();
            $currencyId = $currency->id;

            $totalAmount = $data['qty'] * $data['price'];

            if($data['status_supply']){
                $productPrice = ProductUomPrice::find($data['price_id']);
                $qtyBaseUnit = getSmallestUnit($data['product_id'], $productPrice->unit, $data['qty']);
                $productUomLevel1 = ProductUom::where('product', $data['product_id'])->where('level', '1')->first();
                $qtyBaseUnit = $qtyBaseUnit['qty_in_base_unit'];

                $data['product'] = $data['product_id'];
                    stockUpdate($hdrId,
                    1,
                    $data['product_id'],
                    $productUomLevel1->unit_tujuan, $qtyBaseUnit, $data, 'add', 'return_consigment');
            }

            postingGL($reference, $inventoryAcc->account_id, $inventoryAcc->account->account_name, $inventoryAcc->cd, $totalAmount, $currencyId);
            postingGL($reference, $otherAcc->account_id, $otherAcc->account->account_name, $otherAcc->cd, $totalAmount, $currencyId);

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Retur berhasil disimpan';
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
        $id = $request->id;
        $userId = session('user_id');

        DB::beginTransaction();
        try {
            // Cari data retur berdasarkan ID
            $header = ReturnOfConsigment::find($data['id']);
            if (!$header) {
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Retur tidak ditemukan.',
                ]);
            }

            // Cek apakah retur sudah dibatalkan sebelumnya
            if ($header->status == 'CANCELLED') {
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Retur sudah dibatalkan.',
                ]);
            }

            // Cancelling all GL entries related to this return
            cancelAllGL($header->return_number);

            // Update status retur menjadi 'CANCELLED'
            $header->status = 'CANCELED';
            $header->deleted = date('Y-m-d H:i:s');
            $header->deleted_by = $userId;
            $header->save();

            // Revert stock updates if needed (mengurangi stok jika sudah ditambahkan sebelumnya)
            if ($header->status_supply) {
                $productPrice = ProductUomPrice::find($header->price_id);
                $qtyBaseUnit = getSmallestUnit($header->product_id, $productPrice->unit, $header->qty);
                $productUomLevel1 = ProductUom::where('product', $header->product_id)->where('level', '1')->first();
                $qtyBaseUnit = $qtyBaseUnit['qty_in_base_unit'];

                // Mengurangi stok yang sudah ditambahkan saat retur
                stockUpdate($header->id, 1, $header->product_id, $productUomLevel1->unit_tujuan, $qtyBaseUnit, $data, 'min', 'return_consigment');
            }

            // Commit transaksi
            DB::commit();

            $result['is_valid'] = true;
            $result['message'] = 'Retur berhasil dibatalkan.';
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['is_valid'] = false;
            $result['message'] = $th->getMessage();
        }

        return response()->json($result);
    }

    public function getDetailData($id)
    {
        DB::enableQueryLog();
        $datadb = DB::table($this->getTableName().' as m')
            ->select([
                'm.*',
                'c.nama_customer',
                'i.invoice_number',
            ])
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->leftJoin('sales_invoice_header as i', 'i.id', 'm.invoice_id')
            ->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();

        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();

        return view('web.return_consigment.modal.confirmdelete', $data);
    }

    public function showModalVendor(Request $request)
    {
        $data = $request->all();

        return view('web.return_consigment.modal.datavendor', $data);
    }

    public function showModalProduct(Request $request)
    {
        $data = $request->all();

        return view('web.return_consigment.modal.datavendor', $data);
    }

    public function posted(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;

        DB::beginTransaction();
        try {

            $menu = ReturnOfConsigment::find($data['id']);
            $menu->updated_by = session('user_id');
            $menu->status = 'POSTED';
            $menu->save();
            DB::commit();

            $result['is_valid'] = true;

        } catch (\Throwable $th) {
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }

        return response()->json($result);
    }
}
