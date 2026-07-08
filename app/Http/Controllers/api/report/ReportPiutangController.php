<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use App\Models\Transaction\SalesOrderHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportPiutangController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function getData(Request $request)
    {
        DB::enableQueryLog();
        $data = $request->all();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
        $date_start = $_POST['date_start'] ?? date('Y-m-d');
        $date_end = $_POST['date_end'] ?? date('Y-m-d');

        $datadb = SalesOrderHeader::from('sales_order_headers as m')
            ->select([
                'm.id',
                'm.salesman',
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
                'kec.name as kecamatan',
                'kab.name as kabupaten',
                'dv.cicle_type',
                DB::raw('(sih.total_amount - sih.amount_paid) AS outstanding_amount'),
                DB::raw('DATEDIFF(CURDATE(), sih.invoice_date) AS umur_faktur'),
                DB::raw('DATEDIFF(CURDATE(), sih.due_date) AS overdue'),
                DB::raw('
                    CASE
                        WHEN DATEDIFF(CURDATE(), sih.due_date) <= 0 THEN "Belum Jatuh Tempo"
                        WHEN DATEDIFF(CURDATE(), sih.due_date) BETWEEN 1 AND 3 THEN "0-3 Hari"
                        WHEN DATEDIFF(CURDATE(), sih.due_date) BETWEEN 4 AND 6 THEN "4-6 Hari"
                        WHEN DATEDIFF(CURDATE(), sih.due_date) BETWEEN 7 AND 14 THEN "7-14 Hari"
                        WHEN DATEDIFF(CURDATE(), sih.due_date) BETWEEN 15 AND 20 THEN "15-20 Hari"
                        ELSE "21 Hari ke Atas"
                    END AS cluster_overdue
                '),
                'sih.invoice_date',
                'sih.due_date',
                'sih.total_amount',
                'sih.amount_paid',
                'sih.invoice_number'
            ])
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->join('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.sales_order', 'm.id')->whereNull('sih.deleted');
            })
            ->leftJoin('users as usr', 'usr.id', 'm.salesman')
            ->leftJoin('region as kec', 'kec.id', 'c.kecamatan')
            ->leftJoin('region as kab', 'kab.id', 'c.kota')
            ->leftJoin('daily_visit as dv', function ($q) {
                return $q->on('dv.date_visit', 'm.so_date')
                    ->on('dv.users', 'm.salesman')
                    ->whereNull('dv.deleted');
            })
            // ->where('m.id', '1588')
            // ->where('usr.name', 'SLS-005')
            // ->where('sih.invoice_number', 'SI06260132')
            ->whereNull('m.deleted')
            ->whereNull('sih.deleted');
        if (isset($data['types']) && $data['types'] == 'per-penjual') {
            $datadb->where('m.total_amount', '>', 0);
        } else {
            $datadb->having('outstanding_amount', '>', 0);
        }

        if (isset($data['types'])) {
            if ($data['types'] == 'per-penjual') {
                $datadb->whereBetween('sih.invoice_date', [$date_start, $date_end])
                    ->whereNotNull('usr.name')
                    ->orderBy('usr.name', 'asc');
            } else {
                $datadb->whereDate('sih.invoice_date', '<=', $tanggal);
            }
        } else {
            $datadb->whereDate('sih.invoice_date', '<=', $tanggal);
        }

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
                        ->orWhere('sih.invoice_number', 'LIKE', '%' . $keyword . '%')
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
                        if (isset($_POST['types']) && $_POST['types'] == 'per-penjual') {
                            $datadb->orderBy('usr.name', $_POST['order'][0]['dir']);
                        } else {
                            $datadb->orderBy('sih.invoice_date', $_POST['order'][0]['dir']);
                        }
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
        $data['draw'] = isset($_POST['draw']) ? $_POST['draw'] : '';

        $query = DB::getQueryLog();
        return json_encode($data);
    }
}
