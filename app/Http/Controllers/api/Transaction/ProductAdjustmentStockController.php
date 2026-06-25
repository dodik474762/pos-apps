<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction\ProductAdjustmentStock;
use App\Models\Transaction\ProductAdjustmentStockDtl;

class ProductAdjustmentStockController extends Controller
{
    public function getTableName()
    {
        return "product_adjustment_stock_header";
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
                // 'p.name as product_name',
                // 'p.code as product_code',
                // 'u.name as unit_name',
                'w.name as wh_name',
            ])
            // ->join('product as p', 'p.id', 'm.product')
            // ->leftJoin('unit as u', 'u.id', 'm.unit')
            ->leftJoin('warehouse as w', 'w.id', 'm.warehouse')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.remarks', 'LIKE', '%' . $keyword . '%');
                    // $query->orWhere('p.name', 'LIKE', '%'.$keyword.'%');
                    // $query->orWhere('p.code', 'LIKE', '%'.$keyword.'%');
                    // $query->orWhere('u.name', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('w.name', 'LIKE', '%' . $keyword . '%');
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
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...


            $roles = $data['id'] == '' ? new ProductAdjustmentStock() : ProductAdjustmentStock::find($data['id']);
            if ($data['id'] == '') {
                $roles->code = generateNoAdjStock();
            }
            $roles->remarks = $data['remarks'];
            $roles->warehouse = $data['warehouse_id'];
            $roles->save();
            $headerId = $roles->id;

            foreach ($data['routing'] as $key => $value) {
                if ($value['remove'] != '1') {
                    list($product_uom, $product_id, $product_name) = explode('//', $value['product']);
                    $items = $value['id'] == '' ? new ProductAdjustmentStockDtl() : ProductAdjustmentStockDtl::find($value['id']);
                    $items->header_id = $headerId;
                    $items->product = $product_id;
                    $items->unit = $value['unit'];
                    $items->qty = $value['qty'];
                    $items->price = $value['price'];
                    $items->warehouse = $data['warehouse_id'];
                    $items->save();

                    $qtyBaseUnit = getSmallestUnitV2($product_id, $value['unit'], $value['qty']);
                    $productUomLevel1 =  $qtyBaseUnit;
                    $qtyBaseUnit = !empty($qtyBaseUnit) ? $qtyBaseUnit->nilai_konversi_terkecil * $value['qty'] : 0;

                    $pushItem['product'] = $product_id;
                    stockUpdate(
                        $headerId,
                        $data['warehouse_id'],
                        $product_id,
                        $productUomLevel1->unit_tujuan,
                        $qtyBaseUnit,
                        $pushItem,
                        'add',
                        'adjustment stock'
                    );
                } else {
                    if ($value['id'] != '') {
                        ProductAdjustmentStockDtl::where('id', $value['id'])->delete();
                    }
                }
            }

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function confirmDelete(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $menu = RoutingHeader::find($data['id']);
            $menu->deleted = date('Y-m-d H:i:s');
            $menu->save();

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
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
        return view('web.adjustment_stock.modal.confirmdelete', $data);
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

        return view('web.product.modal.dataproductadjstok', $data);
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

        $stock = DB::table('product_stock')
            ->select('product', DB::raw('SUM(qty) as stock'))
            ->groupBy('product');

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
                DB::raw("COALESCE(ps.stock, 0) as stock_product"),
            ])
            ->leftJoinSub($stock, 'ps', function ($join) {
                $join->on('m.id', '=', 'm.id');
            })
            ->join('product_type as pt', 'pt.id', '=', 'm.product_type')
            ->join('product_uom as pu', 'pu.product', '=', 'm.id')
            ->join('unit as uo', 'uo.id', '=', 'pu.unit_tujuan')
            ->join('unit as u', 'u.id', '=', 'm.unit')
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
}
