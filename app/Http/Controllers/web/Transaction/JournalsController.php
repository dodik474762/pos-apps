<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\JournalsController as TransactionJournalsController;
use App\Http\Controllers\Controller;
use App\Models\Master\Accounts;
use App\Services\Accounting\JournalService;
use Illuminate\Http\Request;

class JournalsController extends Controller
{
    public $akses_menu = [];
    protected $journalService;

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
        $this->journalService = new JournalService();
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/js/controllers/transaction/journals.js'),
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
        return "Journals";
    }

    public function getAccountList()
    {
        return Accounts::select('id', 'code', 'name', 'normal_balance')
            ->whereNull('deleted_at')
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
        $view = view('web.journals.index', $data);
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
        $data['details'] = [];
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['account_list'] = $this->getAccountList();
        $view = view('web.journals.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new TransactionJournalsController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['details'] = $this->journalService->getDetailList($data['id']);
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['account_list'] = $this->getAccountList();
        $view = view('web.journals.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function detail(Request $request)
    {
        $api = new TransactionJournalsController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['details'] = $this->journalService->getDetailList($data['id']);
        $data['title'] = 'Detail ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['account_list'] = $this->getAccountList();
        $view = view('web.journals.detail', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Detail ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
