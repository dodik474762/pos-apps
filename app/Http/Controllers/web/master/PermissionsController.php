<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\PermissionsController as MasterPermissionsController;
use App\Http\Controllers\Controller;
use App\Models\Master\Menu;
use App\Models\Master\Roles;
use Illuminate\Http\Request;

class PermissionsController extends Controller
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
            'js-1' => asset('assets/js/controllers/master/permission.js'),
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
        return "Permission";
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $view = view('web.permission.index', $data);
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
        $data['data_roles'] = Roles::whereNull('deleted')->get()->toArray();
        $data['data_menu'] = Menu::whereNull('deleted')->get()->toArray();
        $data['list_menu_view'] = $this->buildMenu($data['data_menu']);
        $view = view('web.permission.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new MasterPermissionsController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;

        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $view = view('web.permission.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function hasChild($rows, $id)
    {
        foreach ($rows as $row) {
            if ($row['parent'] == $id)
                return true;
        }
        return false;
    }

    public function buildMenu($rows, $parent = 0)
    {
        $result = "<ul '>";
        foreach ($rows as $row) {
            if ($row['parent'] == $parent) {
                $result .= '<li><div class="form-check">
                <input class="form-check-input checkmenudata checkmenu-' . $row['id'] . ' parent-menu-' . $row['parent'] . '" parent_id="' . $row['parent'] . '" type="checkbox" value="" id="checkall" data_id="' . $row['id'] . '">
                <label class="form-check-label" for="defaultCheck2">
                    <b>' . $row['nama'] . '</b>
                </label>
            </div>
            <hr/>
            <div class="row g-3 action-menu-' . $row['id'] . '">
                <div class="col-sm-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="insert">
                        <label class="form-check-label" for="insert">
                            Simpan
                        </label>
                    </div>
                </div>

                <div class="col-sm-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="update">
                        <label class="form-check-label" for="update">
                            Edit
                        </label>
                    </div>
                </div>

                <div class="col-sm-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="delete">
                        <label class="form-check-label" for="delete">
                            Hapus
                        </label>
                    </div>
                </div>

                <div class="col-sm-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="view" >
                        <label class="form-check-label" for="view">
                            Lihat
                        </label>
                    </div>
                </div>

                <div class="col-sm-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="print">
                        <label class="form-check-label" for="print">
                            Print
                        </label>
                    </div>
                </div>
            </div><br/>';
                if ($this->hasChild($rows, $row['id']))
                    $result .= $this->buildMenu($rows, $row['id']);
                $result .= "</li>";
            }
        }
        $result .= "</ul>";

        return $result;
    }
}
