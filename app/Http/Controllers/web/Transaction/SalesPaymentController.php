<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\SalesPaymentController as TransactionSalesPaymentController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Tax;
use App\Models\Transaction\SalesInvoiceHeader;
use App\Models\Transaction\SalesPaymentDtl;
use App\Models\Transaction\SalesPaymentHeader;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SalesPaymentController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/sales_payment.js'),
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
        return 'Sales Payment';
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $data['salesmans'] = User::whereNull('deleted')->whereIn('user_group', [6, 4, 5])->get(['id', 'nik', 'name']);
        $view = view('web.sales_payment.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getListKasBank($payment_method = 'CASH')
    {
        $datadb = DB::table('coa')->where('is_active', 1)
            ->where('parent_code', '1100')
            ->whereNull('deleted')
            ->where('payment_method', $payment_method)
            ->get();
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
        // $data['warehouses'] = Warehouse::whereNull('deleted')->get();
        $data['details'] = [];
        $data['general_ledgers'] = [];
        $payment_method = $data['payment_method'] ?? 'CASH';
        $data['cashBankAccounts'] = $this->getListKasBank($payment_method);
        $view = view('web.sales_payment.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getListCustomer()
    {
        $datadb = SalesInvoiceHeader::whereIn('sales_invoice_header.status', ['POSTED', 'PARTIAL PAID', 'DRAFT'])
            ->select([
                'c.id as id',
                'c.nama_customer',
                'c.code as customer_code',
                'pt.remarks as payment_terms'
            ])
            ->distinct()
            ->join('customer as c', 'c.id', 'sales_invoice_header.customer_id')
            ->join('term_of_payment as pt', 'pt.id', 'c.payment_terms')
            ->whereNull('sales_invoice_header.deleted')
            ->get()
            ->toArray();

        return $datadb;
    }

    public function addAll(Request $request)
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
        $payment_method = $data['payment_method'] ?? 'CASH';
        $data['cashBankAccounts'] = $this->getListKasBank($payment_method);
        $data['data_customer'] = $this->getListCustomer();
        $view = view('web.sales_payment.formaddbulk', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionSalesPaymentController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['cashBankAccounts'] = $this->getListKasBank();
        $data['details'] = SalesPaymentDtl::where('sales_payment_detail.payment_id', $data['id'])
            ->select([
                'sales_payment_detail.*',
                'sih.invoice_number',
                'sih.invoice_date',
                'sih.total_amount as total_amount_invoice',
                'sih.discount_amount',
                'sih.subtotal',
                'sih.amount_paid',
                'c.code as customer_code',
                'c.nama_customer',
                'c.id as customer_id'
            ])
            ->join('sales_invoice_header as sih', 'sih.id', 'sales_payment_detail.invoice_id')
            ->join('customer as c', 'sih.customer_id', 'c.id')
            ->whereNull('sales_payment_detail.deleted')
            ->orderBy('sales_payment_detail.id')
            ->get();

        $data['general_ledgers'] = getGeneralLedger($data['data']->payment_code);
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.sales_payment.formadd', $data);
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
        $data = SalesPaymentHeader::with(['customers', 'items.invoice'])->findOrFail($data['id']);
        // $qr = base64_encode(QrCode::format('png')->size(80)->generate($data->payment_code));
        $qr = '';
        // echo '<pre>';
        // print_r($data->items);
        // die;

        // Kalkulasi total, subtotal, dsb bisa disiapkan di sini

        $pdf = Pdf::loadView('web.sales_payment.print.po-print', compact('data',  'company', 'qr'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('SP-' . $data->payment_code . '.pdf');
    }

    public function cetakRekapan(Request $request)
    {
        $data = $request->all();
        $company = CompanyModel::where('id', session('id_company'))->first();
        $data['data_payment'] = SalesPaymentHeader::with([
            'customers',
            'customers.kecamatans',
            'items.invoice',
            'items.invoice.do',
            'items.invoice.do.so.salesmans',
            'items.invoice.so.salesmans'
        ])
            ->select(['sales_payment_header.*', 'c.nama_customer', 'c.code as customer_code'])
            ->join('customer as c', 'c.id', 'sales_payment_header.customer_id')
            ->where('payment_date', $data['date'])->get();
        $qr = '';

        // echo '<pre>';
        // print_r($data['data_payment']);
        // die;
        $salesmans = isset($data['salesman']) ? $data['salesman'] : '';
        if ($salesmans != '') {
            $salesmans = User::find($salesmans);
        }

        $pdf = Pdf::loadView('web.sales_payment.print.print-rekapan-sp', compact('data',  'company', 'qr', 'salesmans'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('REKAPAN-SP-' . $data['date'] . '.pdf');
    }
}
