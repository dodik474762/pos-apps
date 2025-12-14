<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
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

    public function submitPresensi(Request $request) {
         // Ambil data JSON
        $data = json_decode($request->input('data'), true);
            // Contoh ambil satu field dari JSON
        $presensiDate = $data['presensi_date'] ?? null;
        $userId = $data['user_id'] ?? null;
        // 1. Parse ISO 8601 string langsung
        $periode = Carbon::parse($presensiDate)->setTimezone('Asia/Jakarta');

        // 2. Jika mau format MySQL datetime
        $date = $periode->format('Y-m-d H:i:s');

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
                ->where('presence_date', $periode)
                ->first();

            $fileName = time() . '.jpg';

            $path = $file->move(public_path($dir), $fileName);
            $dbpathlamp = '/' . $dir . '/';

            if (empty($checkSudahAbsen)) {
                $karyawan = Users::where('users.id', $userId)
                    ->select(['users.*', 'k.id as id_karyawan'])
                    ->join('karyawan as k', 'k.nik', 'users.nik')
                    ->first();
                if(empty($karyawan)) {
                    DB::rollBack();
                    $result['message'] = 'Karyawan tidak ditemukan '.$userId;
                    return response()->json($result);
                }

                $absen = new Presence();
                $absen->code = generateCodePresence();
                $absen->creator = $userId;
                $absen->presence_date = $periode;
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
}
