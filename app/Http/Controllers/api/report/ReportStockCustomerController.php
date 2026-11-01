<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ReportStockCustomerController extends Controller
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

        $customer = $_POST['customer'] ?? $_POST['customer_id'] ?? '';
        $company_id = session('id_company');

        $datadb = DB::table('stock_customer as sc')
            ->select([
                'sc.id',
                'c.code as customer_code',
                'c.nama_customer as customer_name',
                'p.code as product_code',
                'p.name as product_name',
                'u.name as unit_name',
                'sc.qty',
                'sc.unit_price',
                'sc.status',
                'sc.is_free_good',
                'v.nama_vendor as principal',
                'sc.foto_path',
            ])
            ->leftJoin('customer as c', 'c.id', 'sc.customer')
            ->leftJoin('product as p', 'p.id', 'sc.product_id')
            ->leftJoin('unit as u', 'u.id', 'sc.unit')
            ->leftJoin('vendor as v', 'v.id', 'p.vendor')
            ->whereNull('sc.deleted')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('sc.company_id', $company_id))
            ->when($customer !== '', fn ($q) => $q->where('sc.customer', $customer));

        $data['recordsTotal'] = (clone $datadb)->count();

        if (!empty($_POST['search']['value'])) {
            $keyword = $_POST['search']['value'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('sc.id', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('c.code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('p.code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('p.name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('u.name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('v.nama_vendor', 'LIKE', '%' . $keyword . '%');
            });
        }

        $data['recordsFiltered'] = (clone $datadb)->count();

        $dir = strtolower($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $col = $_POST['order'][0]['column'] ?? null;

        switch ((string) $col) {
            case '0':
                $datadb->orderBy('sc.id', $dir);
                break;
            case '1':
                $datadb->orderBy('c.code', $dir);
                break;
            case '2':
                $datadb->orderBy('c.nama_customer', $dir);
                break;
            case '3':
                $datadb->orderBy('p.code', $dir);
                break;
            case '4':
                $datadb->orderBy('p.name', $dir);
                break;
            case '5':
                $datadb->orderBy('u.name', $dir);
                break;
            case '6':
                $datadb->orderBy('sc.qty', $dir);
                break;
            case '7':
                $datadb->orderBy('sc.unit_price', $dir);
                break;
            case '8':
                $datadb->orderBy('sc.status', $dir);
                break;
            case '9':
                $datadb->orderBy('sc.is_free_good', $dir);
                break;
            case '10':
                $datadb->orderBy('v.nama_vendor', $dir);
                break;
            case '11':
                $datadb->orderBy('sc.foto_path', $dir);
                break;
            default:
                $datadb->orderBy('c.nama_customer', 'asc')->orderBy('p.name', 'asc');
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
