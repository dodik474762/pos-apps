<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\RoutingApprovalController as MasterRoutingApprovalController;
use App\Http\Controllers\Controller;
use App\Models\Master\Dictionary;
use App\Models\Master\Menu;
use App\Models\Master\RoutingPermission;
use App\Models\Master\RoutingReminder;
use Illuminate\Http\Request;

class RoutingApprovalController extends Controller
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
            'js-1' => asset('assets/js/controllers/master/routing_approval.js'),
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
        return "Routing Approval";
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        // echo '<pre>';
        // print_r($data);die;
        $view = view('web.routing_approval.index', $data);
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
        $data['list_approval'] = Dictionary::whereNull('deleted')->where('context', 'ROUTE_MODULE')->get()->toArray();
        $data['list_module'] = Menu::whereNull('deleted')->whereNotNull('parent')->where('routing', 1)->whereNull('deleted')->get()->toArray();
        $data['groups'] = Dictionary::where('context', 'GROUP')->whereNull('deleted')->get()->toArray();
        $data['routing_item'] = [];
        $data['routing_reminder_item'] = [];
        $view = view('web.routing_approval.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new MasterRoutingApprovalController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;

        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['list_approval'] = Dictionary::whereNull('deleted')->where('context', 'ROUTE_MODULE')->get()->toArray();
        $data['list_module'] = Menu::whereNull('deleted')->whereNotNull('parent')->where('routing', 1)->whereNull('deleted')->get()->toArray();
        $data['groups'] = Dictionary::where('context', 'GROUP')->whereNull('deleted')->get()->toArray();
        $data['routing_item'] = RoutingPermission::where('routing_permission.routing_header', $data['id'])
            ->select(['routing_permission.*', 'k.nama_lengkap as name_user'])
            ->join('users as u', 'u.id', 'routing_permission.users')
            ->join('karyawan as k', 'k.nik', 'u.nik')
            ->whereNull('routing_permission.deleted')
            ->orderBy('routing_permission.id', 'asc')
            ->get()->toArray();
        $data['routing_reminder_item'] = RoutingReminder::where('routing_reminder.routing_header', $data['id'])
            ->select(['routing_reminder.*', 'k.nama_lengkap as name_user'])
            ->join('users as u', 'u.id', 'routing_reminder.users')
            ->join('karyawan as k', 'k.nik', 'u.nik')
            ->whereNull('routing_reminder.deleted')->get()->toArray();
        $view = view('web.routing_approval.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }
}
