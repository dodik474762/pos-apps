<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ReportPromoController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function getData()
    {
        $data = [];
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $principal = $_POST['principal'] ?? '';
        $company_id = session('id_company');

        $datadb = DB::table('product_promo_item as ppi')
            ->select([
                'ppi.id',
                'ppi.promo_name',
                'ppi.date_start',
                'ppi.date_end',
                'ppi.min_qty',
                'ppi.max_qty',
                'ppi.discount_type',
                'ppi.discount_value',
                'ppi.status_aktif',
                'ppi.beban',
                'ppi.kategori_disc',
                'ppid.id as detail_id',
                'ppid.product as product_id',
                'p.code as product_code',
                'p.name as product_name',
                'v.nama_vendor as principal',
                DB::raw('(SELECT COUNT(*) FROM product_promo_item_syarat AS ppis WHERE ppis.product_promo_item = ppi.id) as syarat_count'),
                DB::raw('(SELECT COUNT(*) FROM product_promo_item_detail_free AS ppidf WHERE ppidf.product_promo_item = ppi.id) as free_count'),
            ])
            ->leftJoin('product_promo_item_detail as ppid', 'ppid.product_promo_item', 'ppi.id')
            ->leftJoin('product as p', 'p.id', 'ppid.product')
            ->leftJoin('vendor as v', 'v.id', 'p.vendor')
            ->whereNull('ppi.deleted')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('ppi.company_id', $company_id))
            ->when($principal !== '', fn ($q) => $q->where('p.vendor', $principal));

        $data['recordsTotal'] = (clone $datadb)->count();

        if (!empty($_POST['search']['value'])) {
            $keyword = $_POST['search']['value'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('ppi.promo_name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('p.name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('p.code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('v.nama_vendor', 'LIKE', '%' . $keyword . '%');
            });
        }

        $data['recordsFiltered'] = (clone $datadb)->count();

        $dir = strtolower($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $col = $_POST['order'][0]['column'] ?? null;

        switch ((string) $col) {
            case '0':
                $datadb->orderBy('ppi.id', $dir);
                break;
            case '1':
                $datadb->orderBy('ppi.promo_name', $dir);
                break;
            case '2':
                $datadb->orderBy('v.nama_vendor', $dir);
                break;
            case '3':
                $datadb->orderBy('p.code', $dir);
                break;
            case '4':
                $datadb->orderBy('p.name', $dir);
                break;
            case '5':
                $datadb->orderBy('ppi.min_qty', $dir);
                break;
            case '6':
                $datadb->orderBy('ppi.max_qty', $dir);
                break;
            case '7':
                $datadb->orderBy('ppi.discount_type', $dir);
                break;
            case '8':
                $datadb->orderBy('ppi.discount_value', $dir);
                break;
            case '9':
                $datadb->orderBy('ppi.date_start', $dir);
                break;
            case '10':
                $datadb->orderBy('ppi.date_end', $dir);
                break;
            case '11':
                $datadb->orderBy('ppi.status_aktif', $dir);
                break;
            case '12':
                $datadb->orderBy('ppi.beban', $dir);
                break;
            case '13':
                $datadb->orderBy('ppi.kategori_disc', $dir);
                break;
            case '14':
                $datadb->orderByRaw('syarat_count ' . $dir);
                break;
            case '15':
                $datadb->orderByRaw('free_count ' . $dir);
                break;
            default:
                $datadb->orderBy('ppi.date_start', 'desc')->orderBy('ppi.id', 'desc');
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

    /**
     * PRD (History Promo): daftar promo yang pernah dibuat beserta periode berlakunya,
     * termasuk promo yang sudah tidak aktif / sudah berakhir.
     */
    public function getDataHistory()
    {
        $data = [];
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $principal = $_POST['principal'] ?? '';
        $company_id = session('id_company');

        $datadb = DB::table('product_promo_item as ppi')
            ->select([
                DB::raw('MIN(ppi.id) as id'),
                'ppi.promo_name',
                DB::raw('MIN(ppi.date_start) as date_start'),
                DB::raw('MAX(ppi.date_end) as date_end'),
                DB::raw('MAX(ppi.status_aktif) as status_aktif'),
                DB::raw('COUNT(DISTINCT ppi.id) as item_count'),
                DB::raw('MIN(ppi.created_at) as created_at'),
            ])
            ->whereNull('ppi.deleted')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('ppi.company_id', $company_id))
            ->when($principal !== '', fn ($q) => $q->whereExists(function ($sub) use ($principal) {
                $sub->select(DB::raw(1))
                    ->from('product_promo_item_detail as ppid')
                    ->join('product as p', 'p.id', 'ppid.product')
                    ->whereColumn('ppid.product_promo_item', 'ppi.id')
                    ->where('p.vendor', $principal);
            }))
            ->groupBy('ppi.promo_name', 'ppi.date_start', 'ppi.date_end');

        $data['recordsTotal'] = (clone $datadb)->get()->count();

        if (!empty($_POST['search']['value'])) {
            $keyword = $_POST['search']['value'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('ppi.promo_name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('ppi.date_start', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('ppi.date_end', 'LIKE', '%' . $keyword . '%');
            });
        }

        $data['recordsFiltered'] = (clone $datadb)->get()->count();

        $dir = strtolower($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $col = $_POST['order'][0]['column'] ?? null;

        switch ((string) $col) {
            case '0':
                $datadb->orderByRaw('MIN(ppi.id) ' . $dir);
                break;
            case '1':
                $datadb->orderBy('ppi.promo_name', $dir);
                break;
            case '2':
                $datadb->orderBy('date_start', $dir);
                break;
            case '3':
                $datadb->orderBy('date_end', $dir);
                break;
            case '4':
                $datadb->orderByRaw('MAX(ppi.status_aktif) ' . $dir);
                break;
            case '5':
                $datadb->orderByRaw('COUNT(DISTINCT ppi.id) ' . $dir);
                break;
            default:
                $datadb->orderBy('date_start', 'desc')->orderBy('ppi.promo_name', 'asc');
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

    /**
     * Report Promo Invoice: item promo dari sales_order_promo_item yang sudah
     * memiliki invoice terbit (sales_invoice_header).
     */
    public function getDataInvoice()
    {
        $data = [];
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $principal = $_POST['principal'] ?? '';
        $date_start = $_POST['date_start'] ?? '';
        $date_end = $_POST['date_end'] ?? '';
        $company_id = session('id_company');

        $datadb = DB::table('sales_order_promo_item as sopi')
            ->join('sales_invoice_header as sih', function ($join) use ($company_id) {
                $join->on('sih.sales_order', 'sopi.sales_order_id')
                    ->whereNull('sih.deleted')
                    ->where('sih.status', '!=', 'CANCELED')
                    ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('sih.company_id', $company_id));
            })
            ->join('sales_order_headers as soh', function ($join) use ($company_id) {
                $join->on('soh.id', 'sopi.sales_order_id')
                    ->whereNull('soh.deleted')
                    ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('soh.company_id', $company_id));
            })
            ->join('sales_order_details as sod', function ($join) {
                $join->on('sod.id', 'sopi.sales_order_detail_id')
                    ->whereNull('sod.deleted');
            })
            ->leftJoin('product as p', 'p.id', 'sod.product_id')
            ->leftJoin('vendor as v', 'v.id', 'p.vendor')
            ->leftJoin('customer as c', 'c.id', 'sih.customer_id')
            ->leftJoin('users as usr', 'usr.id', 'soh.salesman')
            ->leftJoin('karyawan as k', 'k.nik', 'usr.nik')
            ->leftJoin('unit as u', 'u.id', 'sod.unit')
            ->leftJoin('product_promo_item as ppi', 'ppi.id', 'sopi.promo')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('sopi.company_id', $company_id))
            ->when($principal !== '', fn ($q) => $q->where('p.vendor', $principal))
            ->when($date_start !== '', fn ($q) => $q->whereDate('sih.invoice_date', '>=', $date_start))
            ->when($date_end !== '', fn ($q) => $q->whereDate('sih.invoice_date', '<=', $date_end))
            ->select([
                'sopi.id',
                'sih.invoice_number',
                'sih.invoice_date',
                'sih.status as invoice_status',
                'soh.so_number',
                'soh.so_date',
                'c.code as customer_code',
                'c.nama_customer as customer_name',
                DB::raw('COALESCE(k.nama_lengkap, usr.name) as salesman_name'),
                DB::raw("COALESCE(NULLIF(sopi.promo_name, ''), ppi.promo_name) as promo_name"),
                'sopi.discount_amount',
                'sopi.discount_percent',
                'p.code as product_code',
                'p.name as product_name',
                'sod.qty',
                'u.name as unit_name',
                'v.nama_vendor as principal',
            ]);

        $data['recordsTotal'] = (clone $datadb)->count();

        if (!empty($_POST['search']['value'])) {
            $keyword = $_POST['search']['value'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('sih.invoice_number', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('soh.so_number', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('c.code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('sopi.promo_name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('ppi.promo_name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('p.code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('p.name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('v.nama_vendor', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('k.nama_lengkap', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('usr.name', 'LIKE', '%' . $keyword . '%');
            });
        }

        $data['recordsFiltered'] = (clone $datadb)->count();

        $dir = strtolower($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $col = $_POST['order'][0]['column'] ?? null;

        switch ((string) $col) {
            case '0':
                $datadb->orderBy('sopi.id', $dir);
                break;
            case '1':
                $datadb->orderBy('sih.invoice_number', $dir);
                break;
            case '2':
                $datadb->orderBy('sih.invoice_date', $dir);
                break;
            case '3':
                $datadb->orderBy('c.nama_customer', $dir);
                break;
            case '4':
                $datadb->orderBy('soh.so_number', $dir);
                break;
            case '5':
                $datadb->orderBy('soh.so_date', $dir);
                break;
            case '6':
                $datadb->orderByRaw('COALESCE(k.nama_lengkap, usr.name) ' . $dir);
                break;
            case '7':
                $datadb->orderByRaw("COALESCE(NULLIF(sopi.promo_name, ''), ppi.promo_name) " . $dir);
                break;
            case '8':
                $datadb->orderBy('p.code', $dir);
                break;
            case '9':
                $datadb->orderBy('p.name', $dir);
                break;
            case '10':
                $datadb->orderBy('sod.qty', $dir);
                break;
            case '11':
                $datadb->orderBy('sopi.discount_amount', $dir);
                break;
            case '12':
                $datadb->orderBy('sopi.discount_percent', $dir);
                break;
            case '13':
                $datadb->orderBy('v.nama_vendor', $dir);
                break;
            default:
                $datadb->orderBy('sih.invoice_date', 'desc')->orderBy('sopi.id', 'desc');
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
