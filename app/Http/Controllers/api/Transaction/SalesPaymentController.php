<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesPaymentController extends Controller
{
     public function getTableName()
    {
        return 'sales_payment_header';
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
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.payment_code', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.payment_date', 'LIKE', '%'.$keyword.'%');
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

    public function getDataDo()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table('delivery_order_header as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'cc.nama_customer',
                'c.code as currency_code',
                'soh.so_number',
                'soh.so_date',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('sales_order_headers as soh', 'soh.id', 'm.so_id')
            ->join('currency as c', 'c.id', 'soh.currency')
            ->whereNull('m.deleted')
            ->whereIn('m.status', ['draft'])
            ->orderBy('m.id', 'asc');
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

             $piutangAcc = AccountMapping::where('module', 'SALES_INVOICE')
                ->where('account_type', 'piutang usaha')
                ->with('account') // kalau kamu pakai relasi
                ->first();

            $penjualanAcc = AccountMapping::where('module', 'SALES_INVOICE')
                ->where('account_type', 'penjualan barang')
                ->with('account')
                ->first();

            $discPenjualanAcc = AccountMapping::where('module', 'SALES_INVOICE')
                ->where('account_type', 'diskon penjualan')
                ->with('account')
                ->first();

            if (! $piutangAcc || ! $penjualanAcc || ! $discPenjualanAcc) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Konfigurasi akun untuk Sales Invoice belum lengkap.',
                ]);
            }

            $tax = Tax::find($data['tax']);
            if(empty($tax)){
                DB::rollBack();
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Tax tidak ditemukan.',
                ]);
            }

            $ppnAccount = Coa::find($tax->coa_id);
            $do = DeliveryOrderHeader::find($data['do_id']);

            // === HEADER ===
            $header = empty($data['id'])
                ? new SalesInvoiceHeader()
                : SalesInvoiceHeader::find($data['id']);

            if (empty($data['id'])) {
                $header->invoice_number = generateNoSI(); // misal helper
                $header->created_by = $userId;
                $header->status = 'DRAFT';
            }

            $subtotal = collect($data['items'])->where('remove', 0)->sum('subtotal');
            $disc_total = collect($data['items'])->where('remove', 0)->sum('discount');
            $tax_amount = $data['tax_base'] / 100 * $subtotal;

            list($cust_id, $cust_name) = explode('//', $data['customer_id']);

            $header->invoice_date = $data['invoice_date'];
            $header->do_id = $data['do_id'];
            $header->warehouse_id = $do->warehouse_id;
            $header->customer_id = $cust_id;
            $header->subtotal = $subtotal;
            $header->discount_amount = $disc_total;
            $header->tax_base = $data['tax_base'];
            $header->tax_id = $data['tax'];
            $header->tax_amount = $tax_amount;
            $header->total_amount = $data['total_amount'];
            $header->save();

            $hdrId = $header->id;

            // === DETAIL ===
            $line_no = 1;
            foreach ($data['items'] as $item) {
                // Skip baris yang ditandai untuk dihapus
                if (!empty($item['remove']) && $item['remove'] == 1) {
                    if (!empty($item['id'])) {
                        $exist = SalesInvoiceDtl::find($item['id']);
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
                    ? new SalesInvoiceDtl()
                    : SalesInvoiceDtl::find($item['id']);

                $detail->invoice_id = $hdrId;
                $detail->so_detail_id = $item['so_detail_id'];
                $detail->product_id = $item['product_id'];
                $detail->qty = $item['qty'];
                $detail->price = $item['price'];
                $detail->discount = $item['discount'];
                $detail->subtotal = $item['subtotal'];
                $detail->line_no = $line_no++;
                $detail->save();

                /*mapping coa */
            }

            $do->status = 'CONFIRMED';
            $do->save();

            $dev_status_log = DeliveryOrderStatusLog::where('do_id', $data['do_id'])->first();
            if (empty($dev_status_log)) {
                $dev_status_log = new DeliveryOrderStatusLog();
                $dev_status_log->do_id = $hdrId;
                $dev_status_log->status_from = 'DRAFT';
                $dev_status_log->status_to = 'CONFIRMED';
                $dev_status_log->changed_by = $userId;
                $dev_status_log->changed_at = now();
                $dev_status_log->save();
            }

            $so = SalesOrderHeader::find($do->so_id);
            $currency = $so->currency;

            $reference = $header->invoice_number;
            if($data['id'] != ''){
                cancelAllGL($reference);
            }

            postingGL($reference, $piutangAcc->account_id, $piutangAcc->account->account_name, $piutangAcc->cd, $subtotal+$tax_amount, $currency);
            postingGL($reference, $penjualanAcc->account_id, $penjualanAcc->account->account_name, $penjualanAcc->cd, ($subtotal+$disc_total), $currency);
            postingGL($reference, $discPenjualanAcc->account_id, $discPenjualanAcc->account->account_name, $discPenjualanAcc->cd, ($disc_total), $currency);
            if(!empty($ppnAccount)){
                $ppnAccount->dc = $ppnAccount->normal_balance == 'Debit' ? 'D' : 'C';
                postingGL($reference, $ppnAccount->id, $ppnAccount->account_name, $ppnAccount->dc, ($tax_amount), $currency);
            }

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Sales Invoice berhasil disimpan';
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

            $menu = SalesInvoiceHeader::find($data['id']);

            if ($menu->status != 'DRAFT') {
                DB::rollBack();
                $result['message'] = 'Tidak dapat dihapus karena status sudah tidak DRAFT';
                return response()->json($result);
            }

            // Soft delete header
            $menu->deleted = date('Y-m-d H:i:s');
            $menu->deleted_by = session('user_id');
            $menu->status = 'CANCELED';
            $menu->save();

            // Ambil detail
            $items = SalesInvoiceDtl::where('invoice_id', $data['id'])->get();

            $do = DeliveryOrderHeader::find($menu->do_id);
            $so = SalesOrderHeader::find($do->so_id);

            foreach ($items as $value) {

                $oldDetail = SalesInvoiceDtl::find($value->id);

                if ($oldDetail) {
                    $value->deleted = date('Y-m-d H:i:s');
                    $value->deleted_by = session('user_id');
                    $value->save();
                }
            }

            // Update Delivery Order
            $do->status = 'DRAFT';
            $do->save();

            // Hapus log status DO
            $log = DeliveryOrderStatusLog::where('do_id', $menu->do_id)
            ->where('status_to', 'CONFIRMED')
            ->first();
            if ($log) {
                $log->delete();
            }

            cancelAllGL($menu->invoice_number);

            DB::commit();
            $result['is_valid'] = true;

        } catch (\Throwable $th) {
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
                'do.do_number',
                'c.nama_customer'
            ])
            ->join('delivery_order_header as do', 'do.id', 'm.do_id')
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();

        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();

        return view('web.sales_payment.modal.confirmdelete', $data);
    }

    public function showModalDO(Request $request)
    {
        $data = $request->all();

        return view('web.sales_payment.modal.datado', $data);
    }

    public function getDoDetail(Request $request){
        $data = $request->all();
        $datadb = DeliveryOrderDtl::where('delivery_order_detail.do_id', $data['do_id'])
        ->select([
            'delivery_order_detail.*',
            'u.name as unit_name',
            'p.code as product_code',
            'p.name as product_name',
            'sod.discount_percent',
            'sod.unit_price',
            'sod.discount_amount',
            'sod.subtotal',
        ])
        ->join('sales_order_details as sod', 'sod.id', 'delivery_order_detail.so_detail_id')
        ->join('product as p', 'p.id', 'delivery_order_detail.product_id')
        ->join('unit as u', 'u.id', 'delivery_order_detail.uom')
        ->whereNull('delivery_order_detail.deleted')
        ->whereNull('sod.deleted')
        // ->whereNull('sod.free_for')
        ->get();

        $data['data'] = $datadb;

        return view('web.sales_payment.datadodetail', $data);
    }
}
