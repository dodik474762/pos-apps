<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\Currency;
use App\Models\Master\Customer;
use App\Models\Master\ProductDisc;
use App\Models\Master\ProductFreeGood;
use App\Models\Master\ProductUom;
use App\Models\Master\ProductUomPrice;
use App\Models\Master\Unit;
use App\Models\Transaction\SalesOrderDetail;
use App\Models\Transaction\SalesOrderHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    public function getTableName()
    {
        return 'sales_order_headers';
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
                'c.code as currency_code',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->join('currency as c', 'c.id', 'm.currency')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.so_number', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.so_date', 'LIKE', '%'.$keyword.'%');
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
            // Pastikan currency default ada
            $currency = Currency::where('code', 'IDR')->first();
            if (!$currency) {
                DB::rollBack();
                return response()->json([
                    'is_valid' => false,
                    'message' => 'Currency IDR tidak ditemukan'
                ]);
            }

            // === HEADER ===
            $header = empty($data['id'])
                ? new SalesOrderHeader()
                : SalesOrderHeader::find($data['id']);

            if (empty($data['id'])) {
                $header->so_number = generateNoSO(); // misal helper
                $header->created_by = $userId;
                $header->status = 'draft';
            }

            $header->so_date = $data['so_date'];
            $header->customer_id = $data['customer_id'];
            $header->payment_term = $data['payment_term'] ?? null;
            $header->salesman = $data['salesman'] ?? null;
            $header->currency = $data['currency'];
            $header->remarks = $data['remarks'] ?? null;
            $header->total_amount = 0; // akan dihitung ulang di bawah
            $header->save();

            $hdrId = $header->id;
            $grandTotal = 0;

            // === DETAIL ===
            foreach ($data['items'] as $item) {
                // Skip baris yang ditandai untuk dihapus
                if (!empty($item['remove']) && $item['remove'] == 1) {
                    if (!empty($item['id'])) {
                        $exist = SalesOrderDetail::find($item['id']);
                        if ($exist && $exist->status !== 'draft') {
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
                    ? new SalesOrderDetail()
                    : SalesOrderDetail::find($item['id']);

                $detail->sales_order_id = $hdrId;
                $detail->product_id = $item['product_id'];
                $detail->qty = $item['qty'];
                $detail->unit = $item['unit_id'];
                $detail->unit_price = $item['price'];
                $detail->discount_type = $item['disc_percent'] == 0 ? 'nominal' : 'percent';
                $detail->discount_percent = $item['disc_percent'];
                $detail->discount_amount = $item['disc_amount'];
                $detail->subtotal = $item['subtotal'];
                $detail->is_free_good = $item['is_freegood'] ?? 0;
                $detail->free_for = $item['free_for'] ?? null;
                $detail->status = $detail->status ?? 'draft';
                $detail->save();

                // Hanya tambahkan ke total jika bukan free good
                if (empty($item['is_freegood'])) {
                    $grandTotal += $item['subtotal'];
                }
            }

            // Update total header
            $header->total_amount = $grandTotal;
            $header->save();

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Sales Order berhasil disimpan';
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
            // code...
            $menu = SalesOrderHeader::find($data['id']);
            if ($menu->status != 'draft') {
                DB::rollBack();
                $result['message'] = 'Tidak dapat dihapus karena status sudah tidak draft';

                return response()->json($result);
            }
            $menu->deleted = date('Y-m-d H:i:s');
            $menu->save();

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
            ])->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();

        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();

        return view('web.sales_order.modal.confirmdelete', $data);
    }

    public function showDataProduct(Request $request)
    {
        $data = $request->all();

        return view('web.product.modal.dataproduct', $data);
    }

    public function showDiscountProduct(Request $request)
    {
        $data = $request->all();
        $data['message'] = '';
        try {
            $data['disc'] = $this->getDataDiscProduct($data);
        } catch (\Throwable $th) {
            $data['message'] = $th->getMessage();
        }

        return view('web.product.datainfoprogramdisk', $data);
    }

    public function showDiscountFreeProduct(Request $request)
    {
        $data = $request->all();
        $data['message'] = '';
        try {
            $data['disc'] = $this->getProductFreeGood($data);
        } catch (\Throwable $th) {
            $data['message'] = $th->getMessage();
        }

        // echo '<pre>';
        // print_r($data);die;

        return view('web.product.datainfoprogramfreegood', $data);
    }

    public function showQtySmallestProduct(Request $request)
    {
        $data = $request->all();
        $data['message'] = '';
        $data['data_uom'] = [];
        try {
            $data_uom = ProductUom::whereNull('product_uom.deleted')->where('product_uom.product', $data['produk_id'])
            ->select(['product_uom.*', 'p.name as product_name', 'p.code'])
            ->join('product as p', 'p.id', 'product_uom.product')
            ->orderBy('product_uom.level')->get();
            $units = collect($data_uom)->pluck('unit_tujuan')->unique()->values()->all();
            $unit = Unit::whereNull('deleted')
            ->whereIn('id', $units)
            ->get();


            $data_result = [];
            foreach ($data_uom as $key => $value) {
                $conversion = getSmallestUnit($value->product, $value->unit_tujuan, 1);
                $unit_name = collect($unit)->where('id', $value->unit_tujuan)->first();
                $conversion['unit'] = $value->unit_tujuan;
                $conversion['unit_name'] = $unit_name->name;
                $conversion['product'] = $value->product;
                $conversion['product_name'] = $value->product_name;
                $conversion['code'] = $value->code;
                $data_result[] = $conversion;
            }

            $data['data_uom'] = $data_result;
        } catch (\Throwable $th) {
            $data['message'] = $th->getMessage();
        }

        // echo '<pre>';
        // print_r($data);die;

        return view('web.product.datauom', $data);
    }

    public function getDiscount(Request $request)
    {
        $discount = ProductDisc::valid(
            $request->product_id,
            $request->unit_id,
            $request->customer_id,
            $request->customer_category_id,
            $request->qty
        )->first();

        return response()->json([
            'found' => $discount ? true : false,
            'data' => $discount,
        ]);
    }

    function getProductFreeGood($params)
    {
        $data = $params;
        $product_id = $data['produk_id'];
        $unit_id = $data['unit'];
        $customer_id = $data['customer'];
        $customerdb = Customer::find($customer_id);
        $customer_category_id = $customerdb->customer_category;

        $datadb = ProductFreeGood::where('product_free_good.product', $product_id)
            // ->where('unit', $unit_id)
            // ->where(function ($q) use ($customer_id) {
            //     $q->where('customer', $customer_id)->orWhereNull('customer');
            // })
            // ->where(function ($q) use ($customer_category_id) {
            //     $q->where('customer_category', $customer_category_id)->orWhereNull('customer_category');
            // })
            ->select([
                'product_free_good.*',
                'p.name as product_name',
                'fp.name as free_product_name',
                'u.name as unit_name',
                'fu.name as free_unit_name',
                'c.nama_customer',
                'cc.category',
                'p.code',
                'fp.code as free_code'
            ])
            ->join('product as p', 'p.id', 'product_free_good.product')
            ->join('product as fp', 'fp.id', 'product_free_good.free_product')
            ->join('unit as u', 'u.id', 'product_free_good.unit')
            ->join('unit as fu', 'fu.id', 'product_free_good.free_unit')
            ->leftJoin('customer as c', 'c.id', 'product_free_good.customer')
            ->leftJoin('customer_category as cc', 'cc.id', 'product_free_good.customer_category')
            ->where('product_free_good.status_aktif', 1)
            ->whereDate('product_free_good.date_start', '<=', now())
            ->where(function ($q) {
                $q->whereDate('product_free_good.date_end', '>=', now())->orWhereNull('date_end');
            })
            // ->where('min_qty', '<=', $qty)
            // ->where(function ($q) use ($qty) {
            //     $q->where('max_qty', '>=', $qty)->orWhereNull('max_qty');
            // })
            ->get();
        return $datadb;
    }

    function getProductPrice($product_id, $unit_id, $customer_id, $qty)
    {
        return ProductUomPrice::where('product', $product_id)
            ->where('unit', $unit_id)
            ->where(function ($q) use ($customer_id) {
                $q->where('customer', $customer_id)->orWhereNull('customer');
            })
            ->where(function ($q) use ($qty) {
                $q->where('min_qty', '<=', $qty)
                ->where(function ($q2) use ($qty) {
                    $q2->where('max_qty', '>=', $qty)->orWhereNull('max_qty');
                });
            })
            ->whereDate('date_start', '<=', now())
            ->orderByDesc('min_qty')
            ->first();
    }

    public function getDataDiscProduct($params)
    {
        $data = $params;
        $product_id = $data['produk_id'];
        $unit_id = $data['unit'];
        $customer_id = $data['customer'];
        $customerdb = Customer::find($customer_id);
        $customer_category_id = $customerdb->customer_category;

        $datadb = ProductDisc::where('product_discount.product', $product_id)
            // ->where(function ($q) use ($customer_id) {
            //     $q->where('customer', $customer_id)->orWhereNull('customer');
            // })
            // ->where(function ($q) use ($customer_category_id) {
            //     $q->where('customer_category', $customer_category_id)->orWhereNull('customer_category');
            // })
            ->select([
                'product_discount.*',
                'u.name as unit_name',
                'c.nama_customer',
                'cc.category',
                'p.name as product_name',
                'p.code',
            ])
            ->join('product as p', 'p.id', 'product_discount.product')
            ->join('unit as u', 'u.id', 'product_discount.unit')
            ->leftJoin('customer as c', 'c.id', 'product_discount.customer')
            ->leftJoin('customer_category as cc', 'cc.id', 'product_discount.customer_category')
            ->where('product_discount.status_aktif', 1)
            ->whereDate('product_discount.date_start', '<=', now())
            ->where(function ($q) {
                $q->whereDate('product_discount.date_end', '>=', now())->orWhereNull('date_end');
            })
            // ->where('min_qty', '<=', $qty)
            // ->where(function ($q) use ($qty) {
            //     $q->where('max_qty', '>=', $qty)->orWhereNull('max_qty');
            // })
            ->get();
        return $datadb;
    }

    public function getDataProduct(Request $request)
    {
        $data = $request->all();
        DB::enableQueryLog();

        $data = [
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'draw' => $_POST['draw'] ?? 1,
        ];

        // --- Base Query ---
        $datadb = DB::table('product as m')
            ->select([
                'm.*',
                'pt.type',
                'u.name as unit_name',
                'uo.name as unit_tujuan_name',
                'uo.id as unit_tujuan_id',
                'pu.id as id_uom',

                // kolom harga dari tabel product_uom_price
                'pup.price as harga',
                'pup.min_qty',
                'pup.max_qty',
                'pup.date_start',
                'pup.date_end',
                'pup.customer_name',
                'pup.id as price_id'
            ])
            ->join('product_type as pt', 'pt.id', '=', 'm.product_type')
            ->join('product_uom as pu', 'pu.product', '=', 'm.id')
            ->join('unit as uo', 'uo.id', '=', 'pu.unit_tujuan')
            ->join('unit as u', 'u.id', '=', 'm.unit')
            ->leftJoin('product_uom_price as pup', function($join) {
                $join->on('pup.product', '=', 'm.id')
                    ->on('pup.unit', '=', 'pu.unit_tujuan')
                    ->whereNull('pup.deleted')
                    ->where(function($query) {
                        $query->whereNull('pup.date_end')
                            ->orWhere('pup.date_end', '>=', now());
                    })
                    ->where('pup.date_start', '<=', now());
            })
            ->whereNull('m.deleted');

            if(isset($data['customer'])){
                if($data['customer'] != ''){
                    $datadb->where('pup.customer', $data['customer']);
                }
            }
        // --- Total tanpa filter ---
        $data['recordsTotal'] = $datadb->count();

        // --- Pencarian ---
        if (!empty($_POST['search']['value'])) {
            $keyword = $_POST['search']['value'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('m.name', 'like', "%{$keyword}%")
                    ->orWhere('m.remarks', 'like', "%{$keyword}%")
                    ->orWhere('m.model_number', 'like', "%{$keyword}%")
                    ->orWhere('pt.type', 'like', "%{$keyword}%")
                    ->orWhere('uo.name', 'like', "%{$keyword}%")
                    ->orWhere('pup.customer_name', 'like', "%{$keyword}%");
            });
        }

        // --- Urutan (Sorting) ---
        if (!empty($_POST['order'][0]['dir'])) {
            $dir = $_POST['order'][0]['dir'];
            $datadb->orderBy('m.id', $dir);
        } else {
            $datadb->orderBy('m.id', 'desc');
        }

        // --- Filtered Count ---
        $data['recordsFiltered'] = $datadb->count();

        // --- Pagination ---
        if (isset($_POST['length']) && $_POST['length'] != -1) {
            $datadb->limit($_POST['length']);
        }
        if (isset($_POST['start'])) {
            $datadb->offset($_POST['start']);
        }

        // --- Eksekusi ---
        $data['data'] = $datadb->get();

        // --- Debug Query (opsional) ---
        $query = DB::getQueryLog();
        // dd($query);

        return response()->json($data);
    }
}
