<?php

namespace App\Services\Accounting;

use App\Models\Transaction\JournalHeader;
use Illuminate\Support\Facades\DB;

/**
 * Integrasi Journal Engine dengan modul Kas/Bank.
 *
 * Kas/Bank membukukan satu perpindahan uang dengan dua akun:
 *   direction = IN  (terima bunga bank, setoran modal, transfer masuk)
 *        Dr Bank/Cash / Cr akun lawan
 *   direction = OUT (bayar biaya admin bank, pengeluaran kas kecil, transfer keluar)
 *        Dr akun lawan / Cr Bank/Cash
 *
 * Berbeda dengan modul lain, kedua akun di sini dipilih LANGSUNG oleh user di
 * form, bukan hasil AccountMappingResolver, karena bank mana yang dipakai dan
 * akun lawan apa yang dipakai bersifat transaksional. Akun lawan boleh
 * memakai default dari mapping per jenis transaksi, tetapi default selalu
 * bisa dioverride di form.
 *
 * Jurnal yang dihasilkan:
 *   1. journal_headers  : 1 baris, reference ke CashBankTransaction
 *   2. journal_details  : tepat 2 baris, satu debit satu credit
 *
 * Nilai tidak berasal dari dokumen lain karena Kas/Bank tidak merujuk dokumen
 * asal. Nominal berasal dari modul, dan sudah termasuk PPN bila transaksi
 * memang mencantumkan PPN; pemisahan PPN tetap menjadi tanggung jawab modul,
 * sama seperti modul lain.
 *
 * Validasi khusus modul ini, di atas aturan standar Journal Engine:
 *   1. kedua akun tidak boleh sama
 *   2. amount harus lebih besar dari 0
 *   3. akun Bank/Cash harus benar-benar berada di bawah header CASH & BANK
 *   4. kedua akun harus aktif dan bukan header account (aturan 5 JournalValidator)
 */
class CashBankTransactionJournalService
{
    const REFERENCE_TYPE = 'CashBankTransaction';
    const TRANSACTION_TYPE = 'CASH_BANK_TRANSACTION';
    const EPSILON = 0.001;

    const DIRECTION_IN = 'IN';
    const DIRECTION_OUT = 'OUT';

    /**
     * Kode header account yang menaungi seluruh akun kas dan bank. Akun
     * Bank/Cash divalidasi dengan menelusuri parent_id sampai ke header ini,
     * sehingga tidak perlu daftar kode akun yang ditulis manual.
     */
    const CASH_BANK_PARENT_CODE = '1100';

    protected $journalService;
    protected $postingService;
    protected $validator;
    protected $resolver;

    public function __construct()
    {
        $this->journalService = new JournalService();
        $this->postingService = new JournalPostingService();
        $this->validator = new JournalValidator();
        $this->resolver = new AccountMappingResolver();
    }

    /**
     * Membuat dan mempost jurnal Kas/Bank.
     *
     * @param  array  $params  reference_id, bank_or_cash_account_id,
     *                         contra_account_id, amount, direction,
     *                         opsional default_contra_role, journal_date,
     *                         description
     * @param  int|null  $userId
     * @return \App\Models\Transaction\JournalHeader
     *
     * @throws JournalValidationException
     */
    public function post($params, $userId = null)
    {
        $params = is_array($params) ? $params : [];

        $referenceId = $params['reference_id'] ?? null;
        if (empty($referenceId)) {
            throw new JournalValidationException('reference_id Kas/Bank wajib diisi.');
        }

        $direction = $this->resolveDirection($params['direction'] ?? null);

        // Idempoten: transaksi yang sudah punya jurnal aktif tidak digandakan.
        $existing = $this->getActiveJournal($referenceId);
        if (! empty($existing)) {
            // Jurnal tertinggal DRAFT, misalnya posting sebelumnya gagal, lalu dicoba lagi.
            if ($existing->status === JournalService::STATUS_DRAFT) {
                return $this->postingService->post($existing->id, $userId);
            }

            return $existing;
        }

        $amount = $this->resolveAmount($params['amount'] ?? null);
        $bankAccountId = $this->resolveBankAccount($params['bank_or_cash_account_id'] ?? null);
        $contraAccountId = $this->resolveContraAccount(
            $params['contra_account_id'] ?? null,
            $params['default_contra_role'] ?? null
        );

        if ($bankAccountId === $contraAccountId) {
            throw new JournalValidationException(
                'Akun Bank/Cash dan akun lawan tidak boleh sama karena kedua sisi akan saling meniadakan.'
            );
        }

        $details = $this->buildDetails($direction, $bankAccountId, $contraAccountId, $amount, $params);

        $header = [
            'journal_date' => $this->resolveJournalDate($params['journal_date'] ?? null),
            'reference_type' => self::REFERENCE_TYPE,
            'reference_id' => $referenceId,
            'description' => $this->buildDescription($params, $direction),
            'details' => $details,
        ];

        $journal = $this->journalService->create($header, true);

        return $this->postingService->post($journal->id, $userId);
    }

    /**
     * Jurnal terakhir yang merujuk transaksi Kas/Bank, untuk validasi hapus.
     */
    public function getJournalForCashBankTransaction($referenceId)
    {
        if (empty($referenceId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $referenceId)
            // Jurnal reversal memakai reference yang sama, sehingga yang dicari di
            // sini adalah jurnal asal transaksi, bukan jurnal pembalikannya.
            ->whereNull('reversal_of_id')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Jurnal yang masih aktif, yaitu DRAFT atau POSTED. Jurnal REVERSED tidak
     * lagi mengunci transaksi.
     */
    public function getActiveJournal($referenceId)
    {
        if (empty($referenceId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $referenceId)
            ->whereNull('reversal_of_id')
            ->whereIn('status', [JournalService::STATUS_DRAFT, JournalService::STATUS_POSTED])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Dua baris jurnal sesuai arah transaksi.
     */
    protected function buildDetails($direction, $bankAccountId, $contraAccountId, $amount, $params)
    {
        $description = $this->buildDescription($params, $direction);
        $bankLabel = 'Kas/Bank ' . $description;
        $contraLabel = $this->buildContraLabel($params, $direction);

        $debitAccount = $direction === self::DIRECTION_IN ? $bankAccountId : $contraAccountId;
        $debitLabel = $direction === self::DIRECTION_IN ? $bankLabel : $contraLabel;
        $creditAccount = $direction === self::DIRECTION_IN ? $contraAccountId : $bankAccountId;
        $creditLabel = $direction === self::DIRECTION_IN ? $contraLabel : $bankLabel;

        return [
            [
                'line_no' => 1,
                'account_id' => $debitAccount,
                'description' => $debitLabel,
                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'line_no' => 2,
                'account_id' => $creditAccount,
                'description' => $creditLabel,
                'debit' => 0,
                'credit' => $amount,
            ],
        ];
    }

    /**
     * Arah transaksi harus dikenal, karena menentukan sisi debit dan kredit.
     */
    protected function resolveDirection($direction)
    {
        $direction = strtoupper(trim((string) $direction));

        if ($direction === self::DIRECTION_IN) {
            return self::DIRECTION_IN;
        }

        if ($direction === self::DIRECTION_OUT) {
            return self::DIRECTION_OUT;
        }

        throw new JournalValidationException(
            'direction Kas/Bank tidak dikenal (' . ($direction === '' ? 'kosong' : $direction)
            . '). Expected ' . self::DIRECTION_IN . ' atau ' . self::DIRECTION_OUT
            . ', jurnal tidak dapat dibuat.'
        );
    }

    protected function resolveAmount($amount)
    {
        $amount = $this->validator->toAmount($amount);

        if ($amount <= self::EPSILON) {
            throw new JournalValidationException(
                'Nominal Kas/Bank harus lebih besar dari 0, jurnal tidak dapat dibuat.'
            );
        }

        return $amount;
    }

    protected function resolveJournalDate($journalDate)
    {
        if (empty($journalDate)) {
            return now()->format('Y-m-d');
        }

        return $journalDate;
    }

    /**
     * Akun kas/bank dipilih user, tidak lewat mapping. Akun wajib ada, bukan
     * header, aktif, dan benar-benar berada di bawah header CASH & BANK.
     */
    protected function resolveBankAccount($accountId)
    {
        if (empty($accountId)) {
            throw new JournalValidationException(
                'Akun Bank/Cash wajib dipilih di form Kas/Bank, jurnal tidak dapat dibuat.'
            );
        }

        $account = $this->assertUsableAccount($accountId, 'pilihan Akun Bank/Cash di form Kas/Bank');

        if (! $this->isCashBankAccount((int) $accountId)) {
            throw new JournalValidationException(
                'Akun ' . $account->code . ' (' . $account->name . ') bukan akun Kas/Bank. '
                . 'Akun Bank/Cash harus berada di bawah header ' . self::CASH_BANK_PARENT_CODE . '.'
            );
        }

        return (int) $account->id;
    }

    /**
     * Akun lawan dipilih user. Bila tidak dipilih, boleh memakai default dari
     * account_mapping_rules sesuai jenis transaksi, dan default itu tetap bisa
     * dioverride karena parameter ini hanya dipakai bila form tidak mengirim
     * akun lawan.
     */
    protected function resolveContraAccount($accountId, $defaultRole)
    {
        if (! empty($accountId)) {
            return (int) $this->assertUsableAccount(
                $accountId,
                'pilihan Akun Lawan di form Kas/Bank'
            )->id;
        }

        if (! empty($defaultRole)) {
            return (int) $this->assertUsableAccount(
                $this->resolver->resolve(self::TRANSACTION_TYPE, $defaultRole),
                false
            )->id;
        }

        throw new JournalValidationException(
            'Akun lawan wajib dipilih di form Kas/Bank. Bila jenis transaksi ini selalu'
            . ' memakai akun yang sama, tambahkan rule di Master > Account Mapping Rules'
            . ' (Transaction Type ' . self::TRANSACTION_TYPE . ', Role untuk jenis transaksi itu)'
            . ' supaya bisa dipakai sebagai default.'
        );
    }

    /**
     * Akun wajib ada, bukan header account, dan aktif. Aturan 5 Journal Engine.
     */
    protected function assertUsableAccount($accountId, $fromUserSelection)
    {
        $account = DB::table('accounts')
            ->select(['id', 'code', 'name', 'is_header', 'is_active', 'parent_id'])
            ->where('id', $accountId)
            ->whereNull('deleted_at')
            ->first();

        $source = $fromUserSelection ? 'form Kas/Bank' : 'Account Mapping Rules';

        if (empty($account)) {
            throw new JournalValidationException(
                'Akun dari ' . $source . ' tidak ditemukan atau sudah dihapus.'
            );
        }
        if ((int) $account->is_header === 1) {
            throw new JournalValidationException(
                'Akun ' . $account->code . ' dari ' . $source . ' adalah header account.'
            );
        }
        if ((int) $account->is_active !== 1) {
            throw new JournalValidationException(
                'Akun ' . $account->code . ' dari ' . $source . ' tidak aktif.'
            );
        }

        return $account;
    }

    /**
     * Menelusuri parent_id naik sampai ketemu header CASH & BANK. Akun di bawah
     * header tersebut dianggap akun kas/bank yang sah.
     */
    protected function isCashBankAccount($accountId)
    {
        $parentId = DB::table('accounts')
            ->where('id', $accountId)
            ->whereNull('deleted_at')
            ->value('parent_id');

        $guard = 0;
        while (! empty($parentId) && $guard++ < 5) {
            $parent = DB::table('accounts')
                ->select(['id', 'code', 'is_header'])
                ->where('id', $parentId)
                ->whereNull('deleted_at')
                ->first();

            if (empty($parent)) {
                return false;
            }

            if ((int) $parent->is_header === 1) {
                return $parent->code === self::CASH_BANK_PARENT_CODE;
            }

            $parentId = $parent->parent_id;
        }

        return false;
    }

    protected function buildDescription($params, $direction)
    {
        $description = $params['description'] ?? null;

        if (! empty($description)) {
            return $description;
        }

        $label = $direction === self::DIRECTION_IN ? 'Penerimaan kas' : 'Pengeluaran kas';

        return $label . ' #' . ($params['reference_id'] ?? '');
    }

    protected function buildContraLabel($params, $direction)
    {
        $label = $params['contra_label'] ?? null;

        if (! empty($label)) {
            return $label;
        }

        return $direction === self::DIRECTION_IN ? 'Pendapatan kas masuk' : 'Beban kas keluar';
    }
}
