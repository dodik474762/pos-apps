<?php

namespace App\Services\Accounting;

use App\Models\Master\Accounts;
use App\Models\Transaction\JournalHeader;
use Illuminate\Support\Facades\DB;

class JournalValidator
{
    const MIN_DETAIL = 2;
    const EPSILON = 0.001;

    protected $periodService;

    public function __construct()
    {
        $this->periodService = new AccountingPeriodService();
    }

    /**
     * Menjalankan seluruh aturan validasi jurnal.
     * Mengembalikan array pesan error (kosong bila valid).
     */
    public function validate($header = [], $details = [], $isCreate = false)
    {
        $errors = [];
        $errors = array_merge($errors, $this->validateStatus($header, $isCreate));
        $errors = array_merge($errors, $this->validateHeader($header));
        $errors = array_merge($errors, $this->validatePeriod($header));
        $errors = array_merge($errors, $this->validateDetails($details));
        $errors = array_merge($errors, $this->validateTotals($details));

        return $errors;
    }

    /**
     * Melempar exception bila terdapat error validasi.
     */
    public function assert($header = [], $details = [], $isCreate = false)
    {
        $errors = $this->validate($header, $details, $isCreate);
        if (! empty($errors)) {
            throw new JournalValidationException(implode(' ', $errors), $errors);
        }
        return true;
    }

    /**
     * Aturan 8: POSTED tidak dapat diedit langsung.
     */
    public function validateStatus($header = [], $isCreate = false)
    {
        $errors = [];

        if ($isCreate) {
            return $errors;
        }

        $journal = $this->resolveJournal($header);
        if (empty($journal)) {
            return $errors;
        }

        if ($journal->status === JournalService::STATUS_POSTED) {
            $errors[] = 'Jurnal ' . $journal->journal_no . ' sudah berstatus POSTED dan bersifat immutable, tidak dapat diedit langsung.';
        } elseif ($journal->status === JournalService::STATUS_REVERSED) {
            $errors[] = 'Jurnal ' . $journal->journal_no . ' sudah berstatus REVERSED, tidak dapat diedit.';
        }

        return $errors;
    }

    /**
     * Validasi header, termasuk aturan 7 (jurnal otomatis wajib reference).
     */
    public function validateHeader($header = [])
    {
        $errors = [];

        $journalDate = $this->periodService->normalizeDate($header['journal_date'] ?? '');
        if (empty($journalDate)) {
            $errors[] = 'Tanggal jurnal wajib diisi dan harus valid.';
        }

        if (isset($header['description']) && mb_strlen((string) $header['description']) > 255) {
            $errors[] = 'Keterangan jurnal maksimal 255 karakter.';
        }

        $errors = array_merge($errors, $this->validateReference($header));

        return $errors;
    }

    /**
     * Aturan 7: jurnal otomatis harus memiliki reference_type dan reference_id.
     */
    public function validateReference($header = [], $isAutomatic = false)
    {
        $errors = [];

        $referenceType = trim((string) ($header['reference_type'] ?? ''));
        $referenceId = $header['reference_id'] ?? null;
        $hasReferenceId = ! empty($referenceId) && $referenceId !== '' && $referenceId !== '0';

        if ($isAutomatic && $referenceType === '') {
            $errors[] = 'Jurnal otomatis wajib memiliki reference_type.';
        }
        if ($isAutomatic && ! $hasReferenceId) {
            $errors[] = 'Jurnal otomatis wajib memiliki reference_id.';
        }
        if ($referenceType !== '' && ! $hasReferenceId) {
            $errors[] = 'reference_id wajib diisi jika reference_type diisi.';
        }
        if ($hasReferenceId && $referenceType === '') {
            $errors[] = 'reference_type wajib diisi jika reference_id diisi.';
        }
        if ($hasReferenceId && ! ctype_digit((string) $referenceId)) {
            $errors[] = 'reference_id harus berupa angka.';
        }

        return array_values(array_unique($errors));
    }

    /**
     * Aturan 6: journal date harus berada pada accounting period OPEN.
     */
    public function validatePeriod($header = [])
    {
        $journalDate = $this->periodService->normalizeDate($header['journal_date'] ?? '');
        if (empty($journalDate)) {
            return [];
        }

        try {
            $this->periodService->ensureOpen($journalDate);
        } catch (JournalValidationException $e) {
            return [$e->getMessage()];
        }

        return [];
    }

    /**
     * Aturan 2, 3, 4, 5: detail jurnal dan akun.
     */
    public function validateDetails($details = [])
    {
        $errors = [];

        $details = is_array($details) ? array_values($details) : [];
        if (empty($details)) {
            return ['Detail jurnal wajib diisi minimal ' . self::MIN_DETAIL . ' baris.'];
        }

        $normalized = [];
        foreach ($details as $index => $detail) {
            $normalized[] = array_merge([
                'line_no' => $index + 1,
                'account_id' => null,
                'debit' => 0,
                'credit' => 0,
            ], is_array($detail) ? $detail : (array) $detail);
        }
        $details = $normalized;

        $accountIds = [];
        foreach ($details as $detail) {
            $accountId = $detail['account_id'] ?? null;
            if (! empty($accountId)) {
                $accountIds[] = $accountId;
            }
        }

        $accounts = $this->getAccounts(array_values(array_unique($accountIds)));

        $validDetail = 0;
        foreach ($details as $index => $detail) {
            $lineNo = (int) ($detail['line_no'] ?? ($index + 1));
            $debit = $this->toAmount($detail['debit'] ?? 0);
            $credit = $this->toAmount($detail['credit'] ?? 0);
            $accountId = $detail['account_id'] ?? null;

            // Aturan 3: debit dan credit tidak boleh negatif.
            if ($debit < 0) {
                $errors[] = 'Baris ' . $lineNo . ': debit tidak boleh negatif.';
            }
            if ($credit < 0) {
                $errors[] = 'Baris ' . $lineNo . ': credit tidak boleh negatif.';
            }
            if ($debit < 0 || $credit < 0) {
                continue;
            }

            // Aturan 4: satu detail tidak boleh memiliki debit dan credit sekaligus > 0.
            if ($debit > self::EPSILON && $credit > self::EPSILON) {
                $errors[] = 'Baris ' . $lineNo . ': debit dan credit tidak boleh keduanya lebih dari 0.';
                continue;
            }

            if ($debit > self::EPSILON || $credit > self::EPSILON) {
                $validDetail++;
            }

            // Aturan 5: account harus aktif dan bukan header account.
            if (empty($accountId)) {
                $errors[] = 'Baris ' . $lineNo . ': akun wajib dipilih.';
                continue;
            }
            if (! isset($accounts[$accountId])) {
                $errors[] = 'Baris ' . $lineNo . ': akun tidak ditemukan atau sudah tidak aktif.';
                continue;
            }
            $account = $accounts[$accountId];
            if ((int) $account->is_header === 1) {
                $errors[] = 'Baris ' . $lineNo . ': akun ' . $account->code . ' adalah header account, tidak dapat dipilih sebagai detail jurnal.';
            }
            if ((int) $account->is_active !== 1) {
                $errors[] = 'Baris ' . $lineNo . ': akun ' . $account->code . ' tidak aktif.';
            }
        }

        // Aturan 2: minimal terdapat 2 detail valid.
        if ($validDetail < self::MIN_DETAIL) {
            $errors[] = 'Jurnal harus memiliki minimal ' . self::MIN_DETAIL . ' detail dengan nominal, saat ini ' . $validDetail . '.';
        }

        return $errors;
    }

    /**
     * Aturan 1: total debit harus sama dengan total credit.
     */
    public function validateTotals($details = [])
    {
        $errors = [];

        $totalDebit = $this->totalAmount($details, 'debit');
        $totalCredit = $this->totalAmount($details, 'credit');

        if (abs($totalDebit - $totalCredit) > self::EPSILON) {
            $errors[] = 'Jurnal tidak balance, total debit ' . number_format($totalDebit, 2) . ' tidak sama dengan total credit ' . number_format($totalCredit, 2) . '.';
        }

        if ($totalDebit <= self::EPSILON) {
            $errors[] = 'Total debit jurnal harus lebih dari 0.';
        }

        return $errors;
    }

    public function totalAmount($details = [], $field = 'debit')
    {
        $total = 0;
        foreach ((array) $details as $detail) {
            $total += $this->toAmount(is_array($detail) ? ($detail[$field] ?? 0) : ($detail->{$field} ?? 0));
        }
        return round($total, 2);
    }

    public function toAmount($value = 0)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        return round((float) $value, 2);
    }

    public function getAccounts($accountIds = [])
    {
        if (empty($accountIds)) {
            return [];
        }

        $rows = DB::table('accounts')
            ->select(['id', 'code', 'name', 'is_header', 'is_active'])
            ->whereIn('id', $accountIds)
            ->whereNull('deleted_at')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->id] = $row;
        }
        return $result;
    }

    public function resolveJournal($header = [])
    {
        $id = $header['id'] ?? null;
        if (empty($id)) {
            return null;
        }
        return JournalHeader::find($id);
    }
}
