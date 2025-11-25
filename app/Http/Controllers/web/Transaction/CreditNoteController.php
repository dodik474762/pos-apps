<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\CreditNoteController as TransactionCreditNoteController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Tax;
use App\Models\Transaction\CreditNoteDtl;
use App\Models\Transaction\CreditNoteHdr;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class CreditNoteController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/credit_note.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Koreksi Penjualan';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Credit Note';
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.credit_note.index', $data);
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
        $view = view('web.credit_note.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionCreditNoteController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['cashBankAccounts'] = $this->getListKasBank();
        $data['details'] = CreditNoteDtl::where('credit_note_detail.credit_note_id', $data['id'])
            ->select([
                'credit_note_detail.*',
                'p.code as product_code',
                'p.name as product_name',
                'sid.qty',
                'sid.discount',
                'sid.tax',
                'sid.tax_amount as tax_amount_invoice',
                'sid.tax_rate',
                'sid.type_tax',
            ])
            ->join('sales_invoice_detail as sid', 'sid.id', 'credit_note_detail.invoice_detail_id')
            ->join('product as p', 'p.id', 'credit_note_detail.product_id')
            ->whereNull('credit_note_detail.deleted')
            ->orderBy('credit_note_detail.id')
            ->get();

        $data['general_ledgers'] = getGeneralLedger($data['data']->credit_note_number);
        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.credit_note.formadd', $data);
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
        $data = CreditNoteHdr::with(['customers', 'items.invoice'])->findOrFail($data['id']);
        $qr = base64_encode(QrCode::format('png')->size(80)->generate($data->payment_code));
        // $qr = '';
        // echo '<pre>';
        // print_r($data->items);
        // die;

        // Kalkulasi total, subtotal, dsb bisa disiapkan di sini

        $pdf = Pdf::loadView('web.credit_note.print.po-print', compact('data',  'company', 'qr'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('SP-'.$data->payment_code.'.pdf');
    }
}
