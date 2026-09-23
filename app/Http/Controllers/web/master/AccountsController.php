<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\AccountsController as MasterAccountsController;
use App\Http\Controllers\Controller;
use App\Models\Master\Accounts;
use App\Models\Master\AccountTypes;
use Illuminate\Http\Request;

class AccountsController extends Controller
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
            'js-1' => asset('assets/js/controllers/master/accounts.js'),
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
        return "Accounts";
    }

    public function getListData($exclude_id = null)
    {
        $query = Accounts::select('id', 'code', 'name')
            ->whereNull('deleted_at')
            ->orderBy('code');
        if(!empty($exclude_id)){
            $query->where('id', '!=', $exclude_id);
        }
        return $query->get()->toArray();
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $view = view('web.accounts.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function add()
    {
        $data['data'] = [];
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['account_type_list'] = AccountTypes::select('id', 'code', 'name')
            ->orderBy('code')
            ->get()
            ->toArray();
        $data['parent_list'] = $this->getListData();
        $view = view('web.accounts.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new MasterAccountsController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['account_type_list'] = AccountTypes::select('id', 'code', 'name')
            ->orderBy('code')
            ->get()
            ->toArray();
        $data['parent_list'] = $this->getListData($data['id']);

        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.accounts.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
