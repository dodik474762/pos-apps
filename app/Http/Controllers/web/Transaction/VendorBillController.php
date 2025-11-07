<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\VendorBillController as TransactionVendorBillController;
use App\Http\Controllers\Controller;
use App\Models\Master\Tax;
use App\Models\Master\Vendor;
use App\Models\Transaction\VendorBillDtl;
use Illuminate\Http\Request;

class VendorBillController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/vendor_bill.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Pembayaran';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Vendor';
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.vendor_bill.index', $data);
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
        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['vendors'] = Vendor::whereNull('deleted')->get();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['data_invoices'] = [];
        $data['general_ledgers'] = [];
        $view = view('web.vendor_bill.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionVendorBillController;
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['vendors'] = Vendor::whereNull('deleted')->get();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['data_invoices'] = VendorBillDtl::where('purchase_invoice_detail.purchase_invoice_id', $data['id'])
            ->select([
                'purchase_invoice_detail.*',
                'p.id as product_id',
                'p.name as product_name',
                'u.name as unit_name',
                'pod.purchase_price',
                'p.code as product_code',
                'po.code as po_number'
            ])
            ->join('purchase_order_detail as pod', 'pod.id', 'purchase_invoice_detail.purchase_order_detail_id')
            ->join('purchase_order as po', 'po.id', 'pod.purchase_order')
            ->join('product as p', 'p.id', 'purchase_invoice_detail.product')
            ->join('unit as u', 'u.id', 'purchase_invoice_detail.unit')
            ->get();

        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['general_ledgers'] = getGeneralLedger($data['data']->invoice_number);
        $view = view('web.vendor_bill.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }
}
