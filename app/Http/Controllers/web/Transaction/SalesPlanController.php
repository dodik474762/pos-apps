<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\SalesPlanController as TransactionSalesPlanController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Customer;
use App\Models\Master\Product;
use App\Models\Transaction\SalesPlanDetail;
use App\Models\Transaction\SalesPlanHeader;
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
        $api = new TransactionSalesPlanController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['salesmen'] = User::where('user_group', '1')->whereNull('deleted')->get(['id', 'name']);
        $data['sales_plan_details'] = SalesPlanDetail::where('sales_plan_detail.header_id', $data['id'])
            ->select([
                'sales_plan_detail.*',
                'p.id as product_id',
                'p.name as product_name',
                'c.nama_customer',
            ])
            ->join('customer as c', 'c.id', 'sales_plan_detail.customer_id')
            ->leftJoin('product as p', 'p.id', 'sales_plan_detail.product_id')
            ->get();

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
        $data = SalesPlanHeader::with(['items'])->findOrFail($data['id']);
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
