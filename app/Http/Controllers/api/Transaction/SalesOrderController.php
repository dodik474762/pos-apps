<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\Currency;
use App\Models\Master\Customer;
use App\Models\Master\ProductDisc;
use App\Models\Master\ProductFreeGood;
use App\Models\Master\ProductUom;
use App\Models\Master\ProductUomPrice;
use App\Models\Master\TermOfPayment;
use App\Models\Master\Unit;
use App\Models\Transaction\ProductPromoItem;
use App\Models\Transaction\SalesOrderDetail;
use App\Models\Transaction\SalesOrderHeader;
use App\Models\Transaction\StockCustomer;
use App\Models\Transaction\SalesOrderPromo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Master\MobileSession;

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
        $datadb = DB::table($this->getTableName() . ' as m')
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
                    $query->where('m.so_number', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.so_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
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
        // print_r($data);die;

        DB::beginTransaction();
        try {
            // Pastikan currency default ada
            $currency = Currency::where('code', 'IDR')->first();
            if (!$currency) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Currency IDR tidak ditemukan',
                ]);
            }


            /*CALCULATE PROMO ITEM */
            $items = collect($data['items'])->filter(function ($item) {
                return empty($item['free_for']);
            });

            // echo '<pre>';
            // print_r($items);die;
            $productIds = $items->pluck('product_id')->toArray();
            $promoItem = $this->getPromoItemAll($productIds);
            $calculatePromo = $this->calculatePromo($items, $promoItem, $productIds, $data['customer_id']);
            // echo '<pre>';
            // print_r($calculatePromo);
            // die;
            /*CALCULATE PROMO ITEM */

            // === HEADER ===
            $platform = 'web';
            $header = empty($data['id'])
                ? new SalesOrderHeader
                : SalesOrderHeader::find($data['id']);

            if (empty($data['id'])) {
                $header->so_number = generateNoSO(); // misal helper
                $header->created_by = $userId;
                $header->status = 'draft';
            } else {
                $platform = $header->platform;
            }


            $header->so_date = $data['so_date'];
            $header->customer_id = $data['customer_id'];
            $header->payment_term = $data['payment_term'] ?? null;
            $header->salesman = $data['salesman'] ?? null;
            $header->currency = $data['currency'];
            $header->discount_amount = !empty($calculatePromo['discount_header']) ? $calculatePromo['discount_header']['discount_amount'] : 0;
            $header->discount_percent = !empty($calculatePromo['discount_header']) ? $calculatePromo['discount_header']['discount_percent'] : 0;
            $header->remarks = $data['remarks'] ?? null;
            $header->total_amount = 0; // akan dihitung ulang di bawah
            $header->platform = $platform; // akan dihitung ulang di bawah
            $header->save();

            $hdrId = $header->id;
            $grandTotal = 0;
            $totalTaxAmount = 0;
            $taxId = 0;
            $taxRate = 0;

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
                                'message' => 'Tidak dapat dihapus karena status sudah bukan draft',
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
                    ? new SalesOrderDetail
                    : SalesOrderDetail::find($item['id']);

                $promoItem = null;
                if (!empty($calculatePromo['result_items'])) {
                    foreach ($calculatePromo['result_items'] as $promo) {
                        $items = $promo['items'];
                        $promoItem = collect($items)->where('product_id', $item['product_id'])->first();
                        if (!empty($promoItem)) {
                            if ($promo['discount_type'] == 'price') {
                                $item['price'] = $promoItem['price'];
                            }
                            $item['disc_percent'] = $promoItem['disc_percent'];
                            $item['disc_amount'] = $promoItem['disc_amount'];
                            $item['subtotal'] = $promoItem['subtotal'];
                            break;
                        }
                    }
                }

                // echo '<pre>';
                // print_r($item);die;

                $detail->sales_order_id = $hdrId;
                $detail->product_id = $item['product_id'];
                $detail->qty = $item['qty'];
                $detail->unit = $item['unit_id'];
                $detail->unit_price = $item['price'];
                $detail->discount_type = $item['disc_percent'] == 0 ? 'nominal' : 'percent';
                $detail->discount_percent = $item['disc_percent'];
                $detail->discount_amount = $item['disc_amount'];
                $detail->tax = $item['tax_amount'];
                $detail->tax_rate = $item['tax_rate'];
                $detail->tax_type = $item['tax_type'];
                $detail->tax_amount = $item['tax_amount'];
                $detail->subtotal = $item['subtotal'];
                $detail->is_free_good = $item['is_freegood'] ?? 0;
                $detail->free_for = $item['free_for'] ?? null;
                $detail->status = $detail->status ?? 'draft';
                $detail->save();

                // Hanya tambahkan ke total jika bukan free good
                if (empty($item['is_freegood'])) {
                    $grandTotal += $item['subtotal'];
                    $totalTaxAmount += $item['tax_amount'];
                    $taxId = $item['tax'];
                    $taxRate = $item['tax_rate'];
                }
            }

            // Update total header
            $header->total_amount = $grandTotal;
            $header->tax_amount = $totalTaxAmount;
            $header->tax_base = $taxRate;
            $header->tax_id = $taxId;
            if ($data['id'] != '') {
                if ($platform == 'mobile') {
                    $header->status = 'draft';
                }
            }
            $header->save();
            $soId = $header->id;

            /*sales order promo */
            SalesOrderPromo::where('sales_order_id', $soId)->delete();
            if (!empty($calculatePromo['discount_header'])) {
                $salesPromo = new SalesOrderPromo();
                $salesPromo->sales_order_id = $soId;
                $salesPromo->promo = $calculatePromo['discount_header']['promo_id'];
                $salesPromo->promo_name = $calculatePromo['discount_header']['promo_name'];
                $salesPromo->discount_percent = $calculatePromo['discount_header']['discount_percent'];
                $salesPromo->discount_amount = $calculatePromo['discount_header']['discount_amount'];
                $salesPromo->save();
            }

            if (!empty($calculatePromo['result_items'])) {
                foreach ($calculatePromo['result_items'] as $promo) {
                    $items = $promo['items'];
                    $promo_id = $promo['promo_id'];
                    $promo_name = $promo['promo_name'];
                    $disc_percent = $items[0]['disc_percent'];
                    $disc_amount = collect($items)->sum('disc_amount');

                    $salesPromo = new SalesOrderPromo();
                    $salesPromo->sales_order_id = $soId;
                    $salesPromo->promo = $promo_id;
                    $salesPromo->promo_name = $promo_name;
                    $salesPromo->discount_percent = $disc_percent;
                    $salesPromo->discount_amount = $disc_amount;
                    $salesPromo->save();
                }
            }



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

    public function calculateDisc($params)
    {
        try {
            //code...
            $qtyBaseUnit = getSmallestUnit($params['product_id'], $params['unit'], $params['qty']);
            $qtySmallest = $qtyBaseUnit['qty_in_base_unit'];
            // get discount product
            $discounts = $this->getDataDiscProduct($params);
            $discount_free = $this->getProductFreeGood($params);

            $applicableDiscount = null;
            foreach ($discounts as $d) {

                $minSmall = getSmallestUnit(
                    $d->product,
                    $d->unit,
                    $d->min_qty
                )['qty_in_base_unit'];

                $maxSmall = getSmallestUnit(
                    $d->product_id,
                    $d->unit,
                    $d->max_qty
                )['qty_in_base_unit'];

                if (
                    $d->product == $params['product_id'] &&
                    $qtySmallest >= $minSmall &&
                    $qtySmallest <= $maxSmall &&
                    (
                        empty($d->customer) ||
                        $d->customer == $params['customer_id']
                    ) &&
                    $params['today'] >= $d->berlaku_from
                ) {
                    $applicableDiscount = $d;
                    break;
                }
            }

            // =========================
            // HITUNG DISKON
            // =========================
            $discPercent = 0;
            $discAmount = 0;
            $discountType = null;

            if ($applicableDiscount) {
                $discountType = $applicableDiscount->discount_type;

                if ($applicableDiscount->discount_type === 'percent') {
                    $discPercent = $applicableDiscount->discount_value;
                    $discAmount = ($params['price'] * $params['qty'] * $discPercent) / 100;
                } else {
                    $discAmount = $applicableDiscount->discount_value;
                }
            }

            $subtotal = ($params['price'] * $params['qty']) - $discAmount;

            // =========================
            // CARI FREE GOOD
            // =========================
            $applicableFree = null;

            foreach ($discount_free as $d) {
                $minSmall = getSmallestUnit(
                    $d->product,
                    $d->unit,
                    $d->min_qty
                )['qty_in_base_unit'];

                $maxSmall = getSmallestUnit(
                    $d->product,
                    $d->unit,
                    $d->max_qty
                )['qty_in_base_unit'];

                if (
                    $d->product == $params['product_id'] &&
                    $qtySmallest >= $minSmall &&
                    $qtySmallest <= $maxSmall &&
                    (
                        empty($d->customer_id) ||
                        $d->customer_id == $params['customer_id']
                    ) &&
                    $params['today'] >= $d->berlaku_from
                ) {
                    $applicableFree = $d;
                    break;
                }
            }


            // =========================
            // RESPONSE
            // =========================
            return [
                'is_valid' => true,
                'qty_base_unit' => $qtySmallest,
                'discount' => $applicableDiscount,
                'discount_type' => $discountType,
                'disc_percent' => $discPercent,
                'disc_amount' => $discAmount,
                'subtotal' => round($subtotal, 2),
                'discount_free' => $applicableFree,
                'free_qty' => $applicableFree->free_qty ?? 0,
                'message' => 'Success'
            ];
        } catch (\Throwable $th) {
            return [
                'is_valid' => false,
                'qty_base_unit' => 0,
                'discount' => 0,
                'discount_type' => $discountType,
                'disc_percent' => 0,
                'disc_amount' => 0,
                'subtotal' => 0,
                'discount_free' => 0,
                'free_qty' => 0,
                'message' => $th->getMessage()
            ];
        }
    }

    public function sync(Request $request)
    {
        $data = json_decode($request->input('data'), true);
        $files_outlet = $request->file('files_outlet');
        $files_ttd = $request->file('files_ttd');
        $users_id = $data['user_id'];

        $periode = Carbon::parse($data['so_date'])->setTimezone('Asia/Jakarta');
        $so_date = $periode->format('Y-m-d H:i:s');

        $check_in_time = null;
        if (isset($data['check_in_time'])) {
            if (!empty($data['check_in_time'])) {
                $check_in_time = Carbon::parse($data['check_in_time'])->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
            }
        }
        $check_out_time = null;
        if (isset($data['check_out_time'])) {
            if (!empty($data['check_out_time'])) {
                $check_out_time = Carbon::parse($data['check_out_time'])->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
            }
        }

        $customers = Customer::where('id', trim($data['customer_id']))->first();
        $customersId = $customers->id;
        $top = TermOfPayment::where('id', $customers->payment_terms)->first();
        $payment_term = $top->nilai;

        $result['is_valid'] = false;
        $result['message'] = '';
        $result['so_date'] = $so_date;
        $result['data'] = $data;

        DB::beginTransaction();
        try {
            $dir = 'berkas/document/sales_order/';
            $dir .= date('Y') . '/' . date('m');
            $pathlamp = public_path() . '/' . $dir . '/';
            // Create the directory if it doesn't exist
            if (!File::isDirectory($pathlamp)) {
                File::makeDirectory($pathlamp, 0777, true, true);
            }

            $fileOutletName = 'outlet_' . time() . '.jpg';
            $fileTtdName = 'signature_' . time() . '.jpg';

            $path = $files_outlet->move(public_path($dir), $fileOutletName);
            $dbpathlampOutlet = '/' . $dir . '/';

            $path = $files_ttd->move(public_path($dir), $fileTtdName);
            $dbpathlampSignature = '/' . $dir . '/';

            $currency = Currency::where('code', 'IDR')->first();
            if (!$currency) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Currency IDR tidak ditemukan',
                ]);
            }
            $currencyId = $currency->id;

            /*update koordinat customer */
            if ($customers) {
                if ($customers->latitude == '') {
                    $customers->latitude = $data['latitude'];
                }
                if ($customers->longitude == '') {
                    $customers->longitude = $data['longitude'];
                }
                if ($customers->customer_category == '2') {
                    $customers->customer_category = '1';
                }
                $customers->save();
            }
            /*update koordinat customer */


            /*CALCULATE PROMO ITEM */
            $items = [];
            $productIds = [];
            foreach ($data['details'] as $i) {
                [$products, $product_unit] = explode(':', $i['product_id']);
                $products = explode('/', $products);
                $product_unit = explode('/', $product_unit);
                $items = [
                    'product_id' => $products[0],
                    'unit_id' => $product_unit[0],
                    'qty' => $i['qty'],
                    'price' => doubleval(trim($product_unit[1]))
                ];
                $productIds[] = $products[0];
            }

            $promoItem = $this->getPromoItemAll($productIds);
            $calculatePromo = $this->calculatePromo($items, $promoItem, $productIds, $customersId);
            /*CALCULATE PROMO ITEM */

            // === HEADER ===
            $header = new SalesOrderHeader;
            $header->so_number = generateNoSO(); // misal helper
            $header->created_by = $users_id;
            $header->status = 'submited';

            $header->so_date = $so_date;
            $header->customer_id = $customersId;
            $header->payment_term = $payment_term;
            $header->salesman = $users_id;
            $header->currency = $currencyId;
            $header->remarks = $data['remarks'];
            $header->total_amount = 0; // akan dihitung ulang di bawah
            $header->platform = 'mobile'; // akan dihitung ulang di bawah
            $header->photo_path = $dbpathlampOutlet . $fileOutletName;
            $header->signature_path = $dbpathlampSignature . $fileTtdName;
            $header->latitude = $data['latitude'];
            $header->longitude = $data['longitude'];
            $header->check_in_time = $check_in_time;
            $header->check_out_time = $check_out_time;
            $header->discount_amount = !empty($calculatePromo['discount_header']) ? $calculatePromo['discount_header']['discount_amount'] : 0;
            $header->discount_percent = !empty($calculatePromo['discount_header']) ? $calculatePromo['discount_header']['discount_percent'] : 0;
            $header->save();

            $hdrId = $header->id;
            $grandTotal = 0;
            $taxAmount = 0;
            $taxId = 0;
            $taxRate = 0;

            // === DETAIL ===
            foreach ($data['details'] as $item) {
                // Item baru atau update
                [$products, $product_unit] = explode(':', $item['product_id']);
                $products = explode('/', $products);
                $product_unit = explode('/', $product_unit);
                // DB::rollBack();
                // return response()->json([
                //     'is_valid' => false,
                //     'message' => 'Product UOM Price tidak ditemukan',
                //     'data'=> $products[0].' dan unit '.$product_unit[0]
                // ]);
                $puom_price = ProductUomPrice::where('product', trim($products[0]))->where('unit', trim($product_unit[0]))
                    ->whereNull('deleted')
                    ->first();

                if (empty($puom_price)) {
                    DB::rollBack();

                    return response()->json([
                        'is_valid' => false,
                        'message' => 'Product UOM Price tidak ditemukan',
                    ]);
                }

                $uom_price_id = $puom_price->id;

                $detail = new SalesOrderDetail;

                $detail->sales_order_id = $hdrId;
                $detail->product_id = trim($products[0]);
                $detail->qty = $item['qty'];
                $detail->unit = trim($product_unit[0]);

                // perhitungan diskon dan free good
                $params['product_id'] = trim($products[0]);
                $params['produk_id'] = trim($products[0]);
                $params['unit'] = trim($product_unit[0]);
                $params['customer'] = '1';
                $params['customer_id'] = '1';
                $params['price'] = doubleval(trim($product_unit[1]));
                $params['today'] = $data['so_date'];
                $params['qty'] = $item['qty'];
                $calculateDisc = $this->calculateDisc($params);

                /*PROMO */
                $promoItem = null;
                $freeGoods = [];
                if (!empty($calculatePromo['result_items'])) {
                    foreach ($calculatePromo['result_items'] as $promo) {
                        $items = $promo['items'];
                        $promoItem = collect($items)->where('product_id', trim($products[0]))->first();
                        if (!empty($promoItem)) {
                            if ($promo['discount_type'] == 'price') {
                                $product_unit[1] = $promoItem['price'];
                            }
                            $calculateDisc['disc_percent'] = $promoItem['disc_percent'];
                            $calculateDisc['disc_amount'] = $promoItem['disc_amount'];
                            $calculateDisc['subtotal'] = $promoItem['subtotal'];
                            $freeGoods = $promo['discount_free'];
                            break;
                        }
                    }
                }
                /*PROMO */

                $detail->unit_price = trim($product_unit[1]);

                $detail->discount_type = $calculateDisc['disc_percent'] == 0 ? 'nominal' : 'percent';
                $detail->discount_percent = $calculateDisc['disc_percent'];
                $detail->discount_amount = $calculateDisc['disc_amount'];
                $detail->subtotal = $calculateDisc['subtotal']; // ini sudah dikurangi diskon
                if (isset($item['taxAmount'])) {
                    $detail->tax_amount = $item['taxAmount'];
                    $detail->tax_rate = $item['tax_rate'];
                    $detail->tax_type = $item['type_tax'];
                    $detail->tax = $item['tax_sale'];

                    $taxAmount += $item['taxAmount'];
                    $taxId = $item['tax_sale'];
                    $taxRate = $item['tax_rate'];
                }
                $detail->is_free_good = 0;
                $detail->status = 'draft';
                $detail->save();

                if (!empty($freeGoods)) {
                    foreach ($freeGoods as $free) {
                        $detail = new SalesOrderDetail();

                        $detail->sales_order_id = $hdrId;
                        $detail->product_id = $free['product'];
                        $detail->qty = $free['qty'];
                        $detail->unit = $free['unit'];
                        $detail->unit_price = 0;
                        $detail->discount_type = $calculateDisc['disc_percent'] == 0 ? 'nominal' : 'percent';
                        $detail->discount_percent = 0;
                        $detail->discount_amount = 0;
                        $detail->subtotal = 0;
                        $detail->is_free_good = 1;
                        $detail->free_for = trim($products[0]);
                        $detail->status = 'draft';
                        $detail->save();
                    }
                }

                $grandTotal += $calculateDisc['subtotal'];
            }

            // Update total header
            $header->total_amount = $grandTotal;
            $header->tax_id = $taxId;
            $header->tax_amount = $taxAmount;
            $header->tax_base = $taxRate;
            $header->save();
            $soId = $header->id;

            /*sales order promo */
            SalesOrderPromo::where('sales_order_id', $soId)->delete();
            if (!empty($calculatePromo['discount_header'])) {
                $salesPromo = new SalesOrderPromo();
                $salesPromo->sales_order_id = $soId;
                $salesPromo->promo = $calculatePromo['discount_header']['promo_id'];
                $salesPromo->promo_name = $calculatePromo['discount_header']['promo_name'];
                $salesPromo->discount_percent = $calculatePromo['discount_header']['discount_percent'];
                $salesPromo->discount_amount = $calculatePromo['discount_header']['discount_amount'];
                $salesPromo->save();
            }

            if (!empty($calculatePromo['result_items'])) {
                foreach ($calculatePromo['result_items'] as $promo) {
                    $items = $promo['items'];
                    $promo_id = $promo['promo_id'];
                    $promo_name = $promo['promo_name'];
                    $disc_percent = $items[0]['disc_percent'];
                    $disc_amount = collect($items)->sum('disc_amount');

                    $salesPromo = new SalesOrderPromo();
                    $salesPromo->sales_order_id = $soId;
                    $salesPromo->promo = $promo_id;
                    $salesPromo->promo_name = $promo_name;
                    $salesPromo->discount_percent = $disc_percent;
                    $salesPromo->discount_amount = $disc_amount;
                    $salesPromo->save();
                }
            }

            // =============================
            // RECALC TAX JIKA ADA DISCOUNT HEADER
            // =============================
            $discAmountHeader = !empty($calculatePromo['discount_header'])
                ? $calculatePromo['discount_header']['discount_amount']
                : 0;


            if ($discAmountHeader > 0) {
                // Hitung ulang tax proporsional per baris
                $details = SalesOrderDetail::where('sales_order_id', $hdrId)
                    ->where('is_free_good', 0)
                    ->get();

                $totalDPP = $details->sum('subtotal');
                $newTaxAmount = 0;
                $newGrandTotal = 0;

                foreach ($details as $det) {
                    $proporsi = $totalDPP > 0 ? $det->subtotal / $totalDPP : 0;
                    $discPorsi = $discAmountHeader * $proporsi;
                    $dppAfterDisc = $det->subtotal - $discPorsi;

                    $taxRate = $det->tax_rate ?? 0;
                    $typeTax = $det->tax_type ?? '';

                    $taxAfterDisc = 0;
                    if ($typeTax == 'include') {
                        $taxAfterDisc = $dppAfterDisc - $dppAfterDisc / (1 + $taxRate / 100);
                        $newGrandTotal += $dppAfterDisc; // sudah include tax
                    } else {
                        $taxAfterDisc = $dppAfterDisc * ($taxRate / 100);
                        $newGrandTotal += $dppAfterDisc + $taxAfterDisc;
                    }

                    $newTaxAmount += $taxAfterDisc;

                    // Update tax_amount per detail
                    $det->tax_amount = $taxAfterDisc;
                    $det->save();
                }

                $grandTotal = $newGrandTotal;
                $taxAmount = $newTaxAmount;
            }

            // Update total header
            $header->total_amount = $grandTotal;
            $header->tax_id = $taxId;
            $header->tax_amount = $taxAmount;
            $header->tax_base = $taxRate;
            $header->save();

            DB::commit();
            $result['is_valid'] = true;
            $result['path'] = $dbpathlampOutlet . $fileOutletName;
            $result['message'] = 'Success';
            $result['sales_order_id'] = $hdrId;
        } catch (\Throwable $th) {
            // throw $th;
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

    public function confirmAlHandheld(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            // code...
            SalesOrderHeader::whereNull('deleted')->where('platform', 'mobile')
                ->where('status', 'submited')
                ->update([
                    'status' => 'draft'
                ]);

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
        $datadb = DB::table($this->getTableName() . ' as m')
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

    public function getListVendor()
    {
        $datadb = DB::table('vendor')->whereNull('deleted')->get();
        return $datadb;
    }

    public function showDataProduct(Request $request)
    {
        $data = $request->all();
        $data['vendors'] = $this->getListVendor();

        return view('web.product.modal.dataproductorder', $data);
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

    public function getPromoItem($produkIds = [])
    {
        $datadb = DB::table('product_promo_item_detail as ppid')
            ->join('product_promo_item as ppi', 'ppi.id', '=', 'ppid.product_promo_item')
            ->whereNull('ppi.deleted')
            ->whereIn('ppid.product', $produkIds)
            ->whereDate('ppi.date_start', '<=', now())
            ->select('ppid.product_promo_item', 'ppid.product')
            ->orderBy('ppi.min_mix', 'desc')
            ->orderBy('ppid.product_promo_item')
            ->get();

        return $datadb;
    }

    public function getPromoHeader($promoIds = [])
    {
        $datadb = ProductPromoItem::select('product_promo_item.*')
            ->with(['promoProducts', 'promoFree'])
            ->whereIn('product_promo_item.id', $promoIds)
            ->orderBy('product_promo_item.min_mix', 'desc')
            ->orderBy('product_promo_item.id')
            ->get();
        return $datadb;
    }

    public function getPromoItemDtl($promoIds = [])
    {
        $datadb = DB::table('product_promo_item_detail as ppid')
            ->join('product_promo_item as ppi', 'ppi.id', '=', 'ppid.product_promo_item')
            ->select(
                'ppid.*',
                'ppi.promo_name',
                'ppi.date_start',
                'ppi.min_qty',
                'ppi.max_qty',
                'ppi.discount_type',
                'ppi.discount_value',
                'p.code as product_code',
                'p.name as product_name',
                'u.name as unit_name',
                'ppi.min_mix',
                'ppi.max_mix',
                'ppi.unit',
                'ppi.kelipatan',
                'ppi.channel_outlet',
                'ppi.sub_channel_outlet',
                'ppi.kategori',
                'ppi.potong_grand_total'
            )
            ->join('product as p', 'p.id', '=', 'ppid.product')
            ->join('unit as u', 'u.id', '=', 'ppi.unit')
            ->whereIn('ppi.id', $promoIds)
            ->orderBy('ppid.product_promo_item')
            ->get();

        return $datadb;
    }

    public function getPromoItemFreeDtl($promoIds = [])
    {
        $datadb = DB::table('product_promo_item_detail_free as ppid')
            ->join('product_promo_item as ppi', 'ppi.id', '=', 'ppid.product_promo_item')
            ->select('ppid.*', 'p.code as product_code', 'ppi.promo_name')
            ->join('product as p', 'p.id', '=', 'ppid.free_product')
            ->whereIn('ppi.id', $promoIds)
            ->orderBy('ppid.product_promo_item')
            ->get();

        return $datadb;
    }

    public function showPromoItem(Request $request)
    {
        $data = $request->all();
        $produkIds = collect($data['items'])
            ->pluck('produk_id')
            ->unique()
            ->values();

        $dataPromo = $this->getPromoItem($produkIds);
        if (count($dataPromo) == 0) {
            return view('web.sales_order.promo-item', $data);
        }

        $groupPromo = $dataPromo->groupBy('product_promo_item');
        $promoIds = $groupPromo->keys()->toArray();
        $data['promoIds'] = $promoIds;
        // echo '<pre>';
        // print_r($data);die;

        $data['promo_item'] = $this->getPromoItemDtl($promoIds);
        $data['product_free'] = $this->getPromoItemFreeDtl($promoIds);

        return view('web.sales_order.promo-item', $data);
    }

    public function getPromoItemAll($produkIds = [])
    {
        $dataPromo = $this->getPromoItem($produkIds);
        if (count($dataPromo) == 0) {
            return [
                'promoIds' => [],
                'promo_item' => [],
                'product_free' => [],
                'promo_header' => [],
            ];
        }
        $groupPromo = $dataPromo->groupBy('product_promo_item');
        $promoIds = $groupPromo->keys()->toArray();
        $data['promoIds'] = $promoIds;
        $data['promo_header'] = $this->getPromoHeader($promoIds);
        $data['promo_item'] = $this->getPromoItemDtl($promoIds);
        $data['product_free'] = $this->getPromoItemFreeDtl($promoIds);

        return $data;
    }

    public function calculateTotalSmallestQty($items = [])
    {
        $qtySmallestAll = 0;
        foreach ($items as $key => $value) {
            $qtyBaseUnit = getSmallestUnitV2($value['product_id'], $value['unit_id'], $value['qty']);
            $qtySmallest = !empty($qtyBaseUnit) ? $qtyBaseUnit->nilai_konversi_terkecil * $value['qty'] : 0;
            $qtySmallestAll += $qtySmallest;
        }

        return $qtySmallestAll;
    }

    private function productMatch($promo, $productIds)
    {
        $promoProduc = $promo->promoProducts
            ->pluck('product')->toArray();
        return in_array($productIds, $promoProduc);
    }

    private function isPromoApplicable($promo, $totalQty, $product_id)
    {
        if ($promo->kategori === 'nominal') {
            $minValue = $promo->min_qty ?: 0;
            $maxValue = $promo->max_qty ?: PHP_INT_MAX;
            return $totalQty >= $minValue && $totalQty <= $maxValue;
        }

        $qtyBaseUnit = getSmallestUnitV2($product_id, $promo->unit, $promo->min_qty);
        $minQtyPromoSmallest = !empty($qtyBaseUnit) ? $qtyBaseUnit->nilai_konversi_terkecil * $promo->min_qty : 0;

        // Jika kelipatan=1, tidak ada batas atas — cukup >= min
        if ($promo->kelipatan == 1) {
            return $totalQty >= $minQtyPromoSmallest;
        }

        // Jika bukan kelipatan, cek range min-max
        $qtyBaseUnit = getSmallestUnitV2($product_id, $promo->unit, $promo->max_qty);
        $maxQtyPromoSmallest = !empty($qtyBaseUnit) ? $qtyBaseUnit->nilai_konversi_terkecil * $promo->max_qty : PHP_INT_MAX;

        return $totalQty >= $minQtyPromoSmallest && $totalQty <= $maxQtyPromoSmallest;
    }

    private function calculateFreeGoods($promo, $totalQty, $product_id)
    {
        $qtyBaseUnit = getSmallestUnitV2($product_id, $promo->unit, $promo->min_qty);
        $minQtyPromoSmallest = !empty($qtyBaseUnit) ? $qtyBaseUnit->nilai_konversi_terkecil * $promo->min_qty : 0;

        // $kelipatan = $promo->kelipatan ?: 1;
        $multiplier = $promo->kelipatan == 0 ? 1 : floor($totalQty / $minQtyPromoSmallest);

        return $promo->promoFree->map(function ($free) use ($multiplier) {
            return [
                'product_id' => $free->free_product,
                'unit' => $free->free_unit,
                'qty' => $free->free_qty * $multiplier,
            ];
        })->toArray();
    }

    public function calculatePromo($items = [], $promoAll = [], $productIds = [], $customer_id = '')
    {
        $resultItems = [];
        $freeGoods = [];
        $grandTotal = 0;

        $customers = [];
        $channel_outlet = '';
        $sub_channel_outlet = '';
        if ($customer_id != '') {
            $customers = Customer::where('id', $customer_id)->first();
            $channel_outlet = $customers->channel_outlet;
            $sub_channel_outlet = $customers->sub_channel_outlet;
        }

        $promoHeaders = $promoAll['promo_header'];


        // =============================
        // LOOP 1: PROMO POTONG GRAND TOTAL
        // =============================
        foreach ($promoHeaders as $promo) {
            if ($promo->potong_grand_total != 1)
                continue; // skip yang bukan grand total

            if ($customer_id != '') {
                $channelMatch = empty($promo->channel_outlet) || $promo->channel_outlet == $channel_outlet;
                $subChannelMatch = empty($promo->sub_channel_outlet) || $promo->sub_channel_outlet == $sub_channel_outlet;
                if (!$channelMatch || !$subChannelMatch)
                    continue;
            }

            $promoProduc = $promo->promoProducts->pluck('product')->toArray();

            $mixTotalPromo = 0;
            $itemsHasDiscount = [];
            foreach ($promoProduc as $v) {
                foreach ($productIds as $k) {
                    if ($k == $v) {
                        $mixTotalPromo += 1;
                        $itemsHasDiscount[] = $v;
                    }
                }
            }

            $mix_min_promo = $promo->min_mix;
            $mix_max_promo = $promo->max_mix;
            if (!($mixTotalPromo >= $mix_min_promo && $mixTotalPromo <= $mix_max_promo))
                continue;

            $itemsValue = [];
            foreach ($itemsHasDiscount as $h) {
                $valItem = collect($items)->where('product_id', $h)->first();
                $itemsValue[] = $valItem;
            }

            $rawSubtotal = 0;
            foreach ($itemsValue as $v) {
                $rawSubtotal += $v['price'] * $v['qty'];
            }

            $isNominalCategory = $promo->kategori === 'nominal';
            $qtySmallestAllProduct = $isNominalCategory
                ? $rawSubtotal
                : $this->calculateTotalSmallestQty($itemsValue);

            $totalPromoAplicable = 0;
            foreach ($itemsValue as $v) {
                if ($this->isPromoApplicable($promo, $qtySmallestAllProduct, $v['product_id'])) {
                    $totalPromoAplicable += 1;
                }
            }

            if (!($totalPromoAplicable >= $mix_min_promo && $totalPromoAplicable <= $mix_max_promo))
                continue;

            // Hitung grand total semua items
            $grandTotalAllItems = 0;
            foreach ($items as $item) {
                $grandTotalAllItems += $item['price'] * $item['qty'];
            }

            $discAmountHeader = 0;
            $discPercentHeader = 0;

            if ($promo->discount_type === 'percent') {
                $discPercentHeader = $promo->discount_value;
                $discAmountHeader = $grandTotalAllItems * ($promo->discount_value / 100);
            }
            if ($promo->discount_type === 'nominal') {
                $qtyBaseUnit = getSmallestUnitV2($itemsValue[0]['product_id'], $promo->unit, $promo->min_qty);
                $minQtyPromoSmallest = !empty($qtyBaseUnit) ? $qtyBaseUnit->nilai_konversi_terkecil * $promo->min_qty : 0;
                $multiplier = $promo->kelipatan == 0 ? 1 : floor($qtySmallestAllProduct / $minQtyPromoSmallest);
                $discAmountHeader = $promo->discount_value * $multiplier;
            }

            $discountHeader = [
                'discount_percent' => $discPercentHeader,
                'discount_amount' => $discAmountHeader,
                'promo_id' => $promo->id,
                'promo_name' => $promo->promo_name
            ];

            break; // promo grand total pertama yang applicable langsung break
        }

        foreach ($promoHeaders as $promo) {
            // =============================
            // FILTER CHANNEL OUTLET
            // =============================
            if ($promo->potong_grand_total != 0)
                continue; // skip yang grand total

            if ($customer_id != '') {

                $channelMatch =
                    empty($promo->channel_outlet) ||
                    $promo->channel_outlet == $channel_outlet;

                $subChannelMatch =
                    empty($promo->sub_channel_outlet) ||
                    $promo->sub_channel_outlet == $sub_channel_outlet;

                if (!$channelMatch || !$subChannelMatch) {
                    continue; // skip promo ini
                }
            }

            $promoProduc = $promo->promoProducts
                ->pluck('product')->toArray();

            //match kan dulu total promo bundle itemnya;
            $mixTotalPromo = 0;
            $itemsHasDiscount = [];
            foreach ($promoProduc as $v) {
                foreach ($productIds as $k) {
                    if ($k == $v) {
                        $mixTotalPromo += 1;
                        $itemsHasDiscount[] = $v;
                    }
                }
            }

            // echo '<pre>';
            // print_r($mixTotalPromo);die;

            $mix_min_promo = $promo->min_mix;
            $mix_max_promo = $promo->max_mix;
            // if ($mix_min_promo != $mixTotalPromo) {
            if (!($mixTotalPromo >= $mix_min_promo && $mixTotalPromo <= $mix_max_promo)) {
                continue;
            }

            $itemsValue = [];
            foreach ($itemsHasDiscount as $h) {
                $valItem = collect($items)->where('product_id', $h)->first();
                $itemsValue[] = $valItem;
            }


            // =============================
            // HITUNG SUBTOTAL MENTAH (sebelum diskon)
            // untuk keperluan promo kategori nominal
            // =============================
            $rawSubtotal = 0;
            foreach ($itemsValue as $v) {
                $rawSubtotal += $v['price'] * $v['qty'];
            }

            // =============================
            // TENTUKAN BASIS PENGECEKAN PROMO
            // qty  → pakai qtySmallestAllProduct
            // nominal → pakai rawSubtotal
            // =============================
            $isNominalCategory = $promo->kategori === 'nominal';

            if ($isNominalCategory) {
                $qtySmallestAllProduct = $rawSubtotal;
            } else {
                $qtySmallestAllProduct = $this->calculateTotalSmallestQty($itemsValue);
            }

            $totalPromoAplicable = 0;
            foreach ($itemsValue as $v) {
                $applicable = $this->isPromoApplicable($promo, $qtySmallestAllProduct, $v['product_id']);
                if (!$applicable) {
                    continue;
                }
                $totalPromoAplicable += 1;
            }

            // if ($totalPromoAplicable != $mix_min_promo) {
            //     continue;
            // }

            // Jadi ini:
            if (!($totalPromoAplicable >= $mix_min_promo && $totalPromoAplicable <= $mix_max_promo)) {
                continue;
            }

            $discountPercent = 0;
            $discountAmounts = 0;
            $grandTotal = 0;

            // Hitung diskon
            // Jika promo mix (max_mix > 1), diskon hanya diterapkan ke 1 produk saja
            $discountApplied = false;
            // debug tanpa die
            foreach ($itemsValue as $v) {
                $discountAmount = 0;

                // Jika promo mix dan diskon sudah diterapkan ke baris lain, skip diskon
                $isMixPromo = $promo->max_mix > 1;
                $shouldApplyDiscount = !$isMixPromo || !$discountApplied;
                if ($shouldApplyDiscount) {
                    if ($promo->discount_type === 'percent') {
                        $discountPercent = $promo->discount_value;
                        $discountAmount = ($v['price'] * $v['qty'])
                            * ($discountPercent / 100);
                        $discountAmounts += $discountAmount;
                    }
                    if ($promo->discount_type === 'nominal') {
                        $qtyBaseUnit = getSmallestUnitV2($v['product_id'], $promo->unit, $promo->min_qty);
                        $minQtyPromoSmallest = !empty($qtyBaseUnit) ? $qtyBaseUnit->nilai_konversi_terkecil * $promo->min_qty : 0;
                        $multiplier = $promo->kelipatan == 0 ? 1 : floor($qtySmallestAllProduct / $minQtyPromoSmallest);

                        $discountAmount = $promo->discount_value * $multiplier;
                        $discountAmounts += $discountAmount;
                    }
                    if ($promo->discount_type == 'price') {
                        $v['price'] = $promo->discount_value;
                    }

                    // Tandai diskon sudah diterapkan untuk promo mix ini
                    if ($isMixPromo) {
                        $discountApplied = true;
                    }
                }

                $subtotal = ($v['price'] * $v['qty']) - $discountAmount;
                $v['subtotal'] = $subtotal;
                $v['discountAmount'] = $discountAmount;
                $v['discountPercent'] = $discountPercent;

                $grandTotal += $subtotal;
            }

            // Hitung free good
            $discountFree = $this->calculateFreeGoods($promo, $qtySmallestAllProduct, $itemsValue[0]['product_id']);
            $freeGoods = array_merge(
                $freeGoods,
                $discountFree
            );


            $resultItems[] = [
                'promo_id' => $promo->id,
                'promo_name' => $promo->promo_name,
                'items' => $itemsValue,
                'discount_type' => $promo->discount_type,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'grand_total' => $grandTotal,
                'discount_free' => $discountFree
            ];

            // break;
        }

        // echo '<pre>';
        // print_r($resultItems);
        // die;

        return [
            'discount_header' => $discountHeader, // ← tambahan untuk potong grand total
            'result_items' => $resultItems,
            'free_goods' => $freeGoods,
        ];
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

    public function getProductFreeGood($params)
    {
        $data = $params;
        $product_id = $data['produk_id'];
        // $unit_id = $data['unit'];
        // $customer_id = $data['customer'];
        // $customerdb = Customer::find($customer_id);
        // $customer_category_id = $customerdb->customer_category;

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
                'fp.code as free_code',
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

    public function getProductPrice($product_id, $unit_id, $customer_id, $qty)
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
        // $unit_id = $data['unit'];
        // $customer_id = $data['customer'];
        // $customerdb = Customer::find($customer_id);
        // $customer_category_id = empty($customer_db) ? 0 : $customerdb->customer_category;

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
                'pup.id as price_id',
                'v.nama_vendor',
                'tx.rate as tax_rate'
            ])
            ->join('product_type as pt', 'pt.id', '=', 'm.product_type')
            ->join('product_uom as pu', 'pu.product', '=', 'm.id')
            ->join('unit as uo', 'uo.id', '=', 'pu.unit_tujuan')
            ->join('unit as u', 'u.id', '=', 'm.unit')
            ->leftJoin('tax as tx', 'tx.id', 'm.tax_sale')
            ->leftJoin('vendor as v', 'v.id', '=', 'm.vendor')
            ->leftJoin('product_uom_price as pup', function ($join) {
                $join->on('pup.product', '=', 'm.id')
                    ->on('pup.unit', '=', 'pu.unit_tujuan')
                    ->whereNull('pup.deleted')
                    ->where(function ($query) {
                        $query->whereNull('pup.date_end')
                            ->orWhere('pup.date_end', '>=', now());
                    })
                    ->where('pup.date_start', '<=', now());
            })
            ->whereNull('m.deleted');

        // if (isset($data['customer'])) {
        //     if ($data['customer'] != '') {
        //         $datadb->where('pup.customer', $data['customer']);
        //     }
        // }

        if (isset($request->principal)) {
            if ($request->principal != '') {
                $datadb->where('m.vendor', $request->principal);
            }
        }

        // echo '<pre>';
        // print_r($data);die;
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
                    ->orWhere('v.nama_vendor', 'like', "%{$keyword}%")
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

    public function getAverageTransaction(Request $request)
    {
        $data = $request->all();
        $month = intval(date('m'));   // Januari
        $year = date('Y');
        $customer = $data['customer_id'] ?? null;

        $data = DB::table('sales_order_headers as soh')
            ->select(
                'soh.customer_id',
                'c.nama_customer',
                DB::raw('COUNT(soh.id) as total_transaksi'),
                DB::raw('SUM(soh.total_amount) as total_nilai'),
                DB::raw('AVG(soh.total_amount) as avg_transaksi')
            )
            ->join('customer as c', 'c.id', 'soh.customer_id')
            ->whereMonth('soh.so_date', $month)
            ->whereYear('soh.so_date', $year)
            ->whereIn('soh.status', ['confirmed', 'completed', 'partial', 'draft'])
            ->whereNull('soh.deleted')
            ->groupBy('soh.customer_id', 'c.nama_customer');
        if ($customer) {
            $data = $data->where('soh.customer_id', $customer);
        }

        $data = $data->first();

        $customer = empty($data) ? $customer : $data->customer_id;
        /*data transaksi terakakhir */
        $last_transaction = SalesOrderHeader::where('customer_id', $customer)->orderBy('id', 'desc')->first();
        $last_product = '';
        if (!empty($last_transaction)) {
            $data->last_transaksi = date('Y-m-d', strtotime($last_transaction->so_date));

            $detailProduct = DB::table('sales_order_details as sod')
                ->select([
                    DB::raw("
                        CONCAT(
                            p.code, '-', 
                            p.name, '-', 
                            sod.qty, ' ', 
                            u.name, '-', 
                            sod.subtotal
                        ) as detail_string
                    ")
                ])
                ->join('product as p', 'p.id', 'sod.product_id')
                ->join('unit as u', 'u.id', 'sod.unit')
                ->where('sod.sales_order_id', $last_transaction->id)
                ->get();

            $last_product = $detailProduct->pluck('detail_string')->implode("\n");
            $data->last_product = $last_product;
        }


        if (empty($data)) {
            $data = [
                'is_valid' => true,
                'data' => [
                    'customer_id' => $customer,
                    // 'customer_name' => $customer_name,
                    'total_transaksi' => 0,
                    'total_nilai' => 0,
                    'avg_transaksi' => 0,
                    'last_transaksi' => '-',
                    'last_product' => '-',
                    'periode' => date('Y-m')
                ]
            ];
        }
        $data->periode = date('Y-m');
        $result['is_valid'] = true;
        $result['data'] = $data;
        return response()->json($result);
    }

    public function closingOrder(Request $request)
    {
        $data = json_decode($request->input('data'), true);
        $users_id = $data['user_id'];
        $now = $data['close_date'];

        $closing_date = Carbon::parse($now)->setTimezone('Asia/Jakarta');
        $closing_date = $closing_date->format('Y-m-d H:i:s');
        $result['is_valid'] = false;
        $result['message'] = '';
        DB::beginTransaction();
        try {

            $mobile_session = MobileSession::where('users', $users_id)
                ->where('date_process', date('Y-m-d'))
                ->first();

            if (empty($mobile_session)) {
                DB::rollBack();
                $result['message'] = 'Session Tidak Ditemukan';
                return response()->json($result);
            }

            $mobile_session->total_visit = $data['total_visit'];
            $mobile_session->total_tagihan = $data['total_tagihan'];
            $mobile_session->total_retur = $data['total_retur'];
            $mobile_session->status = 'CLOSE';
            $mobile_session->updated_at = $closing_date;
            $mobile_session->save();

            DB::commit();
            $result['message'] = 'Success';
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $result['message'] = $th->getMessage();
        }

        return response()->json($result);
    }

    public function stockSubmit(Request $request)
    {
        $data = json_decode($request->input('data'), true);
        $files_outlet = $request->file('files_outlet');
        $users_id = $data['user_id'];

        $result['is_valid'] = false;
        $result['message'] = '';
        DB::beginTransaction();
        try {

            $dir = 'berkas/document/stock_customer/';
            $dir .= date('Y') . '/' . date('m');
            $pathlamp = public_path() . '/' . $dir . '/';
            // Create the directory if it doesn't exist
            if (!File::isDirectory($pathlamp)) {
                File::makeDirectory($pathlamp, 0777, true, true);
            }

            $fileOutletName = 'outlet_' . time() . '.jpg';

            $path = $files_outlet->move(public_path($dir), $fileOutletName);
            $dbpathlampOutlet = '/' . $dir . '/';

            foreach ($data['details'] as $item) {
                [$products, $product_unit] = explode(':', $item['product_id']);
                $products = explode('/', $products);
                $product_unit = explode('/', $product_unit);

                $detail = new StockCustomer();
                $detail->customer = $data['customer'];
                $detail->product_id = trim($products[0]);
                $detail->qty = $item['qty'];
                $detail->unit = trim($product_unit[0]);
                $detail->unit_price = trim($product_unit[1]);
                $detail->discount_type = null;
                $detail->is_free_good = 0;
                $detail->status = 'draft';
                $detail->created_by = $users_id;
                $detail->foto_path = $dbpathlampOutlet . $fileOutletName;
                $detail->save();
            }

            DB::commit();
            $result['message'] = 'Success';
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $result['message'] = $th->getMessage();
        }

        return response()->json($result);
    }
}
