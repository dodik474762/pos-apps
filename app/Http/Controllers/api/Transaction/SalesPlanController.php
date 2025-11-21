<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\Currency;
use App\Models\Transaction\SalesPlanDetail;
use App\Models\Transaction\SalesPlanHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesPlanController extends Controller
{
    public function getTableName()
    {
        return 'sales_plan_header';
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
                    $query->where('m.plan_code', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.period_year', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.period_month', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.status', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('m.description', 'LIKE', '%'.$keyword.'%');
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
        // echo '<pre>';
        // print_r($data);die;
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
                ? new SalesPlanHeader()
                : SalesPlanHeader::find($data['id']);

            if (empty($data['id'])) {
                $header->plan_code = generateNoRoutePlan(); // misal helper
                $header->created_by = $userId;
                $header->status = 'DRAFT';
            }

            $header->salesman = $data['salesman'];
            $header->period_year = $data['period_year'];
            $header->period_month = $data['period_month'] ?? null;
            $header->description = $data['description'] ?? null;
            $header->save();

            $hdrId = $header->id;

            // === DETAIL ===
            foreach ($data['items'] as $item) {
                // Skip baris yang ditandai untuk dihapus
                if (!empty($item['remove']) && $item['remove'] == 1) {
                    if (!empty($item['id'])) {
                        $exist = SalesPlanDetail::find($item['id']);
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
                    ? new SalesPlanDetail()
                    : SalesPlanDetail::find($item['id']);

                list($cust_id, $cust_name) = explode('//', $item['customer_name']);
                $detail->header_id = $hdrId;
                $detail->customer_id = $cust_id;
                if($item['product_name'] != ''){
                    list($prod_id, $prod_name) = explode('//', $item['product_name']);
                    $detail->product_id = $prod_id;
                }else{
                    $detail->product_id = 0;
                }
                $detail->week_number = $item['week_number'];
                $detail->week_type = $item['week_type'];
                $detail->day_of_week = $item['day_of_week'];
                $detail->target_qty = $item['target_qty'] == '' ? 0 : $item['target_qty'];
                $detail->target_value = $item['target_value'] == '' ? 0 : $item['target_value'];
                $detail->note = $item['note'];
                $detail->type = $item['type'];
                $detail->save();
            }

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Sales Plan berhasil disimpan';
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
            $menu = SalesPlanHeader::find($data['id']);
            if ($menu->status != 'DRAFT') {
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

        return view('web.sales_plan.modal.confirmdelete', $data);
    }

    public function showDataProduct(Request $request)
    {
        $data = $request->all();

        return view('web.sales_plan.modal.dataproduct', $data);
    }

    public function showDataCustomer(Request $request)
    {
        $data = $request->all();

        return view('web.sales_plan.modal.datacustomer', $data);
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
            ])
            ->join('product_type as pt', 'pt.id', '=', 'm.product_type')
            ->whereNull('m.deleted');
        // --- Total tanpa filter ---
        $data['recordsTotal'] = $datadb->count();

        // --- Pencarian ---
        if (!empty($_POST['search']['value'])) {
            $keyword = $_POST['search']['value'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('m.name', 'like', "%{$keyword}%")
                    ->orWhere('m.remarks', 'like', "%{$keyword}%")
                    ->orWhere('m.model_number', 'like', "%{$keyword}%")
                    ->orWhere('pt.type', 'like', "%{$keyword}%");
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

    public function getDataCustomer()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $company = session('id_company');
        $akses = session('akses');

        $datadb = DB::table('customer as m')
            ->select([
                'm.*',
                'cc.category as customer_category_name',
                'r.name as city_name',
                'k.name as kecamatan_name',
                'kl.name as kelurahan_name',
            ])
            ->join('customer_category as cc', 'cc.id', 'm.customer_category')
            ->leftJoin('region as r', 'r.id', '=', 'm.kota')
            ->leftJoin('region as k', 'k.id', '=', 'm.kecamatan')
            ->leftJoin('region as kl', 'kl.id', '=', 'm.kelurahan')
            ->whereNull('m.deleted');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.nama_customer', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.pic', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.address', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.email', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.numbering_code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.kota', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('cc.category', 'LIKE', '%' . $keyword . '%');
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
}
