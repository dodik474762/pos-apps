<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\VendorBillController as TransactionVendorBillController;
use App\Http\Controllers\Controller;
use App\Models\Master\Tax;
use App\Models\Master\Vendor;
use App\Models\Transaction\VendorBillDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function getListKasBank(){
        $datadb = DB::table('coa')->where('is_active', 1)
        ->where('parent_code', '1100')
        ->whereNull('deleted')->get();
        return $datadb;
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
        $data['cashBankAccounts'] = $this->getListKasBank();
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
        $data['cashBankAccounts'] = $this->getListKasBank();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['data_invoices'] = VendorBillDtl::where('vendor_payment_detail.vendor_payment_id', $data['id'])
            ->select([
                'pi.*',
                'vendor_payment_detail.id as vendor_payment_detail_id',
                'vendor_payment_detail.remaining_balance',
                'vendor_payment_detail.amount_paid',
            ])
            ->join('purchase_invoice_header as pi', 'pi.id', 'vendor_payment_detail.purchase_invoice_id')
            ->get();
        // echo '<pre>';
        // print_r($data['data_invoices']);die;

        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['general_ledgers'] = getGeneralLedger($data['data']->payment_number);
        $view = view('web.vendor_bill.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }
}
