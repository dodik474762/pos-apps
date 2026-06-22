<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\PackingListController as TransactionPackingListController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\ProductUom;
use App\Models\Master\Tax;
use App\Models\Transaction\PackingList;
use App\Models\Transaction\PackingListDo;
use App\Models\Transaction\PackingListDtl;
use App\Models\Transaction\PackingListReturn;
use App\Models\Transaction\PackingListReturnDtl;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackingListController extends Controller
{
    public $akses_menu = [];

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
    }

    public function getHeaderCss()
    {
        return [
            'js-1' => asset('assets/js/controllers/transaction/packing_list.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Transaksi';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Packing List';
    }

    public function getTitleSr()
    {
        return 'Packing List Pickup Retur';
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.packing_list.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function index_sr()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitleSr();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.packing_list.index_sr', $data);
        $put['title_content'] = $this->getTitleSr();
        $put['title_top'] = $this->getTitleSr();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getKendaraan()
    {
        $datadb = DB::table('vehicle')->whereNull('deleted')->get();
        return $datadb;
    }

    public function add(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['code'] = generateNoPO();
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['list_users'] = User::whereNull('deleted')->get(['id', 'name']);
        $data['list_kendaraan'] = $this->getKendaraan();
        // $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['details'] = [];
        $data['general_ledgers'] = [];
        $view = view('web.packing_list.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function add_sr(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['code'] = generateNoPO();
        $data['title'] = 'Form ' . $this->getTitleSr();
        $data['title_parent'] = $this->getTitleParent();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['list_kendaraan'] = $this->getKendaraan();
        $data['list_users'] = User::whereNull('deleted')->get(['id', 'name']);
        $data['details'] = [];
        $data['general_ledgers'] = [];
        $view = view('web.packing_list.formaddsr', $data);
        $put['title_content'] = $this->getTitleSr();
        $put['title_top'] = 'Form ' . $this->getTitleSr();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionPackingListController;
        $data = $request->all();
        $data['list_kendaraan'] = $this->getKendaraan();

        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['details'] = PackingListDo::where('packing_list_do.packing_list_id', $data['id'])
            ->select(['packing_list_do.*', 'c.code as customer_code', 'c.nama_customer', 'doh.do_number', 'doh.do_date', 'c.id as customer_id'])
            ->with(['detail', 'detail.deliveryDetail', 'detail.deliveryDetail.units', 'detail.product'])
            ->leftJoin('delivery_order_header as doh', 'doh.id', 'packing_list_do.delivery_order_id')
            ->leftJoin('customer as c', 'c.id', 'doh.customer_id')
            ->get();
        // $data['grouped'] = $data['details']
        //     ->pluck('detail')
        //     ->flatten()
        //     ->groupBy([
        //         fn($item) => $item->product->product_code,
        //         fn($item) => $item->deliveryDetail->units->name ?? '',
        //     ]);
        $data['grouped'] = $data['details']
            ->pluck('detail')
            ->flatten()
            ->unique(function ($item) {
                return $item->packing_list_id . '-'
                    . $item->delivery_order_id . '-'
                    . $item->product_id . '-'
                    . $item->qty_do . '-'
                    . $item->qty_packed . '-'
                    . $item->delivery_detail_id;
            })
            ->values()
            ->groupBy([
                fn($item) => $item->product->product_code,
                fn($item) => $item->deliveryDetail->units->name ?? '',
            ]);
        $data['list_users'] = User::whereNull('deleted')->get(['id', 'name']);

        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.packing_list.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function ubah_sr(Request $request)
    {
        $api = new TransactionPackingListController;
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['details'] = PackingListReturn::where('packing_list_sales_return.packing_list_id', $data['id'])
            ->select(['packing_list_sales_return.*', 'c.code as customer_code', 'c.nama_customer', 'sr.return_number as do_number', 'sr.return_date as do_date', 'c.id as customer_id'])
            ->with(['detail', 'detail.returnDetail', 'detail.returnDetail.invoice.so_detail.units', 'detail.product'])
            ->leftJoin('sales_return as sr', 'sr.id', 'packing_list_sales_return.sales_return_id')
            ->leftJoin('customer as c', 'c.id', 'sr.customer_id')
            ->get();
        // echo '<pre>';
        // print_r($data['details']);die;
        $data['grouped'] = $data['details']
            ->pluck('detail')
            ->flatten()
            ->groupBy([
                fn($item) => $item->product->product_code,
                fn($item) => $item->returnDetail->invoice->so_detail->units->name ?? '',
            ]);
        // echo '<pre>';
        // print_r($data['details']);die;
        $data['list_users'] = User::whereNull('deleted')->get(['id', 'name']);

        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.packing_list.formaddsr', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getCustomer($salesmanId)
    {
        $periodYear = intval(date('Y'));  // misal dari form input
        $periodMonth = intval(date('m'));   // misal dari form input

        $customers = DB::table('sales_plan_detail as d')
            ->join('sales_plan_header as h', 'h.id', '=', 'd.header_id')
            ->join('customer as c', 'c.id', '=', 'd.customer_id')
            ->where('h.salesman', $salesmanId)
            ->where('h.period_year', $periodYear)
            ->where('h.period_month', $periodMonth)
            ->whereNull('h.deleted')
            ->select('d.customer_id as id', 'c.nama_customer')
            ->distinct()
            ->get();

        return $customers;
    }

    public function cetak(Request $request)
    {
        $data = $request->all();
        $company = CompanyModel::where('id', session('id_company'))->first();
        $data = PackingList::where('id', $data['id'])->first();
        // echo '<pre>';
        // print_r($data);die;
        $details = PackingListDo::where('packing_list_do.packing_list_id', $data->id)
            ->select([
                'packing_list_do.*',
                'c.code as customer_code',
                'c.nama_customer',
                'doh.do_number',
                'doh.do_date',
                'sih.invoice_number',
                'sih.total_amount',
                'sih.amount_paid',
                'top.remarks as top_name',
                'sih.due_date'
            ])
            ->with(['detail', 'detail.deliveryDetail', 'detail.deliveryDetail.units', 'detail.product'])
            ->join('delivery_order_header as doh', 'doh.id', 'packing_list_do.delivery_order_id')
            ->join('sales_order_headers as soh', 'soh.id', 'doh.so_id')
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.sales_order', 'soh.id')
                    ->whereNull('sih.deleted');
            })
            ->join('customer as c', 'c.id', 'doh.customer_id')
            ->join('term_of_payment as top', 'top.id', 'c.payment_terms')
            // ->where('doh.do_number', 'DO11250004')
            ->get();
        // echo '<pre>';
        // print_r($details);die;
        $doIds = $details->pluck('delivery_order_id')->toArray();

        $packingListDetail = PackingListDtl::whereIn('packing_list_detail.delivery_order_id', $doIds)
            ->select([
                'packing_list_detail.*',
                // 'packing_list_detail.packing_list_id',
                // 'packing_list_detail.delivery_order_id',
                // 'packing_list_detail.product_id',
                // 'packing_list_detail.qty_do',
                // 'packing_list_detail.qty_packed',
                // 'packing_list_detail.delivery_detail_id',
                'doh.do_number',
            ])
            ->with(['product', 'deliveryDetail'])
            ->join('product as p', 'p.id', 'packing_list_detail.product_id')
            ->join('delivery_order_header as doh', 'doh.id', 'packing_list_detail.delivery_order_id')
            ->where('packing_list_detail.packing_list_id', $data->id)
            // ->whereIn('p.code', ['PQ03', 'P26MAY-1A'])
            // ->where('p.id', 40)
            ->orderBy('p.vendor', 'asc')
            ->orderBy('p.code', 'asc')
            ->get();

        // $grouped = $details
        //     ->pluck('detail')
        //     ->flatten()
        //     ->groupBy([
        //         fn($item) => $item->product->product_code,
        //         fn($item) => $item->deliveryDetail->units->name,
        //     ]);

        $grouped = collect($packingListDetail)->groupBy('product_id')->toArray();
        $groupedItem = [];
        foreach ($grouped as $key => $value) {
            $items = $value;
            $totalInSmallQty = 0;
            $remark = '';
            $groupByItemUom = collect($items)->groupBy('delivery_detail.uom');
            $uomIds = $groupByItemUom->keys()->toArray();
            $units = DB::table('unit')->whereIn('id', $uomIds)->get();

            foreach ($items as $v) {
                $remark .= $v['remark'] . ' / ';
                $delivery_detail = $v['delivery_detail'];
                $qtyBaseUnit = getSmallestUnitV2($delivery_detail['product_id'], $delivery_detail['uom'], $v['qty_packed']);
                $qtyProductInSmall = !empty($qtyBaseUnit) ? $qtyBaseUnit->nilai_konversi_terkecil * $v['qty_packed'] : 0;
                $totalInSmallQty += $qtyProductInSmall;
            }


            $inputs = [];
            foreach ($items as $v) {
                $inputs[] = ['unit' => $v['delivery_detail']['uom'], 'qty' => $v['qty_packed']];
            }
            $hasilNormalisasi = normalisasiQtyUom($key, $inputs);

            $levelSmallestUnit = DB::table('product_uom')->where('product', $key)->where('level', '1')->first();
            $largestUnit = getLargestUnit($key, $levelSmallestUnit->unit_dasar, $totalInSmallQty);

            $qtyOriginal = $largestUnit['qty_in_largest_unit'];
            $qtyLarges = ceil($qtyOriginal);

            $isAssembly = ($qtyOriginal != floor($qtyOriginal));

            // === Ambil daftar unit (id => nama) milik product ini, urut dari level terbesar ke terkecil ===
            $allProductUom = DB::table('product_uom')
                ->where('product', $key)
                ->orderByDesc('level')
                ->get();
            // echo '<pre>';
            // print_r($allProductUom);
            // die;

            $unitNames = DB::table('unit')
                ->whereIn('id', $allProductUom->pluck('unit_tujuan')->toArray())
                ->pluck('name', 'id');


            // === Bangun string assembly dari hasil normalisasi, skip yang qty = 0 ===
            $assemblyParts = [];
            foreach ($allProductUom as $uom) {
                $unitCode = $uom->unit_tujuan;
                $qtyUnit = $hasilNormalisasi[$unitCode] ?? 0;

                if ($qtyUnit > 0) {
                    $namaUnit = $unitNames[$unitCode] ?? $unitCode;
                    $assemblyParts[] = $qtyUnit . ' ' . $namaUnit;
                }
            }
            $assemblyNameNormalized = implode(' / ', $assemblyParts);

            $groupedUom = [];
            $assemblysItem = [];
            foreach ($groupByItemUom->toArray() as $key_uom => $u) {
                $items = $u;
                $qty = collect($items)->sum('qty_packed');
                $unit_name = collect($units)->where('id', $key_uom)->first();
                $groupedUom[] = [
                    'unit' => $key_uom,
                    'units' => $unit_name,
                    'qty' => $qty,
                ];
                $assemblysItem[] = $qty . ' ' . $unit_name->name;
            }

            // echo '<pre>';
            // print_r($hasil);
            // print_r($inputs);
            // print_r($assemblysItem);
            // print_r($groupByItemUom);
            // die;
            $groupedItem[] = [
                'product_id' => $key,
                'product_code' => $items[0]['product']['code'],
                'product_name' => $items[0]['product']['name'],
                'remarks' => $remark,
                'conversion' => $largestUnit,
                'assembly' => $isAssembly,
                'groupedUom' => $groupedUom,
                // 'assembly_name' => implode('/', $assemblysItem)
                'assembly_name' => $assemblyNameNormalized
            ];
        }
        // $productLargest = [];
        // foreach ($grouped as $key => $value) {
        //     foreach ($value as $items) {
        //         foreach ($items as $item) {
        //             if (strtolower($item->deliveryDetail->units->name) != 'karton' && strtolower($item->deliveryDetail->units->name) != 'box') {
        //                 $largestUnit = getLargestUnit($item->product->id, $item->deliveryDetail->id, $item->qty_packed);
        //                 $qtyLarge = $largestUnit['qty_in_largest_unit'];
        //                 $productLargest[$item->product->code] = isset($productLargest[$item->product->code]) ? $productLargest[$item->product->code] + $qtyLarge : $qtyLarge;
        //             }
        //         }
        //     }
        // }
        // $qr = base64_encode(QrCode::format('png')->size(80)->generate($data->payment_code));
        $qr = '';



        $productSatuan = ProductUom::where('product', '1')->get()->toArray();

        // Kalkulasi total, subtotal, dsb bisa disiapkan di sini

        $pdf = Pdf::loadView('web.packing_list.print.po-print', compact('data', 'company', 'qr', 'details', 'packingListDetail', 'grouped', 'groupedItem'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('PL-' . $data->payment_code . '.pdf');
    }

    public function cetak_sr(Request $request)
    {
        $data = $request->all();
        $company = CompanyModel::where('id', session('id_company'))->first();
        $data = PackingList::where('id', $data['id'])->first();
        // echo '<pre>';
        // print_r($data);die;
        $details = PackingListReturn::where('packing_list_sales_return.packing_list_id', $data['id'])
            ->select([
                'packing_list_sales_return.*',
                'c.code as customer_code',
                'c.nama_customer',
                'sr.return_number as do_number',
                'sr.return_date as do_date',
                'c.id as customer_id',
                'sih.invoice_number',
                'sih.total_amount',
            ])
            ->with(['detail', 'detail.returnDetail', 'detail.returnDetail.invoice.so_detail.units', 'detail.product'])
            ->leftJoin('sales_return as sr', 'sr.id', 'packing_list_sales_return.sales_return_id')
            ->join('sales_invoice_header as sih', 'sih.id', 'sr.invoice_id')
            ->leftJoin('customer as c', 'c.id', 'sr.customer_id')
            ->get();
        // echo '<pre>';
        // print_r($data['details']);die;

        $doIds = $details->pluck('sales_return_id')->toArray();
        $packingListDetail = PackingListReturnDtl::whereIn('packing_list_sales_return_detail.sales_return_id', $doIds)
            ->select([
                'packing_list_sales_return_detail.*',
                'sr.return_number as do_number',
                'sih.invoice_number',
                'sih.total_amount',
            ])
            ->with(['product'])
            ->join('sales_return as sr', 'sr.id', 'packing_list_sales_return_detail.sales_return_id')
            ->join('sales_invoice_header as sih', 'sih.id', 'sr.invoice_id')
            ->where('packing_list_sales_return_detail.packing_list_id', $data->id)
            ->get();

        $grouped = $details
            ->pluck('detail')
            ->flatten()
            ->groupBy([
                fn($item) => $item->product->product_code,
                fn($item) => $item->returnDetail->invoice->so_detail->units->name
            ]);

        $productLargest = [];
        foreach ($grouped as $key => $value) {
            foreach ($value as $items) {
                foreach ($items as $item) {
                    if (strtolower($item->returnDetail->invoice->so_detail->units->name) != 'karton' && strtolower($item->returnDetail->invoice->so_detail->units->name) != 'box') {
                        $largestUnit = getLargestUnit($item->product->id, $item->returnDetail->id, $item->qty_packed);
                        $qtyLarge = $largestUnit['qty_in_largest_unit'];
                        $productLargest[$item->product->code] = isset($productLargest[$item->product->code]) ? $productLargest[$item->product->code] + $qtyLarge : $qtyLarge;
                    }
                }
            }
        }
        // $qr = base64_encode(QrCode::format('png')->size(80)->generate($data->payment_code));
        $qr = '';



        $productSatuan = ProductUom::where('product', '1')->get()->toArray();

        // Kalkulasi total, subtotal, dsb bisa disiapkan di sini

        $pdf = Pdf::loadView('web.packing_list.print.po-print-sr', compact('data', 'company', 'qr', 'details', 'packingListDetail', 'grouped'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('PL-' . $data->payment_code . '.pdf');
    }

    public function cetakSj(Request $request)
    {
        $data = $request->all();
        $company = CompanyModel::where('id', session('id_company'))->first();
        $data = PackingList::where('id', $data['id'])->first();
        if ($data->sj_number == '') {
            $data->sj_number = str_replace('PL', 'SJ', $data->packing_list_no);
            $data->sj_date = date('Y-m-d H:i:s');
            $data->sj_by = session('user_id');
            $data->save();
        }
        // echo '<pre>';
        // print_r($data);die;
        $details = PackingListDo::where('packing_list_do.packing_list_id', $data->id)
            ->select([
                'packing_list_do.*',
                'c.code as customer_code',
                'c.nama_customer',
                'doh.do_number',
                'doh.do_date',
                'sih.invoice_number',
                'sih.total_amount',
                'sih.amount_paid',
                'kel.name as kelurahan'
            ])
            ->with(['detail', 'detail.deliveryDetail', 'detail.deliveryDetail.units', 'detail.product'])
            ->join('delivery_order_header as doh', 'doh.id', 'packing_list_do.delivery_order_id')
            ->join('sales_order_headers as soh', 'soh.id', 'doh.so_id')
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.sales_order', 'soh.id')
                    ->whereNull('sih.deleted');
            })
            ->join('customer as c', 'c.id', 'doh.customer_id')
            ->leftJoin('region as kel', 'kel.id', 'c.kelurahan')
            // ->where('doh.do_number', 'DO11250004')
            ->get();
        // echo '<pre>';
        // print_r($details);die;
        $doIds = $details->pluck('delivery_order_id')->toArray();
        $packingListDetail = PackingListDtl::whereIn('packing_list_detail.delivery_order_id', $doIds)
            ->select([
                'packing_list_detail.*',
                'doh.do_number',
            ])
            ->with(['product', 'deliveryDetail'])
            ->join('delivery_order_header as doh', 'doh.id', 'packing_list_detail.delivery_order_id')
            ->where('packing_list_detail.packing_list_id', $data->id)
            ->get();

        $grouped = collect($packingListDetail)->groupBy('product_id')->toArray();
        $groupedItem = [];
        foreach ($grouped as $key => $value) {
            $items = $value;
            $totalInSmallQty = 0;
            $remark = '';
            $groupByItemUom = collect($items)->groupBy('delivery_detail.uom');
            $uomIds = $groupByItemUom->keys()->toArray();
            $units = DB::table('unit')->whereIn('id', $uomIds)->get();

            foreach ($items as $v) {
                $remark .= $v['remark'] . ' / ';
                $delivery_detail = $v['delivery_detail'];
                $qtyBaseUnit = getSmallestUnitV2($delivery_detail['product_id'], $delivery_detail['uom'], $v['qty_packed']);
                $qtyProductInSmall = !empty($qtyBaseUnit) ? $qtyBaseUnit->nilai_konversi_terkecil * $v['qty_packed'] : 0;
                $totalInSmallQty += $qtyProductInSmall;
            }

            $levelSmallestUnit = DB::table('product_uom')->where('product', $key)->where('level', '1')->first();
            $largestUnit = getLargestUnit($key, $levelSmallestUnit->unit_dasar, $totalInSmallQty);

            $qtyOriginal = $largestUnit['qty_in_largest_unit'];
            $qtyLarges = ceil($qtyOriginal);

            $isAssembly = ($qtyOriginal != floor($qtyOriginal));

            $groupedUom = [];
            $assemblysItem = [];
            foreach ($groupByItemUom->toArray() as $key_uom => $u) {
                $items = $u;
                $qty = collect($items)->sum('qty_packed');
                $unit_name = collect($units)->where('id', $key_uom)->first();
                $groupedUom[] = [
                    'unit' => $key_uom,
                    'units' => $unit_name,
                    'qty' => $qty,
                ];
                $assemblysItem[] = $qty . ' ' . $unit_name->name;
            }


            $groupedItem[] = [
                'product_id' => $key,
                'product_code' => $items[0]['product']['code'],
                'product_name' => $items[0]['product']['name'],
                'remarks' => $remark,
                'conversion' => $largestUnit,
                'assembly' => $isAssembly,
                'groupedUom' => $groupedUom,
                'assembly_name' => implode('/', $assemblysItem)
            ];
        }
        $qr = '';



        // $productSatuan = ProductUom::where('product', '1')->get()->toArray();

        // Kalkulasi total, subtotal, dsb bisa disiapkan di sini

        $pdf = Pdf::loadView('web.packing_list.print.sj-print', compact('data', 'company', 'qr', 'details', 'packingListDetail', 'grouped', 'groupedItem'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('SJ-' . $data->payment_code . '.pdf');
    }
}
