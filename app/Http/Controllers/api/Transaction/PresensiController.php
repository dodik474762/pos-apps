<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Controllers\web\Transaction\PresensiController as TransactionPresensiController;
use App\Models\Master\Karyawan;
use App\Models\Master\Users;
use App\Models\Transaction\Presence;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PresensiController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function getDataPresensi(Request $request)
    {
        $data = $request->all();
        $date = date('Y-m-d');
        $result['is_valid'] = true;
        $datadb = Presence::where('presence.creator', $data['users'])
            ->select(['presence.*', 'kry.nik'])
            ->join('karyawan as kry', 'kry.id', 'presence.karyawan')
            ->whereNull('presence.deleted')
            ->where('presence.presence_date', '=', $date)
            ->get()->toArray();
        $result['data'] = $datadb;
        return response()->json($result);
    }

    public function submitPresensi(Request $request)
    {
        // Ambil data JSON
        $data = json_decode($request->input('data'), true);
        // Contoh ambil satu field dari JSON
        $presensiDate = $data['presensi_date'] ?? null;
        $userId = $data['user_id'] ?? null;
        // 1. Parse ISO 8601 string langsung
        $periode = Carbon::parse($presensiDate)->setTimezone('Asia/Jakarta');

        // 2. Jika mau format MySQL datetime
        $date = $periode->format('Y-m-d H:i:s');
        $periode_date = $periode->format('Y-m-d');

        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            // $file = $_FILES['files'] ?? null;
            $file = $request->file('files');

            $dir = 'berkas/document/presence/';
            $dir .= date('Y') . '/' . date('m');
            $pathlamp = public_path() . '/' . $dir . '/';
            // Create the directory if it doesn't exist
            if (!File::isDirectory($pathlamp)) {
                File::makeDirectory($pathlamp, 0777, true, true);
            }

            $checkSudahAbsen = Presence::where('creator', $userId)
                ->where('presence_date', $periode_date)
                ->whereNull('deleted')
                ->first();

            $fileName = time() . '.jpg';

            $path = $file->move(public_path($dir), $fileName);
            $dbpathlamp = '/' . $dir . '/';

            if (empty($checkSudahAbsen)) {
                $karyawan = Users::where('users.id', $userId)
                    ->select(['users.*', 'k.id as id_karyawan'])
                    ->join('karyawan as k', 'k.nik', 'users.nik')
                    ->first();
                if (empty($karyawan)) {
                    DB::rollBack();
                    $result['message'] = 'Karyawan tidak ditemukan ' . $userId;
                    return response()->json($result);
                }

                $absen = new Presence();
                $absen->code = generateCodePresence();
                $absen->creator = $userId;
                $absen->presence_date = $periode_date;
                $absen->karyawan = $karyawan->id_karyawan;
                $absen->start_date = $date;
                $absen->status = 'DRAFT';
                $absen->latitude = $data['latitude'];
                $absen->longitude = $data['longitude'];
                $absen->files = isset($fileName) ? $fileName : null;
                $absen->path_files = isset($dbpathlamp) ? $dbpathlamp : null;
                $absen->save();
            } else {
                $date1 = Carbon::parse($checkSudahAbsen->start_date);
                $date2 = $date;
                $totalHour = $date1->diffInMilliseconds($date2);

                $absen = $checkSudahAbsen;
                $absen->end_date = $date;
                $absen->status = 'COMPLETE';
                $absen->total_hours = $totalHour;
                $absen->files_after = isset($fileName) ? $fileName : $absen->files_after;
                $absen->path_files_after = isset($dbpathlamp) ? $dbpathlamp : $absen->path_files_after;
                $absen->latitude_after = $data['latitude'];
                $absen->longitude_after = $data['longitude'];
                $absen->save();
            }

            DB::commit();
            $result['is_valid'] = true;
            $result['data'] = $data;
            $result['file'] = $file;
            $result['presensiDate'] = $periode->format('Y-m-d');
            $result['date'] = $date;
            $result['message'] = 'Presensi Berhasil di Submit';
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $result['message'] = $th->getMessage();
        }

        return response()->json($result);
    }

    public function getTableName()
    {
        return "presence";
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
                'k.nama_lengkap as karyawan_name',
            ])
            ->join('karyawan as k', 'k.id', 'm.karyawan')
            ->whereNull('m.deleted');
        $akses = strtolower(session('akses'));
        $allowed = ['superadmin', 'admin pga', 'bod/boc', 'operational manager', 'supervisor sales'];

        if (!in_array($akses, $allowed)) {
            $datadb->where('m.creator', session('user_id'));
        }

        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.code', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.presence_date', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.remarks', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.files', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('m.files_after', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('k.nama_lengkap', 'LIKE', '%' . $keyword . '%');
                });
            }
            if (isset($_POST['order'][0]['column'])) {
                switch ($_POST['order'][0]['column']) {
                    case 0:
                        $datadb->orderBy('m.id', $_POST['order'][0]['dir']);
                        break;
                    case 1:
                        $datadb->orderBy('m.id', $_POST['order'][0]['dir']);
                        break;
                    case 2:
                        $datadb->orderBy('m.code', $_POST['order'][0]['dir']);
                        break;
                    case 3:
                        $datadb->orderBy('k.nama_lengkap', $_POST['order'][0]['dir']);
                        break;
                    case 4:
                        $datadb->orderBy('m.presence_date', $_POST['order'][0]['dir']);
                        break;
                    case 5:
                        $datadb->orderBy('m.start_date', $_POST['order'][0]['dir']);
                        break;
                    case 6:
                        $datadb->orderBy('m.files', $_POST['order'][0]['dir']);
                        break;
                    case 7:
                        $datadb->orderBy('m.latitude', $_POST['order'][0]['dir']);
                        break;
                    case 8:
                        $datadb->orderBy('m.distance', $_POST['order'][0]['dir']);
                        break;
                    case 9:
                        $datadb->orderBy('m.end_date', $_POST['order'][0]['dir']);
                        break;
                    case 10:
                        $datadb->orderBy('m.files_after', $_POST['order'][0]['dir']);
                        break;
                    case 11:
                        $datadb->orderBy('m.latitude_after', $_POST['order'][0]['dir']);
                        break;
                    case 12:
                        $datadb->orderBy('m.distance_after', $_POST['order'][0]['dir']);
                        break;
                    case 13:
                        $datadb->orderBy('m.total_hours', $_POST['order'][0]['dir']);
                        break;
                    default:
                        // $datadb->orderBy('m.id', $_POST['order'][0]['dir']);
                        break;
                }
                // $datadb->orderBy('m.id', $_POST['order'][0]['dir']);
            }
            $data['recordsFiltered'] = $datadb->get()->count();

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
            $value->files_after_url = $value->path_files_after == '' ? '' : url('/') . $value->path_files_after . '/' . $value->files_after;
            $value->files_url = $value->path_files == '' ? '' : url('/') . $value->path_files . '/' . $value->files;
            $value->start_date = $value->start_date == '' ? '' : date('d/m/Y H:i:s', strtotime($value->start_date));
            $value->end_date = $value->end_date == '' ? '' : date('d/m/Y H:i:s', strtotime($value->end_date));
            $value->presence_date = $value->presence_date == '' ? '' : date('d/m/Y', strtotime($value->presence_date));
            $value->total_hours = $value->total_hours == '' ? '' : number_format($value->total_hours / 1000, 0, ',', '.');
            $value->akses = session('akses');
            // url('/') . $data->path_files . '/' . $data->files
            $resultdb[] = $value;
        }
        $data['data'] = $resultdb;
        $data['draw'] = $_POST['draw'];
        $query = DB::getQueryLog();
        // echo '<pre>';
        // print_r($query);die;
        return json_encode($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        return view('web.presence.modal.confirmdelete', $data);
    }

    public function confirmDelete(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $menu = Presence::find($data['id']);
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

    public function showDataKaryawan(Request $request)
    {
        $data = $request->all();
        return view('web.presence.modal.datakaryawan', $data);
    }

    public function submit(Request $request)
    {
        $data = $request->all();
        $post =  $data;
        $result['is_valid'] = false;
        $post['presenceDate'] = $data['presence_date'];
        $post['users'] = session('user_id');

        $periode = date('Y-m-d');
        DB::beginTransaction();
        try {
            list($idKry, $nameKry) = explode('//', $post['nik']);
            $dataUsersKry = Karyawan::where('karyawan.id', $idKry)
                ->select(['u.*'])
                ->join('users as u', 'u.nik', 'karyawan.nik')
                ->whereNull('u.deleted')
                ->first();
            if (empty($dataUsersKry)) {
                DB::rollBack();
                $result['message'] = 'Data Karyawan tidak ditemukan';
                return response()->json($result);
            } else {
                $post['users'] = $dataUsersKry->id;
            }

            // New file directory
            $dir = 'berkas/document/presence/';
            $dir .= date('Y') . '/' . date('m');
            $pathlamp = public_path() . '/' . $dir . '/';
            // Create the directory if it doesn't exist
            if (!File::isDirectory($pathlamp)) {
                File::makeDirectory($pathlamp, 0777, true, true);
            }

            // Gunakan nama file yang diposting
            $fileName = empty($data['file']) ? '' : $data['file']->getClientOriginalName();

            if (!empty($data['file'])) {
                $files = $data['file'];
                $files->move($pathlamp, $fileName);
            }

            $dbpathlamp = '/' . $dir . '/';

            $checkSudahAbsen = Presence::where('creator', $post['users'])
                ->where('presence_date', $periode)
                ->whereNull('deleted')
                ->first();
            // echo '<pre>';
            // print_r($checkSudahAbsen);die;

            if (empty($checkSudahAbsen)) {
                $karyawan = Users::where('users.id', $post['users'])
                    ->select(['users.*', 'k.id as id_karyawan'])
                    ->join('karyawan as k', 'k.nik', 'users.nik')
                    ->first();

                $absen = new Presence();
                $absen->code = generateCodePresence();
                $absen->creator = $post['users'];
                $absen->presence_date = $periode;
                $absen->karyawan = $karyawan->id_karyawan;
                $absen->start_date = $post['presenceDate'];
                $absen->status = 'DRAFT';
                $absen->latitude = $post['latitude'];
                $absen->longitude = $post['longitude'];
                $absen->files = isset($fileName) ? $fileName : null;
                $absen->path_files = isset($dbpathlamp) ? $dbpathlamp : null;
                $absen->save();
            } else {
                $date1 = Carbon::parse($checkSudahAbsen->start_date);
                $date2 = $post['presenceDate'];

                $totalHour = $date1->diffInMilliseconds($date2);

                $absen = $checkSudahAbsen;
                $absen->end_date = $post['presenceDate'];
                $absen->status = 'COMPLETE';
                $absen->total_hours = $totalHour;
                $absen->files_after = isset($fileName) ? $fileName : $absen->files_after;
                $absen->path_files_after = isset($dbpathlamp) ? $dbpathlamp : $absen->path_files_after;
                $absen->latitude_after = $post['latitude'];
                $absen->longitude_after = $post['longitude'];
                $absen->save();
            }
            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Upload Success ';
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $result['message'] = $th->getMessage();
        }

        // return response()->json($result);
        if ($result['is_valid']) {
            return redirect()->action([TransactionPresensiController::class, 'index'], ['success' => $result['message']]);
        } else {
            return redirect()->action([TransactionPresensiController::class, 'index'], ['error' => $result['message']]);
        }
    }
}
