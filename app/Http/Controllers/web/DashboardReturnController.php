<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardReturnController extends Controller
{
    const TYPE_GOOD = 'good';

    private $company_id;

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->company_id = session('id_company');
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/js/controllers/dashboard_return.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return 'Monitoring';
    }

    public function getTitle()
    {
        return 'Dashboard Return';
    }

    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->resolveRange($request);

        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        $data['username'] = session('username');
        $data['akses'] = session('akses');
        $data['summary'] = $this->getSummary($startDate, $endDate);
        $data['monthly'] = $this->getMonthly($startDate, $endDate);

        $view = view('web.dashboard_return.index', $data);

        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    private function resolveRange(Request $request)
    {
        $startDate = (string) $request->get('start_date', date('Y-01-01'));
        $endDate = (string) $request->get('end_date', date('Y-m-d'));

        try {
            $start = Carbon::parse($startDate)->startOfDay();
        } catch (\Throwable $th) {
            $start = Carbon::now()->startOfYear();
        }

        try {
            $end = Carbon::parse($endDate)->endOfDay();
        } catch (\Throwable $th) {
            $end = Carbon::now()->endOfDay();
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start->format('Y-m-d'), $end->format('Y-m-d')];
    }

    private function returnBase($startDate, $endDate)
    {
        return DB::table('sales_return as sr')
            ->whereNull('sr.deleted')
            ->where('sr.types', self::TYPE_GOOD)
            ->whereBetween('sr.return_date', [$startDate, $endDate])
            ->when($this->company_id !== null && $this->company_id !== '', fn ($q) => $q->where('sr.company_id', $this->company_id));
    }

    public function getSummary($startDate = '', $endDate = '')
    {
        [$startDate, $endDate] = $this->normalizeRange($startDate, $endDate);

        $header = $this->returnBase($startDate, $endDate)
            ->selectRaw('COUNT(*) as total_return')
            ->selectRaw('COUNT(DISTINCT sr.invoice_id) as total_invoice')
            ->selectRaw('COUNT(DISTINCT sr.customer_id) as total_customer')
            ->first();

        $detail = DB::table('sales_return as sr')
            ->join('sales_return_detail as srd', function ($join) {
                $join->on('srd.return_id', '=', 'sr.id')
                    ->whereNull('srd.deleted');
            })
            ->whereNull('sr.deleted')
            ->where('sr.types', self::TYPE_GOOD)
            ->whereBetween('sr.return_date', [$startDate, $endDate])
            ->when($this->company_id !== null && $this->company_id !== '', fn ($q) => $q->where('sr.company_id', $this->company_id))
            ->selectRaw('COALESCE(SUM(srd.qty_return), 0) as total_qty')
            ->selectRaw('COUNT(DISTINCT srd.product_id) as total_product')
            ->first();

        return [
            'total_return' => (int) ($header->total_return ?? 0),
            'total_invoice' => (int) ($header->total_invoice ?? 0),
            'total_customer' => (int) ($header->total_customer ?? 0),
            'total_qty' => (float) ($detail->total_qty ?? 0),
            'total_product' => (int) ($detail->total_product ?? 0),
        ];
    }

    public function getMonthly($startDate = '', $endDate = '')
    {
        [$startDate, $endDate] = $this->normalizeRange($startDate, $endDate);

        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->startOfMonth();

        $labels = [];
        $keys = [];
        while ($start->lte($end)) {
            $labels[] = $start->locale('id')->translatedFormat('M Y');
            $keys[] = $start->format('Y-m');
            $start->addMonth();
        }

        $counts = $this->returnBase($startDate, $endDate)
            ->selectRaw("DATE_FORMAT(sr.return_date, '%Y-%m') as periode")
            ->selectRaw('COUNT(*) as jumlah')
            ->groupBy('periode')
            ->pluck('jumlah', 'periode');

        $jumlah = [];
        foreach ($keys as $key) {
            $jumlah[] = (int) ($counts[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'jumlah' => $jumlah,
        ];
    }

    private function normalizeRange($startDate, $endDate)
    {
        $start = empty($startDate) ? Carbon::now()->startOfYear() : Carbon::parse($startDate)->startOfDay();
        $end = empty($endDate) ? Carbon::now()->endOfDay() : Carbon::parse($endDate)->endOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start->format('Y-m-d'), $end->format('Y-m-d')];
    }

    public function getGrafik(Request $request)
    {
        [$startDate, $endDate] = $this->resolveRange($request);

        $result['is_valid'] = true;
        $result['data'] = $this->getMonthly($startDate, $endDate);

        return response()->json($result);
    }

    public function getData(Request $request)
    {
        [$startDate, $endDate] = $this->resolveRange($request);
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        // PRD: dokumen referensi Invoice/DO/PL. DO diresolusi dari do_id invoice
        // atau DO terakhir milik sales order (relasi yang dipakai daftar invoice).
        $deliveryOrderSub = DB::table('delivery_order_header as doh')
            ->whereNull('doh.deleted')
            ->select('doh.id', 'doh.so_id', 'doh.do_number', 'doh.do_date')
            ->whereRaw('doh.id = (
            SELECT MAX(d2.id)
            FROM delivery_order_header AS d2
            WHERE d2.so_id = doh.so_id
              AND d2.deleted IS NULL
        )');

        $packingListSub = DB::table('packing_list_do as pld')
            ->join('packing_list as pl', function ($q) {
                $q->on('pl.id', 'pld.packing_list_id')
                    ->whereNull('pl.deleted');
            })
            ->select('pld.delivery_order_id', 'pl.id', 'pl.packing_list_no', 'pl.packing_date')
            ->whereRaw('pl.id = (
            SELECT MAX(pl2.id)
            FROM packing_list_do AS pld2
            INNER JOIN packing_list AS pl2 ON pl2.id = pld2.packing_list_id
            WHERE pld2.delivery_order_id = pld.delivery_order_id
              AND pl2.deleted IS NULL
        )');

        $detailAgg = DB::table('sales_return_detail as srd')
            ->leftJoin('users as receiver', 'receiver.id', '=', 'srd.received_by')
            ->selectRaw('srd.return_id, COALESCE(SUM(srd.qty_return), 0) as total_qty')
            ->selectRaw('MAX(srd.received_date) as received_date')
            ->selectRaw("GROUP_CONCAT(DISTINCT receiver.name ORDER BY receiver.name SEPARATOR ', ') as received_by_name")
            ->whereNull('srd.deleted')
            ->groupBy('srd.return_id');

        $cancelAgg = DB::table('sales_return_detail as srd_cancel')
            ->join('sales_invoice_detail as sid_cancel', 'sid_cancel.id', '=', 'srd_cancel.invoice_detail_id')
            ->selectRaw('srd_cancel.return_id, COUNT(srd_cancel.id) as canceled_item_count')
            ->selectRaw('COALESCE(SUM(srd_cancel.qty_return), 0) as canceled_qty')
            ->whereNull('srd_cancel.deleted')
            ->groupBy('srd_cancel.return_id');

        $datadb = DB::table('sales_return as sr')
            ->leftJoin('sales_invoice_header as sih', 'sih.id', '=', 'sr.invoice_id')
            ->leftJoinSub($deliveryOrderSub, 'do', function ($join) {
                $join->on('do.so_id', '=', 'sih.sales_order')->orOn('do.id', '=', 'sih.do_id');
            })
            ->leftJoinSub($packingListSub, 'pl', function ($join) {
                $join->on('pl.delivery_order_id', '=', 'do.id');
            })
            ->leftJoin('customer as c', 'c.id', '=', 'sr.customer_id')
            ->leftJoin('users as u', 'u.id', '=', 'sr.created_by')
            ->leftJoinSub($detailAgg, 'srd', function ($join) {
                $join->on('srd.return_id', '=', 'sr.id');
            })
            ->leftJoinSub($cancelAgg, 'sid', function ($join) {
                $join->on('sid.return_id', '=', 'sr.id');
            })
            ->whereNull('sr.deleted')
            ->where('sr.types', self::TYPE_GOOD)
            ->whereBetween('sr.return_date', [$startDate, $endDate])
            ->when($this->company_id !== null && $this->company_id !== '', fn ($q) => $q->where('sr.company_id', $this->company_id))
            ->select([
                'sr.*',
                'sih.invoice_number',
                'sih.invoice_date',
                'sih.total_amount as invoice_total',
                'sih.status as invoice_status',
                'c.code as customer_code',
                'c.nama_customer',
                'u.name as created_by_name',
                DB::raw("CASE
                    WHEN sr.return_type = 'CORRECTION' THEN 'admin_correction'
                    WHEN sr.reason LIKE 'Auto return dari delivery confirm%' THEN 'driver'
                    ELSE 'manual'
                END as return_source"),
                DB::raw('COALESCE(srd.total_qty, 0) as total_qty'),
                DB::raw('srd.received_date as received_date'),
                DB::raw('srd.received_by_name as received_by_name'),
                DB::raw('COALESCE(sid.canceled_item_count, 0) as canceled_item_count'),
                DB::raw('COALESCE(sid.canceled_qty, 0) as canceled_qty'),
                'do.do_number',
                'do.do_date',
                'pl.packing_list_no',
                'pl.packing_date',
            ]);

        $data['recordsTotal'] = (clone $datadb)->count();

        $keyword = $request->input('search.value');
        if (! empty($keyword)) {
            $datadb->where(function ($query) use ($keyword) {
                $query->where('sr.return_number', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('sr.return_date', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('sr.status', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('sr.return_type', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('sr.reason', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('sih.invoice_number', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('sih.invoice_date', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('do.do_number', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('do.do_date', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('pl.packing_list_no', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('pl.packing_date', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('c.code', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('u.name', 'LIKE', '%' . $keyword . '%');
            });
        }

        $data['recordsFiltered'] = (clone $datadb)->count();

        $orderColumn = (int) $request->input('order.0.column', 2);
        $orderDir = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        switch ($orderColumn) {
            case 1:
                $datadb->orderBy('sr.return_number', $orderDir);
                break;
            case 2:
                $datadb->orderBy('sr.return_date', $orderDir);
                break;
            case 4:
                $datadb->orderBy('sih.invoice_date', $orderDir);
                break;
            case 5:
                $datadb->orderBy('do.do_number', $orderDir);
                break;
            case 6:
                $datadb->orderBy('do.do_date', $orderDir);
                break;
            case 7:
                $datadb->orderBy('pl.packing_list_no', $orderDir);
                break;
            case 8:
                $datadb->orderBy('pl.packing_date', $orderDir);
                break;
            case 9:
                $datadb->orderBy('c.nama_customer', $orderDir);
                break;
            case 10:
                $datadb->orderBy('srd.total_qty', $orderDir);
                break;
            case 11:
                $datadb->orderBy('sr.return_type', $orderDir);
                break;
            case 12:
                $datadb->orderBy('sr.status', $orderDir);
                break;
            case 13:
                $datadb->orderBy('sid.canceled_item_count', $orderDir);
                break;
            case 14:
                $datadb->orderBy('srd.received_date', $orderDir);
                break;
            case 15:
                $datadb->orderBy('srd.received_by_name', $orderDir);
                break;
            case 16:
                $datadb->orderBy('u.name', $orderDir);
                break;
            default:
                $datadb->orderBy('sr.return_date', 'desc')->orderBy('sr.id', 'desc');
                break;
        }

        $length = (int) $request->input('length', 25);
        if ($length > 0) {
            $datadb->limit($length);
        }
        $datadb->offset((int) $request->input('start', 0));

        $data['data'] = $datadb->get()->toArray();
        $data['draw'] = (int) $request->input('draw', 0);

        return response()->json($data);
    }

    public function getDetail(Request $request)
    {
        $id = $request->get('id');
        $result['is_valid'] = false;

        $header = DB::table('sales_return as sr')
            ->leftJoin('sales_invoice_header as sih', 'sih.id', '=', 'sr.invoice_id')
            ->leftJoin('customer as c', 'c.id', '=', 'sr.customer_id')
            ->leftJoin('users as u', 'u.id', '=', 'sr.created_by')
            ->where('sr.id', $id)
            ->whereNull('sr.deleted')
            ->when($this->company_id !== null && $this->company_id !== '', fn ($q) => $q->where('sr.company_id', $this->company_id))
            ->select([
                'sr.*',
                'sih.invoice_number',
                'sih.invoice_date',
                'sih.total_amount as invoice_total',
                'sih.status as invoice_status',
                'sih.do_id as invoice_do_id',
                'sih.sales_order as invoice_sales_order',
                'c.code as customer_code',
                'c.nama_customer',
                'u.name as created_by_name',
            ])
            ->first();

        if (empty($header)) {
            $result['message'] = 'Data sales return tidak ditemukan.';

            return response()->json($result);
        }

        // Penanda sumber retur: koreksi admin (FIX), driver, atau manual.
        $header->return_source = $header->return_type === 'CORRECTION'
            ? 'admin_correction'
            : (stripos((string) $header->reason, 'Auto return dari delivery confirm') === 0 ? 'driver' : 'manual');

        // PRD: lengkapi detail dengan referensi DO dan PL dari invoice asal.
        $header->do_number = null;
        $header->do_date = null;
        $header->packing_list_no = null;
        $header->packing_date = null;
        if (! empty($header->invoice_sales_order) || ! empty($header->invoice_do_id)) {
            $documents = DB::table('delivery_order_header as doh')
                ->whereNull('doh.deleted')
                ->where(function ($q) use ($header) {
                    if (! empty($header->invoice_sales_order)) {
                        $q->where('doh.so_id', $header->invoice_sales_order);
                    }
                    if (! empty($header->invoice_do_id)) {
                        $q->orWhere('doh.id', $header->invoice_do_id);
                    }
                })
                ->orderByDesc('doh.id')
                ->select(['doh.id', 'doh.do_number', 'doh.do_date'])
                ->first();

            if (! empty($documents)) {
                $header->do_number = $documents->do_number;
                $header->do_date = $documents->do_date;

                $packingList = DB::table('packing_list_do as pld')
                    ->join('packing_list as pl', function ($q) {
                        $q->on('pl.id', 'pld.packing_list_id')->whereNull('pl.deleted');
                    })
                    ->where('pld.delivery_order_id', $documents->id)
                    ->orderByDesc('pl.id')
                    ->select(['pl.packing_list_no', 'pl.packing_date'])
                    ->first();

                if (! empty($packingList)) {
                    $header->packing_list_no = $packingList->packing_list_no;
                    $header->packing_date = $packingList->packing_date;
                }
            }
        }

        $returnDetails = DB::table('sales_return_detail as srd')
            ->leftJoin('sales_invoice_detail as sid', 'sid.id', '=', 'srd.invoice_detail_id')
            ->leftJoin('product as p', 'p.id', '=', 'srd.product_id')
            ->leftJoin('unit as uom', 'uom.id', '=', 'p.unit')
            ->where('srd.return_id', $id)
            ->whereNull('srd.deleted')
            ->select([
                'srd.*',
                'p.code as product_code',
                'p.name as product_name',
                'uom.name as unit_name',
                'sid.qty as invoice_qty',
                'sid.price as invoice_price',
                'sid.return_qty as invoice_return_qty',
                'sid.flag_cancel',
            ])
            ->orderBy('srd.id')
            ->get();

        $canceledItems = DB::table('sales_return_detail as srd_cancel')
            ->join('sales_invoice_detail as sid', 'sid.id', '=', 'srd_cancel.invoice_detail_id')
            ->leftJoin('product as p', 'p.id', '=', 'sid.product_id')
            ->leftJoin('unit as uom', 'uom.id', '=', 'p.unit')
            ->where('srd_cancel.return_id', $id)
            ->whereNull('srd_cancel.deleted')
            ->select([
                'sid.*',
                'p.code as product_code',
                'p.name as product_name',
                'uom.name as unit_name',
            ])
            ->orderBy('sid.line_no')
            ->get();

        $result['is_valid'] = true;
        $result['header'] = $header;
        $result['return_details'] = $returnDetails;
        $result['canceled_items'] = $canceledItems;

        return response()->json($result);
    }

    public function confirmReceived(Request $request)
    {
        $id = (int) $request->input('id');
        $userId = (int) session('user_id');
        $result['is_valid'] = false;

        if (empty($id) || empty($userId)) {
            $result['message'] = 'Data user atau retur tidak valid.';

            return response()->json($result);
        }

        $header = DB::table('sales_return')
            ->where('id', $id)
            ->whereNull('deleted')
            ->where('types', self::TYPE_GOOD)
            ->when($this->company_id !== null && $this->company_id !== '', fn ($q) => $q->where('company_id', $this->company_id))
            ->first();

        if (empty($header)) {
            $result['message'] = 'Data sales return tidak ditemukan.';

            return response()->json($result);
        }

        $detailCount = DB::table('sales_return_detail')
            ->where('return_id', $id)
            ->whereNull('deleted')
            ->when($this->company_id !== null && $this->company_id !== '', fn ($q) => $q->where('company_id', $this->company_id))
            ->count();

        if ($detailCount === 0) {
            $result['message'] = 'Detail sales return tidak ditemukan.';

            return response()->json($result);
        }

        $now = now();
        DB::table('sales_return_detail')
            ->where('return_id', $id)
            ->whereNull('deleted')
            ->when($this->company_id !== null && $this->company_id !== '', fn ($q) => $q->where('company_id', $this->company_id))
            ->update([
                'received_date' => $now,
                'received_by' => $userId,
                'updated_at' => $now,
            ]);

        $result['is_valid'] = true;
        $result['message'] = 'Barang retur berhasil diterima.';

        return response()->json($result);
    }
}
