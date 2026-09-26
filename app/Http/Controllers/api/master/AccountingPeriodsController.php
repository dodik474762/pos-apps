<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Transaction\AccountingPeriod;
use App\Services\Accounting\AccountingPeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingPeriodsController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new AccountingPeriodService();
    }

    public function getTableName()
    {
        return "accounting_periods";
    }

    public function getData()
    {
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select(['m.*'])
            ->orderBy('m.year', 'desc')
            ->orderBy('m.month', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.year', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.description', 'LIKE', '%' . $keyword . '%');
                });
            }
            if (isset($_POST['order'][0]['column'])) {
                $datadb->orderBy('m.year', $_POST['order'][0]['dir']);
            }
            $data['recordsFiltered'] = $datadb->get()->count();

            if (isset($_POST['length'])) {
                $datadb->limit($_POST['length']);
            }
            if (isset($_POST['start'])) {
                $datadb->offset($_POST['start']);
            }
        }
        $data['data'] = $datadb->get()->toArray();
        if (isset($_POST['draw'])) {
            $data['draw'] = $_POST['draw'];
        }
        return json_encode($data);
    }

    public function submit(Request $request)
    {
        $data = $request->all();
        return response()->json($this->service->save($data));
    }

    public function generateYear(Request $request)
    {
        $data = $request->all();
        $year = empty($data['year']) ? date('Y') : $data['year'];
        return response()->json($this->service->generateYear($year, $data['description'] ?? ''));
    }

    public function close(Request $request)
    {
        $data = $request->all();
        return response()->json($this->service->close($data['id']));
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        return response()->json($this->service->reopen($data['id']));
    }

    public function confirmDelete(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            $period = AccountingPeriod::find($data['id']);
            if (empty($period)) {
                throw new \RuntimeException('Periode akuntansi tidak ditemukan');
            }

            $journal = DB::table('journal_headers')->where('accounting_period_id', $period->id)->count();
            if ($journal > 0) {
                throw new \RuntimeException('Periode memiliki ' . $journal . ' jurnal sehingga tidak dapat dihapus');
            }

            $period->delete();

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['message'] = $th->getMessage();
        }
        return response()->json($result);
    }

    public function getDetailData($id)
    {
        DB::enableQueryLog();
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select(['m.*'])
            ->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();
        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        return view('web.accounting_periods.modal.confirmdelete', $data);
    }
}
