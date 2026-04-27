<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportVisitController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function getData()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');

        $datadb = DB::table('sales_order_headers as m')
            ->select([
                'm.id',
                'm.salesman',
                // 'm.unit',
                'm.so_date',
                'c.nama_customer',
                'c.code as customer_code',
                'c.channel_outlet',
                'm.remarks',
                'm.check_in_time',
                'm.check_out_time',
                'usr.name as salesman_name',
                'm.status',
                'm.platform',
                'pr.start_date as absen_time',
                DB::raw('SEC_TO_TIME(
    GREATEST(
        TIMESTAMPDIFF(MINUTE,
            COALESCE(
                LAG(m.check_out_time) OVER (
                    PARTITION BY m.salesman 
                    ORDER BY m.check_in_time
                ),
                CONCAT(DATE(m.so_date), " 08:00:00")
            ),
            m.check_in_time
        ),
    0)
) as lama_di_jalan')
            ])
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->leftJoin('users as usr', 'usr.id', 'm.salesman')
            ->leftJoin('presence as pr', 'pr.creator', 'm.salesman')
            ->whereDate('m.so_date', '=', $tanggal)
            ->whereDate('pr.presence_date', '=', $tanggal)
            ->whereNull('m.deleted')
            ->orderBy('usr.name', 'asc')
            ->orderBy('m.check_in_time', 'asc');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();

            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.salesman', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.so_date', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('c.code', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('c.channel_outlet', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.remarks', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.check_in_time', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.check_out_time', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('usr.name', 'LIKE', '%' . $keyword . '%');
                });
            }

            if (isset($_POST['order'][0]['column'])) {
                switch ($_POST['order'][0]['column']) {
                    case 0:
                        $datadb->orderBy('m.salesman', $_POST['order'][0]['dir']);
                        break;
                    case 1:
                        $datadb->orderBy('m.so_date', $_POST['order'][0]['dir']);
                        break;
                    case 2:
                        $datadb->orderBy('c.nama_customer', $_POST['order'][0]['dir']);
                        break;
                    case 3:
                        $datadb->orderBy('c.code', $_POST['order'][0]['dir']);
                        break;
                    case 4:
                        $datadb->orderBy('c.channel_outlet', $_POST['order'][0]['dir']);
                        break;
                    case 5:
                        $datadb->orderByRaw('m.check_in_time ' . $_POST['order'][0]['dir']);
                        break;
                    case 6:
                        $datadb->orderByRaw('m.check_out_time ' . $_POST['order'][0]['dir']);
                        break;
                    case 7:
                        $datadb->orderByRaw('m.salesman ' . $_POST['order'][0]['dir']);
                        break;
                    default:
                        $datadb->orderBy('m.salesman', 'asc');
                        break;
                }
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

        foreach ($datadb as $value) {
            $resultdb[] = $value;
        }

        $data['data'] = $resultdb;
        $data['draw'] = $_POST['draw'];

        $query = DB::getQueryLog();
        return json_encode($data);
    }
}
