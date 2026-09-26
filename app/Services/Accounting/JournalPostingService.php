<?php

namespace App\Services\Accounting;

use App\Models\Transaction\GeneralLedger;
use App\Models\Transaction\JournalDetail;
use App\Models\Transaction\JournalHeader;
use Illuminate\Support\Facades\DB;

class JournalPostingService
{
    const EPSILON = 0.001;

    protected $validator;
    protected $periodService;

    public function __construct()
    {
        $this->validator = new JournalValidator();
        $this->periodService = new AccountingPeriodService();
    }

    /**
     * Posting mengubah DRAFT menjadi POSTED setelah seluruh validasi berhasil.
     * Seluruh proses wajib atomic menggunakan database transaction.
     */
    public function post($journalId, $userId = null)
    {
        return DB::transaction(function () use ($journalId, $userId) {
            // 1. Validasi header.
            $journal = JournalHeader::where('id', $journalId)->lockForUpdate()->first();
            if (empty($journal)) {
                throw new JournalValidationException('Jurnal tidak ditemukan.');
            }
            if ($journal->status === JournalService::STATUS_POSTED) {
                throw new JournalValidationException('Jurnal ' . $journal->journal_no . ' sudah berstatus POSTED.');
            }
            if ($journal->status === JournalService::STATUS_REVERSED) {
                throw new JournalValidationException('Jurnal ' . $journal->journal_no . ' sudah berstatus REVERSED.');
            }

            $details = JournalDetail::where('journal_id', $journal->id)
                ->orderBy('line_no', 'asc')
                ->get()
                ->toArray();

            $this->validator->validateStatus(['id' => $journal->id], false);
            $this->assertNoErrors($this->validator->validateHeader([
                'journal_date' => $journal->journal_date,
                'description' => $journal->description,
                'reference_type' => $journal->reference_type,
                'reference_id' => $journal->reference_id,
            ]));

            // 2. Validasi period.
            $period = $this->periodService->ensureOpen($journal->journal_date);

            // 3. Validasi detail dan account.
            $this->assertNoErrors($this->validator->validateDetails($details));

            // 4. Hitung total debit dan credit.
            $totalDebit = $this->validator->totalAmount($details, 'debit');
            $totalCredit = $this->validator->totalAmount($details, 'credit');

            // 5. Pastikan balance.
            $this->assertNoErrors($this->validator->validateTotals($details));

            // 6. Update status menjadi POSTED.
            $journal->accounting_period_id = $period->id;
            $journal->status = JournalService::STATUS_POSTED;

            // 7. Simpan posted_at dan posted_by.
            $journal->posted_at = date('Y-m-d H:i:s');
            $journal->posted_by = empty($userId) ? session('user_id') : $userId;
            $journal->save();

            // Journal Engine menjadi penghubung Account Mapping dengan General Ledger.
            $this->projectToGeneralLedger($journal, $details);

            // 8. Commit transaction (Dicek oleh DB::transaction di atas).
            return $journal;
        });
    }

    public function assertNoErrors($errors = [])
    {
        if (! empty($errors)) {
            throw new JournalValidationException(implode(' ', $errors), $errors);
        }
        return true;
    }

    /**
     * Menuliskan jurnal POSTED ke General Ledger, satu baris per detail jurnal.
     */
    public function projectToGeneralLedger($journal, $details = [])
    {
        $accountIds = [];
        foreach ($details as $detail) {
            $accountIds[] = $detail['account_id'] ?? null;
        }

        $accounts = DB::table('accounts')
            ->select(['id', 'code', 'name'])
            ->whereIn('id', array_filter(array_unique($accountIds)))
            ->get()
            ->keyBy('id');

        $userId = empty($journal->posted_by) ? 0 : $journal->posted_by;

        foreach ($details as $detail) {
            $debit = $this->validator->toAmount($detail['debit'] ?? 0);
            $credit = $this->validator->toAmount($detail['credit'] ?? 0);

            $dc = $debit > 0 ? 'D' : 'C';
            $amount = $debit > 0 ? $debit : $credit;
            if ($amount <= self::EPSILON) {
                continue;
            }

            $account = $accounts[$detail['account_id']] ?? null;

            $gl = new GeneralLedger();
            $gl->reference = $journal->journal_no;
            $gl->posting_date = $journal->journal_date;
            $gl->account_id = $detail['account_id'];
            $gl->account_name = empty($account) ? '' : $account->code . ' - ' . $account->name;
            $gl->dc = $dc;
            $gl->amount = $amount;
            $gl->description = $detail['description'] ?? $journal->description;
            $gl->created_by = $userId;
            $gl->currency = 1;
            $gl->save();
        }

        return true;
    }
}
