<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\SalesReturnController as TransactionSalesReturnController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Tax;
use App\Models\Transaction\SalesPaymentHeader;
use App\Models\Transaction\SalesReturnDtl;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SalesReturnController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/sales_return.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Pengembalian Penjualan';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Sales Return';
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.sales_return.index', $data);
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

    public function add(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['code'] = generateNoPO();
        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        // $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['details'] = [];
        $data['general_ledgers'] = [];
        $data['cashBankAccounts'] = $this->getListKasBank();
        $view = view('web.sales_return.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionSalesReturnController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['cashBankAccounts'] = $this->getListKasBank();
        $data['details'] = SalesReturnDtl::where('sales_return_detail.return_id', $data['id'])
            ->select([
                'sales_return_detail.*',
                'p.code as product_code',
                'p.name as product_name',
                'sid.qty',
                'sid.discount',
                'sid.tax',
                'sid.tax_amount as tax_amount_invoice',
                'sid.tax_rate',
                'sid.type_tax',
            ])
            ->join('sales_invoice_detail as sid', 'sid.id', 'sales_return_detail.invoice_detail_id')
            ->join('product as p', 'p.id', 'sales_return_detail.product_id')
            ->whereNull('sales_return_detail.deleted')
            ->orderBy('sales_return_detail.id')
            ->get();

        $data['general_ledgers'] = getGeneralLedger($data['data']->return_number);
        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.sales_return.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getCustomer($salesmanId){
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
        $data = SalesPaymentHeader::with(['customers', 'items.invoice'])->findOrFail($data['id']);
        $qr = base64_encode(QrCode::format('png')->size(80)->generate($data->payment_code));
        // $qr = '';
        // echo '<pre>';
        // print_r($data->items);
        // die;

        // Kalkulasi total, subtotal, dsb bisa disiapkan di sini

        $pdf = Pdf::loadView('web.sales_return.print.po-print', compact('data',  'company', 'qr'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('SP-'.$data->payment_code.'.pdf');
    }
}
