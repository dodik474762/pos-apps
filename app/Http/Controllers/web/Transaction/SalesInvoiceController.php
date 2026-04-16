<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\SalesInvoiceController as TransactionSalesInvoiceController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Karyawan;
use App\Models\Master\Tax;
use App\Models\Transaction\DeliveryOrderHeader;
use App\Models\Transaction\SalesInvoiceDtl;
use App\Models\Transaction\SalesInvoiceHeader;
use App\Models\Transaction\SalesOrderHeader;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SalesInvoiceController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/sales_invoice.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Tagihan';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Sales Invoice';
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.sales_invoice.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function getAllInvoiceCetak($date = '', $state = '')
    {
        $date = $date == '' ? date('Y-m-d') : date('Y-m-d', strtotime($date));
        $datadb = DB::table('sales_invoice_header as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'cc.nama_customer',
                'do.do_number',
                'do.do_date',
                'w.name as warehouse_name',
                'so.so_number',
                'so.so_date'
            ])
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('customer as cc', 'cc.id', 'm.customer_id')
            ->leftJoin('delivery_order_header as do', 'do.id', 'm.do_id')
            ->leftJoin('sales_order_headers as so', 'so.id', 'm.sales_order')
            ->join('warehouse as w', 'w.id', 'm.warehouse_id')
            ->where('m.invoice_date', $date)
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if ($state == '') {
            $datadb->where(function ($q) {
                return $q->where('m.reprint', 1)
                    ->orWhereNull('m.print_date');
            });
        }

        $datadb = $datadb->get();

        return $datadb;
    }

    public function cetakAll(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $data['invoices'] = $this->getAllInvoiceCetak($data['tanggal'], $data['state']);
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.sales_invoice.cetakall', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
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
        $view = view('web.sales_invoice.formaddso', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function addFromDo(Request $request)
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
        $view = view('web.sales_invoice.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionSalesInvoiceController;
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->where('tax_type', 'Output')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['details'] = SalesInvoiceDtl::where('sales_invoice_detail.invoice_id', $data['id'])
            ->select([
                'sales_invoice_detail.*',
                'p.id as product_id',
                'p.name as product_name',
                'p.code as product_code',
                'u.name as unit_name',
                'soh.discount_amount as discount_amount_header'
            ])
            ->join('sales_order_details as sod', 'sod.id', 'sales_invoice_detail.so_detail_id')
            ->join('sales_order_headers as soh', 'soh.id', 'sod.sales_order_id')
            ->join('product as p', 'p.id', 'sales_invoice_detail.product_id')
            ->join('unit as u', 'u.id', 'sod.unit')
            ->whereNull('sales_invoice_detail.deleted')
            ->orderBy('sales_invoice_detail.id')
            ->get();

        $data['general_ledgers'] = getGeneralLedger($data['data']->invoice_number);
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = $data['data']->do_id != '' ? view('web.sales_invoice.formadd', $data) : view('web.sales_invoice.formaddso', $data);
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
        $data = SalesInvoiceHeader::with(['so', 'do.so', 'customers', 'customers.top', 'warehouses', 'items.products', 'items.so_detail.units'])->findOrFail($data['id']);
        // $qr = base64_encode(QrCode::format('png')->size(80)->generate($data->invoice_number));
        $qr = '';

        $promo_item = DB::table('sales_order_promo_item as sopi')
            ->where('sales_order_id', $data->so->id)
            ->get();

        $promo = DB::table('sales_order_promo as sop')
            ->select([
                'sop.promo_name',
                DB::raw('MAX(sop.discount_percent) as discount_percent'),
                DB::raw('SUM(sop.discount_amount) as total_potongan')
            ])
            ->where('sop.sales_order_id', $data->so->id)
            ->groupBy('sop.promo_name')
            ->get();

        // echo '<pre>';
        // print_r($promo);
        // die;

        $total_print = $data->print_total == '' ? 0 : $data->print_total;
        SalesInvoiceHeader::where('id', $data->id)->update([
            'print_total' => $total_print + 1,
            'print_by' => session('user_id'),
            'print_date' => now(),
            'reprint' => 0, // tidak reprint
        ]);
        $do = $data['do_id'] != '' ? DeliveryOrderHeader::where('id', $data->do_id)->first() : [];
        $so = $data['do_id'] != '' ? SalesOrderHeader::where('id', $do->so_id)->first() : SalesOrderHeader::where('id', $data->sales_order)->first();
        $salesman = Karyawan::where('id', $so->salesman)->first();
        $salesman_name = ! empty($salesman) ? $salesman->nama_lengkap : '-';

        $tax = DB::table('tax')->where('id', $so->tax_id)->first();
        $ppn_val = '';
        if (!empty($tax)) {
            $ppn_val = number_format($tax->rate, 0, ',', '.');
        }

        $customPaper = [0, 0, 612.0, 792.0]; //Letter
        $pdf = Pdf::loadView('web.sales_invoice.print.po-printa5', compact('data', 'company', 'qr', 'so', 'salesman_name', 'promo', 'promo_item', 'ppn_val'))
            ->setPaper($customPaper, 'portrait');

        return $pdf->stream('SI-' . $data->invoice_number . '.pdf');
    }

    public function multiplePrint(Request $request)
    {
        $ids = explode(',', $request->ids);

        $invoices = SalesInvoiceHeader::with([
            'so',
            'do.so',
            'do.so.salesman',
            'customers',
            'customers.top',
            'warehouses',
            'items.products',
            'items.so_detail.units',
        ])
            ->whereIn('id', $ids)
            ->get();

        // echo '<pre>';
        // print_r($invoices);die;
        $company = CompanyModel::where('id', session('id_company'))->first();

        // Update print count per invoice
        foreach ($invoices as $data) {
            $total_print = empty($data->print_total) ? 0 : $data->print_total;
            $invUpdate = SalesInvoiceHeader::where('id', $data->id);
            $invUpdate->update([
                'print_total' => $total_print + 1,
                'print_by' => session('user_id'),
                'print_date' => now(),
                'reprint' => 0,
            ]);

            $so = $data['do_id'] != '' ? $data->do->so : $data->so;
            $so_id = $so ? $so->id : null;
            $ppn_val = '';

            if ($so_id) {
                $data->promo_item = DB::table('sales_order_promo_item as sopi')
                    ->where('sales_order_id', $so_id)
                    ->get();

                $data->promo = DB::table('sales_order_promo as sop')
                    ->select([
                        'sop.promo_name',
                        DB::raw('MAX(sop.discount_percent) as discount_percent'),
                        DB::raw('SUM(sop.discount_amount) as total_potongan')
                    ])
                    ->where('sop.sales_order_id', $so_id)
                    ->groupBy('sop.promo_name')
                    ->get();

                $tax = DB::table('tax')->where('id', $so->tax_id)->first();
                if (!empty($tax)) {
                    $ppn_val = number_format($tax->rate, 0, ',', '.');
                }
            } else {
                $data->promo_item = collect();
                $data->promo = collect();
            }

            $data->ppn_value = $ppn_val;
        }

        $customPaper = [0, 0, 612.0, 792.0]; //Letter
        $pdf = Pdf::loadView('web.sales_invoice.print.multipleprinta5', compact('invoices', 'company'))
            ->setPaper($customPaper, 'portrait');

        return $pdf->stream('Multiple-SIInvoice.pdf');
    }
}
