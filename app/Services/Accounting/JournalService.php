<?php

namespace App\Services\Accounting;

use App\Models\Transaction\JournalDetail;
use App\Models\Transaction\JournalHeader;
use Illuminate\Support\Facades\DB;

class JournalService
{
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_POSTED = 'POSTED';
    const STATUS_REVERSED = 'REVERSED';

    protected $validator;
    protected $periodService;

    public function __construct()
    {
        $this->validator = new JournalValidator();
        $this->periodService = new AccountingPeriodService();
    }

    public function getTableName()
    {
        return "journal_headers";
    }

    public function getStatusList()
    {
        return [
            self::STATUS_DRAFT => 'DRAFT',
            self::STATUS_POSTED => 'POSTED',
            self::STATUS_REVERSED => 'REVERSED',
        ];
    }

    public function generateJournalNo($prefix = 'JV', $companyId = null)
    {
        return generateNoJournal($prefix, $companyId);
    }

    /**
     * Membersihkan dan menomori ulang detail jurnal.
     * Menerima array maupun string JSON hasil pengiriman dari form.
     */
    public function normalizeDetails($details = [])
    {
        if (is_string($details)) {
            $decoded = json_decode($details, true);
            $details = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }
        if (empty($details) || ! is_array($details)) {
            return [];
        }

        $result = [];
        $lineNo = 0;

        foreach ($details as $detail) {
            $detail = is_array($detail) ? $detail : (array) $detail;

            $debit = $this->validator->toAmount($detail['debit'] ?? 0);
            $credit = $this->validator->toAmount($detail['credit'] ?? 0);
            $accountId = $detail['account_id'] ?? null;

            // Baris tanpa nominal dan tanpa akun diabaikan, bukan error.
            if (empty($accountId) && $debit <= 0 && $credit <= 0) {
                continue;
            }

            $lineNo++;
            $result[] = [
                'line_no' => $lineNo,
                'account_id' => empty($accountId) ? null : $accountId,
                'description' => isset($detail['description']) && $detail['description'] !== '' ? $detail['description'] : null,
                'debit' => $debit,
                'credit' => $credit,
            ];
        }

        return $result;
    }

    public function getDetailList($journalId)
    {
        $companyId = session('id_company');

        return JournalDetail::where('journal_id', $journalId)
            ->when($companyId !== null && $companyId !== '', fn ($q) => $q->where('company_id', $companyId))
            ->orderBy('line_no', 'asc')
            ->get();
    }

    public function find($id)
    {
        $companyId = session('id_company');

        $journal = JournalHeader::when($companyId !== null && $companyId !== '', fn ($q) => $q->where('company_id', $companyId))
            ->find($id);
        if (empty($journal)) {
            return null;
        }

        $journal->details = $this->getDetailList($id);
        return $journal;
    }

    public function getTotal($details = [])
    {
        return [
            'debit' => $this->validator->totalAmount($details, 'debit'),
            'credit' => $this->validator->totalAmount($details, 'credit'),
        ];
    }

    /**
     * Company aktif: dari payload bila ada, selain itu dari session.
     */
    protected function resolveCompanyId($data = [])
    {
        $companyId = $data['company_id'] ?? null;
        if ($companyId === null || $companyId === '') {
            $companyId = session('id_company');
        }
        return ($companyId === null || $companyId === '') ? null : $companyId;
    }

    /**
     * Membuat jurnal baru berstatus DRAFT.
     */
    public function create($data = [], $isAutomatic = false)
    {
        $details = $this->normalizeDetails($data['details'] ?? []);
        $header = $this->buildHeader($data);
        $companyId = $this->resolveCompanyId($data);
        $header['company_id'] = $companyId;

        $errors = $this->validator->validate($header, $details, true);
        $errors = array_merge($errors, $this->validator->validateReference($header, $isAutomatic));
        if (! empty($errors)) {
            throw new JournalValidationException(implode(' ', $errors), $errors);
        }

        $period = $this->periodService->ensureOpen($header['journal_date'], $companyId);

        return DB::transaction(function () use ($header, $details, $period, $companyId) {
            $journal = new JournalHeader();
            if ($companyId !== null) {
                $journal->company_id = $companyId;
            }
            $journal->journal_no = $this->generateJournalNo('JV', $companyId);
            $journal->journal_date = $header['journal_date'];
            $journal->accounting_period_id = $period->id;
            $journal->reference_type = $header['reference_type'];
            $journal->reference_id = $header['reference_id'];
            $journal->description = $header['description'];
            $journal->status = self::STATUS_DRAFT;
            $journal->reversal_of_id = $header['reversal_of_id'] ?? null;
            $journal->save();

            $this->syncDetails($journal->id, $details, $companyId);

            return $journal;
        });
    }

    /**
     * Mengubah jurnal yang masih berstatus DRAFT.
     */
    public function update($id, $data = [])
    {
        $data['id'] = $id;
        $details = $this->normalizeDetails($data['details'] ?? []);
        $header = $this->buildHeader($data);
        $companyId = $this->resolveCompanyId($data);
        $header['company_id'] = $companyId;

        $errors = $this->validator->validate($header, $details, false);
        if (! empty($errors)) {
            throw new JournalValidationException(implode(' ', $errors), $errors);
        }

        $period = $this->periodService->ensureOpen($header['journal_date'], $companyId);

        return DB::transaction(function () use ($id, $header, $details, $period) {
            $journal = JournalHeader::where('id', $id)->lockForUpdate()->first();
            if (empty($journal)) {
                throw new JournalValidationException('Jurnal tidak ditemukan.');
            }
            if ($journal->status !== self::STATUS_DRAFT) {
                throw new JournalValidationException('Jurnal ' . $journal->journal_no . ' sudah berstatus ' . $journal->status . ' dan tidak dapat diedit.');
            }

            $journal->journal_date = $header['journal_date'];
            $journal->accounting_period_id = $period->id;
            $journal->reference_type = $header['reference_type'];
            $journal->reference_id = $header['reference_id'];
            $journal->description = $header['description'];
            $journal->save();

            $this->syncDetails($journal->id, $details, $journal->company_id);

            return $journal;
        });
    }

    /**
     * Menghapus jurnal yang masih berstatus DRAFT.
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            $journal = JournalHeader::where('id', $id)->lockForUpdate()->first();
            if (empty($journal)) {
                throw new JournalValidationException('Jurnal tidak ditemukan.');
            }
            if ($journal->status !== self::STATUS_DRAFT) {
                throw new JournalValidationException('Jurnal ' . $journal->journal_no . ' sudah berstatus ' . $journal->status . '. Jurnal yang sudah diposting tidak dapat dihapus, gunakan jurnal reversal.');
            }

            JournalDetail::where('journal_id', $journal->id)->delete();
            $journal->delete();

            return true;
        });
    }

    public function syncDetails($journalId, $details = [], $companyId = null)
    {
        JournalDetail::where('journal_id', $journalId)->delete();
        foreach ($details as $detail) {
            $row = new JournalDetail();
            if ($companyId !== null && $companyId !== '') {
                $row->company_id = $companyId;
            }
            $row->journal_id = $journalId;
            $row->line_no = $detail['line_no'];
            $row->account_id = $detail['account_id'];
            $row->description = $detail['description'];
            $row->debit = $detail['debit'];
            $row->credit = $detail['credit'];
            $row->save();
        }
    }

    public function buildHeader($data = [])
    {
        $journalDate = $this->periodService->normalizeDate($data['journal_date'] ?? '');
        $referenceId = $data['reference_id'] ?? null;

        return [
            'id' => $data['id'] ?? null,
            'journal_date' => $journalDate,
            'reference_type' => isset($data['reference_type']) && $data['reference_type'] !== '' ? $data['reference_type'] : null,
            'reference_id' => empty($referenceId) || $referenceId === '0' ? null : $referenceId,
            'description' => isset($data['description']) && $data['description'] !== '' ? $data['description'] : null,
            'reversal_of_id' => empty($data['reversal_of_id']) ? null : $data['reversal_of_id'],
        ];
    }

    /**
     * Query dasar list jurnal.
     */
    public function getQuery()
    {
        $companyId = session('id_company');

        return DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
                'ap.year as period_year',
                'ap.month as period_month',
                'ap.status as period_status',
                'uj.name as posted_by_name',
                'ur.journal_no as reversal_of_no',
            ])
            ->leftJoin('accounting_periods as ap', 'm.accounting_period_id', '=', 'ap.id')
            ->leftJoin('users as uj', 'm.posted_by', '=', 'uj.id')
            ->leftJoin('journal_headers as ur', 'm.reversal_of_id', '=', 'ur.id')
            ->when($companyId !== null && $companyId !== '', fn ($q) => $q->where('m.company_id', $companyId));
    }
}
