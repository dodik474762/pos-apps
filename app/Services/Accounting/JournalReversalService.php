<?php

namespace App\Services\Accounting;

use App\Models\Transaction\JournalDetail;
use App\Models\Transaction\JournalHeader;
use Illuminate\Support\Facades\DB;

class JournalReversalService
{
    protected $validator;
    protected $periodService;
    protected $postingService;

    public function __construct()
    {
        $this->validator = new JournalValidator();
        $this->periodService = new AccountingPeriodService();
        $this->postingService = new JournalPostingService();
    }

    /**
     * Jurnal POSTED tidak dihapus. Sistem membuat jurnal reversal baru
     * dengan debit dan credit dibalik, jurnal asli menjadi REVERSED.
     */
    public function reverse($journalId, $data = [])
    {
        return DB::transaction(function () use ($journalId, $data) {
            $userId = empty($data['user_id']) ? session('user_id') : $data['user_id'];
            $reversalDate = $this->periodService->normalizeDate($data['journal_date'] ?? '') ?: date('Y-m-d');

            // Jurnal asli harus berstatus POSTED.
            $original = JournalHeader::where('id', $journalId)->lockForUpdate()->first();
            if (empty($original)) {
                throw new JournalValidationException('Jurnal tidak ditemukan.');
            }

            // Tidak boleh dilakukan dua kali untuk jurnal yang sama.
            $existing = JournalHeader::where('reversal_of_id', $original->id)
                ->whereIn('status', [JournalService::STATUS_DRAFT, JournalService::STATUS_POSTED])
                ->first();
            if (! empty($existing)) {
                throw new JournalValidationException('Jurnal ' . $original->journal_no . ' sudah pernah direversal melalui jurnal ' . $existing->journal_no . '.');
            }

            if ($original->status === JournalService::STATUS_REVERSED) {
                throw new JournalValidationException('Jurnal ' . $original->journal_no . ' sudah berstatus REVERSED.');
            }
            if ($original->status !== JournalService::STATUS_POSTED) {
                throw new JournalValidationException('Hanya jurnal berstatus POSTED yang dapat direversal. Jurnal ' . $original->journal_no . ' berstatus ' . $original->status . '.');
            }

            // Reversal tetap mengikuti validasi period.
            $period = $this->periodService->ensureOpen($reversalDate);

            $originalDetails = JournalDetail::where('journal_id', $original->id)
                ->orderBy('line_no', 'asc')
                ->get()
                ->toArray();

            // Detail reversal: debit dan credit dibalik.
            $details = $this->buildReversalDetails($originalDetails, $reversalDate, $original->journal_no);

            $this->postingService->assertNoErrors($this->validator->validateDetails($details));
            $this->postingService->assertNoErrors($this->validator->validateTotals($details));

            $description = $this->buildDescription($original, $data['description'] ?? '');

            $reversal = new JournalHeader();
            $reversal->journal_no = generateNoJournal('JVR');
            $reversal->journal_date = $reversalDate;
            $reversal->accounting_period_id = $period->id;
            $reversal->reference_type = $original->reference_type;
            $reversal->reference_id = $original->reference_id;
            $reversal->description = $description;
            $reversal->status = JournalService::STATUS_POSTED;
            $reversal->posted_at = date('Y-m-d H:i:s');
            $reversal->posted_by = $userId;
            $reversal->reversal_of_id = $original->id;
            $reversal->save();

            $journalService = new JournalService();
            $journalService->syncDetails($reversal->id, $details);

            // Jurnal asli menjadi REVERSED.
            $original->status = JournalService::STATUS_REVERSED;
            $original->save();

            $this->postingService->projectToGeneralLedger($reversal, $details);

            return $reversal;
        });
    }

    public function buildReversalDetails($originalDetails = [], $reversalDate = '', $originalNo = '')
    {
        $details = [];
        $lineNo = 0;

        foreach ($originalDetails as $detail) {
            $detail = is_array($detail) ? $detail : (array) $detail;
            $debit = $this->validator->toAmount($detail['debit'] ?? 0);
            $credit = $this->validator->toAmount($detail['credit'] ?? 0);
            $amount = $debit > 0 ? $debit : $credit;
            if ($amount <= JournalValidator::EPSILON) {
                continue;
            }

            $lineNo++;
            $details[] = [
                'line_no' => $lineNo,
                'account_id' => $detail['account_id'] ?? null,
                'description' => 'Reversal ' . $originalNo,
                'debit' => $credit,
                'credit' => $debit,
            ];
        }

        return $details;
    }

    public function buildDescription($original, $remark = '')
    {
        $description = 'Reversal jurnal ' . $original->journal_no;
        if (! empty($remark)) {
            $description .= ' - ' . $remark;
        }
        return mb_substr($description, 0, 255);
    }
}
