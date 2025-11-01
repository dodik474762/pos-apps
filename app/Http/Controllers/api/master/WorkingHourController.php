<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\WorkingHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkingHourController extends Controller
{
    public function getTableName()
    {
        return "working_hours";
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
            ])
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.day', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.remarks', 'LIKE', '%' . $keyword . '%');
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

    public function submit(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        $existDay = WorkingHour::where('day', $data['day'])->whereNull('deleted')->first();
        $validation = true;
        if (!empty($existDay) && $data['id'] == '') {
            $validation = false;
        }
        DB::beginTransaction();
        try {
            //code...
            if ($validation) {
                $roles = $data['id'] == '' ? new WorkingHour() : WorkingHour::find($data['id']);
                $roles->day = $data['day'];
                $roles->start_hour = $data['start_hour'];
                $roles->end_hour = $data['end_hour'];
                $roles->remarks = $data['remarks'];
                $roles->holiday = $data['holiday'];
                $roles->save();
                DB::commit();
                $result['is_valid'] = true;
            } else {
                $result['message'] = 'Data sudah ada';
                DB::rollBack();
            }
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function confirmDelete(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $menu = WorkingHour::find($data['id']);
            $menu->deleted = date('Y-m-d H:i:s');
            $menu->save();

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function getDetailData($id)
    {
        DB::enableQueryLog();
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
            ])->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();
        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        return view('web.working_hour.modal.confirmdelete', $data);
    }
}
