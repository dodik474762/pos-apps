<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\AccountMapping;
use App\Models\Master\Coa;
use App\Models\Master\Currency;
use App\Models\Master\ProductUom;
use App\Models\Transaction\PurchaseInvoiceDtl;
use App\Models\Transaction\PurchaseReturn;
use App\Models\Transaction\PurchaseReturnDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function getTableName()
    {
        return 'purchase_return';
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
                'w.name as warehouse_name',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('vendor as v', 'v.id', 'm.vendor')
            ->join('warehouse as w', 'w.id', 'm.warehouse')
            ->join('currency as c', 'c.id', 'm.currency')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.code', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.return_date', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.status', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('v.nama_vendor', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('w.name', 'LIKE', '%'.$keyword.'%');
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
        $userId = session('user_id');
        $result = ['is_valid' => false];

        DB::beginTransaction();
        try {
            // Ambil currency IDR
            $currency = Currency::where('code', 'IDR')->first();
            if (empty($currency)) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Currency IDR tidak ditemukan.',
                ]);
            }
            $currency_id = $currency->id;

            // Ambil konfigurasi akun
            if ($data['return_type'] == 'FROM_INVOICE') {
                // Return dari invoice

                $hutangAccount = AccountMapping::where('module', 'PURCHASE_RETURN')
                    ->where('account_type', 'hutang usaha')
                    ->with('account')->first();

                $ppnAccount = AccountMapping::where('module', 'PURCHASE_RETURN')
                    ->where('account_type', 'ppn masukan')
                    ->with('account')->first();

                $invAccount = AccountMapping::where('module', 'PURCHASE_RETURN')
                    ->where('account_type', 'inventory')
                    ->with('account')->first();
            } else {
                // Return dari GR (perpetual)
                $returnAccount = AccountMapping::where('module', 'PURCHASE_RETURN')
                    ->where('account_type', 'inventory')
                    ->with('account')->first();

                $hutangAccount = AccountMapping::where('module', 'PURCHASE_RETURN')
                    ->where('account_type', 'grir')
                    ->with('account')->first();
            }

            if (! $hutangAccount || ($data['return_type'] == 'FROM_INVOICE' && (! $ppnAccount))) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Konfigurasi akun untuk Purchase Return belum lengkap.',
                ]);
            }

            // Simpan atau update header Purchase Return
            $hdr = $data['id'] == '' ? new PurchaseReturn : PurchaseReturn::find($data['id']);
            if ($data['id'] == '') {
                $hdr->code = generatePRN(); // fungsi auto generate nomor retur pembelian
                $hdr->created_by = $userId;
            }

            $hdr->return_type = $data['return_type'];
            $hdr->reference_id = $data['reference_id'];
            $hdr->return_date = $data['return_date'];
            $hdr->reason = $data['reason'];
            $hdr->status = 'draft';
            $hdr->total_amount = $data['total_amount'];
            $hdr->warehouse = $data['warehouse_id'];
            $hdr->vendor = $data['vendor'];
            $hdr->currency = $currency_id;
            $hdr->updated_at = now();
            $hdr->save();

            $hdrId = $hdr->id;
            $returnNumber = $hdr->code;

            // Hapus detail lama jika ada
            PurchaseReturnDtl::where('purchase_return_id', $hdrId)->delete();
            // Simpan detail baru
            foreach ($data['items'] as $key => $value) {
                $detail = new PurchaseReturnDtl;
                $detail->purchase_return_id = $hdrId;
                $detail->product = $value['item_id'];
                $detail->qty = $value['qty'];
                $detail->unit = $value['unit'];
                $detail->unit_price = $value['unit_price'];
                $detail->reason = $value['reason_detail'] ?? null;
                $detail->reference_detail_id = $value['reference_detail'] ?? null;
                $detail->created_at = now();
                $detail->return_type = $data['return_type'];
                $detail->save();

                $amount = floatval($value['qty']) * floatval($value['unit_price']);

                // Posting ke General Ledger
                $reference = $returnNumber.'-ITEM-'.$value['item_id'];

                if ($data['return_type'] == 'FROM_INVOICE') {
                    // Return dari invoice
                    $invoice_dtl = PurchaseInvoiceDtl::find($value['reference_detail']);
                    $ppn = $invoice_dtl->tax;
                    $discount_percent = $invoice_dtl->discount_percent;
                    $discount_amount = $invoice_dtl->discount_amount / $invoice_dtl->qty;
                    $diskon_total_percent = $discount_percent * $amount / 100;

                    $discountAmount = $diskon_total_percent + $discount_amount;
                    $subtotal = $amount - $discountAmount;
                    $ppnAmount = $ppn * $subtotal / 100;
                    $grand_total = $subtotal + $ppnAmount;

                    // Debit Hutang Usaha (mengurangi kewajiban)
                    postingGL($reference, $hutangAccount->account_id, $hutangAccount->account->account_name, $hutangAccount->cd, $grand_total, $currency_id);

                    // Kredit Inventory
                    postingGL($reference, $invAccount->account_id, $invAccount->account->account_name, $invAccount->cd, $subtotal, $currency_id);

                    // Kredit PPN Masukan
                    if ($ppnAmount > 0) {
                        postingGL($reference, $ppnAccount->account_id, $ppnAccount->account->account_name, $ppnAccount->cd, $ppnAmount, $currency_id);
                    }
                } else {
                    // Return dari GR (perpetual)
                    // Debit GR/IR (mengurangi kewajiban)
                    postingGL($reference, $hutangAccount->account_id, $hutangAccount->account->account_name, $hutangAccount->cd, $amount, $currency_id);

                    // Kredit Persediaan
                    postingGL($reference, $returnAccount->account_id, $returnAccount->account->account_name, $returnAccount->cd, $amount, $currency_id);
                }

                $value['product'] = $value['item_id'];
                $qtyBaseUnit = getSmallestUnit($value['item_id'], $value['unit'], $value['qty']);
                $productUomLevel1 = ProductUom::where('product', $value['item_id'])->where('level', '1')->first();
                $qtyBaseUnit = $qtyBaseUnit['qty_in_base_unit'];
                stockUpdate($hdrId, $data['warehouse_id'], $value['item_id'], $productUomLevel1->unit_tujuan, $qtyBaseUnit, $value, 'min', 'purchase_return');
            }

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Purchase Return berhasil disimpan.';
        } catch (\Throwable $th) {
            DB::rollBack();
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
            $payment = PurchaseReturn::with(['details'])->find($data['id']);
            if (empty($payment)) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Vendor Payment tidak ditemukan.',
                ]);
            }

            if ($payment->status != 'draft') {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Tidak dapat dihapus karena status sudah tidak draft.',
                ]);
            }

            // ambil mapping akun untuk rollback GL
            $hutangAccount = AccountMapping::where('module', 'VENDOR_PAYMENT')
                ->where('account_type', 'hutang usaha')
                ->with('account')
                ->first();

            if (! $hutangAccount) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Mapping akun hutang usaha belum dikonfigurasi.',
                ]);
            }

            $kasBankAccount = Coa::find($payment->account_id);
            if (! $kasBankAccount) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Akun kas / bank tidak ditemukan.',
                ]);
            }

            // tandai header payment sebagai deleted
            $payment->deleted = now();
            $payment->deleted_by = session('user_id');
            $payment->status = 'deleted';
            $payment->save();

            // Loop setiap detail payment
            foreach ($payment->details as $detail) {
                $invoice = PurchaseInvoiceHeader::find($detail->purchase_invoice_id);
                if (! $invoice) {
                    DB::rollBack();

                    return response()->json([
                        'is_valid' => false,
                        'message' => 'Invoice ID '.$detail->purchase_invoice_id.' tidak ditemukan.',
                    ]);
                }

                // rollback status invoice (kembalikan menjadi "posted")
                // hitung ulang total pembayaran selain yang dihapus
                $totalPaid = DB::table('vendor_payment_detail')
                    ->where('purchase_invoice_id', $invoice->id)
                    ->whereNull('deleted')
                    ->where('vendor_payment_id', '!=', $payment->id)
                    ->sum('amount_paid');

                if ($totalPaid <= 0) {
                    $invoice->status = 'posted'; // belum ada pembayaran
                } elseif ($totalPaid < $invoice->total_amount) {
                    $invoice->status = 'partial'; // pembayaran sebagian
                } else {
                    $invoice->status = 'paid'; // masih fully paid
                }
                $invoice->save();

                // batalkan jurnal (GL)
                $reference = $payment->payment_number.'-'.$invoice->invoice_number;

                cancelGL($reference, $hutangAccount->account_id, $hutangAccount->account->account_name, $hutangAccount->cd);
                cancelGL($reference, $kasBankAccount->id, $kasBankAccount->account_name, 'C');

                // tandai detail sebagai deleted
                $detail->deleted = now();
                $detail->deleted_by = session('user_id');
                $detail->save();
            }

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Vendor Payment berhasil dibatalkan dan dihapus.';
        } catch (\Throwable $th) {
            DB::rollBack();
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
                'v.nama_vendor',
            ])
            ->join('vendor as v', 'v.id', 'm.vendor')
            ->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();

        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();

        return view('web.purchase_return.modal.confirmdelete', $data);
    }

    public function getReferences(Request $request)
    {
        $vendor = $request->get('vendor');
        $returnType = $request->get('return_type'); // FROM_GR / FROM_INVOICE

        if ($returnType === 'FROM_GR') {
            // Ambil semua Goods Receipt milik supplier ini
            $references = DB::table('goods_receipt_header')
                ->select('goods_receipt_header.id', 'goods_receipt_header.gr_number as reference_number',
                    'goods_receipt_header.received_date as reference_date', 'goods_receipt_header.total_amount')
                ->where('goods_receipt_header.vendor', $vendor)
                ->join('purchase_order as po', 'po.id', '=', 'goods_receipt_header.purchase_order')
                ->whereNotIn('po.status', ['invoiced', 'closed', 'canceled'])
                ->whereNull('goods_receipt_header.deleted')
                ->orderByDesc('goods_receipt_header.received_date')
                ->get();
        } elseif ($returnType === 'FROM_INVOICE') {
            // Ambil semua Purchase Invoice milik supplier ini
            $references = DB::table('purchase_invoice_header')
                ->select('id', 'invoice_number as reference_number', 'invoice_date as reference_date', 'total_amount')
                ->where('vendor', $vendor)
                ->whereNull('deleted')
                ->orderByDesc('invoice_date')
                ->get();
        } else {
            $references = collect([]);
        }

        return response()->json($references);
    }

    public function getReferencesDetail(Request $request)
    {
        $id = $request->get('id');
        $type = $request->get('type'); // FROM_GR atau FROM_INVOICE

        if ($type === 'FROM_GR') {
            $subReturn = DB::table('purchase_return_detail')
                ->select('reference_detail_id', DB::raw('SUM(qty) as total_qty_returned'))
                ->where('return_type', 'FROM_GR')
                ->groupBy('reference_detail_id');

            $details = DB::table('goods_receipt_detail as grd')
                ->join('product as p', 'p.id', '=', 'grd.product')
                ->join('unit as u', 'u.id', '=', 'grd.unit')
                ->join('purchase_order_detail as pod', 'pod.id', '=', 'grd.purchase_order_detail')
                ->leftJoinSub($subReturn, 'prd', function ($join) {
                    $join->on('prd.reference_detail_id', '=', 'grd.id');
                })
                ->where('grd.goods_receipt_header', $id)
                ->whereNull('grd.deleted')
                ->select(
                    'grd.id as gr_detail_id',
                    'p.id as item_id',
                    'p.name as item_name',
                    'u.name as unit_name',
                    'u.id as unit',
                    'grd.qty_received as qty',
                    'pod.purchase_price as unit_price',
                    DB::raw('COALESCE(prd.total_qty_returned, 0) as qty_returned')
                )
                ->get();
        } elseif ($type === 'FROM_INVOICE') {
            $subReturn = DB::table('purchase_return_detail')
                ->select('reference_detail_id', DB::raw('SUM(qty) as total_qty_returned'))
                ->where('return_type', 'FROM_INVOICE')
                ->groupBy('reference_detail_id');

            $details = DB::table('purchase_invoice_detail as pid')
                ->join('product as p', 'p.id', '=', 'pid.product')
                ->join('purchase_order_detail as pod', 'pod.id', '=', 'pid.purchase_order_detail_id')
                ->join('unit as u', 'u.id', '=', 'pod.unit')
                ->leftJoinSub($subReturn, 'prd', function ($join) {
                    $join->on('prd.reference_detail_id', '=', 'pid.id');
                })
                ->where('pid.purchase_invoice_id', $id)
                ->whereNull('pid.deleted')
                ->select(
                    'pid.id as invoice_detail_id',
                    'p.id as item_id',
                    'p.name as item_name',
                    'u.name as unit_name',
                    'u.id as unit',
                    'pid.qty as qty',
                    'pod.purchase_price as unit_price',
                    DB::raw('COALESCE(prd.total_qty_returned, 0) as qty_returned')
                )
                ->get();
        } else {
            $details = collect([]);
        }

        return response()->json($details);
    }

    public function loadInvoices(Request $request)
    {
        $data = $request->all();
        $vendorId = $data['vendor'];
        try {
            // code...
            $data['invoices'] = DB::table('purchase_invoice_header as h')
                ->leftJoin('vendor_payment_detail as d', function ($join) {
                    $join->on('d.purchase_invoice_id', '=', 'h.id')
                        ->whereNull('d.deleted');
                })
                ->select(
                    'h.id',
                    'h.invoice_number',
                    'h.invoice_date',
                    'h.total_amount',
                    DB::raw('COALESCE(SUM(d.amount_paid), 0) as total_paid'),
                    DB::raw('(h.total_amount - COALESCE(SUM(d.amount_paid), 0)) as outstanding')
                )
                ->where('h.vendor', $vendorId)
                ->whereNull('h.deleted')
                ->groupBy('h.id', 'h.invoice_number', 'h.invoice_date', 'h.total_amount')
                ->having('outstanding', '>', 0)
                ->get();

            // echo '<pre>';
            // print_r($data);die;

            return view('web.purchase_return.datainvoice', $data);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
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

        return view('web.purchase_return.dataproductpo', $data);
    }
}
