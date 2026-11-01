<?php

namespace App\Http\Controllers\web\report;

use App\Http\Controllers\Controller;
use App\Models\Master\Users;
use Illuminate\Http\Request;

class ReportVisitController extends Controller
{
    public $akses_menu = [];

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/js/controllers/report/report_visit.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Report";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Report Detail Kunjungan";
    }

    public function getTitleSummary()
    {
        return "Report Summary Kunjungan";
    }

    private function getSalesmans()
    {
        return Users::whereNull('deleted')
            ->whereIn('user_group', [6, 4])
            ->when(session('id_company') !== null && session('id_company') !== '', fn ($q) => $q->where('company_id', session('id_company')))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $data['salesmans'] = $this->getSalesmans();
        $view = view('web.report_visit.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function summary(Request $request)
    {
        $data = $request->all();
        $data['data'] = [];
        $data['title'] = $this->getTitleSummary();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $data['salesmans'] = $this->getSalesmans();
        $view = view('web.report_visit.summary_visit', $data);
        $put['title_content'] = $this->getTitleSummary();
        $put['title_top'] = $this->getTitleSummary();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
