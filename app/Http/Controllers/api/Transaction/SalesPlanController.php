<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\Currency;
use App\Models\Transaction\SalesPlanDetail;
use App\Models\Transaction\SalesPlanDetailRoute;
use App\Models\Transaction\SalesPlanHeader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Imports\SalesPlanImport;
use App\Models\Transaction\DailyVisit;
use App\Models\Transaction\DailyVisitCustomer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

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
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'us.name as salesname',
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('users as us', 'us.id', 'm.salesman')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.plan_code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.period_year', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.period_month', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.description', 'LIKE', '%' . $keyword . '%');
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
                    'message' => 'Currency IDR tidak ditemukan',
                ]);
            }

            // === HEADER ===
            $header = empty($data['id'])
                ? new SalesPlanHeader
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
                        $exist = SalesPlanDetailRoute::find($item['id']);
                        if ($exist && $exist->status !== 'DRAFT') {
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
                    ? new SalesPlanDetailRoute
                    : SalesPlanDetailRoute::find($item['id']);

                [$cust_id, $cust_name] = explode('//', $item['customer_name']);
                $detail->header_id = $hdrId;
                $detail->customer_id = $cust_id;
                $detail->visit_circle = $item['visit_type'];
                $detail->visit_mon = $item['visit_mon'];
                $detail->visit_tue = $item['visit_tue'];
                $detail->visit_wed = $item['visit_wed'];
                $detail->visit_thu = $item['visit_thu'];
                $detail->visit_fri = $item['visit_fri'];
                $detail->visit_sat = $item['visit_sat'];
                $detail->visit_sun = $item['visit_sun'];
                $detail->note = $item['note'];
                $detail->pjp_status = $item['type'];
                if ($item['type'] == 'EXTRA CALL') {
                    if ($item['id'] == '') {
                        $detail->date_extra_call = date('Y-m-d');
                    }
                }
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

    public function getSalesRoutePlan(Request $request)
    {
        // $today = Carbon::today();
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $data = $request->all();


        $weekNow = $today->isoWeek();
        $dayNow = strtolower($today->format('D'));
        $weekOfMonth = $this->weekOfMonth($today);

        $salesman = isset($data['salesman']) ? $data['salesman'] : 1;
        $route = $this->getDailyVisits($salesman, $today);
        $datadb = $route;

        // echo '<pre>';
        // print_r($datadb);
        // die;

        $message = '';
        DB::beginTransaction();
        try {
            $exist = DailyVisit::where('users', $salesman)->where('date_visit', $today->format('Y-m-d'))->first();
            $daily = empty($exist) ? new DailyVisit() : $exist;
            $daily->users = $salesman;
            $daily->date_visit = $today->format('Y-m-d');
            $daily->total_visit = count($datadb);
            $daily->cicle_type = count($datadb) > 0 ? $datadb[0]->visit_circle_code : '-';
            $daily->status = 'draft';
            $daily->save();
            $dailyId = $daily->id;

            if (!empty($datadb)) {
                DailyVisitCustomer::where('daily_visit', $dailyId)->delete();
                foreach ($datadb as $key => $value) {
                    $dailyDtl = new DailyVisitCustomer();
                    $dailyDtl->daily_visit = $dailyId;
                    $dailyDtl->customer = $value->customer_id;
                    $dailyDtl->cicle_type = $value->visit_circle_code;
                    $dailyDtl->type_route = $value->pjp_status;
                    $dailyDtl->status = $value->outlet_status;
                    $dailyDtl->save();
                }
            }

            DB::commit();
            $message = 'Success Download';
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = 'Error Download ' . $th->getMessage();
        }


        $result['is_valid'] = empty($datadb) ? false : true;
        $result['data'] = $datadb;
        $result['week'] = [
            'week_number' => $weekOfMonth,
            'day_of_week' => $dayNow,
            'iso_week' => $weekNow,
            'date' => $today->format('Y-m-d'),
            'message' => $message
        ];

        return response()->json($result);
    }

    public function getDailyVisits($salesmanId, $date = null)
    {
        $date = $date ?? Carbon::today();

        $weekNow = $date->isoWeek();           // ISO week number
        // echo $date->isoWeekYear(); die;
        $weekOfMonth = $this->weekOfMonth($date);
        $dayColumn = 'spd.visit_' . strtolower($date->format('D')); // mon,tue,...

        // ISO Week (ISO-8601)

        // Minggu selalu mulai Senin

        // Week number berjalan terus lintas bulan & tahun

        // Tidak pernah “reset” di awal bulan

        DB::enableQueryLog();
        $invoiceSubquery = DB::table('sales_invoice_header as sih')
            ->select(
                'sih.customer_id',
                DB::raw('SUM(sih.total_amount - sih.amount_paid) AS total_outstanding')
            )
            ->whereIn('sih.status', ['POSTED', 'PARTIAL PAID'])
            ->whereNull('sih.deleted')
            ->groupBy('sih.customer_id');

        $datadb = DB::table('sales_plan_detail_route as spd')
            ->join('sales_plan_header as sph', 'sph.id', '=', 'spd.header_id')
            ->join('customer as c', 'c.id', '=', 'spd.customer_id')
            ->leftJoin('term_of_payment as top', 'c.payment_terms', '=', 'top.id')
            ->join('customer_category as cc', 'cc.id', '=', 'c.customer_category')
            ->join('dictionary as vc', 'vc.id', '=', 'spd.visit_circle')
            ->leftJoin('region as pr', 'pr.id', '=', 'c.provinsi')
            ->leftJoin('region as kt', 'kt.id', '=', 'c.kota')
            ->leftJoin('region as kc', 'kc.id', '=', 'c.kecamatan')
            ->leftJoin('region as kl', 'kl.id', '=', 'c.kelurahan')
            ->leftJoinSub($invoiceSubquery, 'inv', function ($join) {
                $join->on('inv.customer_id', '=', 'c.id');
            })
            ->where('sph.salesman', $salesmanId)
            ->where($dayColumn, 1)
            ->where(function ($q) use ($weekNow, $weekOfMonth, $date) {
                // =========================
                // EXTRA CALL
                // =========================
                $q->where(function ($extra) use ($date) {
                    $extra->where('spd.pjp_status', 'EXTRA CALL')
                        ->whereDate('spd.date_extra_call', $date);
                });


                // =========================
                // PERMANEN (pakai logic lama)
                // =========================
                $q->orWhere(function ($permanent) use ($weekNow, $weekOfMonth) {

                    $permanent->where('spd.pjp_status', 'PERMANEN')
                        ->where(function ($cycle) use ($weekNow, $weekOfMonth) {

                            // WEEKLY
                            $cycle->where('spd.visit_circle', 12);

                            // BIWEEKLY GANJIL
                            $cycle->orWhere(function ($qq) use ($weekNow) {
                                $qq->where('spd.visit_circle', 13)
                                    ->whereRaw('MOD(?,2)=1', [$weekNow]);
                            });

                            // BIWEEKLY GENAP
                            $cycle->orWhere(function ($qq) use ($weekNow) {
                                $qq->where('spd.visit_circle', 14)
                                    ->whereRaw('MOD(?,2)=0', [$weekNow]);
                            });

                            // 3 WEEK
                            $cycle->orWhere(function ($qq) use ($weekNow) {
                                $qq->where('spd.visit_circle', 15)
                                    ->whereRaw('MOD(?,3)=0', [$weekNow]);
                            });

                            // MONTHLY
                            $cycle->orWhere(function ($qq) use ($weekOfMonth) {
                                $qq->where('spd.visit_circle', 16)
                                    ->whereRaw('? = 1', [$weekOfMonth]);
                            });
                        });
                });
            })
            ->select(
                'sph.*',
                'spd.*',
                'c.code as customer_code',
                'c.nama_customer',
                'pr.name as nama_provinsi',
                'kt.name as nama_kota',
                'kc.name as nama_kecamatan',
                'kl.name as nama_kelurahan',
                'c.address',
                'cc.category',
                DB::raw('COALESCE(inv.total_outstanding, 0) as total_outstanding'),
                'vc.keterangan as visit_circle_name',
                'vc.term_id as visit_circle_code',
                'c.latitude',
                'c.longitude',
                'c.payment_terms',
                'top.code as top_code',
                'top.nilai as top_nilai'
            );

        $datadb = $datadb->get();
        // echo '<pre>';
        // print_r(DB::getQueryLog());die;
        return $datadb;
    }

    private function weekOfMonth(Carbon $date): int
    {
        $firstWeek = $date->copy()->startOfMonth()->isoWeek();
        return $date->isoWeek() - $firstWeek + 1;
    }

    public function submit_import(Request $request)
    {
        $data = $request->all();
        $userId = session('user_id');
        // validasi file
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
                        $fail('File harus berformat CSV, XLSX, atau XLS.');
                    }
                }
            ]
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'File tidak valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ambil file
        $file = $request->file('file');

        // nama file
        // $filename = time() . '_' . $file->getClientOriginalName();

        // // simpan ke storage/app/import
        // $path = $file->storeAs('import', $filename);


        $import = new SalesPlanImport();

        Excel::import($import, $file);

        // ambil data excel (BELUM masuk DB)
        $rows = $import->rows;
        $rows = collect($rows)->toArray();

        $idCircleKunjungan = collect($rows)
            ->pluck('id_circle_kunjungan')
            ->unique()
            ->values()
            ->toArray();

        $idCustomer = collect($rows)
            ->pluck('customer_code')
            ->unique()
            ->values()
            ->toArray();

        $masterCircle = DB::table('dictionary')
            ->whereIn('term_id', $idCircleKunjungan)
            ->get()
            ->keyBy('term_id')
            ->toArray();

        $masterCustomer = DB::table('customer')
            ->whereIn('code', $idCustomer)
            ->get()
            ->keyBy('code')
            ->toArray();
        // echo '<pre>';
        // print_r($masterCircle);
        // die;

        $salesman = !empty($rows) ? $rows[0]['kode_sales'] : '';


        $result['is_valid'] = false;
        $result['message'] = 'Error';

        $users = DB::table('users')->where('username', $salesman)->first();
        if (empty($users)) {
            $result['message'] = 'User Tidak Ditemukan ' . $salesman;
            return response()->json($result);
        }

        DB::beginTransaction();
        try {
            $productRowsImport = 0;
            $data['salesman'] = $users->id;

            // === HEADER ===
            $header = new SalesPlanHeader();

            $header->plan_code = generateNoRoutePlan(); // misal helper
            $header->created_by = $userId;
            $header->status = 'DRAFT';

            $header->salesman = $data['salesman'];
            $header->period_year = date('Y');
            $header->period_month = date('m');
            $header->save();

            $hdrId = $header->id;

            // $hdrId = 27;

            // Item baru atau update
            foreach ($rows as $key => $item) {
                $detail = new SalesPlanDetailRoute();
                $cust_id = isset($masterCustomer[$item['customer_code']]->id) ? $masterCustomer[$item['customer_code']]->id : 0;
                $item['visit_type'] = isset($masterCircle[$item['id_circle_kunjungan']]->id) ? $masterCircle[$item['id_circle_kunjungan']]->id : 0;
                $itemVisit = $item['visit_type'];
                if ($item['visit_type'] == '13') {
                    $itemVisit = '14';
                }
                if ($item['visit_type'] == '14') {
                    $itemVisit = '13';
                }

                // echo '<pre>';
                // print_r($item['visit_type']);die;

                $detail->header_id = $hdrId;
                $detail->customer_id = $cust_id;
                $detail->visit_circle = $itemVisit;
                $detail->visit_mon = $item['senin'] == 'Y' ? 1 : 0;
                $detail->visit_tue = $item['selasa'] == 'Y' ? 1 : 0;
                $detail->visit_wed = $item['rabu'] == 'Y' ? 1 : 0;
                $detail->visit_thu = $item['kamis'] == 'Y' ? 1 : 0;
                $detail->visit_fri = $item['jumat'] == 'Y' ? 1 : 0;
                $detail->visit_sat = $item['sabtu'] == 'Y' ? 1 : 0;
                $detail->visit_sun = $item['minggu'] == 'Y' ? 1 : 0;
                $detail->note = 'IMPORT';
                $detail->pjp_status = $item['status_pjp'];
                if ($item['status_pjp'] == 'EXTRA CALL') {
                    $detail->date_extra_call = date('Y-m-d');
                }
                $detail->save();
                $productRowsImport += 1;
            }


            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Success ' . $productRowsImport . ' Imported';
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['message'] = 'Error ' . $th->getMessage();
        }

        return response()->json($result);
    }
}
