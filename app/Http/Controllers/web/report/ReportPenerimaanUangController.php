<?php

namespace App\Http\Controllers\web\report;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ReportPenerimaanUangController extends Controller
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
            'js-1' => asset('assets/js/controllers/report/report_penerimaan_uang.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        ];
    }

    public function getTitleParent()
    {
        return 'Report';
    }

    public function getTableName()
    {
        return '';
    }

    public function getTitle()
    {
        return 'Report Penerimaan Uang';
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $company_id = session('id_company');
        $data['salesmans'] = User::whereNull('deleted')
            ->whereIn('user_group', [6, 4, 5])
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))
            ->orderBy('name')
            ->get(['id', 'nik', 'name']);
        $view = view('web.report_penerimaan_uang.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }
}
