<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\PackingList;
use App\Models\Transaction\PackingListSalesman;
use App\Models\Transaction\PackingListSalesmanInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PLTagihanController extends Controller
{
    public function getTableName()
    {
        return 'packing_list_salesman';
    }

    public function getData()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'k.nama_lengkap as salesman_name',
                'sl.name as kode_sales'
            ])
            ->join('users as sl', 'sl.id', 'm.salesman')
            ->join('users as u', 'u.id', 'm.created_by')
            ->join('karyawan as k', 'k.nik', 'sl.nik')
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.packing_list_no', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.packing_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('sl.name', 'LIKE', '%' . $keyword . '%');;
                    $query->orWhere('sl.nik', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('k.nama_lengkap', 'LIKE', '%' . $keyword . '%');
                });
            }
            if (isset($_POST['order'][0]['column'])) {
                $datadb->orderBy('m.id', $_POST['order'][0]['dir']);
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
        $data['draw'] = $_POST['draw'];
        $query = DB::getQueryLog();

        // echo '<pre>';
        // print_r($query);die;
        return json_encode($data);
    }

    public function generatePL(Request $request)
    {
        $data = $request->all();
        $userId = session('user_id');
        $result = ['is_valid' => false];

        // 🔥 decode JSON string jadi array
        $data['items'] = is_string($data['items']) ? json_decode($data['items'], true) : $data['items'];

        DB::beginTransaction();
        try {

            $existPL = PackingListSalesman::where('packing_date', $data['pl_date'])
                ->where('salesman', $data['salesman'])
                ->first();
            $header = empty($existPL) ? new PackingListSalesman() : $existPL;

            if (empty($existPL)) {
                $header->packing_list_no = generateNoPLTagihan(); // misal helper
                $header->created_by = $userId;
            }

            $header->packing_date = $data['pl_date'];
            $header->salesman = $data['salesman'];
            $header->remarks = 'PL TAGIHAN';
            $header->type_transaction = 'PL';
            $header->status = 'draft';
            $header->save();

            $hdrId = $header->id;

            // HAPUS dulu SEMUA detail packing list ini, di luar loop
            PackingListSalesmanInvoice::where('packing_list_id', $hdrId)->delete();

            // === DETAIL DO===
            $details = empty($data['items']) ? [] : collect($data['items']);
            if (empty($details)) {
                DB::rollBack();
                $result['is_valid'] = false;
                $result['message'] = 'Detail Invoice tidak boleh kosong';
                return response()->json($result);
            }

            foreach ($data['items'] as $key => $value) {
                $detail = new PackingListSalesmanInvoice();

                $detail->packing_list_id = $hdrId;
                $detail->invoice_id = $value['invoice_id'];
                $detail->save();
            }


            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Packing List Tagihan berhasil disimpan';
            $result['so_id'] = $hdrId;
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['is_valid'] = false;
            $result['message'] = $th->getMessage();
        }

        return response()->json($result);
    }
}
