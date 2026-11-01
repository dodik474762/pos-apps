<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ReportDeliveryController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function getData()
    {
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $start_date = $_POST['start_date'] ?? date('Y-m-01');
        $end_date = $_POST['end_date'] ?? date('Y-m-d');
        $principal = $_POST['principal'] ?? '';
        $company_id = session('id_company');

        $datadb = DB::table('packing_list as pl')
            ->select([
                'pl.id',
                'pl.packing_list_no',
                'pl.packing_date',
                'pl.vehicle_no',
                'pl.driver_name',
                'pl.expedition_name',
                'pl.status as packing_status',
                'pl_do.id as packing_list_do_id',
                'pl_do.delivery_order_id',
                'pl_do.confirm_date',
                'pl_do.status as delivery_status',
                'pl_do.receive_wh_date',
                'doh.do_number',
                'doh.do_date',
                'c.code as customer_code',
                'c.nama_customer',
                'pld.id as detail_id',
                'pld.product_id',
                'pld.qty_do',
                'pld.qty_packed',
                'pld.qty_received',
                'pld.remark as detail_remark',
                'pld.receive_wh_date as detail_receive_wh_date',
                'p.code as product_code',
                'p.name as product_name',
                'v.nama_vendor as principal',
            ])
            ->leftJoin('packing_list_do as pl_do', 'pl_do.packing_list_id', 'pl.id')
            ->leftJoin('delivery_order_header as doh', function ($q) {
                return $q->on('doh.id', 'pl_do.delivery_order_id')->whereNull('doh.deleted');
            })
            ->leftJoin('packing_list_detail as pld', function ($q) {
                return $q->on('pld.packing_list_id', 'pl.id')
                    ->on('pld.delivery_order_id', 'pl_do.delivery_order_id');
            })
            ->leftJoin('product as p', 'p.id', 'pld.product_id')
            ->leftJoin('vendor as v', 'v.id', 'p.vendor')
            ->leftJoin('customer as c', 'c.id', 'doh.customer_id')
            ->whereNull('pl.deleted')
            ->where('pl.type_transaction', 'PL')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('pl.company_id', $company_id))
            ->whereDate('pl.packing_date', '>=', $start_date)
            ->whereDate('pl.packing_date', '<=', $end_date)
            ->when($principal !== '', fn ($q) => $q->where('p.vendor', $principal));

        // Total (tanpa search)
        $data['recordsTotal'] = (clone $datadb)->count();

        // Search
        if (!empty($_POST['search']['value'])) {
            $keyword = $_POST['search']['value'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('pl.packing_list_no', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('pl.packing_date', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('pl.vehicle_no', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('pl.driver_name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('pl.expedition_name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('doh.do_number', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('doh.do_date', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('c.code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('p.code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('p.name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('v.nama_vendor', 'LIKE', '%' . $keyword . '%');
            });
        }

        $data['recordsFiltered'] = (clone $datadb)->count();

        // Order (divalidasi, anti SQL injection)
        $dir = strtolower($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $col = $_POST['order'][0]['column'] ?? null;

        switch ((string) $col) {
            case '1':
                $datadb->orderBy('pl.packing_date', $dir);
                break;
            case '2':
                $datadb->orderBy('pl.packing_list_no', $dir);
                break;
            case '3':
                $datadb->orderBy('doh.do_date', $dir);
                break;
            case '4':
                $datadb->orderBy('doh.do_number', $dir);
                break;
            case '5':
                $datadb->orderBy('pl_do.confirm_date', $dir);
                break;
            case '6':
                $datadb->orderBy('pld.receive_wh_date', $dir);
                break;
            case '7':
                $datadb->orderBy('c.nama_customer', $dir);
                break;
            case '8':
                $datadb->orderBy('v.nama_vendor', $dir);
                break;
            case '9':
                $datadb->orderBy('p.code', $dir);
                break;
            case '10':
                $datadb->orderBy('p.name', $dir);
                break;
            case '11':
                $datadb->orderBy('pld.qty_do', $dir);
                break;
            case '12':
                $datadb->orderBy('pld.qty_packed', $dir);
                break;
            case '13':
                $datadb->orderBy('pld.qty_received', $dir);
                break;
            case '14':
                $datadb->orderBy('pl.status', $dir);
                break;
            case '15':
                $datadb->orderBy('pl.driver_name', $dir);
                break;
            case '16':
                $datadb->orderBy('pl.vehicle_no', $dir);
                break;
            case '17':
                $datadb->orderBy('pl.expedition_name', $dir);
                break;
            default:
                $datadb->orderBy('pl.packing_date', 'desc')
                    ->orderBy('pl.id', 'desc')
                    ->orderBy('pld.id', 'asc');
                break;
        }

        // Paging
        if (isset($_POST['length']) && (int) $_POST['length'] > 0) {
            $datadb->limit((int) $_POST['length']);
            $datadb->offset((int) ($_POST['start'] ?? 0));
        }

        $data['data'] = $datadb->get()->toArray();
        $data['draw'] = $_POST['draw'] ?? 0;

        return json_encode($data);
    }
}
