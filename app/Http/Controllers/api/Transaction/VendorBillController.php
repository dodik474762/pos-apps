<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\AccountMapping;
use App\Models\Master\Currency;
use App\Models\Transaction\PurchaseInvoiceHeader;
use App\Models\Transaction\VendorBillDtl;
use App\Models\Transaction\VendorBillHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorBillController extends Controller
{
    public function getTableName()
    {
        return 'vendor_payment_header';
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
                    $query->where('m.payment_number', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.payment_date', 'LIKE', '%'.$keyword.'%');
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
        $users = session('user_id');
        $result['is_valid'] = false;

        DB::beginTransaction();
        try {
            // Ambil currency IDR
            $currency = Currency::where('code', 'IDR')->first();
            if (empty($currency)) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Currency IDR tidak ditemukan',
                ]);
            }

            $currency_id = $currency->id;

            // Ambil konfigurasi akun
            $hutangAccount = AccountMapping::where('module', 'VENDOR_PAYMENT')
                ->where('account_type', 'hutang usaha')
                ->with('account')
                ->first();

            $kasBankAccount = AccountMapping::where('module', 'VENDOR_PAYMENT')
                ->where('account_type', 'kas/bank')
                ->with('account')
                ->first();

            if (! $hutangAccount || ! $kasBankAccount) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Konfigurasi akun untuk Vendor Payment belum lengkap.',
                ]);
            }

            // Simpan header
            $roles = $data['id'] == '' ? new VendorBillHeader : VendorBillHeader::find($data['id']);
            if ($data['id'] == '') {
                $roles->payment_number = generateVPN(); // fungsi auto generate nomor pembayaran
                $roles->created_by = $users;
            }

            $roles->payment_date = $data['payment_date'];
            $roles->vendor = $data['vendor'];
            $roles->payment_method = $data['payment_method'];
            $roles->reference_number = $data['reference_number'];
            $roles->total_payment = $data['total_payment'];
            $roles->remarks = $data['remarks'];
            $roles->status = 'draft';
            $roles->currency = $currency_id;
            $roles->save();

            $hdrId = $roles->id;
            $payment_number = $roles->payment_number;

            // Loop data detail (invoice yang dibayar)
            foreach ($data['invoices'] as $key => $value) {
                $invoice = PurchaseInvoiceHeader::find($value['invoice_id']);
                if (empty($invoice)) {
                    DB::rollBack();

                    return response()->json([
                        'is_valid' => false,
                        'message' => 'Invoice ID '.$value['invoice_id'].' tidak ditemukan.',
                    ]);
                }

                $amountPaid = floatval($value['amount_paid']);
                $remaining = max($invoice->total_amount - $amountPaid, 0);

                // Simpan ke vendor_payment_detail
                $detail = new VendorBillDtl;
                $detail->vendor_payment_id = $hdrId;
                $detail->purchase_invoice_id = $invoice->id;
                $detail->amount_paid = $amountPaid;
                $detail->remaining_balance = $remaining;
                $detail->save();

                // Update invoice
                $totalPaidBefore = DB::table('vendor_payment_detail')
                    ->where('purchase_invoice_id', $invoice->id)
                    ->whereNull('deleted')
                    ->sum('amount_paid');

                $invoice->status = ($totalPaidBefore >= $invoice->total_amount) ? 'paid' : 'posted';
                $invoice->updated_at = now();
                $invoice->save();

                // Posting ke General Ledger
                $reference = $payment_number.'-'.$invoice->invoice_number;

                // Debit Hutang Usaha (mengurangi kewajiban)
                postingGL(
                    $reference,
                    $hutangAccount->account_id,
                    $hutangAccount->account->account_name,
                    $hutangAccount->cd,
                    $amountPaid,
                    $currency_id
                );

                // Kredit Kas / Bank
                postingGL(
                    $reference,
                    $kasBankAccount->account_id,
                    $kasBankAccount->account->account_name,
                    $kasBankAccount->cd,
                    $amountPaid,
                    $currency_id
                );
            }

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Vendor Payment berhasil disimpan.';
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

            $hutangAccount = AccountMapping::where('module', 'PURCHASE_INVOICE')
                ->where('account_type', 'hutang usaha')
                ->with('account')
                ->first();

            $grirAccount = AccountMapping::where('module', 'PURCHASE_INVOICE')
                ->where('account_type', 'grir')
                ->with('account')
                ->first();

            $ppnMasukanAccount = AccountMapping::where('module', 'PURCHASE_INVOICE')
                ->where('account_type', 'ppn masukan')
                ->with('account')
                ->first();

            $discAccount = AccountMapping::where('module', 'PURCHASE_INVOICE')
                ->where('account_type', 'diskon pembelian')
                ->with('account')
                ->first();

            $menu = PurchaseInvoiceHeader::find($data['id']);
            $invoice_number = $menu->invoice_number;
            if ($menu->status != 'draft') {
                DB::rollBack();
                $result['message'] = 'Tidak dapat dihapus karena status sudah tidak open';

                return response()->json($result);
            }
            $menu->deleted = date('Y-m-d H:i:s');
            $menu->deleted_by = session('user_id');
            $menu->save();

            $items = PurchaseInvoiceDtl::where('purchase_invoice_id', $data['id'])->get();

            $data_po = [];
            $all_po = [];
            foreach ($items as $value) {
                $value->deleted = date('Y-m-d H:i:s');
                $value->deleted_by = session('user_id');
                $value->save();

                $po_detail = PurchaseOrderDetail::find($value->purchase_order_detail_id);
                $qty_total_outstanding_received = $po_detail->qty - ($po_detail->qty_received == '' ? 0 : $po_detail->qty_received);
                if ($qty_total_outstanding_received <= 0) {
                    $po_detail->status = 'received';
                } else {
                    $po_detail->status = 'partial-received';
                }
                $po_detail->save();

                $data_po[$po_detail->purchase_order] = $po_detail->status;
                $all_po[] = $po_detail->purchase_order;

                $reference = $invoice_number.'-'.$value->purchase_order_detail_id;
                cancelGL($reference, $grirAccount->account_id, $grirAccount->account->account_name, $grirAccount->cd);
                cancelGL($reference, $discAccount->account_id, $discAccount->account->account_name, $discAccount->cd);
                cancelGL($reference, $ppnMasukanAccount->account_id, $ppnMasukanAccount->account->account_name, $ppnMasukanAccount->cd);
                cancelGL($reference, $hutangAccount->account_id, $hutangAccount->account->account_name, $hutangAccount->cd);
            }

            $all_po = array_unique($all_po);
            $po = PurchaseOrder::whereIn('id', $all_po)->get();
            foreach ($po as $key => $value) {
                if (isset($data_po[$value->id])) {
                    $value->status = $data_po[$value->id];
                    $value->save();
                } else {
                    DB::rollBack();
                    $result['message'] = 'Tidak dapat dihapus karena status PO - Tidak tersedia';

                    return response()->json($result);
                }
            }

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

        return view('web.vendor_bill.modal.confirmdelete', $data);
    }

    public function showDataInvoice(Request $request)
    {
        $data = $request->all();

        return view('web.vendor_bill.modal.datainvoice', $data);
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

            return view('web.vendor_bill.datainvoice', $data);
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

        return view('web.vendor_bill.dataproductpo', $data);
    }
}
