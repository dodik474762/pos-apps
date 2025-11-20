<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\GoodReceiptController as TransactionGoodReceiptController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Tax;
use App\Models\Master\Vendor;
use App\Models\Master\Warehouse;
use App\Models\Transaction\GoodReceipt;
use App\Models\Transaction\GoodReceiptDtl;
use App\Models\Transaction\PurchaseOrder;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GoodReceiptController extends Controller
{
    public $akses_menu = [];
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/js/controllers/transaction/good_receipt.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Penerimaan";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Penerimaan Barang";
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.good_receipt.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function add()
    {
        $data['data'] = [];
        $data['code'] = generateNoPO();
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['vendors'] = Vendor::whereNull('deleted')->get();
        $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['purchase_orders'] = PurchaseOrder::whereNull('purchase_order.deleted')
        ->whereIn('purchase_order.status', ['draft', 'approved', 'partial-received'])
        ->with(['vendors'])
        ->get();
        $data['data_item'] = [];
        $data['general_ledgers'] = [];
        $view = view('web.good_receipt.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionGoodReceiptController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['vendors'] = Vendor::whereNull('deleted')->get();
        $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['data_item'] = GoodReceiptDtl::where('goods_receipt_detail.goods_receipt_header', $data['id'])
            ->select([
                'goods_receipt_detail.*',
                'p.id as product_id',
                'p.name as product_name',
                'u.name as unit_name',
                'pod.purchase_price',
                'p.code as product_code',
            ])
            ->join('purchase_order_detail as pod', 'pod.id', 'goods_receipt_detail.purchase_order_detail')
            ->join('product as p', 'p.id', 'goods_receipt_detail.product')
            ->join('unit as u', 'u.id', 'goods_receipt_detail.unit')
            ->whereNull('goods_receipt_detail.deleted')
            ->get();

        $data['purchase_orders'] = PurchaseOrder::whereNull('purchase_order.deleted')
            ->whereIn('purchase_order.status', ['draft', 'approved', 'partial-received'])
            ->with(['vendors'])
            ->get();
        $data['general_ledgers'] = getGeneralLedger($data['data']->gr_number);
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.good_receipt.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function cetak(Request $request)
    {
        $data = $request->all();
        $company = CompanyModel::where('id', session('id_company'))->first();
        $data = GoodReceipt::with(['po','po.vendors', 'po.warehouses', 'items.products', 'items.units'])->findOrFail($data['id']);
        $qr = base64_encode(QrCode::format('png')->size(80)->generate($data->gr_number));
        // $qr = '';
        // echo '<pre>';
        // print_r($data);
        // die;

        // Kalkulasi total, subtotal, dsb bisa disiapkan di sini
        $total = $data->items->sum('subtotal');

        $pdf = Pdf::loadView('web.good_receipt.print.gr-print', compact('data', 'total', 'company', 'qr'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('PO-' . $data->code . '.pdf');
    }
}
