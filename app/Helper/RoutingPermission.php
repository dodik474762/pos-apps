<?php

use App\models\master\DocumentTransaction;
use App\Models\Own\DocumentTransaction as OwnDocumentTransaction;
use App\Models\Own\RoutingPermission;
use App\Models\Own\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

function getPersmission($menu = "", $nik = ""){
    $user = session('user_id');
    $group_user = session('akses');

    // DB::enableQueryLog();
    $dataMenu = RoutingPermission::where('ug.akses', $group_user)
    ->select([
        'routing_permission.*',
        'am.kode_menu',
        'ug.akses'
    ])
    ->join('menu as am', 'am.id', '=', 'routing_permission.menu')
    ->join('roles as ug', 'ug.id', '=', 'routing_permission.roles')
    ->where('am.kode_menu', $menu);
    // if($state != ''){
    //     $dataMenu->where('routing_permission.state', $state);
    // }

    if($nik != ''){
        $dataMenu->where('routing_permission.nik', $nik);
    }
    $dataMenu = $dataMenu->first();
    // echo '<pre>';
    // print_r(DB::getQueryLog());die;

    $result = [];
    if(!empty($dataMenu)){
        return $dataMenu;
    }
    return $result;
}

function getPersmissionCreator($menu, $nik, $group = ""){
    DB::enableQueryLog();
    $dataAkses = User::where('users.nik', $nik)
    ->select([
        'users.*',
        'ug.akses',
        'kry.distributor'
    ])
    ->join('roles as ug', 'ug.id', 'users.roles')
    ->join('karyawan as kry', 'kry.nik', 'users.nik')
    ->whereNull('users.deleted');  


    if($group != ''){
        $dataAkses->where('ug.akses', $group);
    }else{
        $dataAkses->whereIn('ug.akses', ['DISTRIBUTOR']);
    }
    $dataAkses = $dataAkses->first();

    $group_user = !empty($dataAkses) ? $dataAkses->akses : '';
    $distributor = !empty($dataAkses) ? $dataAkses->distributor : '';
    // echo '<pre>';
    // print_r($dataAkses);die;

    DB::enableQueryLog();
    $dataMenu = RoutingPermission::where('ug.akses', $group_user)
    ->select([
        'routing_permission.*',
        'am.kode_menu',
        'ug.akses',
    ])
    ->join('menu as am', 'am.id', '=', 'routing_permission.menu')
    ->join('routing_header as rh', 'rh.id', 'routing_permission.routing_header')
    ->join('roles as ug', 'ug.id', '=', 'rh.roles')
    ->where('am.kode_menu', $menu)
    ->where('rh.distributor', $distributor);

    $dataMenu = $dataMenu->first();
    // echo '<pre>';
    // print_r($dataMenu);die;


    $result = [];
    if(!empty($dataMenu)){
        $dataNextApproval = getPersmissionNextAcc($menu, '', '', $distributor);
        $dataMenu['next_approval'] = $dataNextApproval;
        return $dataMenu;
    }
    return $result;
}

function getRoutingPermission($menu, $nik, $akses = ""){
    DB::enableQueryLog();
    $dataAkses = User::where('users.nik', $nik)
    ->select([
        'users.*',
        'ug.akses',
        'kry.distributor'
    ])
    ->join('roles as ug', 'ug.id', 'users.roles')
    ->join('karyawan as kry', 'kry.nik', 'users.nik')
    ->whereNull('users.deleted');  


    if($akses != ''){
        $dataAkses->where('ug.akses', $akses);
    }else{
        $dataAkses->whereIn('ug.akses', ['DISTRIBUTOR']);
    }
    $dataAkses = $dataAkses->first();

    $akses = !empty($dataAkses) ? $dataAkses->akses : '';
    $distributor = !empty($dataAkses) ? $dataAkses->distributor : '';
    // echo '<pre>';
    // print_r($dataAkses);die;

    DB::enableQueryLog();
    $dataMenu = RoutingPermission::where('ug.akses', $akses)
    ->select([
        'routing_permission.*',
        'am.kode_menu',
        'ug.akses',
    ])
    ->join('menu as am', 'am.id', '=', 'routing_permission.menu')
    ->join('routing_header as rh', 'rh.id', 'routing_permission.routing_header')
    ->join('roles as ug', 'ug.id', '=', 'rh.roles')
    ->where('am.kode_menu', $menu)
    ->where('rh.distributor', $distributor);

    $dataMenu = $dataMenu->first();
    // echo '<pre>';
    // print_r($dataMenu);die;


    $result = [];
    if(!empty($dataMenu)){
        $dataNextApproval = getPersmissionNextAcc($menu, '', '', $distributor);
        $dataMenu->next_approval = $dataNextApproval;
        return $dataMenu;
    }
    return $result;
}

function getPersmissionAcc($menu = "", $nik = "", $akses = ""){
    DB::enableQueryLog();
    $dataMenu = RoutingPermission::select([
        'routing_permission.*',
        'am.kode_menu',
        'ug.akses',
        'rh.distributor'
    ])
    ->join('menu as am', 'am.id', '=', 'routing_permission.menu')
    ->join('routing_header as rh', 'rh.id', 'routing_permission.routing_header')
    ->join('roles as ug', 'ug.id', '=', 'rh.roles')
    ->where('am.kode_menu', $menu);
    $dataMenu->where('routing_permission.nik', $nik);
    $dataMenu = $dataMenu->first();

    // echo '<pre>';
    // print_r($dataMenu);die;

    $result = [];
    if(!empty($dataMenu)){
        $dataNextApproval = getPersmissionNextAcc($menu, $dataMenu->state, $nik, $dataMenu->distributor);
        $dataMenu->next_approval = $dataNextApproval;
        return $dataMenu;
    }
    return $result;
}

function getPersmissionNext($menu = "", $state = "", $nik = ""){
    // echo $nik;die;
    $dataAkses = RoutingPermission::select([
        'rh.*'
    ])
    ->join('menu as am', 'am.id', '=', 'routing_permission.menu')
    ->join('routing_header as rh', 'rh.id', 'routing_permission.routing_header')
    ->where('am.kode_menu', $menu)
    ->orderBy('routing_permission.id', 'asc');
    if($nik != ''){
        $dataAkses->where('routing_permission.nik', $nik);
    }
    $dataAkses = $dataAkses->first();
    $distributor = $dataAkses->distributor;

    DB::enableQueryLog();
    $dataMenu = RoutingPermission::select([
        'routing_permission.*',
        'am.kode_menu',
        'ug.akses'
    ])
    ->join('menu as am', 'am.id', '=', 'routing_permission.menu')
    ->join('roles as ug', 'ug.id', '=', 'routing_permission.roles')
    ->join('routing_header as rh', 'rh.id', 'routing_permission.routing_header')
    ->where('am.kode_menu', $menu)
    ->where('routing_permission.prev_state', $state)
    ->where('rh.distributor', $distributor);


    $dataMenu = $dataMenu->first();

    // echo '<pre>';
    // print_r(DB::getQueryLog());die;

    $result = [];
    if(!empty($dataMenu)){
        return $dataMenu;
    }
    return $result;
}

function getPersmissionNextAcc($menu = "", $state = "", $nik = "", $distributor = ''){
    // echo $nik;die;
    $dataAkses = RoutingPermission::select([
        'rh.*'
    ])
    ->join('menu as am', 'am.id', '=', 'routing_permission.menu')
    ->join('routing_header as rh', 'rh.id', 'routing_permission.routing_header')
    ->where('am.kode_menu', $menu)
    ->where('rh.distributor', $distributor)
    ->orderBy('routing_permission.id', 'asc');
    if($nik != ''){
        $dataAkses->where('routing_permission.nik', $nik);
    }
    $dataAkses = $dataAkses->first();

    DB::enableQueryLog();
    $dataMenu = RoutingPermission::select([
        'routing_permission.*',
        'am.kode_menu',
        'ug.akses'
    ])
    ->join('menu as am', 'am.id', '=', 'routing_permission.menu')
    ->join('routing_header as rh', 'rh.id', 'routing_permission.routing_header')
    ->join('roles as ug', 'ug.id', '=', 'rh.roles')
    ->where('am.kode_menu', $menu)    
    ->where('rh.distributor', $distributor)
    ->orderBy('routing_permission.id', 'asc');
    if($state != ''){
        $dataMenu->where('routing_permission.prev_state', $state);
    }


    $dataMenu = $dataMenu->first();

    // echo '<pre>';
    // print_r(DB::getQueryLog());die;

    $result = [];
    if(!empty($dataMenu)){
        return $dataMenu;
    }
    return $result;
}

function getListAccApproval($document = ''){
    $datadb = OwnDocumentTransaction::where('doct.id', $document)
    ->select([
        'kry.nama',
        'act.created_at as waktu_transaksi',
        'document_transaction.state'
    ])
    ->join('actor as act', 'act.id', 'document_transaction.actor')
    ->join('document as doct', 'doct.id', 'document_transaction.document')
    ->join('users as usr', 'usr.id', 'act.users')
    ->join('karyawan as kry', 'kry.nik', 'usr.nik')
    ->orderBy('document_transaction.id', 'asc')
    ->get()->toArray();
    return $datadb;
}

function getAllRulesRouting($menu = '', $distributor = '31'){

    $dataMenu = RoutingPermission::select([
        'routing_permission.*',
        'am.kode_menu',
        'ug.akses',
        'kry.nama'
    ])
    ->join('menu as am', 'am.id', '=', 'routing_permission.menu')
    ->join('roles as ug', 'ug.id', '=', 'routing_permission.roles')
    ->join('routing_header as rh', 'rh.id', 'routing_permission.routing_header')
    ->join('karyawan as kry', 'kry.nik', 'routing_permission.nik')
    ->where('am.kode_menu', $menu)
    ->where('rh.distributor', $distributor)
    ->orderBy('routing_permission.id', 'asc');
    return $dataMenu->get()->toArray();
}

function getRoutingByNik($menu = '', $nik = '', $distributor = '31'){
    $dataMenu = RoutingPermission::select([
        'routing_permission.*',
        'am.kode_menu',
        'ug.akses',
        'kry.nama'
    ])
    ->join('menu as am', 'am.id', '=', 'routing_permission.menu')
    ->join('roles as ug', 'ug.id', '=', 'routing_permission.roles')
    ->join('routing_header as rh', 'rh.id', 'routing_permission.routing_header')
    ->join('karyawan as kry', 'kry.nik', 'routing_permission.nik')
    ->where('am.kode_menu', $menu)
    ->where('rh.distributor', $distributor)
    ->where('routing_permission.nik', $nik)
    ->orderBy('routing_permission.id', 'asc');
    return $dataMenu->first();
}

function getNikOwner(){
    return "00006700001";
}

function getPermissionOwner(){
    $data = [
        'to_users' => getNikOwner()
    ];
    return $data;
}
