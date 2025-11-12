<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Customer;
use App\Models\Master\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SalesPlanController extends Controller
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
            'js-1' => asset('assets/js/controllers/transaction/sales_plan.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Route Sales Plan';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Plan';
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.sales_plan.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function add()
    {
        $data['data'] = []; // Data header kosong untuk form
        $data['plan_code'] = generatePlanCode(); // Fungsi generate plan_code, bisa dibuat di model/helper
        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();

        // Dropdown
        $data['salesmen'] = User::where('user_group', '1')->whereNull('deleted')->get(['id', 'name']);
        $data['customers'] = Customer::whereNull('deleted')->get(['id', 'nama_customer']);
        $data['products'] = Product::whereNull('deleted')->get(['id', 'name']);

        $data['data_item'] = []; // Data detail kosong

        $view = view('web.sales_plan.formadd', $data);

        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionSalesOrderController;
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['customers'] = Customer::whereNull('deleted')->get();
        $data['taxes'] = Tax::where('is_active', 1)
            ->whereNull('deleted')
            ->orderBy('tax_name')
            ->get(['id', 'tax_name', 'rate']);
        $data['data_item'] = SalesOrderDetail::where('purchase_order_detail.purchase_order', $data['id'])
            ->select([
                'purchase_order_detail.*',
                'p.id as product_id',
                'p.name as product_name',
                'u.name as unit_name',
            ])
            ->join('product as p', 'p.id', 'purchase_order_detail.product')
            ->join('unit as u', 'u.id', 'purchase_order_detail.unit')
            ->get();

        $data['currencies'] = Currency::whereNull('deleted')->get();
        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.sales_plan.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function cetak(Request $request)
    {
        $data = $request->all();
        $company = CompanyModel::where('id', session('id_company'))->first();
        $data = SalesOrderHeader::with(['customers', 'items.products', 'items.units'])->findOrFail($data['id']);
        $qr = base64_encode(QrCode::format('png')->size(80)->generate($data->code));
        // $qr = '';
        // echo '<pre>';
        // print_r($data);
        // die;

        // Kalkulasi total, subtotal, dsb bisa disiapkan di sini
        $total = $data->items->sum('subtotal');

        $pdf = Pdf::loadView('web.sales_plan.print.po-print', compact('data', 'total', 'company', 'qr'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('SALESPLAN-'.$data->code.'.pdf');
    }
}
