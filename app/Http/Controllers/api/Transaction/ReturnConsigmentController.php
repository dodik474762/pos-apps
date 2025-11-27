<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\AccountMapping;
use App\Models\Master\Currency;
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

            $header = ReturnOfConsigment::find($id);

            if (! $header) {
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Credit Note tidak ditemukan.',
                ]);
            }

            if ($header->status == 'CANCELED') {
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Credit Note sudah dibatalkan.',
                ]);
            }

            // ambil semua detail termasuk yg sudah deleted
            $details = CreditNoteDtl::where('credit_note_id', $id)->whereNull('deleted')->get();

            foreach ($details as $dt) {

                $invoice = CreditNoteDtl::find($dt->invoice_detail_id);

                if ($invoice) {
                    // Kembalikan return_qty
                    $cn_amount = (($dt->unit_price * $dt->qty_affected) - $dt->discount_amount + $dt->tax_amount);
                    $invoice->credit_note_amount = $invoice->credit_note_amount - $cn_amount;

                    if ($invoice->credit_note_amount < 0) {
                        DB::rollBack();

                        return response()->json([
                            'is_valid' => false,
                            'message' => 'Cancel gagal: Credit Note amount menjadi minus.',
                        ]);
                    }

                    $invoice->save();
                }
            }

            // Batalkan jurnal (GL)
            cancelAllGL($header->return_number);

            // Ubah status
            $header->status = 'CANCELLED';
            $header->deleted = now();
            $header->deleted_by = $userId;
            $header->save();

            DB::commit();

            return response()->json([
                'is_valid' => true,
                'message' => 'Credit Note berhasil dibatalkan.',
            ]);

        } catch (\Throwable $th) {

            DB::rollBack();

            return response()->json([
                'is_valid' => false,
                'message' => $th->getMessage(),
            ]);
        }
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
