<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\AccountMapping;
use App\Models\Master\Currency;
use App\Models\Master\ProductUom;
use App\Models\Transaction\SalesInvoiceDtl;
use App\Models\Transaction\SalesInvoiceHeader;
use App\Models\Transaction\SalesOrderDetail;
use App\Models\Transaction\SalesReturnDtl;
use App\Models\Transaction\SalesReturnHdr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function getTableName()
    {
        return 'sales_return';
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
                'cc.nama_customer',
                'i.invoice_number',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->leftJoin('sales_invoice_header as i', 'i.id', 'm.invoice_id')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.return_type', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.return_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.return_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('i.invoice_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.nama_customer', 'LIKE', '%' . $keyword . '%');
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

    public function getDataInvoice(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table('sales_invoice_header as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'cc.nama_customer',
                'do.do_number',
                'do.do_date',
                'w.name as warehouse_name',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->leftJoin('delivery_order_header as do', function ($q) {
                return $q->on('do.id', 'm.do_id')->whereNull('do.deleted');
            })
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            ->whereIn('m.status', ['POSTED', 'PARTIAL PAID', 'PAID', 'PACKED'])
            ->where('m.customer_id', $data['customer'])
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        // echo '<pre>';
        // print_r($datadb->toSql());
        // die;
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.invoice_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.invoice_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('do.do_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('do.do_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.due_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('w.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.nama_customer', 'LIKE', '%' . $keyword . '%');
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
                    $query->where('soh.so_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('soh.so_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.do_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.do_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.nama_customer', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.code', 'LIKE', '%' . $keyword . '%');
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
        $userId = session('user_id');
        $result = ['is_valid' => false];

        // echo '<pre>';
        // print_r($data);die;

        DB::beginTransaction();
        try {

            $penjualanAcc = AccountMapping::where('module', 'SALES_RETURN')
                ->where('account_type', 'penjualan barang')
                ->with('account') // kalau kamu pakai relasi
                ->first();

            $ppnKeluaranAcc = AccountMapping::where('module', 'SALES_RETURN')
                ->where('account_type', 'ppn keluaran')
                ->with('account')
                ->first();

            $discAcc = AccountMapping::where('module', 'SALES_RETURN')
                ->where('account_type', 'diskon penjualan')
                ->with('account')
                ->first();

            $piutangAcc = AccountMapping::where('module', 'SALES_RETURN')
                ->where('account_type', 'piutang usaha')
                ->with('account')
                ->first();

            $depositAcc = AccountMapping::where('module', 'SALES_RETURN')
                ->where('account_type', 'deposit pelanggan')
                ->with('account')
                ->first();

            if (! $penjualanAcc || ! $ppnKeluaranAcc || ! $discAcc  || ! $depositAcc || ! $piutangAcc) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Konfigurasi akun untuk Sales Return belum lengkap.',
                ]);
            }

            if (empty($data['id'])) {
                $allow_retur_customer = getMaxReturCustomer($data['customer_id']);
                if (!$allow_retur_customer) {
                    DB::rollBack();

                    return response()->json([
                        'is_valid' => false,
                        'message' => 'Jumlah retur customer sudah mencapai batas maksimal.',
                    ]);
                }
            }

            // === HEADER ===
            $header = empty($data['id'])
                ? new SalesReturnHdr
                : SalesReturnHdr::find($data['id']);

            if (empty($data['id'])) {
                $header->return_number = generateNoReturn(); // misal helper
                $header->created_by = $userId;
                $header->status = 'DRAFT';
            }

            $header->return_date = $data['return_date'];
            $header->customer_id = $data['customer_id'];
            $header->return_type = $data['return_type'];
            $header->refund_amount = $data['refund_amount'];
            $header->deposit_amount = $data['deposit_amount'];
            $header->total_return_value = 0;
            $header->reason = $data['reason'];
            $header->invoice_id = $data['invoice_id'];
            $header->save();

            $hdrId = $header->id;

            $reference = $header->return_number;
            if ($data['id'] != '') {
                cancelAllGL($reference);
            }

            // === DETAIL ===
            $totalAmount = 0;
            $disc_total = 0;
            $net_total = 0;
            $tax_total = 0;
            $line_no = 1;
            foreach ($data['items'] as $key => $value) {
                // Skip baris yang ditandai untuk dihapus
                if (! empty($value['remove']) && $value['remove'] == 1) {
                    if (! empty($value['id'])) {
                        $exist = SalesReturnDtl::find($value['id']);
                        if ($exist) {
                            $exist->deleted = now();
                            $exist->deleted_by = $userId;
                            $exist->save();

                            $invoice = SalesInvoiceDtl::find($value['invoice_detail_id']);
                            $return_qty = $invoice->return_qty - $value['qty_return'];
                            $invoice->return_qty = $return_qty;
                            $invoice->save();
                        }
                    }

                    continue;
                }

                // Item baru atau update
                $detail = empty($value['id'])
                    ? new SalesReturnDtl
                    : SalesReturnDtl::find($value['id']);

                $detail->return_id = $hdrId;
                $detail->product_id = $value['product_id'];
                $detail->qty_return = $value['qty_return'];
                $detail->unit_price = $value['unit_price'];
                $detail->discount_amount = $value['discount_return'];
                $detail->tax_amount = $value['tax_amount_return'];
                $detail->type_tax = $value['type_tax'];
                $detail->tax_rate = $value['tax_rate'];
                $detail->invoice_detail_id = $value['invoice_detail_id'];
                $detail->tax = $value['tax'];
                $detail->save();

                $disc_total += $value['discount_return'];
                $tax_total += $value['tax_amount_return'];
                $totalAmount += (($value['unit_price'] * $value['qty_return']));
                $net_total += (($value['unit_price'] * $value['qty_return']) - $value['discount_return']);
                // + $value['tax_amount_return']);

                /* mapping coa */

                $invoice = SalesInvoiceDtl::find($value['invoice_detail_id']);
                $total_return = 0;
                if ($value['id'] == '') {
                    $total_return = $invoice->return_qty + $value['qty_return'];
                } else {
                    $total_return = $invoice->return_qty - $value['qty_return_old'] + $value['qty_return'];
                }
                $invoice->return_qty = $total_return;

                $outstanding = $invoice->qty - $invoice->return_qty;
                if ($outstanding < 0) {
                    DB::rollBack();

                    return response()->json([
                        'is_valid' => false,
                        'message' => 'Jumlah return melebihi outstanding invoice ',
                    ]);
                }
                $invoice->save();

                /*menambah stock gudang */
                $so_detail = SalesOrderDetail::find($invoice->so_detail_id);
                $qtyBaseUnit = getSmallestUnit($value['product_id'], $so_detail->unit, $value['qty_return']);
                $productUomLevel1 = ProductUom::where('product', $value['product_id'])->where('level', '1')->first();
                $qtyBaseUnit = $qtyBaseUnit['qty_in_base_unit'];

                $value['product'] = $value['product_id'];
                stockUpdate(
                    $hdrId,
                    $invoice->warehouse_id,
                    $value['product_id'],
                    $productUomLevel1->unit_tujuan,
                    $qtyBaseUnit,
                    $value,
                    'add',
                    'sales_return'
                );
                // recalculateFrom('0', date('Y-m-d'), date('Y-m-d'), $invoice->warehouse_id, null, $value['product_id']);
                /*menambah stock gudang */
            }

            $updateHdr = SalesReturnHdr::find($hdrId);
            $updateHdr->total_return_value = $net_total;
            $updateHdr->save();

            $currency = Currency::where('code', 'IDR')->first();
            $currencyId = $currency->id;

            postingGL($reference, $penjualanAcc->account_id, $penjualanAcc->account->account_name, $penjualanAcc->cd, $totalAmount, $currencyId);
            postingGL($reference, $ppnKeluaranAcc->account_id, $ppnKeluaranAcc->account->account_name, $ppnKeluaranAcc->cd, ($tax_total), $currencyId);
            postingGL($reference, $discAcc->account_id, $discAcc->account->account_name, $discAcc->cd, ($disc_total), $currencyId);
            if ($data['return_type'] == 'REFUND') {
                postingGL($reference, $piutangAcc->account_id, $piutangAcc->account->account_name, $piutangAcc->cd, ($net_total), $currencyId);
            }
            if ($data['return_type'] == 'DEPOSIT') {
                postingGL($reference, $depositAcc->account_id, $depositAcc->account->account_name, $depositAcc->cd, ($net_total), $currencyId);
            }

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Sales Return berhasil disimpan';
            $result['so_id'] = $hdrId;
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['is_valid'] = false;
            $result['message'] = $th->getMessage();
        }

        return response()->json($result);
    }

    function hitungSummaryAll($invoice_number = '', $product_id = 0, $unit_id = 0, $qty_retur = 0, $product_code = '')
    {
        $total_subtotal = 0;
        $total_disc     = 0;
        $total_tax      = 0;
        $total_net      = 0;

        $invoice = SalesInvoiceHeader::where('invoice_number', trim($invoice_number))->first();
        $items = SalesInvoiceDtl::where('sales_invoice_detail.invoice_id', $invoice->id)
            ->where('sales_invoice_detail.product_id', trim($product_id))
            ->whereNull('sales_invoice_detail.deleted')
            ->select(['sales_invoice_detail.*'])
            ->join('sales_order_details as sod', 'sod.id', 'sales_invoice_detail.so_detail_id')
            ->where('sod.unit_price', '>', 0)
            ->where('sod.unit', trim($unit_id))->first();

        if (empty($items)) {
            return [
                'is_valid' => false,
                'message' => 'Data Product ' . $product_code . ' tidak ditemukan di Invoice ini : ' . trim($invoice_number),
            ];
        }

        $qty            = floatval($qty_retur ?? 0);
        $qty_invoice    = floatval($items->qty ?? 0);
        $price          = floatval($items->price ?? 0);
        $discount       = floatval($items->discount ?? 0);
        $type_tax       = strtoupper($items->type_tax ?? 'NON-TAX');
        $tax_rate       = floatval($items->tax_rate ?? 0);

        // subtotal
        $subtotal = $price * $qty;

        // diskon proporsional
        $discount_return = ($qty_invoice > 0)
            ? ($qty / $qty_invoice) * $discount
            : 0;

        $net_before_tax = $subtotal - $discount_return;

        // pajak
        $tax_amount = 0;
        $net_total  = 0;

        switch (strtoupper($type_tax)) {
            case 'INCLUDE':
                // sudah termasuk pajak
                $tax_amount = $net_before_tax - ($net_before_tax / (1 + $tax_rate / 100));
                $net_total  = $net_before_tax;
                break;

            case 'EXCLUDE':
                // belum termasuk pajak
                $tax_amount = $net_before_tax * ($tax_rate / 100);
                $net_total  = $net_before_tax + $tax_amount;
                break;

            case 'NON-TAX':
            default:
                $tax_amount = 0;
                $net_total  = $net_before_tax;
                break;
        }

        // simpan ke item (opsional, untuk response)
        $items->subtotal         = round($subtotal, 2);
        $items->discount_return  = round($discount_return, 2);
        $items->tax_amount       = round($tax_amount, 2);
        $items->net_total        = round($net_total, 2);

        // total summary
        $total_subtotal += $subtotal;
        $total_disc     += $discount_return;
        $total_tax      += $tax_amount;
        $total_net      += $net_total;

        // refund / deposit
        $refund_amount  = 0;
        $deposit_amount = 0;

        $refund_amount = round($total_net, 2);

        return [
            'is_valid' => true,
            'items'           => $items,
            'item_subtotal' => $subtotal,
            'item_discount' => $discount_return,
            'item_tax'      => $tax_amount,
            'item_net'      => $net_total,
            'type_tax' => $type_tax,
            'total_subtotal'  => round($total_subtotal, 2),
            'total_discount'  => round($total_disc, 2),
            'total_tax'       => round($total_tax, 2),
            'total_net'       => round($total_net, 2),
            'refund_amount'   => $refund_amount,
            'deposit_amount'  => $deposit_amount,
        ];
    }


    public function sync(Request $request)
    {
        $data = json_decode($request->input('data'), true);
        $userId = $data['user_id'];

        $result['is_valid'] = false;
        $result['data'] = $data;
        list($customer_id, $customer_code, $customer_name, $outstanding_amount, $invoice_number) = explode('/', $data['customer_id']);
        [$products, $product_unit] = explode(':', $data['product_id']);
        $products = explode('/', $products);
        $product_unit = explode('/', $product_unit);


        $product_id = $products[0];
        $product_code = trim($products[1]);
        $unit_id = $product_unit[0];
        $result['product_id'] = trim($product_id);
        $result['unit_id'] = trim($unit_id);

        DB::beginTransaction();
        try {
            $periode = Carbon::parse($data['return_date'])->setTimezone('Asia/Jakarta');
            $return_date = $periode->format('Y-m-d');


            $penjualanAcc = AccountMapping::where('module', 'SALES_RETURN')
                ->where('account_type', 'penjualan barang')
                ->with('account') // kalau kamu pakai relasi
                ->first();

            $ppnKeluaranAcc = AccountMapping::where('module', 'SALES_RETURN')
                ->where('account_type', 'ppn keluaran')
                ->with('account')
                ->first();

            $discAcc = AccountMapping::where('module', 'SALES_RETURN')
                ->where('account_type', 'diskon penjualan')
                ->with('account')
                ->first();

            $kasBankAcc = AccountMapping::where('module', 'SALES_RETURN')
                ->where('account_type', 'kas bank')
                ->with('account')
                ->first();

            $depositAcc = AccountMapping::where('module', 'SALES_RETURN')
                ->where('account_type', 'deposit pelanggan')
                ->with('account')
                ->first();

            if (! $penjualanAcc || ! $ppnKeluaranAcc || ! $discAcc || ! $kasBankAcc || ! $depositAcc) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Konfigurasi akun untuk Sales Return belum lengkap.',
                ]);
            }

            if (empty($data['id'])) {
                $allow_retur_customer = getMaxReturCustomer(trim($customer_id));
                if (!$allow_retur_customer) {
                    DB::rollBack();

                    return response()->json([
                        'is_valid' => false,
                        'message' => 'Jumlah retur customer sudah mencapai batas maksimal.',
                    ]);
                }
            }

            $calculateReturn = $this->hitungSummaryAll($invoice_number, $product_id, $unit_id, $data['qty_return'], $product_code);

            // === HEADER ===
            $header = new SalesReturnHdr();

            if (empty($data['id'])) {
                $header->return_number = generateNoReturn(); // misal helper
                $header->created_by = $userId;
                $header->status = 'DRAFT';
            }

            $header->return_date = $return_date;
            $header->customer_id = trim($customer_id);
            $header->return_type = 'REFUND';
            $header->types = $data['return_type'];
            $header->refund_amount = $calculateReturn['refund_amount'];
            $header->deposit_amount = $calculateReturn['deposit_amount'];
            $header->total_return_value = 0;
            $header->reason = $data['reason'];
            $header->platform = 'mobile';
            $header->invoice_id = $calculateReturn['items']->invoice_id;
            $header->save();

            $hdrId = $header->id;
            $reference = $header->return_number;

            // === DETAIL ===
            $totalAmount = 0;
            $disc_total = 0;
            $net_total = 0;
            $tax_total = 0;

            // Item baru atau update
            $detail = new SalesReturnDtl();

            $detail->return_id = $hdrId;
            $detail->product_id = trim($product_id);
            $detail->qty_return = $data['qty_return'];
            $detail->unit_price = $calculateReturn['items']->price;
            $detail->discount_amount = $calculateReturn['total_discount'];
            $detail->tax_amount = $calculateReturn['total_tax'];
            $detail->type_tax = $calculateReturn['type_tax'];
            $detail->tax_rate = $calculateReturn['items']->tax_rate;
            $detail->invoice_detail_id = $calculateReturn['items']->id;
            $detail->tax = $calculateReturn['items']->tax;
            $detail->save();

            $disc_total += $calculateReturn['total_discount'];
            $tax_total += $calculateReturn['total_tax'];
            $totalAmount += (($calculateReturn['items']->price * $data['qty_return']));
            $net_total += (($calculateReturn['items']->price * $data['qty_return']) - $calculateReturn['total_discount'] + $calculateReturn['total_tax']);

            $invoice = SalesInvoiceDtl::find($calculateReturn['items']->id);
            $total_return = $invoice->return_qty + $data['qty_return'];
            $invoice->return_qty = $total_return;

            $outstanding = $invoice->qty - $invoice->return_qty;
            if ($outstanding < 0) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Jumlah return melebihi outstanding invoice ',
                ]);
            }

            $invoice->save();

            /*menambah stock gudang */
            if ($data['return_type'] == 'good') {
                $so_detail = SalesOrderDetail::find($invoice->so_detail_id);
                $qtyBaseUnit = getSmallestUnit(trim($product_id), $so_detail->unit, $data['qty_return']);
                $productUomLevel1 = ProductUom::where('product', trim($product_id))->where('level', '1')->first();
                $qtyBaseUnit = $qtyBaseUnit['qty_in_base_unit'];

                $value['product'] = trim($product_id);
                stockUpdate(
                    $hdrId,
                    $invoice->warehouse_id,
                    trim($product_id),
                    $productUomLevel1->unit_tujuan,
                    $qtyBaseUnit,
                    $value,
                    'add',
                    'sales_return'
                );

                // recalculateFrom('0', date('Y-m-d'), date('Y-m-d'), $invoice->warehouse_id, null, $product_id);
            }
            /*menambah stock gudang */

            $updateHdr = SalesReturnHdr::find($hdrId);
            $updateHdr->total_return_value = $net_total;
            $updateHdr->save();

            $currency = Currency::where('code', 'IDR')->first();
            $currencyId = $currency->id;

            DB::commit();

            $result['is_valid'] = true;
            $result['message'] = 'Data berhasil disimpan';
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
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

            $header = SalesReturnHdr::find($id);

            if (! $header) {
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Sales Return tidak ditemukan.',
                ]);
            }

            if ($header->status == 'CANCELED') {
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Sales Return sudah dibatalkan.',
                ]);
            }

            // ambil semua detail termasuk yg sudah deleted
            $details = SalesReturnDtl::where('return_id', $id)->whereNull('deleted')->get();

            foreach ($details as $dt) {

                $invoice = SalesInvoiceDtl::find($dt->invoice_detail_id);

                if ($invoice) {
                    // Kembalikan return_qty
                    $invoice->return_qty = $invoice->return_qty - $dt->qty_return;

                    if ($invoice->return_qty < 0) {
                        DB::rollBack();

                        return response()->json([
                            'is_valid' => false,
                            'message' => 'Cancel gagal: return qty menjadi minus.',
                        ]);
                    }

                    $invoice->save();

                    /*mengurangi stock gudang */
                    $so_detail = SalesOrderDetail::find($invoice->so_detail_id);
                    $qtyBaseUnit = getSmallestUnit($dt->product_id, $so_detail->unit, $dt->qty_affected);
                    $productUomLevel1 = ProductUom::where('product', $dt->product_id)->where('level', '1')->first();
                    $qtyBaseUnit = $qtyBaseUnit['qty_in_base_unit'];

                    $value['product'] = $dt->product_id;
                    stockUpdate(
                        $id,
                        $invoice->warehouse_id,
                        $dt->product_id,
                        $productUomLevel1->unit_tujuan,
                        $qtyBaseUnit,
                        $value,
                        'min',
                        'sales_return_cancel'
                    );
                    // recalculateFrom('0', date('Y-m-d'), date('Y-m-d'), $invoice->warehouse_id, null, $dt->product_id);
                    /*mengurangi stock gudang */
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
                'message' => 'Sales Return berhasil dibatalkan.',
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
        $datadb = DB::table($this->getTableName() . ' as m')
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

        return view('web.sales_return.modal.confirmdelete', $data);
    }

    public function showModalCustomer(Request $request)
    {
        $data = $request->all();

        return view('web.sales_return.modal.datacustomer', $data);
    }

    public function showModalInvoice(Request $request)
    {
        $data = $request->all();

        return view('web.sales_return.modal.datainvoice', $data);
    }

    public function getProductInvoice(Request $request)
    {
        $data = $request->all();
        $invoice = $data['invoice'];
        $datadb = DB::table('sales_invoice_detail as sid')
            ->select(
                'sid.id',
                'sid.product_id',
                'sid.qty',
                'sid.price',
                'sid.discount',
                'sid.subtotal',
                'sid.return_qty',
                'p.code as product_code',
                'p.name as product_name',
                'sid.tax',
                'sid.tax_amount',
                'sid.tax_rate',
                'sid.type_tax',
                DB::raw('(sid.qty - sid.return_qty) AS outstanding_can_return'),
            )
            ->join('product as p', 'p.id', 'sid.product_id')
            ->whereNull('sid.deleted')            // tidak termasuk deleted
            ->where('sid.invoice_id', $invoice)
            ->get();
        // echo '<pre>';
        // print_r($datadb);die;

        $data['data'] = $datadb;

        return view('web.sales_return.datainvoiceoutstanding', $data);
    }

    public function posted(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;

        DB::beginTransaction();
        try {

            $menu = SalesReturnHdr::find($data['id']);
            $menu->updated_by = session('user_id');
            $menu->status = 'POSTED';
            $menu->save();

            if ($menu->platform == 'mobile') {
                $penjualanAcc = AccountMapping::where('module', 'SALES_RETURN')
                    ->where('account_type', 'penjualan barang')
                    ->with('account') // kalau kamu pakai relasi
                    ->first();

                $ppnKeluaranAcc = AccountMapping::where('module', 'SALES_RETURN')
                    ->where('account_type', 'ppn keluaran')
                    ->with('account')
                    ->first();

                $discAcc = AccountMapping::where('module', 'SALES_RETURN')
                    ->where('account_type', 'diskon penjualan')
                    ->with('account')
                    ->first();

                $kasBankAcc = AccountMapping::where('module', 'SALES_RETURN')
                    ->where('account_type', 'kas bank')
                    ->with('account')
                    ->first();

                $depositAcc = AccountMapping::where('module', 'SALES_RETURN')
                    ->where('account_type', 'deposit pelanggan')
                    ->with('account')
                    ->first();

                if (! $penjualanAcc || ! $ppnKeluaranAcc || ! $discAcc || ! $kasBankAcc || ! $depositAcc) {
                    DB::rollBack();

                    return response()->json([
                        'is_valid' => false,
                        'message' => 'Konfigurasi akun untuk Sales Return belum lengkap.',
                    ]);
                }

                $currency = Currency::where('code', 'IDR')->first();
                $currencyId = $currency->id;

                $reference = $menu->return_number;

                $salesReturnDetails = SalesReturnDtl::where('return_id', $data['id'])->whereNull('deleted')->get()->toArray();

                $totalAmount = 0;
                $tax_total = 0;
                $net_total = 0;
                $disc_total = 0;

                foreach ($salesReturnDetails as $value) {
                    $disc_total += $value['discount_amount'];
                    $tax_total += $value['tax_amount'];
                    $totalAmount += (($value['unit_price'] * $value['qty_return']));
                    $net_total += (($value['unit_price'] * $value['qty_return']) - $value['discount_amount'] + $value['tax_amount']);
                }

                postingGL($reference, $penjualanAcc->account_id, $penjualanAcc->account->account_name, $penjualanAcc->cd, $totalAmount, $currencyId);
                postingGL($reference, $ppnKeluaranAcc->account_id, $ppnKeluaranAcc->account->account_name, $ppnKeluaranAcc->cd, ($tax_total), $currencyId);
                postingGL($reference, $discAcc->account_id, $discAcc->account->account_name, $discAcc->cd, ($disc_total), $currencyId);
                if ($menu->return_type == 'REFUND') {
                    postingGL($reference, $kasBankAcc->account_id, $kasBankAcc->account->account_name, $kasBankAcc->cd, ($net_total), $currencyId);
                }
                if ($menu->return_type == 'DEPOSIT') {
                    postingGL($reference, $depositAcc->account_id, $depositAcc->account->account_name, $depositAcc->cd, ($net_total), $currencyId);
                }
            }
            DB::commit();

            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }

        return response()->json($result);
    }
}
