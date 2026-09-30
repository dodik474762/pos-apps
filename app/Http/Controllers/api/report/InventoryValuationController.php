<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InventoryValuationController extends Controller
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

        $dateStart = $_POST['date_start'] ?? date('Y-m-01');
        $dateEnd   = $_POST['date_end']   ?? date('Y-m-d');
        $typeStock = $_POST['type_stock'] ?? 'rm';
        $itemCode  = $_POST['item_code']  ?? '';

        $baseQuery = DB::table('stock_cards as sc')
            ->leftJoin('product as p', 'p.code', '=', 'sc.item_code')
            ->leftJoin('warehouse as wh', 'wh.id', '=', 'sc.wh_code')
            ->select([
                'sc.id',
                'sc.item_code',
                'sc.trans_date',

                'p.name as item_name',
                'wh.name as warehouse_name',
                'sc.note',
                'sc.reference_type',
                'sc.cost_source',

                DB::raw('ROUND(sc.opening_balance, 2) as opening_balance'),
                DB::raw('ROUND(sc.qty_in, 2) as qty_in'),
                DB::raw('ROUND(sc.qty_out, 2) as qty_out'),
                DB::raw('ROUND(sc.qty_adjust, 2) as qty_adjust'),
                DB::raw('ROUND(sc.qty_transfer_out, 2) as qty_transfer_out'),
                DB::raw('ROUND(sc.qty_transfer_in, 2) as qty_transfer_in'),
                DB::raw('ROUND(sc.qty_return_in, 2) as qty_return_in'),
                DB::raw('ROUND(sc.closing_balance, 2) as closing_balance'),

                DB::raw('ROUND(sc.opening_value, 2) as opening_value'),
                DB::raw('ROUND(sc.unit_cost, 4) as unit_cost'),
                DB::raw('ROUND(sc.value_in, 2) as value_in'),
                DB::raw('ROUND(sc.value_out, 2) as value_out'),
                DB::raw('ROUND(sc.nominal_value, 2) as nominal_value'),
                DB::raw('ROUND(sc.closing_value, 2) as closing_value'),
                DB::raw('ROUND(sc.avg_cost, 4) as avg_cost'),
            ])
            ->where('sc.type_stock', $typeStock)
            // ->where('sc.item_code', 'PROD-08260001')
            ->whereBetween('sc.trans_date', [$dateStart, $dateEnd])
            ->when($itemCode, fn($q) => $q->where('sc.item_code', $itemCode))
            ->orderBy('sc.item_code')
            ->orderBy('sc.trans_date')
            ->orderBy('sc.id');

        $data['recordsTotal'] = (clone $baseQuery)->count();

        if (isset($_POST['search']['value']) && $_POST['search']['value'] !== '') {
            $keyword = $_POST['search']['value'];
            $baseQuery->where(function ($query) use ($keyword) {
                $query->where('sc.item_code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('p.name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('wh.name', 'LIKE', '%' . $keyword . '%');
            });
        }

        $data['recordsFiltered'] = (clone $baseQuery)->count();

        if (isset($_POST['order'][0]['column'])) {
            $dir = $_POST['order'][0]['dir'] === 'desc' ? 'desc' : 'asc';
            switch ($_POST['order'][0]['column']) {
                case 1:
                    $baseQuery->reorder()->orderBy('sc.item_code', $dir)->orderBy('sc.trans_date')->orderBy('sc.id');
                    break;
                case 2:
                    $baseQuery->reorder()->orderBy('p.name', $dir)->orderBy('sc.item_code')->orderBy('sc.trans_date')->orderBy('sc.id');
                    break;
                case 3:
                    $baseQuery->reorder()->orderBy('wh.name', $dir)->orderBy('sc.item_code')->orderBy('sc.trans_date')->orderBy('sc.id');
                    break;
                case 4:
                    $baseQuery->reorder()->orderBy('sc.trans_date', $dir)->orderBy('sc.id', $dir);
                    break;
                case 5:
                    $baseQuery->reorder()->orderByRaw('sc.opening_balance ' . $dir);
                    break;
                case 6:
                    $baseQuery->reorder()->orderByRaw('sc.qty_in ' . $dir);
                    break;
                case 7:
                    $baseQuery->reorder()->orderByRaw('sc.qty_out ' . $dir);
                    break;
                case 8:
                    $baseQuery->reorder()->orderByRaw('sc.qty_adjust ' . $dir);
                    break;
                case 9:
                    $baseQuery->reorder()->orderByRaw('sc.closing_balance ' . $dir);
                    break;
                case 10:
                    $baseQuery->reorder()->orderByRaw('sc.opening_value ' . $dir);
                    break;
                case 11:
                    $baseQuery->reorder()->orderByRaw('sc.unit_cost ' . $dir);
                    break;
                case 12:
                    $baseQuery->reorder()->orderByRaw('sc.value_in ' . $dir);
                    break;
                case 13:
                    $baseQuery->reorder()->orderByRaw('sc.value_out ' . $dir);
                    break;
                case 14:
                    $baseQuery->reorder()->orderByRaw('sc.nominal_value ' . $dir);
                    break;
                case 15:
                    $baseQuery->reorder()->orderByRaw('sc.closing_value ' . $dir);
                    break;
                case 16:
                    $baseQuery->reorder()->orderByRaw('sc.avg_cost ' . $dir);
                    break;
                case 17:
                    $baseQuery->reorder()->orderBy('sc.cost_source', $dir)->orderBy('sc.item_code')->orderBy('sc.trans_date')->orderBy('sc.id');
                    break;
                case 18:
                    $baseQuery->reorder()->orderBy('sc.note', $dir)->orderBy('sc.item_code')->orderBy('sc.trans_date')->orderBy('sc.id');
                    break;
                case 19:
                    $baseQuery->reorder()->orderBy('sc.reference_type', $dir)->orderBy('sc.item_code')->orderBy('sc.trans_date')->orderBy('sc.id');
                    break;
                default:
                    $baseQuery->reorder()->orderBy('sc.item_code')->orderBy('sc.trans_date')->orderBy('sc.id');
                    break;
            }
        }

        if (isset($_POST['length']) && (int) $_POST['length'] !== -1) {
            $baseQuery->limit((int) $_POST['length']);
        }
        if (isset($_POST['start'])) {
            $baseQuery->offset((int) $_POST['start']);
        }

        $data['data'] = $baseQuery->get()->toArray();
        $data['draw'] = (int) ($_POST['draw'] ?? 1);

        return json_encode($data);
    }

    public function getSummary()
    {
        $dateStart = $_POST['date_start'] ?? date('Y-m-01');
        $dateEnd   = $_POST['date_end']   ?? date('Y-m-d');
        $typeStock = $_POST['type_stock'] ?? 'rm';
        $itemCode  = $_POST['item_code']  ?? '';

        $baseQuery = DB::table('stock_cards as sc')
            ->leftJoin('product as p', 'p.code', '=', 'sc.item_code')
            ->leftJoin('warehouse as wh', 'wh.id', '=', 'sc.wh_code')
            ->select([
                'sc.item_code',
                'sc.wh_code',

                DB::raw('SUM(sc.qty_in) as qty_in'),
                DB::raw('SUM(sc.qty_out) as qty_out'),
                DB::raw('SUM(sc.qty_adjust) as qty_adjust'),

                DB::raw('SUM(sc.value_in) as value_in'),
                DB::raw('SUM(sc.value_out) as value_out'),
                DB::raw('SUM(sc.nominal_value) as nominal_value'),

                // opening_value = opening_value baris pertama item+warehouse di dalam periode
                DB::raw("(SELECT f.opening_value FROM stock_cards f
                 WHERE f.item_code = sc.item_code
                   AND f.wh_code = sc.wh_code
                   AND f.type_stock = sc.type_stock
                   AND f.trans_date BETWEEN ? AND ?
                 ORDER BY f.trans_date ASC, f.id ASC
                 LIMIT 1) as opening_value"),

                // closing_value = closing_value baris terakhir item+warehouse di dalam periode
                DB::raw("(SELECT l.closing_value FROM stock_cards l
                 WHERE l.item_code = sc.item_code
                   AND l.wh_code = sc.wh_code
                   AND l.type_stock = sc.type_stock
                   AND l.trans_date BETWEEN ? AND ?
                 ORDER BY l.trans_date DESC, l.id DESC
                 LIMIT 1) as closing_value"),
            ])
            ->addBinding([$dateStart, $dateEnd, $dateStart, $dateEnd], 'select')
            ->where('sc.type_stock', $typeStock)
            ->whereBetween('sc.trans_date', [$dateStart, $dateEnd])
            ->when($itemCode, fn($q) => $q->where('sc.item_code', $itemCode))
            ->groupBy('sc.item_code', 'sc.wh_code', 'sc.type_stock');

        if (isset($_POST['search']['value']) && $_POST['search']['value'] !== '') {
            $keyword = $_POST['search']['value'];
            $baseQuery->where(function ($query) use ($keyword) {
                $query->where('sc.item_code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('p.name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('wh.name', 'LIKE', '%' . $keyword . '%');
            });
        }

        $rows = $baseQuery->get();

        $summary['opening_value'] = (float) $rows->sum('opening_value');
        $summary['value_in']      = (float) $rows->sum('value_in');
        $summary['value_out']     = (float) $rows->sum('value_out');
        $summary['nominal_value'] = (float) $rows->sum('nominal_value');
        $summary['closing_value'] = (float) $rows->sum('closing_value');
        $summary['qty_in']        = (float) $rows->sum('qty_in');
        $summary['qty_out']       = (float) $rows->sum('qty_out');
        $summary['qty_adjust']    = (float) $rows->sum('qty_adjust');
        $summary['item_total']    = $rows->count();

        return json_encode($summary);
    }
}
