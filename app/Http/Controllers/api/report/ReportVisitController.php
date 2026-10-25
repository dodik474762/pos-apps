<?php

namespace App\Http\Controllers\api\report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportVisitController extends Controller
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

        $tanggal     = $_POST['tanggal'] ?? date('Y-m-d');
        $tanggal_end = $_POST['tanggal_end'] ?? date('Y-m-d');
        $company_id  = session('id_company');
        $companyFilter = ($company_id !== null && $company_id !== '') ? ' AND prev.company_id = ' . (int) $company_id : '';

        // Presence: 1 baris per salesman per hari
        $presence = DB::table('presence')
            ->selectRaw('creator, DATE(presence_date) as presence_day, MIN(start_date) as start_date')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))
            ->whereDate('presence_date', '>=', $tanggal)
            ->whereDate('presence_date', '<=', $tanggal_end)
            ->groupBy('creator', DB::raw('DATE(presence_date)'));

        $datadb = DB::table('sales_order_headers as m')
            ->select([
                'm.id',
                'm.salesman',
                'm.so_date',
                'c.nama_customer',
                'c.code as customer_code',
                'c.channel_outlet',
                'm.remarks',
                'm.check_in_time',
                'm.check_out_time',
                'k.nama_lengkap as salesman_name',
                'usr.name as salesman_nik',
                'm.status',
                'm.platform',
                'pr.start_date as absen_time',
                DB::raw("
                SEC_TO_TIME(
                    GREATEST(
                        TIMESTAMPDIFF(
                            SECOND,
                            COALESCE(
                                (
                                    SELECT prev.check_out_time
                                    FROM sales_order_headers AS prev
                                    WHERE prev.salesman = m.salesman
                                      AND DATE(prev.so_date) = DATE(m.so_date)
                                      AND prev.check_in_time < m.check_in_time
                                      AND prev.deleted IS NULL{$companyFilter}
                                    ORDER BY prev.check_in_time DESC
                                    LIMIT 1
                                ),
                                pr.start_date  -- fallback: jam absen jika kunjungan pertama
                            ),
                            m.check_in_time
                        ),
                    0)
                ) AS lama_di_jalan
            "),
            ])
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->leftJoin('users as usr', 'usr.id', 'm.salesman')
            ->leftJoin('karyawan as k', 'k.nik', 'usr.nik')
            ->leftJoinSub($presence, 'pr', function ($join) {
                $join->on('pr.creator', '=', 'm.salesman')
                    ->on('pr.presence_day', '=', DB::raw('DATE(m.so_date)'));
            })
            ->whereDate('m.so_date', '>=', $tanggal)
            ->whereDate('m.so_date', '<=', $tanggal_end)
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('m.company_id', $company_id))
            ->whereNull('m.deleted');
        // ->where('c.code', 'CUST09260056'); // hapus baris ini jika hanya untuk testing

        // Total (tanpa search)
        $data['recordsTotal'] = (clone $datadb)->count();

        // Search
        if (!empty($_POST['search']['value'])) {
            $keyword = $_POST['search']['value'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('m.salesman', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('m.so_date', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('c.code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('c.channel_outlet', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('m.remarks', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('m.check_in_time', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('m.check_out_time', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('usr.name', 'LIKE', '%' . $keyword . '%');
            });
        }

        $data['recordsFiltered'] = (clone $datadb)->count();

        // Order (divalidasi, anti SQL injection)
        $dir = strtolower($_POST['order'][0]['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $col = $_POST['order'][0]['column'] ?? null;

        switch ((string) $col) {
            case '0':
                $datadb->orderBy('m.salesman', $dir);
                break;
            case '1':
                $datadb->orderBy('m.so_date', $dir);
                break;
            case '2':
                $datadb->orderBy('c.nama_customer', $dir);
                break;
            case '3':
                $datadb->orderBy('c.code', $dir);
                break;
            case '4':
                $datadb->orderBy('c.channel_outlet', $dir);
                break;
            case '5':
                $datadb->orderBy('m.check_in_time', $dir);
                break;
            case '6':
                $datadb->orderBy('m.check_out_time', $dir);
                break;
            case '7':
                $datadb->orderBy('m.salesman', $dir);
                break;
            default:
                $datadb->orderBy('usr.name', 'asc')->orderBy('m.check_in_time', 'asc');
                break;
        }

        // Paging
        if (isset($_POST['length']) && (int) $_POST['length'] > 0) {
            $datadb->limit((int) $_POST['length']);
            $datadb->offset((int) ($_POST['start'] ?? 0));
        }

        $data['data'] = $datadb->get()->toArray();
        $data['draw'] = $_POST['draw'] ?? 0;

        return json_encode($data);
    }

    public function getDataSummary()
    {
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $tanggal     = $_POST['tanggal'] ?? date('Y-m-d');
        $tanggal_end = $_POST['tanggal_end'] ?? date('Y-m-d');
        $company_id  = session('id_company');
        $companyFilter = ($company_id !== null && $company_id !== '') ? ' AND dvd.company_id = ' . (int) $company_id : '';

        // Presence: 1 baris per salesman per hari (absen paling awal)
        $presence = DB::table('presence')
            ->selectRaw('creator, DATE(presence_date) as presence_day, MIN(start_date) as start_date')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))
            ->whereDate('presence_date', '>=', $tanggal)
            ->whereDate('presence_date', '<=', $tanggal_end)
            ->groupBy('creator', DB::raw('DATE(presence_date)'));

        // Daily visit: 1 baris per user per tanggal
        $dailyVisit = DB::table('daily_visit')
            ->selectRaw('users, date_visit, MIN(id) as id, MAX(total_visit) as total_visit')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))
            ->whereNull('deleted')
            ->groupBy('users', 'date_visit');

        $datadb = DB::table('sales_order_headers as m')
            ->select([
                'usr.name as salesman_nik',
                'k.nama_lengkap as salesman_name',
                'm.so_date',
                'pr.start_date as jam_berangkat',
                'dv.total_visit',
                DB::raw('MAX(m.check_out_time) as jam_kembali'),

                // Waktu Jual = SUM durasi tiap outlet
                DB::raw('SEC_TO_TIME(
                GREATEST(
                    SUM(GREATEST(TIMESTAMPDIFF(SECOND, m.check_in_time, m.check_out_time), 0)),
                0)
            ) as waktu_jual'),

                // Rata-rata lama transaksi per outlet
                DB::raw('TIME_FORMAT(
                SEC_TO_TIME(
                    AVG(GREATEST(TIMESTAMPDIFF(SECOND, m.check_in_time, m.check_out_time), 0))
                ),
            "%H:%i:%s") as avg_lama_transaksi'),

                // Lama di Jalan = Total Keluar Kantor - Waktu Jual
                DB::raw('SEC_TO_TIME(
                GREATEST(
                    TIMESTAMPDIFF(SECOND, pr.start_date, MAX(m.check_out_time))
                    - SUM(GREATEST(TIMESTAMPDIFF(SECOND, m.check_in_time, m.check_out_time), 0)),
                0)
            ) as lama_di_jalan'),

                // Total Keluar Kantor = absen s/d check_out terakhir
                DB::raw('SEC_TO_TIME(
                GREATEST(
                    TIMESTAMPDIFF(SECOND, pr.start_date, MAX(m.check_out_time)),
                0)
            ) as total_keluar_kantor'),

                DB::raw('COUNT(m.id) as total_call'),

                // Call sesuai PJP
                DB::raw('SUM(CASE WHEN EXISTS (
                SELECT 1 FROM daily_visit_customer dvd
                WHERE dvd.daily_visit = dv.id AND dvd.customer = m.customer_id
            ) THEN 1 ELSE 0 END) as total_call_pjp'),

                // Call di luar PJP
                DB::raw('SUM(CASE WHEN NOT EXISTS (
                SELECT 1 FROM daily_visit_customer dvd
                WHERE dvd.daily_visit = dv.id AND dvd.customer = m.customer_id
            ) THEN 1 ELSE 0 END) as total_call_extra'),
            ])
            ->join('customer as c', 'c.id', 'm.customer_id')
            ->leftJoin('users as usr', 'usr.id', 'm.salesman')
            ->leftJoin('karyawan as k', 'k.nik', 'usr.nik')
            ->leftJoinSub($presence, 'pr', function ($join) {
                $join->on('pr.creator', '=', 'm.salesman')
                    ->on('pr.presence_day', '=', DB::raw('DATE(m.so_date)'));
            })
            ->leftJoinSub($dailyVisit, 'dv', function ($join) {
                $join->on('dv.users', '=', 'm.salesman')
                    ->on('dv.date_visit', '=', 'm.so_date');
            })
            ->whereDate('m.so_date', '>=', $tanggal)
            ->whereDate('m.so_date', '<=', $tanggal_end)
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('m.company_id', $company_id))
            ->whereNull('m.deleted')
            ->groupBy('m.salesman', 'usr.name', 'k.nama_lengkap', 'm.so_date', 'pr.start_date', 'dv.total_visit', 'dv.id');

        // Total (tanpa search)
        $data['recordsTotal'] = DB::query()->fromSub((clone $datadb), 'x')->count();

        // Search
        if (!empty($_POST['search']['value'])) {
            $keyword = $_POST['search']['value'];
            $datadb->where(function ($query) use ($keyword) {
                $query->where('usr.name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('k.nama_lengkap', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('m.so_date', 'LIKE', '%' . $keyword . '%');
            });
        }

        $data['recordsFiltered'] = DB::query()->fromSub((clone $datadb), 'x')->count();

        // Order (divalidasi, anti SQL injection)
        $dir = strtolower($_POST['order'][0]['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $col = $_POST['order'][0]['column'] ?? null;

        switch ((string) $col) {
            case '1':
                $datadb->orderBy('usr.name', $dir);
                break;
            case '2':
                $datadb->orderBy('m.so_date', $dir);
                break;
            case '3':
                $datadb->orderBy('pr.start_date', $dir);
                break;
            default:
                $datadb->orderBy('usr.name', 'asc')->orderBy('m.so_date', 'asc');
                break;
        }

        // Paging
        if (isset($_POST['length']) && (int) $_POST['length'] > 0) {
            $datadb->limit((int) $_POST['length']);
            $datadb->offset((int) ($_POST['start'] ?? 0));
        }

        $data['data'] = $datadb->get()->toArray();
        $data['draw'] = $_POST['draw'] ?? 0;

        return json_encode($data);
    }
}
