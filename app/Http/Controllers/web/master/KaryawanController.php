<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\KaryawanController as MasterKaryawanController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\Dictionary;
use App\Models\Master\KaryawanGroup;
use App\Models\Master\KaryawanHasProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
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
            'js-1' => asset('assets/js/controllers/master/karyawan.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Master";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Karyawan";
    }

    public function getListBankAccount()
    {
        return Dictionary::where('context', 'BNK')->whereNull('deleted')->get()->toArray();
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r(session()->all());die;
        $view = view('web.karyawan.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function getListKaryawanGroup($id)
    {
        $datadb = KaryawanGroup::whereNull('karyawan_group.deleted')
            ->select(['karyawan_group.*', 'd.keterangan as group_name'])
            ->join('dictionary as d', 'd.term_id', 'karyawan_group.group')
            ->where('karyawan_group.karyawan', $id)
            ->get()->toArray();

        return $datadb;
    }

    public function add()
    {
        $data['data'] = [];
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['data_company'] = CompanyModel::whereNull('deleted')->where('status', 'APPROVED')->get()->toArray();
        $data['groups'] = Dictionary::where('context', 'GROUP')->whereNull('deleted')->get()->toArray();
        $data['akses'] = session('akses');
        $data['company'] = session('id_company');
        $data['list_bank'] = $this->getListBankAccount();
        $data['karyawan_product'] = [];
        $data['products'] = [];
        $data['karyawan_group'] = [];
        $view = view('web.karyawan.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function getListProduct($id){
        $datadb = KaryawanHasProduct::whereNull('karyawan_has_product.deleted')
        ->select(['karyawan_has_product.*', 'p.code as kode_product', 'p.name as nama_product'])
        ->join('product as p', 'p.id', 'karyawan_has_product.product')
        ->where('karyawan_has_product.karyawan', $id)
        ->get();
        return $datadb;
    }

    public function getAllProduct(){
        $datadb = DB::table('product as p')->whereNull('p.deleted')
        ->select(['p.*', 'v.nama_vendor'])
        ->leftJoin('vendor as v', 'v.id', 'p.vendor')
        ->get();
        return $datadb;
    }

    public function ubah(Request $request)
    {
        $api = new MasterKaryawanController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['akses'] = session('akses');
        $data['company'] = session('id_company');
        $data['list_bank'] = $this->getListBankAccount();
        $data['data_company'] = CompanyModel::whereNull('deleted')->where('status', 'APPROVED')->get()->toArray();
        $data['groups'] = Dictionary::where('context', 'GROUP')->whereNull('deleted')->get()->toArray();
        $data['karyawan_group'] = $this->getListKaryawanGroup($data['id']);
        $data['karyawan_product'] = $this->getListProduct($data['id']);
        $data['products'] = $this->getAllProduct();
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.karyawan.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
