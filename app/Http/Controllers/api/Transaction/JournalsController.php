<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\Accounts;
use App\Services\Accounting\JournalPostingService;
use App\Services\Accounting\JournalReversalService;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\JournalValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalsController extends Controller
{
    protected $journalService;
    protected $postingService;
    protected $reversalService;

    public function __construct()
    {
        $this->journalService = new JournalService();
        $this->postingService = new JournalPostingService();
        $this->reversalService = new JournalReversalService();
    }

    public function getTableName()
    {
        return "journal_headers";
    }

    public function getData()
    {
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = $this->journalService->getQuery();
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.journal_no', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.description', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.reference_type', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('ur.journal_no', 'LIKE', '%' . $keyword . '%');
                });
            }
            if (isset($_POST['search']['status']) && $_POST['search']['status'] != '') {
                $datadb->where('m.status', $_POST['search']['status']);
            }
            if (isset($_POST['search']['start_date']) && $_POST['search']['start_date'] != '') {
                $datadb->whereDate('m.journal_date', '>=', $_POST['search']['start_date']);
            }
            if (isset($_POST['search']['end_date']) && $_POST['search']['end_date'] != '') {
                $datadb->whereDate('m.journal_date', '<=', $_POST['search']['end_date']);
            }
            if (isset($_POST['order'][0]['column'])) {
                $datadb->orderBy('m.journal_no', $_POST['order'][0]['dir']);
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
        $result['is_valid'] = false;
        try {
            $journal = $data['id'] == '' ? $this->journalService->create($data) : $this->journalService->update($data['id'], $data);
            $result['is_valid'] = true;
            $result['id'] = $journal->id;
            $result['journal_no'] = $journal->journal_no;
        } catch (JournalValidationException $e) {
            $result['message'] = $e->getMessage();
        } catch (\Throwable $th) {
            $result['message'] = $th->getMessage();
        }
        return response()->json($result);
    }

    public function posted(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        try {
            $journal = $this->postingService->post($data['id']);
            $result['is_valid'] = true;
            $result['journal_no'] = $journal->journal_no;
        } catch (JournalValidationException $e) {
            $result['message'] = $e->getMessage();
        } catch (\Throwable $th) {
            $result['message'] = $th->getMessage();
        }
        return response()->json($result);
    }

    public function reversal(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        try {
            $reversal = $this->reversalService->reverse($data['id'], $data);
            $result['is_valid'] = true;
            $result['journal_no'] = $reversal->journal_no;
            $result['journal_id'] = $reversal->id;
        } catch (JournalValidationException $e) {
            $result['message'] = $e->getMessage();
        } catch (\Throwable $th) {
            $result['message'] = $th->getMessage();
        }
        return response()->json($result);
    }

    public function confirmDelete(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        try {
            $this->journalService->delete($data['id']);
            $result['is_valid'] = true;
        } catch (JournalValidationException $e) {
            $result['message'] = $e->getMessage();
        } catch (\Throwable $th) {
            $result['message'] = $th->getMessage();
        }
        return response()->json($result);
    }

    public function getDetailData($id)
    {
        DB::enableQueryLog();
        $journal = $this->journalService->getQuery()->where('m.id', $id)->first();
        if (empty($journal)) {
            $query = DB::getQueryLog();
            return response()->json(null);
        }

        $details = $this->journalService->getDetailList($id)
            ->map(function ($row) {
                $account = Accounts::select('code', 'name')->find($row->account_id);
                $row->account_code = empty($account) ? '' : $account->code;
                $row->account_name = empty($account) ? '' : $account->name;
                return $row;
            })
            ->toArray();

        $journal->details = $details;
        $query = DB::getQueryLog();
        return response()->json($journal);
    }

    public function getAccountList(Request $request)
    {
        $accounts = Accounts::select('id', 'code', 'name', 'normal_balance')
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('is_header', 0)
            ->orderBy('code')
            ->get()
            ->toArray();
        return response()->json($accounts);
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        return view('web.journals.modal.confirmdelete', $data);
    }

    public function showModalPost(Request $request)
    {
        $data = $request->all();
        $data['journal'] = $this->journalService->find($data['id']);
        return view('web.journals.modal.confirmpost', $data);
    }

    public function showModalReversal(Request $request)
    {
        $data = $request->all();
        $data['journal'] = $this->journalService->find($data['id']);
        return view('web.journals.modal.confirmreversal', $data);
    }
}
