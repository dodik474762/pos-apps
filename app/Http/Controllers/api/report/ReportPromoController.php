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
}
