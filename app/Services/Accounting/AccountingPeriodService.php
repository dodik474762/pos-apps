<?php

namespace App\Services\Accounting;

use App\Models\Transaction\AccountingPeriod;
use Illuminate\Support\Facades\DB;

class AccountingPeriodService
{
    const STATUS_OPEN = 'OPEN';
    const STATUS_CLOSED = 'CLOSED';
    const STATUS_LOCKED = 'LOCKED';

    public function getTableName()
    {
        return "accounting_periods";
    }

    public function getStatusList()
    {
        return [
            self::STATUS_OPEN => 'OPEN',
            self::STATUS_CLOSED => 'CLOSED',
            self::STATUS_LOCKED => 'LOCKED',
        ];
    }

    public function getMonthList()
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    public function normalizeDate($date = '')
    {
        if (empty($date)) {
            return null;
        }
        $timestamp = strtotime((string) $date);
        if ($timestamp === false) {
            return null;
        }
        return date('Y-m-d', $timestamp);
    }

    public function getPeriodLabel($period)
    {
        $month = isset($this->getMonthList()[$period->month]) ? $this->getMonthList()[$period->month] : $period->month;
        return $month . ' ' . $period->year;
    }

    /**
     * Cari periode akuntansi yang mencakup sebuah tanggal.
     */
    public function findByDate($date = '')
    {
        $date = $this->normalizeDate($date);
        if (empty($date)) {
            return null;
        }

        return AccountingPeriod::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }

    public function findByYearMonth($year, $month)
    {
        return AccountingPeriod::where('year', $year)
            ->where('month', $month)
            ->first();
    }

    public function isOpen($period)
    {
        if (empty($period)) {
            return false;
        }
        return $period->status === self::STATUS_OPEN;
    }

    /**
     * Aturan 6: journal date harus berada pada accounting period OPEN.
     */
    public function ensureOpen($date = '')
    {
        $date = $this->normalizeDate($date);
        if (empty($date)) {
            throw new JournalValidationException('Tanggal jurnal tidak valid.');
        }

        $period = $this->findByDate($date);
        if (empty($period)) {
            throw new JournalValidationException('Periode akuntansi untuk tanggal ' . date('d-m-Y', strtotime($date)) . ' belum dibuat, jurnal tidak dapat diproses.');
        }

        if (! $this->isOpen($period)) {
            throw new JournalValidationException('Periode akuntansi ' . $this->getPeriodLabel($period) . ' berstatus ' . $period->status . '. Jurnal hanya dapat diposting pada periode OPEN.');
        }

        return $period;
    }

    public function save($data = [])
    {
        $result['is_valid'] = false;

        $year = isset($data['year']) ? (int) $data['year'] : 0;
        $month = isset($data['month']) ? (int) $data['month'] : 0;
        if ($year < 2000 || $year > 2100) {
            $result['message'] = 'Tahun periode tidak valid';
            return $result;
        }
        if ($month < 1 || $month > 12) {
            $result['message'] = 'Bulan periode tidak valid';
            return $result;
        }

        $startDate = $this->normalizeDate($data['start_date'] ?? '');
        $endDate = $this->normalizeDate($data['end_date'] ?? '');
        if (empty($startDate) || empty($endDate)) {
            $result['message'] = 'Tanggal awal dan akhir periode wajib diisi';
            return $result;
        }
        if ($startDate > $endDate) {
            $result['message'] = 'Tanggal awal periode tidak boleh lebih besar dari tanggal akhir periode';
            return $result;
        }

        $exist = $this->findByYearMonth($year, $month);
        if (! empty($exist) && (empty($data['id']) || $exist->id != $data['id'])) {
            $result['message'] = 'Periode ' . $this->getPeriodLabel($exist) . ' sudah ada';
            return $result;
        }

        $status = isset($data['status']) && $data['status'] != '' ? $data['status'] : self::STATUS_OPEN;
        if (! array_key_exists($status, $this->getStatusList())) {
            $result['message'] = 'Status periode tidak valid';
            return $result;
        }

        DB::beginTransaction();
        try {
            $period = empty($data['id']) ? new AccountingPeriod() : AccountingPeriod::find($data['id']);
            $period->year = $year;
            $period->month = $month;
            $period->start_date = $startDate;
            $period->end_date = $endDate;
            $period->status = $status;
            $period->description = $data['description'] ?? null;
            if ($status === self::STATUS_CLOSED) {
                $period->closed_at = $period->closed_at ?: date('Y-m-d H:i:s');
                $period->closed_by = $period->closed_by ?: session('user_id');
            } else {
                $period->closed_at = null;
                $period->closed_by = null;
            }
            $period->save();

            DB::commit();
            $result['is_valid'] = true;
            $result['id'] = $period->id;
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['message'] = $th->getMessage();
        }

        return $result;
    }

    public function close($id, $userId = null)
    {
        $result['is_valid'] = false;

        DB::beginTransaction();
        try {
            $period = AccountingPeriod::find($id);
            if (empty($period)) {
                throw new \RuntimeException('Periode akuntansi tidak ditemukan');
            }

            $postedJournal = DB::table('journal_headers')
                ->whereBetween('journal_date', [$period->start_date, $period->end_date])
                ->whereIn('status', [JournalService::STATUS_DRAFT, JournalService::STATUS_POSTED])
                ->count();
            if ($postedJournal > 0) {
                throw new \RuntimeException('Periode masih memiliki ' . $postedJournal . ' jurnal aktif, tidak dapat ditutup');
            }

            $period->status = self::STATUS_CLOSED;
            $period->closed_at = date('Y-m-d H:i:s');
            $period->closed_by = empty($userId) ? session('user_id') : $userId;
            $period->save();

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['message'] = $th->getMessage();
        }

        return $result;
    }

    public function reopen($id)
    {
        $result['is_valid'] = false;

        DB::beginTransaction();
        try {
            $period = AccountingPeriod::find($id);
            if (empty($period)) {
                throw new \RuntimeException('Periode akuntansi tidak ditemukan');
            }

            $period->status = self::STATUS_OPEN;
            $period->closed_at = null;
            $period->closed_by = null;
            $period->save();

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['message'] = $th->getMessage();
        }

        return $result;
    }

    /**
     * Buat periode satu tahun berjalan (12 bulan) beserta status OPEN.
     */
    public function generateYear($year, $description = '')
    {
        $result['is_valid'] = false;
        $created = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            for ($month = 1; $month <= 12; $month++) {
                $exist = $this->findByYearMonth($year, $month);
                if (! empty($exist)) {
                    $skipped++;
                    continue;
                }

                $period = new AccountingPeriod();
                $period->year = $year;
                $period->month = $month;
                $period->start_date = date('Y-m-01', strtotime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01'));
                $period->end_date = date('Y-m-t', strtotime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01'));
                $period->status = self::STATUS_OPEN;
                $period->description = $description;
                $period->save();
                $created++;
            }

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = $created . ' periode dibuat, ' . $skipped . ' periode sudah ada';
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['message'] = $th->getMessage();
        }

        return $result;
    }

    public function getListPeriod($year = null)
    {
        $query = AccountingPeriod::select('*');
        if (! empty($year)) {
            $query->where('year', $year);
        }
        return $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }
}
