<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\Currency;
use App\Models\Transaction\DeliveryOrderDtl;
use App\Models\Transaction\DeliveryOrderHeader;
use App\Models\Transaction\PackingList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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


        DB::beginTransaction();
        try {

             $piutangAcc = AccountMapping::where('module', 'SALES_PAYMENT')
                ->where('account_type', 'piutang usaha')
                ->with('account') // kalau kamu pakai relasi
                ->first();

            $discBayarAcc = AccountMapping::where('module', 'SALES_PAYMENT')
                ->where('account_type', 'diskon bayar')
                ->with('account')
                ->first();

            if (! $piutangAcc || ! $discBayarAcc) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Konfigurasi akun untuk Sales Payment belum lengkap.',
                ]);
            }

            $kasAccount = Coa::find($data['account_id']);
            // === HEADER ===

            $header = empty($data['id'])
                ? new SalesPaymentHeader()
                : SalesPaymentHeader::find($data['id']);

            if (empty($data['id'])) {
                $header->payment_code = generateNoSP(); // misal helper
                $header->created_by = $userId;
                $header->status = 'PENDING';
            }

            $header->payment_date = $data['payment_date'];
            $header->customer_id = $data['customer_id'];
            $header->payment_method = $data['payment_method'];
            $header->total_amount = 0;
            $header->discount_amount = 0;
            $header->net_amount = 0;
            $header->reference_no = $data['reference_no'];
            $header->remarks = $data['remarks'];
            $header->coa_kas = $data['account_id'];
            $header->bulk = $data['bulk'];
            $header->save();

            $hdrId = $header->id;

            $reference = $header->payment_code;
            if($data['id'] != ''){
                cancelAllGL($reference);
            }

            // === DETAIL ===
            $totalAmount = 0;
            $disc_total = 0;
            $net_total = 0;
            $line_no = 1;
            foreach ($data['details'] as $key=>$value) {
                // Skip baris yang ditandai untuk dihapus
                if (!empty($value['remove']) && $value['remove'] == 1) {
                    if (!empty($value['id'])) {
                        $exist = SalesPaymentDtl::find($value['id']);
                        if ($exist) {
                            $exist->deleted = now();
                            $exist->deleted_by = $userId;
                            $exist->save();
                        }
                    }
                    continue;
                }

                $outstanding_amount = $value['outstanding_amount'] - $value['allocated_amount'];
                if($outstanding_amount < 0){
                    DB::rollBack();
                    return response()->json([
                        'is_valid' => false,
                        'message' => 'Allocated amount tidak boleh lebih besar dari Outstanding Amount pada baris ke-'.($key+1)
                    ]);
                }

                $jumlahInvoicePayment = SalesPaymentDtl::where('invoice_id', $value['invoice_id'])->count();
                $disc_amount = 0;
                if($jumlahInvoicePayment == 0 || $jumlahInvoicePayment == 1){
                    $disc_amount = $value['discount_amount'];
                    $disc_total += $disc_amount;
                }

                if($value['allocated_amount'] > 0){
                    $net_total += ($value['allocated_amount'] - $disc_amount);
                }

                if($value['allocated_amount'] < $disc_amount){
                    DB::rollBack();
                    return response()->json([
                        'is_valid' => false,
                        'message' => 'Allocated amount tidak boleh lebih kecil dari Discount Amount '.$disc_amount.' pada baris ke-'.($key+1)
                    ]);

                }

                $totalAmount += $value['allocated_amount'];

                // Item baru atau update
                $detail = empty($value['id'])
                    ? new SalesPaymentDtl()
                    : SalesPaymentDtl::find($value['id']);

                $detail->payment_id = $hdrId;
                $detail->invoice_id = $value['invoice_id'];
                $detail->allocated_amount = $value['allocated_amount'];
                $detail->outstanding_amount = $value['outstanding_amount'];
                $detail->line_no = $line_no++;
                $detail->save();

                /*mapping coa */

                $invoice = SalesInvoiceHeader::find($value['invoice_id']);
                $total_paid = 0;
                if($value['id'] == ''){
                    $total_paid = $invoice->amount_paid +$value['allocated_amount'];
                }else{
                    $total_paid = $invoice->amount_paid - $value['allocated_amount_old'] + $value['allocated_amount'];
                }
                $invoice->amount_paid = $total_paid;
                if($outstanding_amount == 0){
                    $invoice->status = 'PAID';
                }else{
                    $invoice->status = 'PARTIAL PAID';
                }
                $invoice->save();


            }

            $currency = Currency::where('code', 'IDR')->first();
            $currencyId = $currency->id;

            $update = SalesPaymentHeader::find($hdrId);
            $update->total_amount = $totalAmount;
            $update->discount_amount = $disc_total;
            $update->net_amount = $net_total;
            $update->save();

            postingGL($reference, $piutangAcc->account_id, $piutangAcc->account->account_name, $piutangAcc->cd, $totalAmount, $currencyId);

            $kasAccount->cd = $kasAccount->normal_balance == 'Debit' ? 'D' : 'C';
            postingGL($reference, $kasAccount->id, $kasAccount->account_name, $kasAccount->cd, ($net_total), $currencyId);
            if($disc_total > 0){
                postingGL($reference, $discBayarAcc->account_id, $discBayarAcc->account->account_name, $discBayarAcc->cd, ($disc_total), $currencyId);
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
                'c.nama_customer'
            ])
            ->join('customer as c', 'c.id', 'm.customer_id')
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
}
