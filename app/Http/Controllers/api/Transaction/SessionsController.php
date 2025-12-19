<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionsController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function getTableName()
    {
        return "mobile_session";
    }

    public function getData()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'k.nama_lengkap as karyawan_name',
            ])
            ->join('users as u', 'u.id', 'm.users')
            ->join('karyawan as k', 'k.nik', 'u.nik')
            ->whereNull('m.deleted');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.date_process', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.status', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('k.nama_lengkap', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('k.nik', 'LIKE', '%' . $keyword . '%');
                });
            }
            if (isset($_POST['order'][0]['column'])) {
                switch ($_POST['order'][0]['column']) {
                    case 0:
                        $datadb->orderBy('m.id', $_POST['order'][0]['dir']);
                        break;
                    default:
                        // $datadb->orderBy('m.id', $_POST['order'][0]['dir']);
                        break;
                }
                // $datadb->orderBy('m.id', $_POST['order'][0]['dir']);
            }
            $data['recordsFiltered'] = $datadb->get()->count();

            if (isset($_POST['length'])) {
                $datadb->limit($_POST['length']);
            }
            if (isset($_POST['start'])) {
                $datadb->offset($_POST['start']);
            }
        }
        $resultdb = [];
        $datadb = $datadb->get()->toArray();
        foreach ($datadb as $key => $value) {
            $value->akses = session('akses');
            $resultdb[] = $value;
        }
        $data['data'] = $resultdb;
        $data['draw'] = $_POST['draw'];
        $query = DB::getQueryLog();
        // echo '<pre>';
        // print_r($query);die;
        return json_encode($data);
    }
}
