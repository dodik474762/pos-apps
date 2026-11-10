<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\SalesPlanController as TransactionSalesPlanController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Customer;
use App\Models\Master\Dictionary;
use App\Models\Master\Product;
use App\Models\Transaction\SalesPlanDetail;
use App\Models\Transaction\SalesPlanDetailRoute;
use App\Models\Transaction\SalesPlanHeader;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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

    public function getListVisitType()
    {
        $datadb = Dictionary::where('context', 'VISIT_TYPE')->get()->toArray();
        return $datadb;
    }

    public function add()
    {
        $data['data'] = []; // Data header kosong untuk form
        $data['plan_code'] = generatePlanCode(); // Fungsi generate plan_code, bisa dibuat di model/helper
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['period_year'] = (int) date('Y');
        $data['period_month'] = (int) date('m');

        // Dropdown
        $data['salesmen'] = User::whereNull('deleted')->get(['id', 'name']);
        $data['used_salesmen'] = SalesPlanHeader::whereNull('deleted')
            ->where('company_id', session('id_company'))
            ->where('period_year', $data['period_year'])
            ->where('period_month', $data['period_month'])
            ->pluck('salesman')
            ->map(fn($id) => (int) $id)
            ->toArray();

        $data['data_item'] = []; // Data detail kosong
        $data['visit_types'] = $this->getListVisitType();

        $view = view('web.sales_plan.formaddnew', $data);

        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
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
        $data['salesmen'] = User::whereNull('deleted')->get(['id', 'name']);
        $data['period_year'] = $data['data']->period_year;
        $data['period_month'] = $data['data']->period_month;
        $data['used_salesmen'] = SalesPlanHeader::whereNull('deleted')
            ->where('company_id', session('id_company'))
            ->where('period_year', $data['period_year'])
            ->where('period_month', $data['period_month'])
            ->where('id', '<>', $data['id'])
            ->pluck('salesman')
            ->map(fn($id) => (int) $id)
            ->toArray();
        $data['sales_plan_details'] = SalesPlanDetailRoute::where('sales_plan_detail_route.header_id', $data['id'])
            ->select([
                'sales_plan_detail_route.*',
                'c.nama_customer',
                'c.code as kode_customer',
            ])
            ->join('customer as c', 'c.id', 'sales_plan_detail_route.customer_id')
            ->get();

        $data['visit_types'] = $this->getListVisitType();
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.sales_plan.formaddnew', $data);
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

        return $pdf->stream('SALESPLAN-' . $data->code . '.pdf');
    }

    public function import(Request $request)
    {
        $api = new TransactionSalesPlanController();
        $data = $request->all();

        $data['data'] = [];
        $data['title_parent'] = $this->getTitleParent();
        $data['title'] = 'Form Import ' . $this->getTitle();
        $view = view('web.sales_plan.form_import', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function downloadTemplate(Request $request)
    {
        $type = strtolower((string) $request->get('type', 'kandidat'));
        $sourceFile = $type === 'register'
            ? base_path('dokumen/REV_PJP REGISTER OUTLET_UBAH RUTE_26 MARET.xlsx')
            : base_path('dokumen/REV_PJP KANDIDAT_UBAH RUTE_26 MARET.xlsx');

        if (!file_exists($sourceFile)) {
            abort(404, 'File template tidak ditemukan');
        }

        $reader = IOFactory::createReaderForFile($sourceFile);
        $reader->setReadDataOnly(true);
        $sourceSheet = $reader->load($sourceFile)->getActiveSheet();
        $header = $sourceSheet->rangeToArray('A1:' . $sourceSheet->getHighestColumn() . '1')[0];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PJP');
        $sheet->fromArray($header, null, 'A1');

        foreach (range('A', $sourceSheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $fileName = $type === 'register'
            ? 'Template_Import_PJP_Register_Outlet.xlsx'
            : 'Template_Import_PJP_Kandidat.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $header = SalesPlanHeader::whereNull('deleted')->findOrFail($request->get('id'));
        $salesman = User::find($header->salesman);

        $details = DB::table('sales_plan_detail_route as d')
            ->join('customer as c', 'c.id', '=', 'd.customer_id')
            ->leftJoin('dictionary as v', 'v.id', '=', 'd.visit_circle')
            ->where('d.header_id', $header->id)
            ->orderBy('d.id')
            ->get([
                'd.*',
                'c.code as customer_code',
                'c.nama_customer',
                'v.term_id as circle_term_id',
                'v.keterangan as circle_name',
            ]);

        $visitCircles = DB::table('dictionary')
            ->where('context', 'VISIT_TYPE')
            ->get()
            ->keyBy('id');

        $sourceFile = base_path('dokumen/REV_PJP KANDIDAT_UBAH RUTE_26 MARET.xlsx');
        if (!file_exists($sourceFile)) {
            abort(404, 'File template tidak ditemukan');
        }

        $reader = IOFactory::createReaderForFile($sourceFile);
        $reader->setReadDataOnly(true);
        $sourceSheet = $reader->load($sourceFile)->getActiveSheet();
        $templateHeader = $sourceSheet->rangeToArray('A1:' . $sourceSheet->getHighestColumn() . '1')[0];

        $rows = [$templateHeader];

        foreach ($details as $index => $detail) {
            $dbCircle = (int) $detail->visit_circle;
            $exportCircle = $dbCircle;
            if ($dbCircle === 13) {
                $exportCircle = 14;
            } elseif ($dbCircle === 14) {
                $exportCircle = 13;
            }

            $circle = $visitCircles[$exportCircle] ?? null;
            $outletStatus = strtoupper((string) ($detail->outlet_status ?? 'REGULER'));
            if ($outletStatus !== 'KANDIDAT') {
                $outletStatus = 'REGULER';
            }

            $rows[] = [
                $index + 1,
                $salesman->username ?? '',
                $salesman->name ?? '',
                $detail->customer_code,
                $detail->nama_customer,
                $outletStatus === 'KANDIDAT' ? 2 : 1,
                $outletStatus,
                $circle->term_id ?? $detail->circle_term_id,
                $circle->keterangan ?? $detail->circle_name,
                'P1',
                $detail->pjp_status,
                (int) $detail->visit_mon === 1 ? 'Y' : 'N',
                (int) $detail->visit_tue === 1 ? 'Y' : 'N',
                (int) $detail->visit_wed === 1 ? 'Y' : 'N',
                (int) $detail->visit_thu === 1 ? 'Y' : 'N',
                (int) $detail->visit_fri === 1 ? 'Y' : 'N',
                (int) $detail->visit_sat === 1 ? 'Y' : 'N',
                (int) $detail->visit_sun === 1 ? 'Y' : 'N',
            ];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PJP');
        $sheet->fromArray($rows, null, 'A1');

        foreach (range('A', $sourceSheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $fileName = 'SalesPlan_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $header->plan_code) . '_Import.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPo(Request $request)
    {
        $api = new TransactionSalesPlanController();
        $data = $request->all();

        $data['data'] = [];
        $data['title_parent'] = $this->getTitleParent();
        $data['title'] = 'Form Export PO ' . $this->getTitle();
        $view = view('web.sales_plan.export_po', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
