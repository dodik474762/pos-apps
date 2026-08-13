<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\AccountMapping;
use App\Models\Master\Coa;
use App\Models\Master\ProductUom;
use App\Models\Master\Tax;
use Illuminate\Http\Request;
use App\Models\Transaction\DeliveryOrderDtl;
use App\Models\Transaction\DeliveryOrderHeader;
use App\Models\Transaction\DeliveryOrderStatusLog;
use App\Models\Transaction\SalesInvoiceDtl;
use App\Models\Transaction\SalesInvoiceHeader;
use App\Models\Transaction\SalesInvoiceTagihan;
use App\Models\Transaction\SalesOrderHeader;
use App\Models\Transaction\SalesOrderDetail;
use App\Models\Transaction\SalesPaymentDtl;
use Illuminate\Support\Facades\DB;

class SalesInvoiceController extends Controller
{
    public function getTableName()
    {
        return 'sales_invoice_header';
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
                'cc.code as customer_code',
                'do.do_number',
                'do.do_date',
                'w.name as warehouse_name',
                'top.remarks as payment_terms',
                DB::raw('m.total_amount - COALESCE(m.amount_paid,0) as amount_remaining'),
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('delivery_order_header as do', 'do.id', 'm.do_id')
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            ->leftJoin('term_of_payment as top', 'top.id', 'cc.payment_terms')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            if (isset($_POST['start_date']) && $_POST['start_date'] != '') {
                $datadb->where('m.invoice_date', '>=', $_POST['start_date']);
            }
            if (isset($_POST['end_date']) && $_POST['end_date'] != '') {
                $datadb->where('m.invoice_date', '<=', $_POST['end_date']);
            }
            if (isset($_POST['belum_lunas']) && $_POST['belum_lunas'] == '1') {
                $datadb->whereNotIn('m.status', ['PAID', 'CANCELLED', 'CANCELED', 'paid', 'cancelled', 'canceled']);
            }
            if (isset($_POST['sudah_lunas']) && $_POST['sudah_lunas'] == '1') {
                $datadb->whereIn('m.status', ['PAID', 'paid']);
            }
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
                    $query->orWhere('top.remarks', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.code', 'LIKE', '%' . $keyword . '%');
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

    public function getDataTagihan()
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
                'cc.code as customer_code',
                'so.so_number',
                'so.so_date',
                'w.name as warehouse_name',
                'top.remarks as payment_terms',
                'sit.id as tagihan_id',
                'sit.tgl_tagih',
                DB::raw('m.total_amount - COALESCE(m.amount_paid,0) as amount_remaining')
            ])
            ->join('sales_invoice_tagihan as sit', 'sit.invoice_id', 'm.id')
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('sales_order_headers as so', 'so.id', 'm.sales_order')
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            ->leftJoin('term_of_payment as top', 'top.id', 'cc.payment_terms')
            ->whereNull('m.deleted')
            // ->where('m.id', 800)
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            if (isset($_POST['start_date']) && $_POST['start_date'] != '') {
                $datadb->where('m.invoice_date', '>=', $_POST['start_date']);
            }
            if (isset($_POST['end_date']) && $_POST['end_date'] != '') {
                $datadb->where('m.invoice_date', '<=', $_POST['end_date']);
            }
            if (isset($_POST['belum_lunas']) && $_POST['belum_lunas'] == '1') {
                $datadb->whereNotIn('m.status', ['PAID', 'CANCELLED', 'CANCELED', 'paid', 'cancelled', 'canceled']);
            }
            if (isset($_POST['sudah_lunas']) && $_POST['sudah_lunas'] == '1') {
                $datadb->whereIn('m.status', ['PAID', 'paid']);
            }
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.invoice_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.invoice_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('so.so_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('so.so_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.due_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('w.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('top.remarks', 'LIKE', '%' . $keyword . '%');
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

    public function getDataFromSO()
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
                'cc.code as customer_code',
                'so.so_number',
                'so.so_date',
                'w.name as warehouse_name',
                'top.remarks as payment_terms',
                'pl.packing_list_no',
                'do.do_number',
                DB::raw('m.total_amount - COALESCE(m.amount_paid,0) as amount_remaining')
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('sales_order_headers as so', 'so.id', 'm.sales_order')
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            ->leftJoin('term_of_payment as top', 'top.id', 'cc.payment_terms')
            ->leftJoin('delivery_order_header as do', function ($q) {
                return $q->on('do.so_id', 'so.id')
                    ->whereNull('do.deleted');
            })
            ->leftJoin('packing_list_do as pld', function ($q) {
                return $q->on('pld.delivery_order_id', 'do.id');
            })
            ->leftJoin('packing_list as pl', function ($q) {
                return $q->on('pl.id', 'pld.packing_list_id')
                    ->whereNull('pl.deleted')
                    ->where('pl.packing_date', '>', '2026-06-29');
            })
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            if (isset($_POST['start_date']) && $_POST['start_date'] != '') {
                $datadb->where('m.invoice_date', '>=', $_POST['start_date']);
            }
            if (isset($_POST['end_date']) && $_POST['end_date'] != '') {
                $datadb->where('m.invoice_date', '<=', $_POST['end_date']);
            }
            if (isset($_POST['belum_lunas']) && $_POST['belum_lunas'] == '1') {
                $datadb->whereNotIn('m.status', ['PAID', 'CANCELLED', 'CANCELED', 'paid', 'cancelled', 'canceled']);
            }
            if (isset($_POST['sudah_lunas']) && $_POST['sudah_lunas'] == '1') {
                $datadb->whereIn('m.status', ['PAID', 'paid']);
            }
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.invoice_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.invoice_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('so.so_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('so.so_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.due_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('w.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('top.remarks', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.nama_customer', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('pl.packing_list_no', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('do.do_number', 'LIKE', '%' . $keyword . '%');
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

    public function getDataFromSOCanceled()
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
                'cc.code as customer_code',
                'so.so_number',
                'so.so_date',
                'w.name as warehouse_name',
                'top.remarks as payment_terms',
                DB::raw('m.total_amount - COALESCE(m.amount_paid,0) as amount_remaining')
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('sales_order_headers as so', 'so.id', 'm.sales_order')
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            ->leftJoin('term_of_payment as top', 'top.id', 'cc.payment_terms')
            // ->whereNull('m.deleted')
            ->whereIn('m.status', ['CANCELLED', 'CANCELED'])
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.invoice_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.invoice_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('so.so_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('so.so_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.due_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('w.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('top.remarks', 'LIKE', '%' . $keyword . '%');
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

    public function getDataSo()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table('sales_order_headers as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'cc.nama_customer',
                'c.code as currency_code',
                'cc.address',
                'top.remarks as top_name'
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('currency as c', 'c.id', 'm.currency')
            ->leftJoin('term_of_payment as top', 'top.id', 'cc.payment_terms')
            ->leftJoin('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.sales_order', 'm.id')->whereNull('sih.deleted');
            })
            ->whereNull('m.deleted')
            ->where('m.total_amount', '>', 0)
            ->whereNotIn('m.status', ['canceled'])
            ->whereNull('sih.id')
            ->orderBy('m.id', 'asc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.so_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.so_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('top.remarks', 'LIKE', '%' . $keyword . '%');
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
        if (!empty($data['itemsChoose'])) {
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

        if (!empty($exceptPoDetailId)) {
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
        // print_r($data);
        // die;

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

            if (!$piutangAcc || !$penjualanAcc || !$discPenjualanAcc) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Konfigurasi akun untuk Sales Invoice belum lengkap.',
                ]);
            }

            if (empty($data['items'])) {
                DB::rollBack();
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Item tidak boleh kosong.',
                ]);
            }

            //cek jika ada tipe tax yang berbeda dalam 1 invoice
            if (count(array_unique(array_column($data['items'], 'type_tax'))) > 1) {
                // DB::rollBack();
                // return response()->json([
                //     'is_valid' => false,
                //     'message' => 'Tidak dapat menyimpan Sales Invoice dengan Tipe Tax yang berbeda dalam 1 invoice.',
                // ]);
            }

            //cek jika ada tax yang berbeda dalam 1 invoice
            if (count(array_unique(array_column($data['items'], 'tax'))) > 1) {
                // DB::rollBack();
                // return response()->json([
                //     'is_valid' => false,
                //     'message' => 'Tidak dapat menyimpan Sales Invoice dengan Tax yang berbeda dalam 1 invoice.',
                // ]);
            }

            $tax_amount = collect($data['items'])->where('remove', 0)->sum('tax_amount');

            $data['tax'] = $data['items'][0]['tax'] == '0' || $data['items'][0]['tax'] == '11' ? 1 : $data['items'][0]['tax'];
            $type_pajak = $data['items'][0]['type_tax'];
            $tax = Tax::find($data['tax']);
            if (empty($tax)) {
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

            list($cust_id, $cust_name) = explode('//', $data['customer_id']);

            if ($data['id'] == '') {
                $policyCreateInvoice = checkCustomerCreditLimit($cust_id, $data['id']);
                if (!$policyCreateInvoice['status']) {
                    DB::rollBack();
                    return response()->json([
                        'is_valid' => false,
                        'message' => $policyCreateInvoice['message']
                    ]);
                }
            }


            $data['total_amount'] = $subtotal;
            $header->invoice_date = $data['invoice_date'];
            $header->do_id = isset($data['do_id']) ? $data['do_id'] : null;
            $header->sales_order = isset($data['so_id']) ? $data['so_id'] : null;
            $header->warehouse_id = empty($data['do_id']) ? 1 : $do->warehouse_id;
            $header->customer_id = $cust_id;
            $header->subtotal = $subtotal;
            $header->discount_amount = 0;
            $header->tax_base = $tax->rate;
            $header->tax_id = $data['tax'];
            $header->is_packing = $data['is_packing'];
            $header->tax_amount = $tax_amount;
            $header->total_amount = $data['total_amount'];
            if ($data['id'] != '') {
                // $header->reprint = 1; //reprint
            }
            $header->save();

            $hdrId = $header->id;

            /*cek sudah ada pembayaran */
            if ($header->amount_paid > 0) {
                DB::rollBack();
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Invoice sudah ada pembayaran. tidak dapat mengedit invoice.',
                ]);
            }

            // === DETAIL ===
            $line_no = 1;
            $batalItem = false;
            $soIdBatal = [];
            $qtyChangedItems = []; // so_detail_id => new_qty
            foreach ($data['items'] as $item) {
                // Skip baris yang ditandai untuk dihapus
                if (!empty($item['remove']) && $item['remove'] == 1) {
                    if (!empty($item['id'])) {
                        $exist = SalesInvoiceDtl::find($item['id']);
                        if ($exist) {
                            $exist->deleted = now();
                            $exist->deleted_by = $userId;
                            $exist->save();

                            $batalItem = true;
                            $soIdBatal[] = $item['so_detail_id'];

                            // 🔥 Kembalikan stock karena item dibatalkan
                            $soDetailCancel = SalesOrderDetail::find($item['so_detail_id']);
                            if ($soDetailCancel) {
                                $qtyBaseUnitCancel = getSmallestUnit($item['product_id'], $soDetailCancel->unit, $item['qty']);
                                $productUomLevel1Cancel = ProductUom::where('product', $item['product_id'])->where('level', '1')->first();
                                if ($productUomLevel1Cancel) {
                                    if ($header->status == 'PACKED' || $header->status == 'PAID') {
                                        // stockUpdate($hdrId, $header->warehouse_id, $item['product_id'], $productUomLevel1Cancel->unit_tujuan, $qtyBaseUnitCancel['qty_in_base_unit'], $item, 'add', 'sales_invoice_cancel');
                                    }
                                }
                            }
                        }
                    }
                    continue;
                }

                // Deteksi perubahan qty
                if (!empty($item['qty_changed']) && $item['qty_changed'] == 1 && !empty($item['so_detail_id'])) {
                    $qtyChangedItems[$item['so_detail_id']] = $item['qty'];
                }

                // Item baru atau update
                // Item baru atau update
                $isNew = empty($item['id']);
                $oldQty = 0;

                $detail = $isNew
                    ? new SalesInvoiceDtl()
                    : SalesInvoiceDtl::find($item['id']);

                if (!$isNew && $detail) {
                    $oldQty = $detail->qty; // simpan qty lama sebelum di-overwrite
                }

                $detail->invoice_id = $hdrId;
                $detail->so_detail_id = $item['so_detail_id'];
                $detail->product_id = $item['product_id'];
                $detail->qty = $item['qty'];
                $detail->price = $item['price'];
                $detail->discount = $item['discount'];
                $detail->subtotal = $item['subtotal'];
                $detail->tax = $item['tax'];
                $detail->tax_amount = $item['tax_amount'];
                $detail->tax_rate = $item['tax_rate'] == '11' ? 1 : $item['tax_rate'];
                $detail->type_tax = $item['type_tax'];
                $detail->line_no = $line_no++;
                $detail->save();


                // 🔥 Update stock: kurangi stock sesuai qty
                $soDetailForUnit = SalesOrderDetail::find($item['so_detail_id']);
                if ($soDetailForUnit) {
                    // Hitung selisih qty supaya tidak double-kurangi saat edit invoice
                    $qtyDiff = $isNew ? $item['qty'] : ($item['qty'] - $oldQty);

                    if ($qtyDiff != 0) {
                        $qtyBaseUnit = getSmallestUnit($item['product_id'], $soDetailForUnit->unit, abs($qtyDiff));
                        $productUomLevel1 = ProductUom::where('product', $item['product_id'])->where('level', '1')->first();

                        if ($productUomLevel1) {
                            if ($header->status == 'PACKED' || $header->status == 'PAID') {
                                $stockType = $qtyDiff > 0 ? 'reduce' : 'add'; // qty naik = kurangi stock lebih banyak, qty turun = kembalikan sebagian
                                // stockUpdate($hdrId, $header->warehouse_id, $item['product_id'], $productUomLevel1->unit_tujuan, $qtyBaseUnit['qty_in_base_unit'], $item, $stockType, 'sales_invoice');
                            }
                        }
                    }
                }
                /*mapping coa */
            }

            if ($data['is_packing'] == '1') {
                if (!empty($do)) {
                    $do->status = 'CONFIRMED';
                    $do->save();
                }
            }

            if (!empty($data['do_id'])) {
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
            }


            $discountHeaderSo = 0;
            $so = empty($data['so_id']) ? SalesOrderHeader::find($do->so_id) : SalesOrderHeader::find($data['so_id']);
            if ($data['id'] == '') {
                $updateInv = SalesInvoiceHeader::where('id', $hdrId)->first();
                if ($so->payment_term == '' || $so->payment_term == 0) {
                    $updateInv->due_date = $data['invoice_date'];
                    $updateInv->save();
                } else {
                    $dueDate = date('Y-m-d', strtotime($data['invoice_date'] . ' + ' . $so->payment_term . ' days'));
                    $updateInv->due_date = $dueDate;
                    $updateInv->save();
                }

                $discountHeaderSo = $so->discount_amount == '' ? 0 : $so->discount_amount;
            }
            // echo '<pre>';
            // print_r($so);
            // die;

            if ($batalItem) {
                if ($discountHeaderSo > 0) {
                    DB::rollBack();
                    return response()->json([
                        'is_valid' => false,
                        'message' => 'Tidak dapat menghapus item karena sudah ada diskon, batalkan invoice terlebih dahulu'
                    ]);
                }
            }

            $header->subtotal = $subtotal;
            $header->discount_amount = $discountHeaderSo;
            $header->total_amount = $subtotal - $discountHeaderSo;
            $header->save();

            $currency = $so->currency;

            $reference = $header->invoice_number;
            if ($data['id'] != '') {
                cancelAllGL($reference);
            }

            /*check credit limit */
            $cl_check = canInvoiceProcessCL($cust_id, $subtotal);
            if (!$cl_check['status']) {
                DB::rollBack();
                return response()->json([
                    'is_valid' => false,
                    'message' => $cl_check['message']
                ]);
            }

            postingGL($reference, $piutangAcc->account_id, $piutangAcc->account->account_name, $piutangAcc->cd, ($subtotal - $discountHeaderSo), $currency);
            postingGL($reference, $penjualanAcc->account_id, $penjualanAcc->account->account_name, $penjualanAcc->cd, ($subtotal), $currency);
            postingGL($reference, $discPenjualanAcc->account_id, $discPenjualanAcc->account->account_name, $discPenjualanAcc->cd, ($discountHeaderSo), $currency);

            // if ($type_pajak == 'exclude') {
            //     if (!empty($ppnAccount)) {
            //         $ppnAccount->dc = $ppnAccount->normal_balance == 'Debit' ? 'D' : 'C';
            //         postingGL($reference, $ppnAccount->id, $ppnAccount->account_name, $ppnAccount->dc, ($tax_amount), $currency);
            //     }
            // }

            /*GENERATE SO */
            if ($data['id'] != '') {
                $needGenerateSo = $batalItem || !empty($qtyChangedItems);
                if ($needGenerateSo) {
                    $soProcess = new SalesOrderController();
                    $so_payload['so_date'] = $so->so_date;
                    $so_payload['customer_id'] = $so->customer_id;
                    $so_payload['currency'] = $so->currency;
                    $so_payload['salesman'] = $so->salesman;
                    $so_payload['payment_term'] = $so->payment_term;
                    $so_payload['warehouse'] = $so->warehouse;
                    $so_payload['remarks'] = 'KOREKSI INVOICE NO ' . $header->invoice_number;

                    // Ambil SO detail, exclude yang dibatal, override qty yang berubah
                    $soDetails = SalesOrderDetail::where('sales_order_id', $so->id)
                        ->select(['*', 'unit as unit_id', 'unit_price as price', DB::raw('qty * unit_price as total_price')])
                        ->whereNull('deleted')
                        ->whereNotIn('id', $soIdBatal)
                        ->get()
                        ->map(function ($row) use ($qtyChangedItems) {
                            $rowArray = $row->toArray();
                            // Override qty jika ada perubahan dari invoice
                            if (!empty($qtyChangedItems[$row->id])) {
                                $rowArray['qty'] = $qtyChangedItems[$row->id];
                                $rowArray['total_price'] = $rowArray['qty'] * $rowArray['price'];
                            }
                            return $rowArray;
                        })
                        ->toArray();

                    // echo '<pre>';
                    // print_r($soDetails);
                    // die;

                    $so_payload['items'] = $soDetails;
                    $so_payload['need_generate_so'] = $needGenerateSo;

                    // Buat Request object manual
                    $request = new Request();
                    $request->replace($so_payload);

                    $response = $soProcess->submit($request);

                    // Ambil hasil response jika perlu
                    $resultSo = json_decode($response->getContent(), true);
                    // echo '<pre>';
                    // print_r($resultSo);
                    // die;

                    if (!$resultSo['is_valid']) {
                        // handle error
                        throw new \Exception('Gagal generate SO: ' . $resultSo['message']);
                    }

                    /*update so old to correction */
                    $updateOldSo = SalesOrderHeader::find($so->id);
                    $oldStatusSo = $updateOldSo->status;

                    $updateOldSo->status = 'correction';
                    $updateOldSo->save();

                    $newSoId = $resultSo['so_id'];
                    $sodb = SalesOrderHeader::find($newSoId);
                    $sodb->ref_so_id = $so->id;
                    $sodb->status = $oldStatusSo;
                    $sodb->save();

                    /*update do */
                    $doHeaders = DeliveryOrderHeader::where('so_id', $so->id)->whereNull('deleted')->first();
                    $doIdHeader = 0;
                    if (!empty($doHeaders)) {
                        // DB::rollBack();
                        // return response()->json([
                        //     'is_valid' => false,
                        //     'message' => 'DO sudah dibuat, tidak bisa diubah, batalkan DO terlebih dahulu. No DO : ' . $doHeaders->do_number
                        // ]);

                        $doHeaders->so_id = $newSoId;
                        $doHeaders->save();

                        $doIdHeader = $doHeaders->id;

                        /*update do detail */
                        $doDetail = DeliveryOrderDtl::where('do_id', $doIdHeader)->get();
                        foreach ($doDetail as $row) {
                            $soDetailNew = SalesOrderDetail::where('sales_order_id', $newSoId)->where('product_id', $row->product_id)->first();
                            if (!empty($soDetailNew)) {
                                $row->so_detail_id = $soDetailNew->id;
                                $row->save();
                            }
                        }
                    }

                    /*generate invoice */
                    $soToNewInvoice = $soProcess->getAllSalesNotInvoice($so_payload['so_date'], $so_payload['so_date'], '', [$newSoId]);
                    $newInvoiceId = 0;
                    $newInvoiceNumber = '';
                    foreach ($soToNewInvoice as $v) {
                        $process = $soProcess->saveInvoice($v);
                        $process = json_decode($process->getContent(), true);
                        if ($process['is_valid']) {
                            $newInvoiceId = $process['invoice_id'];
                            $newInvoiceNumber = $process['invoice_number'];
                        } else {
                            DB::rollBack();
                            return response()->json([
                                'is_valid' => false,
                                'message' => 'Gagal Process invoice ' . $process['message']
                            ]);
                        }
                    }

                    $cancelInvoiceOld = SalesInvoiceHeader::find($data['id']);

                    $dueDate = date('Y-m-d', strtotime($data['invoice_date'] . ' + ' . $so->payment_term . ' days'));
                    if ($newInvoiceId == 0) {
                        DB::rollBack();
                        return response()->json([
                            'is_valid' => false,
                            'message' => 'Gagal Process invoice ' . $reference . ' new invoice : ' . $newInvoiceId
                        ]);
                    }
                    $updateInvoiceHeader = SalesInvoiceHeader::find($newInvoiceId);
                    // echo '<pre>';
                    // print_r($updateInvoiceHeader);
                    // die;
                    $updateInvoiceHeader->invoice_number = $reference;
                    $updateInvoiceHeader->invoice_date = $data['invoice_date'];
                    $updateInvoiceHeader->due_date = $dueDate;
                    $updateInvoiceHeader->status = $cancelInvoiceOld->status; //ini sesuai kondisi status invoice sebelumnya
                    $updateInvoiceHeader->save();

                    $cancelInvoiceOld->status = 'CANCELED';
                    $cancelInvoiceOld->deleted = date('Y-m-d H:i:s');
                    $cancelInvoiceOld->deleted_by = session('user_id');
                    $cancelInvoiceOld->save();

                    cancelAllGL($reference);
                    updateAllGL($reference, $newInvoiceNumber);
                }
            }


            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Sales Invoice berhasil disimpan';
            $result['so_id'] = $hdrId;
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['is_valid'] = false;
            $result['message'] = $th->getMessage() . ' ' . $th->getLine();
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

            if ($menu->amount_paid > 0) {
                DB::rollBack();
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Invoice sudah ada pembayaran. tidak dapat mengedit invoice.',
                ]);
            }
            // if ($menu->status != 'DRAFT' && $menu->status != 'PACKED') {
            //     DB::rollBack();
            //     $result['message'] = 'Tidak dapat dihapus karena status sudah ' . $menu->status;
            //     return response()->json($result);
            // }

            // Soft delete header


            $menu->deleted = date('Y-m-d H:i:s');
            $menu->deleted_by = session('user_id');
            $menu->status = 'CANCELED';
            $menu->save();

            // Ambil detail
            $items = SalesInvoiceDtl::where('invoice_id', $data['id'])->get();

            $do = DeliveryOrderHeader::find($menu->do_id);
            if (empty($do)) {
                $so = SalesOrderHeader::find($menu->sales_order);
                $so->status = 'draft';
                $so->save();
            } else {
                $so = SalesOrderHeader::find($do->so_id);
                $so->status = 'draft';
                $so->save();
            }

            foreach ($items as $value) {

                $oldDetail = SalesInvoiceDtl::find($value->id);

                if ($oldDetail) {
                    // 🔥 Kembalikan stock sebelum soft delete
                    $soDetailRestore = SalesOrderDetail::find($oldDetail->so_detail_id);
                    if ($soDetailRestore) {
                        $qtyBaseUnitRestore = getSmallestUnit($oldDetail->product_id, $soDetailRestore->unit, $oldDetail->qty);
                        $productUomLevel1Restore = ProductUom::where('product', $oldDetail->product_id)->where('level', '1')->first();
                        if ($productUomLevel1Restore) {
                            if ($menu->status == 'PACKED' || $menu->status == 'PAID') {
                                // stockUpdate($menu->id, $menu->warehouse_id, $oldDetail->product_id, $productUomLevel1Restore->unit_tujuan, $qtyBaseUnitRestore['qty_in_base_unit'], (array) $oldDetail, 'add', 'sales_invoice_cancel');
                            }
                        }
                    }

                    $value->deleted = date('Y-m-d H:i:s');
                    $value->deleted_by = session('user_id');
                    $value->save();
                }
            }

            // Update Delivery Order
            if (!empty($do)) {
                $do->status = 'DRAFT';
                $do->save();
            }

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

    public function posted(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;

        DB::beginTransaction();
        try {

            $menu = SalesInvoiceHeader::find($data['id']);

            if ($menu->status != 'DRAFT') {
                DB::rollBack();
                $result['message'] = 'Tidak dapat diposting karena status sudah tidak DRAFT';
                return response()->json($result);
            }
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

    public function lanjutKoreksi(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;

        $salesPayment = new SalesPaymentController();

        DB::beginTransaction();
        try {
            $menu = SalesInvoiceHeader::find($data['id']);

            $salesPaymentDtl = SalesPaymentDtl::where('sales_payment_detail.invoice_id', $menu->id)
                ->join('sales_payment_header', 'sales_payment_header.id', '=', 'sales_payment_detail.payment_id')
                ->select(['sales_payment_header.id as payment_id', 'sales_payment_header.payment_code', 'sales_payment_header.verificator_id'])
                ->whereNull('sales_payment_detail.deleted')
                ->whereNull('sales_payment_header.deleted')
                ->get();

            foreach ($salesPaymentDtl as $value) {
                if ($value->verificator_id != '') {
                    DB::rollBack();
                    $result['message'] = 'Payment ' . $value->payment_code . ' sudah terverifikasi';
                    return response()->json($result);
                }

                $payload['id'] = $value->payment_id;
                $request_cancel = new Request();
                $request_cancel->replace($payload);

                $response = $salesPayment->confirmDelete($request_cancel);

                // Ambil hasil response jika perlu
                $resultCancel = json_decode($response->getContent(), true);
                if (!$resultCancel['is_valid']) {
                    DB::rollBack();
                    $result['message'] = $resultCancel['message'];
                    return response()->json($result);
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

    public function cancelTagihan(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;

        DB::beginTransaction();
        try {

            $menu = SalesInvoiceTagihan::find($data['id']);
            $menu->delete();

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }

        return response()->json($result);
    }

    public function tagihkan(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;

        DB::beginTransaction();
        try {

            $menu = SalesInvoiceHeader::find($data['id']);

            $tgl_tagihan = $data['tgl_tagihan'];
            $tagihan = SalesInvoiceTagihan::where('invoice_id', $data['id'])->whereNull('deleted')
                ->where('tgl_tagih', $tgl_tagihan)->first();


            $sales_order = SalesOrderHeader::where('id', $menu->sales_order)->first();
            if (empty($sales_order)) {
                DB::rollBack();
                $result['message'] = 'Sales Order ' . $menu->sales_order . ' tidak ditemukan';
                return response()->json($result);
            }
            $tagih = empty($tagihan) ? new SalesInvoiceTagihan() : $tagihan;
            $tagih->invoice_id = $data['id'];


            $newTagihan = new SalesInvoiceTagihan();
            $newTagihan->invoice_id = $data['id'];
            $newTagihan->tgl_tagih = $tgl_tagihan;
            $newTagihan->customer_id = $menu->customer_id;
            $newTagihan->salesman_id = $data['salesman'];
            $newTagihan->save();

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
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'do.do_number',
                'c.nama_customer',
                'so.so_number',
                'so.so_date'
            ])
            ->leftJoin('delivery_order_header as do', 'do.id', 'm.do_id')
            ->leftJoin('sales_order_headers as so', 'so.id', 'm.sales_order')
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();

        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();

        return view('web.sales_invoice.modal.confirmdelete', $data);
    }

    public function showModalDO(Request $request)
    {
        $data = $request->all();

        return view('web.sales_invoice.modal.datado', $data);
    }

    public function showModalSO(Request $request)
    {
        $data = $request->all();

        return view('web.sales_invoice.modal.dataso', $data);
    }

    public function getDoDetail(Request $request)
    {
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
                'sod.subtotal', // subtotal sudah net (after discount)
                'p.type_tax',
                'p.tax_sale',
                't.rate as tax',
                // Hitung tax_amount sesuai tipe pajak
                DB::raw("
                CASE
                    WHEN p.type_tax = 'include' THEN (sod.subtotal - (sod.subtotal / (1 + t.rate/100)))
                    WHEN p.type_tax = 'exclude' THEN (sod.subtotal * (t.rate/100))
                    ELSE 0
                END AS tax_amount
            "),
                // Hitung line total = subtotal + tax_amount
                DB::raw("
                sod.subtotal +
                CASE
                    WHEN p.type_tax = 'include' THEN (sod.subtotal - (sod.subtotal / (1 + t.rate/100)))
                    WHEN p.type_tax = 'exclude' THEN (sod.subtotal * (t.rate/100))
                    ELSE 0
                END AS line_total
            ")
            ])
            ->join('sales_order_details as sod', 'sod.id', 'delivery_order_detail.so_detail_id')
            ->join('product as p', 'p.id', 'delivery_order_detail.product_id')
            ->join('tax as t', 't.id', 'p.tax_sale')
            ->join('unit as u', 'u.id', 'delivery_order_detail.uom')
            ->whereNull('delivery_order_detail.deleted')
            ->whereNull('sod.deleted')
            // ->whereNull('sod.free_for')
            ->get();

        $data['data'] = $datadb;

        return view('web.sales_invoice.datadodetail', $data);
    }

    public function getSoDetail(Request $request)
    {
        $data = $request->all();
        $datadb = SalesOrderDetail::where('sales_order_details.sales_order_id', $data['so_id'])
            ->select([
                'sales_order_details.*',
                'u.name as unit_name',
                'p.code as product_code',
                'p.name as product_name',
                'p.type_tax',
                'p.tax_sale',
                't.rate as tax',
                'soh.discount_amount as discount_amount_header',
                // Hitung tax_amount sesuai tipe pajak
                DB::raw("
                CASE
                    WHEN p.type_tax = 'include' THEN (sales_order_details.subtotal - (sales_order_details.subtotal / (1 + t.rate/100)))
                    WHEN p.type_tax = 'exclude' THEN (sales_order_details.subtotal * (t.rate/100))
                    ELSE 0
                END AS tax_amount
            "),
                // Hitung line total = subtotal + tax_amount
                DB::raw("
                sales_order_details.subtotal +
                CASE
                    WHEN p.type_tax = 'include' THEN (sales_order_details.subtotal - (sales_order_details.subtotal / (1 + t.rate/100)))
                    WHEN p.type_tax = 'exclude' THEN (sales_order_details.subtotal * (t.rate/100))
                    ELSE 0
                END AS line_total
            ")
            ])
            ->join('sales_order_headers as soh', 'soh.id', 'sales_order_details.sales_order_id')
            ->join('product as p', 'p.id', 'sales_order_details.product_id')
            ->join('tax as t', 't.id', 'p.tax_sale')
            ->join('unit as u', 'u.id', 'sales_order_details.unit')
            ->whereNull('sales_order_details.deleted')
            ->get();

        $data['data'] = $datadb;

        return view('web.sales_invoice.datasodetail', $data);
    }

    public function getOutstandingInvoice(Request $request)
    {
        $data = $request->all();
        $customerId = isset($data['customer']) ? ($data['customer'] == '' ? '0' : $data['customer']) : '0';
        try {
            $customers = explode(',', $customerId);
            $customers = array_unique($customers);
        } catch (\Throwable $th) {
            //throw $th;
            $customers = [];
        }

        $invoicesIds = [];
        $plIds = [];
        if ($customerId == '0' || empty($customers)) {
            /*driver invoice */
            $pl = new PackingListController();
            $packingListCustomer = $pl->getDataPackingList($request)->original;
            if (!empty($packingListCustomer['data'])) {
                foreach ($packingListCustomer['data'] as $key => $value) {
                    $customers[] = $value->customer_id;
                    $invoicesIds[] = $value->invoice_id;
                    $plIds[] = $value->id;
                }

                $customers = array_unique($customers);
            }
            /*driver invoice */
        }

        if (isset($data['akses'])) {
            if (strtolower($data['akses']) != 'administrator' && strtolower($data['akses']) != 'driver') {
                $tagihanOther = SalesInvoiceTagihan::where('tgl_tagih', date('Y-m-d'))->where('salesman_id', $data['users'])->get();
                $customers = array_merge($customers, $tagihanOther->pluck('customer_id')->unique()->toArray());
                $customers = array_filter(array_unique($customers));
            }
        }

        // $customers = [
        //     2335
        // ];
        $result['message'] = '';
        $result['is_valid'] = true;
        try {
            //code...
            $datadb = DB::table('sales_invoice_header as sih')
                ->select(
                    'sih.id',
                    'sih.invoice_number',
                    'sih.invoice_date',
                    'sih.customer_id',
                    'sih.total_amount',
                    'sih.discount_amount',
                    'sih.subtotal',
                    'sih.amount_paid',
                    'c.code as customer_code',
                    'c.nama_customer',
                    'sih.status',
                    DB::raw('(sih.subtotal - sih.discount_amount) AS total_before_discount'),
                    DB::raw('(sih.total_amount - sih.amount_paid) AS outstanding_amount')
                )
                ->join('customer as c', 'c.id', '=', 'sih.customer_id')
                ->join('sales_order_headers as soh', function ($q) {
                    return $q->on('soh.id', 'sih.sales_order')->whereNull('soh.deleted');
                })
                ->join('delivery_order_header as doh', function ($q) {
                    return $q->on('doh.so_id', 'soh.id')->whereNull('doh.deleted');
                })
                // ->whereIn('sih.status', ['POSTED', 'PARTIAL PAID', 'PACKED', 'DRAFT'])       // hanya invoice yang sudah diposting
                ->whereNotIn('sih.status', ['CANCELED'])
                ->whereNull('sih.deleted')            // tidak termasuk deleted
                ->having('outstanding_amount', '>', 0);  // hanya invoice yang masih punya sisa tagihan

            // echo '<pre>';
            // print_r($plIds);
            // die;
            if (!empty($plIds)) {
                $datadb->join('packing_list_do as pld', function ($q) {
                    return $q->on('pld.delivery_order_id', 'doh.id');
                });
                $datadb->whereIn('pld.id', $plIds);
            }
            $datadb->whereIn('sih.customer_id', $customers);
            // $datadb->whereIn('sih.customer_id', [290]);

            if (isset($data['akses'])) {
                if (strtolower($data['akses']) != 'driver' && strtolower($data['akses']) != 'administrator') {
                    $datadb->where('c.payment_terms', '!=', '3');
                    $datadb->where('soh.salesman', $data['users']);
                }
            }

            if (!empty($invoicesIds)) {
                $datadb->whereIn('sih.id', $invoicesIds);
            }

            $datadb = $datadb->get();

            // echo '<pre>';
            // print_r($datadb);
            // die;

            foreach ($datadb as $invoice) {
                $invoice->detail_item = DB::table('sales_invoice_detail as sid')
                    ->select([
                        'sid.id',
                        'p.id as product_id',
                        'sid.qty',
                        'sid.price as unit_price',
                        'sid.subtotal',
                        'p.code as product_code',
                        'p.name as product_name',
                        'u.name as unit_name'

                    ])
                    ->join('product as p', 'p.id', 'sid.product_id')
                    ->join('sales_order_details as sod', 'sod.id', 'sid.so_detail_id')
                    ->join('unit as u', 'u.id', 'sod.unit')
                    ->where('sid.invoice_id', $invoice->id)
                    ->whereNull('sid.deleted')
                    ->get();
            }

            $result['message'] = 'Success';
            $result['customers'] = $customers;
        } catch (\Throwable $th) {
            $result['is_valid'] = false;
            $result['message'] = $th->getMessage();
        }

        $result['data'] = $datadb;
        return response()->json($result);
    }
}
