<?php

namespace App\Http\Controllers\web;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Master\Region;

class DashboardController extends Controller
{
    private $userGroup;
    private $id_user;

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->userGroup = session('akses');
        $this->id_user = session('user_id');
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/libs/leaflet/leaflet.js'),
            'js-2' => asset('assets/js/controllers/dashboard.js'),
            'js-3' => asset('assets/js/controllers/notification.js'),
            'css-1' => asset('assets/libs/leaflet/leaflet.css'),
        );
    }

    public function getTitleParent()
    {
        return "Monitoring";
    }

    public function getTableName()
    {
        return "";
    }

    public function index()
    {
        $year = date('Y');
        $data['data'] = [];
        $data['username'] = session('username');
        $data['data_province'] = Region::whereNull('parent')->whereNull('deleted')->get()->toArray();
        $view = view('web.dashboard.index', $data);

        $put['group_karyawan'] = $this->getListGroupKaryawan();
        $put['title_content'] = 'Dashboard';
        $put['title_top'] = 'Dashboard';
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }


    public function getListGroupKaryawan()
    {
        $data = DB::table('karyawan_group as kg')->whereNull('kg.deleted')
        ->select(['kg.*', 'dic.keterangan as group_name'])
        ->join('karyawan as kry', 'kry.id', '=', 'kg.karyawan')
        ->join('dictionary as dic', 'dic.term_id', '=', 'kg.group')
        ->join('users as usr', 'usr.nik', '=', 'kry.nik')
        ->where('usr.id', session('user_id'))
        ->get()->toArray();
        return $data;
    }

}
