<?php

namespace App\Http\Controllers\api\monitoring;

use App\Http\Controllers\Controller;
use App\Services\ClosingPeriodQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClosingPeriodController extends Controller
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

        $type = $_POST['type'] ?? '';

        // Filter periode dari select accounting_periods (OPEN), default bulan berjalan.
        $period = ClosingPeriodQuery::resolvePeriod($_POST['period_id'] ?? null);
        $datadb = ClosingPeriodQuery::make($type, $period['start'], $period['end']);

        if (!empty($datadb)) {
            $data['recordsTotal'] = (clone $datadb)->count();

            if (isset($_POST['search']['value']) && $_POST['search']['value'] !== '') {
                $keyword = $_POST['search']['value'];
                $searchColumns = ClosingPeriodQuery::searchColumns($type);
                if (!empty($searchColumns)) {
                    $datadb->where(function ($query) use ($keyword, $searchColumns) {
                        foreach ($searchColumns as $index => $column) {
                            if ($index === 0) {
                                $query->where($column, 'LIKE', '%' . $keyword . '%');
                            } else {
                                $query->orWhere($column, 'LIKE', '%' . $keyword . '%');
                            }
                        }
                    });
                }
            }

            $data['recordsFiltered'] = (clone $datadb)->count();

            $column = null;
            $dir = 'asc';
            if (isset($_POST['order'][0]['column'])) {
                $dir = $_POST['order'][0]['dir'] === 'desc' ? 'desc' : 'asc';
                $column = ClosingPeriodQuery::orderColumn($type, (int) $_POST['order'][0]['column']);
            }
            if (empty($column)) {
                $defaultOrder = ClosingPeriodQuery::defaultOrder($type);
                $column = $defaultOrder['column'];
                $dir = $defaultOrder['dir'];
            }
            $datadb->orderBy($column, $dir);

            if (isset($_POST['length']) && (int) $_POST['length'] !== -1) {
                $datadb->limit((int) $_POST['length']);
            }
            if (isset($_POST['start'])) {
                $datadb->offset((int) $_POST['start']);
            }

            $data['data'] = $datadb->get()->toArray();
        }

        $data['draw'] = (int) ($_POST['draw'] ?? 1);

        DB::getQueryLog();
        return json_encode($data);
    }
}
