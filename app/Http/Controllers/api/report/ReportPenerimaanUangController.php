<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ReportPenerimaanUangController extends Controller
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
        $salesman = $_POST['salesman'] ?? '';
        $company_id = session('id_company');

        $datadb = DB::table('receive_payment_header as rph')
            ->select([
                'rph.id',
                'rph.code',
                'rph.receive_date',
                'rph.visit_date',
                'rph.total_amount as header_total',
                'rph.reference_no',
                'rph.remarks',
                'rph.status',
                'rph.type_trans',
                'rpd.id as detail_id',
                'rpd.invoice_id',
                'rpd.amount_paid',
                'rpd.line_no',
                'sih.invoice_number',
                'sih.invoice_date',
                'sih.due_date',
                'sih.total_amount as invoice_total',
                'sih.amount_paid as invoice_paid',
                'sih.status as invoice_status',
                'c.code as customer_code',
                'c.nama_customer',
                'u.name as salesman_name',
                'u.nik as salesman_nik',
                'k.nama_lengkap as salesman_full_name',
                'b.name as branch_name',
                'w.name as warehouse_name',
                'doh.do_number',
                'doh.do_date',
            ])
            ->leftJoin('receive_payment_detail as rpd', function ($q) {
                return $q->on('rpd.receive_id', 'rph.id')->whereNull('rpd.deleted');
            })
            ->leftJoin('sales_invoice_header as sih', function ($q) {
                return $q->on('sih.id', 'rpd.invoice_id')->whereNull('sih.deleted');
            })
            ->leftJoin('customer as c', 'c.id', 'sih.customer_id')
            ->leftJoin('users as u', 'u.id', 'rph.salesman')
            ->leftJoin('karyawan as k', 'k.nik', 'u.nik')
            ->leftJoin('branch as b', 'b.id', 'u.branch')
            ->leftJoin('warehouse as w', 'w.id', 'sih.warehouse_id')
            ->leftJoin('delivery_order_header as doh', 'doh.id', 'sih.do_id')
            ->whereNull('rph.deleted')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('rph.company_id', $company_id))
            ->whereDate('rph.receive_date', '>=', $start_date)
            ->whereDate('rph.receive_date', '<=', $end_date)
            ->when($salesman !== '', fn ($q) => $q->where('rph.salesman', $salesman));

        $data['recordsTotal'] = (clone $datadb)->count();

        if (! empty($_POST['search']['value'])) {
            $keyword = $_POST['search']['value'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('rph.code', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('rph.receive_date', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('rph.visit_date', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('rph.status', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('rph.type_trans', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('rph.reference_no', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('u.name', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('u.nik', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('k.nama_lengkap', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('c.code', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('c.nama_customer', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('sih.invoice_number', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('sih.invoice_date', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('doh.do_number', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('w.name', 'LIKE', '%'.$keyword.'%');
            });
        }

        $data['recordsFiltered'] = (clone $datadb)->count();

        $dir = strtolower($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $col = $_POST['order'][0]['column'] ?? null;

        switch ((string) $col) {
            case '1':
                $datadb->orderBy('rph.receive_date', $dir);
                break;
            case '2':
                $datadb->orderBy('rph.code', $dir);
                break;
            case '3':
                $datadb->orderBy('rph.visit_date', $dir);
                break;
            case '4':
                $datadb->orderBy('u.name', $dir);
                break;
            case '5':
                $datadb->orderBy('c.nama_customer', $dir);
                break;
            case '6':
                $datadb->orderBy('sih.invoice_number', $dir);
                break;
            case '7':
                $datadb->orderBy('sih.invoice_date', $dir);
                break;
            case '8':
                $datadb->orderBy('sih.due_date', $dir);
                break;
            case '9':
                $datadb->orderBy('doh.do_number', $dir);
                break;
            case '10':
                $datadb->orderBy('w.name', $dir);
                break;
            case '11':
                $datadb->orderBy('rpd.amount_paid', $dir);
                break;
            case '12':
                $datadb->orderBy('rph.total_amount', $dir);
                break;
            case '13':
                $datadb->orderBy('rph.status', $dir);
                break;
            case '14':
                $datadb->orderBy('rph.type_trans', $dir);
                break;
            default:
                $datadb->orderBy('rph.receive_date', 'desc')
                    ->orderBy('rph.id', 'desc')
                    ->orderBy('rpd.line_no', 'asc');
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
