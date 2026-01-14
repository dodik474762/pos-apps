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
        // $data['warehouses'] = Warehouse::whereNull('deleted')->get();
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
        $data['grouped'] = $data['details']
            ->pluck('detail')
            ->flatten()
            ->groupBy([
                fn($item) => $item->product->product_code,
                fn($item) => $item->deliveryDetail->units->name ?? '',
            ]);
        // echo '<pre>';
        // print_r($data['details']);die;

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
            ->select(['packing_list_do.*', 'c.code as customer_code', 'c.nama_customer', 'doh.do_number', 'doh.do_date', 'sih.invoice_number', 'sih.total_amount'])
            ->with(['detail', 'detail.deliveryDetail', 'detail.deliveryDetail.units', 'detail.product'])
            ->join('delivery_order_header as doh', 'doh.id', 'packing_list_do.delivery_order_id')
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.do_id', 'doh.id')
                    ->whereNull('sih.deleted');
            })
            ->join('customer as c', 'c.id', 'doh.customer_id')
            // ->where('doh.do_number', 'DO11250004')
            ->get();
        $doIds = $details->pluck('delivery_order_id')->toArray();
        $packingListDetail = PackingListDtl::whereIn('packing_list_detail.delivery_order_id', $doIds)
            ->select([
                'packing_list_detail.*',
                'doh.do_number',
            ])
            ->with(['product'])
            ->join('delivery_order_header as doh', 'doh.id', 'packing_list_detail.delivery_order_id')
            ->where('packing_list_detail.packing_list_id', $data->id)
            ->get();

        $grouped = $details
            ->pluck('detail')
            ->flatten()
            ->groupBy([
                fn($item) => $item->product->product_code,
                fn($item) => $item->deliveryDetail->units->name,
            ]);

        $productLargest = [];
        foreach ($grouped as $key => $value) {
            foreach ($value as $items) {
                foreach ($items as $item) {
                    if (strtolower($item->deliveryDetail->units->name) != 'karton' && strtolower($item->deliveryDetail->units->name) != 'box') {
                        $largestUnit = getLargestUnit($item->product->id, $item->deliveryDetail->id, $item->qty_packed);
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

        $pdf = Pdf::loadView('web.packing_list.print.po-print', compact('data', 'company', 'qr', 'details', 'packingListDetail', 'grouped'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('PL-' . $data->payment_code . '.pdf');
    }
}
