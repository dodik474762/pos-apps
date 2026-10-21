<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\AccountMappingRulesController as MasterAccountMappingRulesController;
use App\Http\Controllers\Controller;
use App\Models\Master\Accounts;
use App\Models\Master\CompanyModel;
use App\Models\Master\Warehouse;
use Illuminate\Http\Request;

class AccountMappingRulesController extends Controller
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
            'js-1' => asset('assets/js/controllers/master/account_mapping_rules.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Accounting";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Account Mapping Rules";
    }

    public function getListAccount()
    {
        $company_id = session('id_company');
        return Accounts::select('id', 'code', 'name')
            ->whereNull('deleted_at')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))
            ->where('is_active', 1)
            ->where('is_header', 0)
            ->orderBy('code')
            ->get()
            ->toArray();
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $view = view('web.account_mapping_rules.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function add()
    {
        $company_id = session('id_company');
        $data['data'] = [];
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['account_list'] = $this->getListAccount();
        $data['warehouse_list'] = Warehouse::select('id', 'name', 'code')
            ->whereNull('deleted')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))
            ->orderBy('name')
            ->get()
            ->toArray();
        $data['company_list'] = CompanyModel::select('id', 'nama_company')
            ->whereNull('deleted')
            ->orderBy('nama_company')
            ->get()
            ->toArray();
        $view = view('web.account_mapping_rules.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new MasterAccountMappingRulesController();
        $company_id = session('id_company');
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['account_list'] = $this->getListAccount();
        $data['warehouse_list'] = Warehouse::select('id', 'name', 'code')
            ->whereNull('deleted')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))
            ->orderBy('name')
            ->get()
            ->toArray();
        $data['company_list'] = CompanyModel::select('id', 'nama_company')
            ->whereNull('deleted')
            ->orderBy('nama_company')
            ->get()
            ->toArray();

        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.account_mapping_rules.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
