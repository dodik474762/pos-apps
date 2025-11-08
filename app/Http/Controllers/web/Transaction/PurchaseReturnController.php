<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\PurchaseReturnController as TransactionPurchaseReturnController;
use App\Http\Controllers\Controller;
use App\Models\Master\Tax;
use App\Models\Master\Vendor;
use App\Models\Master\Warehouse;
use App\Models\Transaction\GoodReceipt;
use App\Models\Transaction\PurchaseInvoiceHeader;
use App\Models\Transaction\PurchaseReturnDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/purchase_return.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Pengembalian';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Retur Produk';
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.purchase_return.index', $data);
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
        $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['details'] = [];
        $data['general_ledgers'] = [];
        $data['cashBankAccounts'] = $this->getListKasBank();
        $view = view('web.purchase_return.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionPurchaseReturnController;
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['vendors'] = Vendor::whereNull('deleted')->get();
        $data['cashBankAccounts'] = $this->getListKasBank();
        $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['details'] = PurchaseReturnDtl::where('purchase_return_detail.purchase_return_id', $data['id'])
            ->select([
                'purchase_return_detail.*',
                'purchase_return_detail.product as item_id',
                'p.name as item_name',
                'u.name as unit_name',
            ])
            ->join('product as p', 'p.id', '=', 'purchase_return_detail.product')
            ->join('unit as u', 'u.id', '=', 'purchase_return_detail.unit')
            ->get();
        $data['reference'] = $data['data']->return_type == 'FROM_INVOICE' ? PurchaseInvoiceHeader::find($data['data']->reference_id) : GoodReceipt::find($data['data']->reference_id);
        // echo '<pre>';
        // print_r($data['details']);die;

        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['general_ledgers'] = getGeneralLedger($data['data']->code);
        $view = view('web.purchase_return.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }
}
