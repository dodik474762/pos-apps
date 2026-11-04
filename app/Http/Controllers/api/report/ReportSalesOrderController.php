<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ReportSalesOrderController extends Controller
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
        $session_vendor_id = session('vendor_id');
        if (! empty($session_vendor_id)) {
            $principal = $session_vendor_id;
        }
        $company_id = session('id_company');

        $datadb = DB::table('sales_order_headers as so')
            ->select([
                'so.id',
                'so.so_number',
                'so.so_date',
                'so.status as so_status',
                'so.platform',
                'so.total_amount',
                'so.discount_percent',
                'so.discount_amount',
                'so.tax_amount',
                'so.remarks',
                'so.check_in_time',
                'so.check_out_time',
                'so.photo_path',
                'so.signature_path',
                'so.checkin_path',
                'so.owner_path',
                'c.code as customer_code',
                'c.nama_customer',
                'u.name as salesman_name',
                'b.name as branch_name',
                'w.name as warehouse_name',
                'cur.code as currency_code',
                'sod.id as detail_id',
                'sod.product_id',
                'sod.qty',
                'sod.unit_price',
                'sod.discount_amount as detail_discount_amount',
                'sod.subtotal',
                'sod.is_free_good',
                'p.code as product_code',
                'p.name as product_name',
                'un.name as unit_name',
                'v.nama_vendor as principal',
                'sih.invoice_number',
                'sih.invoice_date',
            ])
            ->leftJoin('sales_order_details as sod', function ($q) {
                return $q->on('sod.sales_order_id', 'so.id')->whereNull('sod.deleted');
            })
            ->leftJoin('product as p', 'p.id', 'sod.product_id')
            ->leftJoin('unit as un', 'un.id', 'sod.unit')
            ->leftJoin('vendor as v', 'v.id', 'p.vendor')
            ->leftJoin('customer as c', 'c.id', 'so.customer_id')
            ->leftJoin('users as u', 'u.id', 'so.salesman')
            ->leftJoin('branch as b', 'b.id', 'so.branch')
            ->leftJoin('warehouse as w', 'w.id', 'so.warehouse')
            ->leftJoin('currency as cur', 'cur.id', 'so.currency')
            ->leftJoin('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.sales_order', 'so.id')->whereNull('sih.deleted');
            })
            ->whereNull('so.deleted')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('so.company_id', $company_id))
            ->whereDate('so.so_date', '>=', $start_date)
            ->whereDate('so.so_date', '<=', $end_date)
            ->when($principal !== '', fn ($q) => $q->where('p.vendor', $principal));

        $data['recordsTotal'] = (clone $datadb)->count();

        if (! empty($_POST['search']['value'])) {
            $keyword = $_POST['search']['value'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('so.so_number', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('so.so_date', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('so.status', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('so.platform', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('c.code', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('c.nama_customer', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('u.name', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('b.name', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('w.name', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('p.code', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('p.name', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('v.nama_vendor', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('sih.invoice_number', 'LIKE', '%'.$keyword.'%');
            });
        }

        $data['recordsFiltered'] = (clone $datadb)->count();

        $dir = strtolower($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $col = $_POST['order'][0]['column'] ?? null;

        switch ((string) $col) {
            case '1':
                $datadb->orderBy('so.so_date', $dir);
                break;
            case '2':
                $datadb->orderBy('so.so_number', $dir);
                break;
            case '3':
                $datadb->orderBy('c.nama_customer', $dir);
                break;
            case '4':
                $datadb->orderBy('u.name', $dir);
                break;
            case '5':
                $datadb->orderBy('b.name', $dir);
                break;
            case '6':
                $datadb->orderBy('w.name', $dir);
                break;
            case '7':
                $datadb->orderBy('p.code', $dir);
                break;
            case '8':
                $datadb->orderBy('p.name', $dir);
                break;
            case '9':
                $datadb->orderBy('v.nama_vendor', $dir);
                break;
            case '10':
                $datadb->orderBy('sod.qty', $dir);
                break;
            case '11':
                $datadb->orderBy('un.name', $dir);
                break;
            case '12':
                $datadb->orderBy('sod.unit_price', $dir);
                break;
            case '13':
                $datadb->orderBy('sod.subtotal', $dir);
                break;
            case '14':
                $datadb->orderBy('so.status', $dir);
                break;
            case '15':
                $datadb->orderBy('so.platform', $dir);
                break;
            case '16':
                $datadb->orderBy('sih.invoice_number', $dir);
                break;
            default:
                $datadb->orderBy('so.so_date', 'desc')
                    ->orderBy('so.id', 'desc')
                    ->orderBy('sod.id', 'asc');
                break;
        }

        if (isset($_POST['length']) && (int) $_POST['length'] > 0) {
            $datadb->limit((int) $_POST['length']);
            $datadb->offset((int) ($_POST['start'] ?? 0));
        }

        $data['data'] = $datadb->get()->toArray();
        $data['draw'] = $_POST['draw'] ?? 0;

        return json_encode($data);
    }
}
