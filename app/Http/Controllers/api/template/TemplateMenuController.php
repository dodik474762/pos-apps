<?php

namespace App\Http\Controllers\api\template;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class TemplateMenuController extends Controller
{
    public function generateMenuWeb(){
        // $user_id = session('user_id');
        $user_id = Session::get('user_id');
        $dataMenu = DB::table('menu as m')
        ->distinct()
        ->select([
            'm.id',
            'm.nama',
            'm.icon',
            'm.menu_alias',
            'm.url',
            'm.parent',
            'm.sort',
            'up.insert',
            'up.update',
            'up.delete',
            'up.view',
            'up.print',
        ])
        ->join('users_permissions as up', 'up.menu', 'm.id')
        ->join('users_group as ug', 'ug.id', 'up.users_group')
        ->join('users as u', 'u.user_group', 'ug.id')
        ->where('u.id', $user_id)
        ->whereNull('up.deleted')
        ->orderBy('m.parent')
        ->orderBy('m.sort')
        ->get()->toArray();
        $dataFix = $dataMenu;
        return $this->buildMenu($dataFix);
    }

    public function buildMenu($rows,$parent=0)
    {
        $result = "";
        foreach ($rows as $key => $row)
        {
            if ($row->parent == $parent){
                $url = trim($row->url) == '' || trim($row->url) == '-' ? '#collapse-left-menu-'.$row->id.'' : URL::to($row->url);
                $url_has_index = $row->url.'/index';
                $url_has_add = $row->url.'/add';
                $url_has_ubah = $row->url.'/ubah';
                $expanded_menu = request()->is($row->url) || request()->is($url_has_index) || request()->is($url_has_add) || request()->is($url_has_ubah) ? 'true' : 'false';
                $menu_togle = trim($row->url) == '-' || trim($row->url) == '' ? 'data-bs-toggle="collapse"  role="button" aria-expanded="'.$expanded_menu.'" aria-controls="collapse-left-menu-'.$row->id.'"' : '';
                $menu_togle_context = trim($row->url) == '-' || trim($row->url) == '' ? 'collapse menu-dropdown' : '';
                $menu_active = request()->is($row->url) || request()->is($url_has_index) || request()->is($url_has_add) || request()->is($url_has_ubah) ? 'active' : '';
                $linkName = "<i class='".$row->icon."'></i>
                <span>".$row->nama."</span>";
                if($row->parent != ''){
                    $linkName = $row->menu_alias == '' ? $row->nama : $row->menu_alias;
                }
                $result .= "<li data_id='".$row->id."' id='left-menu-".$row->id."' parent_menu='".$row->parent."' class='nav-item parent-menu-".$row->parent."' >
                <a  href='" .$url . "' class='nav-link  ".$menu_active." ' ".$menu_togle.">
                    ".$linkName."
                </a>
                ";
                $result.="
                        <ul class='navbar-nav' id='navbar-nav'>
                    ";
                if ($this->hasChild($rows,$row->id)){
                    $showChild = $menu_active == 'active' ? 'show' : '';
                    $result .= '<div class="'.$menu_togle_context.' '.$showChild.'" id="collapse-left-menu-'.$row->id.'">
                    <ul class="nav nav-sm flex-column">';
                    $result.= $this->buildMenu($rows,$row->id);
                    $result .='</ul></div>';
                }
                $result.="</ul>";
                $result.="</li>";
            }
        }
        return $result;
    }

    public function hasChild($rows,$id) {
        foreach ($rows as $key => $row) {
            if ($row->parent == $id)
            return true;
        }
        return false;
    }
}
