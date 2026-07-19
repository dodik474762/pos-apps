<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\ProductStock;
use App\Models\Transaction\StockCard;
use App\Models\Transaction\StockClosing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClosingStockController extends Controller
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

        $dateStart = $_POST['date_start'] ?? date('Y-m-01');
        $dateEnd   = $_POST['date_end']   ?? date('Y-m-d');
        $typeStock = $_POST['type_stock'] ?? 'rm';
        $itemCode  = $_POST['item_code']  ?? '';

        // Agregasi mutasi periode per item_code (ganti loop foreach -> 1x SUM ter-GROUP BY)
        $periodSumSql = "(
        SELECT
            item_code,
            SUM(qty_in)           as total_in,
            SUM(qty_out)          as total_out,
            SUM(qty_adjust)       as total_adjust,
            SUM(qty_transfer_out) as total_transfer_out,
            SUM(qty_transfer_in)  as total_transfer_in,
            SUM(qty_return_in)    as total_return_in
        FROM stock_cards
        WHERE trans_date BETWEEN '" . $dateStart . "' AND '" . $dateEnd . "'
        GROUP BY item_code
    ) as ps";

        // Driving table = daftar item_code unik sesuai type_stock (+ filter item_code opsional)
        $itemListSql = "(
        SELECT DISTINCT item_code
        FROM stock_cards
        WHERE type_stock = '" . $typeStock . "'"
            . ($itemCode ? " AND item_code = '" . $itemCode . "'" : "") . "
    ) as sc";

        $datadb = DB::table(DB::raw($itemListSql))
            ->leftJoin(DB::raw($periodSumSql), 'ps.item_code', '=', 'sc.item_code')
            ->leftJoin('product as p', 'p.code', '=', 'sc.item_code')
            ->select([
                'sc.item_code',
                DB::raw("'" . $dateStart . "' as date_start"),
                DB::raw("'" . $dateEnd . "' as date_end"),

                // opening_balance = closing_balance terakhir sebelum date_start
                DB::raw("COALESCE((
                SELECT closing_balance
                FROM stock_cards
                WHERE item_code = sc.item_code
                  AND trans_date < '" . $dateStart . "'
                ORDER BY trans_date DESC, id DESC
                LIMIT 1
            ), 0) as opening_balance"),

                DB::raw('COALESCE(ps.total_in, 0) as total_in'),
                DB::raw('COALESCE(ps.total_out, 0) as total_out'),
                DB::raw('COALESCE(ps.total_adjust, 0) as total_adjust'),
                DB::raw('COALESCE(ps.total_transfer_out, 0) as total_transfer_out'),
                DB::raw('COALESCE(ps.total_transfer_in, 0) as total_transfer_in'),
                DB::raw('COALESCE(ps.total_return_in, 0) as total_return_in'),

                // closing_balance = opening + in - out + adjust - transfer_out + transfer_in + return_in
                // (expr opening_balance diduplikasi, sama seperti pola stock_future di query pertama,
                // karena alias SELECT tidak bisa dipakai ulang di baris SELECT yang sama)
                DB::raw("(
                COALESCE((
                    SELECT closing_balance
                    FROM stock_cards
                    WHERE item_code = sc.item_code
                      AND trans_date < '" . $dateStart . "'
                    ORDER BY trans_date DESC, id DESC
                    LIMIT 1
                ), 0)
                + COALESCE(ps.total_in, 0)
                - COALESCE(ps.total_out, 0)
                + COALESCE(ps.total_adjust, 0)
                - COALESCE(ps.total_transfer_out, 0)
                + COALESCE(ps.total_transfer_in, 0)
                + COALESCE(ps.total_return_in, 0)
            ) as closing_balance"),

                'p.name as item_name',
            ])
            ->orderBy('p.name');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();

            if (isset($_POST['search']['value']) && $_POST['search']['value'] !== '') {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('sc.item_code', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('p.name', 'LIKE', '%' . $keyword . '%');
                });
            }

            if (isset($_POST['order'][0]['column'])) {
                $dir = $_POST['order'][0]['dir'] === 'desc' ? 'desc' : 'asc';
                switch ($_POST['order'][0]['column']) {
                    case 0:
                        $datadb->orderBy('sc.item_code', $dir);
                        break;
                    case 1:
                        $datadb->orderBy('p.name', $dir);
                        break;
                    case 2:
                        $datadb->orderByRaw('opening_balance ' . $dir);
                        break;
                    case 3:
                        $datadb->orderByRaw('total_in ' . $dir);
                        break;
                    case 4:
                        $datadb->orderByRaw('total_out ' . $dir);
                        break;
                    case 5:
                        $datadb->orderByRaw('closing_balance ' . $dir);
                        break;
                    default:
                        $datadb->orderBy('p.name', 'asc');
                        break;
                }
            }

            $data['recordsFiltered'] = $datadb->get()->count();

            if (isset($_POST['length']) && (int) $_POST['length'] !== -1) {
                $datadb->limit((int) $_POST['length']);
            }
            if (isset($_POST['start'])) {
                $datadb->offset((int) $_POST['start']);
            }
        }

        $data['data']  = $datadb->get()->toArray();
        $data['draw']  = (int) ($_POST['draw'] ?? 1);

        $query = DB::getQueryLog();
        return json_encode($data);
    }

    public function getStockCardLogDetail()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $dateStart = $_POST['date_start'] ?? date('Y-m-01');
        $dateEnd   = $_POST['date_end']   ?? date('Y-m-d');
        $typeStock = $_POST['type_stock'] ?? 'rm';
        $itemCode  = $_POST['item_code']  ?? '';
        $dateNow = $_POST['tanggal'] ?? date('Y-m-d');

        $datadb = DB::table('stock_cards as sc')
            ->leftJoin('product as p', 'p.code', '=', 'sc.item_code')
            ->leftJoin('warehouse as wh', 'wh.id', '=', 'sc.wh_code')
            ->select([
                'sc.id',
                'sc.item_code',
                'sc.trans_date',
                DB::raw('ROUND(sc.qty_in, 2) as qty_in'),
                DB::raw('ROUND(sc.qty_out, 2) as qty_out'),
                DB::raw('ROUND(sc.qty_adjust, 2) as qty_adjust'),
                DB::raw('ROUND(sc.qty_transfer_out, 2) as qty_transfer_out'),
                DB::raw('ROUND(sc.qty_transfer_in, 2) as qty_transfer_in'),
                DB::raw('ROUND(sc.qty_return_in, 2) as qty_return_in'),
                DB::raw('ROUND(sc.closing_balance, 2) as closing_balance'),
                DB::raw('ROUND(sc.opening_balance, 2) as opening_balance'),

                // opening_balance_row juga dibulatkan
                DB::raw("ROUND((
        SELECT COALESCE(prev.closing_balance, 0)
        FROM stock_cards prev
        WHERE prev.item_code = sc.item_code
          AND (
                prev.trans_date < sc.trans_date
                OR (prev.trans_date = sc.trans_date AND prev.id < sc.id)
          )
        ORDER BY prev.trans_date DESC, prev.id DESC
        LIMIT 1
    ), 2) as opening_balance_row"),


                'p.name as item_name',
                'wh.name as warehouse_name',
                'sc.note',
                'sc.reference_type'
            ])
            ->where('sc.type_stock', $typeStock)
            ->where('trans_date', $dateNow)
            // ->whereBetween('sc.trans_date', [$dateStart, $dateEnd])
            ->when($itemCode, fn($q) => $q->where('sc.item_code', $itemCode))
            ->orderBy('sc.item_code')
            ->orderBy('sc.trans_date')
            ->orderBy('sc.id');

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();

            if (isset($_POST['search']['value']) && $_POST['search']['value'] !== '') {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('sc.item_code', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('p.name', 'LIKE', '%' . $keyword . '%');
                });
            }

            if (isset($_POST['order'][0]['column'])) {
                $dir = $_POST['order'][0]['dir'] === 'desc' ? 'desc' : 'asc';
                switch ($_POST['order'][0]['column']) {
                    case 0:
                        $datadb->reorder()->orderBy('sc.item_code', $dir)->orderBy('sc.trans_date')->orderBy('sc.id');
                        break;
                    case 1:
                        $datadb->reorder()->orderBy('sc.trans_date', $dir)->orderBy('sc.id', $dir);
                        break;
                    case 2:
                        $datadb->reorder()->orderBy('sc.qty_in', $dir);
                        break;
                    case 3:
                        $datadb->reorder()->orderBy('sc.qty_out', $dir);
                        break;
                    case 4:
                        $datadb->reorder()->orderBy('sc.closing_balance', $dir);
                        break;
                    default:
                        $datadb->reorder()->orderBy('sc.item_code')->orderBy('sc.trans_date')->orderBy('sc.id');
                        break;
                }
            }

            $data['recordsFiltered'] = $datadb->get()->count();

            if (isset($_POST['length']) && (int) $_POST['length'] !== -1) {
                $datadb->limit((int) $_POST['length']);
            }
            if (isset($_POST['start'])) {
                $datadb->offset((int) $_POST['start']);
            }
        }

        $data['data'] = $datadb->get()->toArray();
        $data['draw'] = (int) ($_POST['draw'] ?? 1);

        $query = DB::getQueryLog();
        return json_encode($data);
    }

    public function closing(Request $request)
    {
        DB::enableQueryLog();
        $params = $request->all();
        $result['is_valid'] = false;
        $result['message'] = 'Gagal Melakukan Closing Stock.';

        DB::beginTransaction();
        try {
            $closing = StockClosing::where('closing_date', $params['tanggal'])->first();
            if (!empty($closing)) {
                DB::rollBack();
                $result['message'] = 'Tanggal ' . date('d F Y', strtotime($params['tanggal'])) . ' sudah di-closing.';
                return response()->json($result);
            }

            $stockClosing = new StockClosing();
            $stockClosing->closing_date = $params['tanggal'];
            $stockClosing->created_by = session('user_id');
            $stockClosing->save();


            /*update stock cards close_date */
            $stockCards = StockCard::where('stock_cards.trans_date', $params['tanggal'])
                ->select([
                    'stock_cards.item_code',
                    'p.id as product',
                    'stock_cards.wh_code',
                    'stock_cards.closing_balance',
                ])
                ->leftJoin('product as p', 'p.code', '=', 'stock_cards.item_code')
                ->whereIn('stock_cards.id', function ($q) use ($params) {
                    $q->selectRaw('MAX(id)')
                        ->from('stock_cards')
                        ->where('trans_date', $params['tanggal'])
                        ->groupBy('item_code');
                })
                ->get();

            // echo '<pre>';
            // print_r($stockCards);
            // die;
            foreach ($stockCards as $stockCard) {
                $productStock = ProductStock::where('product', $stockCard->product)
                    ->where('warehouse', $stockCard->wh_code)
                    ->first();
                if (!empty($productStock)) {
                    $productStock->qty = $stockCard->closing_balance;
                    $productStock->save();
                }
            }


            $result['is_valid'] = true;
            $result['message'] = 'Berhasil Melakukan Closing Stock.';
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['message'] = 'Gagal Melakukan Closing Stock - ' . $th->getMessage() . ' LINE - ' . $th->getLine();
        }

        // $query = DB::getQueryLog();
        // $result['query'] = $query;
        return response()->json($result);
    }
}
