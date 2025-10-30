<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\CustomerController as MasterCustomerController;
use App\Http\Controllers\Controller;
use App\Models\Master\CompanyModel;
use App\Models\Master\CustomerCategory;
use App\Models\Master\Region;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    //
    public $akses_menu = [];
    public function __construct(){
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/js/controllers/master/customer.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent(){
        return "Data";
    }

    public function getTableName(){
        return "";
    }

    public function getTitle(){
        return "Customer";
    }

    public function index(){
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r(session()->all());die;
        $view = view('web.customer.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function add(){
        $data['data'] = [];
        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['data_category'] = CustomerCategory::whereNull('deleted')->get()->toArray();
        $data['akses'] = session('akses');
        $data['company'] = session('id_company');
        $data['data_province'] = Region::whereNull('parent')->whereNull('deleted')->get()->toArray();
        $view = view('web.customer.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function ubah(Request $request){
        $api = new MasterCustomerController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $data['akses'] = session('akses');
        $data['company'] = session('id_company');
        $data['data_category'] = CustomerCategory::whereNull('deleted')->get()->toArray();

        $data['title'] = 'Form '.$this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['data_province'] = Region::whereNull('parent')->whereNull('deleted')->get()->toArray();
        $view = view('web.customer.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form '.$this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
