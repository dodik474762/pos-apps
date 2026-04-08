<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportStockController extends Controller
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
        $tiga_bulan_lalu = date('Y-m-d', strtotime('-3 months', strtotime($tanggal)));

        $datadb = DB::table('product_stock_move as m')
            ->select([
                'm.product',
                'm.warehouse',
                'm.unit',
                'p.code as product_code',
                'p.name as product_name',
                'w.name as warehouse_name',
                'u.name as unit_name',

                // Semua transaksi s/d tanggal dipilih
                DB::raw('SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_in ELSE 0 END) as total_masuk'),
                DB::raw('SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_out ELSE 0 END) as total_keluar'),
                DB::raw('SUM(CASE WHEN DATE(m.created_at) <= "' . $tanggal . '" THEN m.qty_in - m.qty_out ELSE 0 END) as stok_tersedia'),

                // Khusus 3 bulan terakhir dari tanggal dipilih
                DB::raw('SUM(CASE WHEN DATE(m.created_at) BETWEEN "' . $tiga_bulan_lalu . '" AND "' . $tanggal . '" THEN m.qty_in ELSE 0 END) as masuk_3bln'),
                DB::raw('SUM(CASE WHEN DATE(m.created_at) BETWEEN "' . $tiga_bulan_lalu . '" AND "' . $tanggal . '" THEN m.qty_out ELSE 0 END) as keluar_3bln'),
                DB::raw('SUM(CASE WHEN DATE(m.created_at) BETWEEN "' . $tiga_bulan_lalu . '" AND "' . $tanggal . '" THEN m.qty_in - m.qty_out ELSE 0 END) as stok_3bln'),
            ])
            ->join('product as p', 'p.id', 'm.product')
            ->join('warehouse as w', 'w.id', 'm.warehouse')
            ->join('unit as u', 'u.id', 'm.unit')
            ->whereDate('m.created_at', '<=', $tanggal)
            ->groupBy('m.product', 'm.warehouse', 'm.unit', 'p.code', 'p.name', 'w.name', 'u.name')
            // ->having('stok_tersedia', '>', 0)
            ->orderBy('p.name')
            ->orderBy('u.name');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();

            // Filter pencarian
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.product', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.warehouse', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('m.move_type', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('p.code', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('p.name', 'LIKE', '%' . $keyword . '%');
                });
            }

            // Sorting
            if (isset($_POST['order'][0]['column'])) {
                switch ($_POST['order'][0]['column']) {
                    case 0:
                        $datadb->orderBy('m.product', $_POST['order'][0]['dir']);
                        break;
                    case 1:
                        $datadb->orderBy('m.warehouse', $_POST['order'][0]['dir']);
                        break;
                    case 2:
                        $datadb->orderBy('m.unit', $_POST['order'][0]['dir']);
                        break;
                    case 3:
                        $datadb->orderByRaw('SUM(m.qty_in) ' . $_POST['order'][0]['dir']);
                        break;
                    case 4:
                        $datadb->orderByRaw('SUM(m.qty_out) ' . $_POST['order'][0]['dir']);
                        break;
                    case 5:
                        $datadb->orderByRaw('(SUM(m.qty_in) - SUM(m.qty_out)) ' . $_POST['order'][0]['dir']);
                        break;
                    default:
                        $datadb->orderBy('m.product', 'asc');
                        break;
                }
            }

            $data['recordsFiltered'] = $datadb->get()->count();

            // Pagination
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
            $value->stok_tersedia = number_format($value->stok_tersedia, 0, ',', '.');
            $value->total_masuk   = number_format($value->total_masuk, 0, ',', '.');
            $value->total_keluar  = number_format($value->total_keluar, 0, ',', '.');            
            $value->stok_3bln = number_format($value->stok_3bln, 0, ',', '.');
            $value->hari_kerja = 25;
            $value->avg_omset = $value->stok_3bln / $value->hari_kerja;
            $value->stock_scd = $value->stok_tersedia / $value->avg_omset;
            $resultdb[] = $value;
        }

        $data['data'] = $resultdb;
        $data['draw'] = $_POST['draw'];

        $query = DB::getQueryLog();
        return json_encode($data);
    }
}
