<?php

namespace App\Services\Accounting;

use App\Models\Transaction\JournalHeader;
use App\Models\Transaction\SalesPaymentHeader;
use Illuminate\Support\Facades\DB;

/**
 * Integrasi Journal Engine dengan Customer Payment (Sales Payment).
 *
 * Jurnal yang dihasilkan:
 *   1. journal_headers  : 1 baris, reference ke sales_payment_header
 *   2. journal_details  : baris 1 debit akun Bank/Cash pilihan user,
 *                         baris 2 credit akun AR hasil account mapping
 *
 * Akun Bank/Cash diambil langsung dari pilihan user di form pembayaran, bukan
 * dari account_mapping_rules, karena satu perusahaan dapat memiliki banyak akun
 * bank dan penentuannya bersifat transaksional. Akun AR tetap dari mapping rule
 * CUSTOMER_PAYMENT / AR. Bila AR dipisah per kategori customer, resolver dapat
 * dikembangkan lebih lanjut dengan menambahkan kolom category pada rules.
 */
class CustomerPaymentJournalService
{
    const REFERENCE_TYPE = 'CustomerPayment';
    const TRANSACTION_TYPE = 'CUSTOMER_PAYMENT';
    const ROLE_AR = 'AR';
    const ROLE_BANK = 'BANK';

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
     * Membuat dan memposting jurnal penerimaan pembayaran customer.
     *   Dr Bank/Cash (pilihan user) / Cr AR (hasil mapping) sebesar net_amount
     *
     * Amount memakai net_amount, yaitu jumlah yang benar-benar diterima.
     * Overpayment maupun partial payment diterima apa adanya; alokasi ke invoice
     * mana yang dilunasi dan berapa sisa outstanding-nya tetap menjadi tanggung
     * jawab subledger piutang, bukan Journal Engine.
     *
     * @param  int  $paymentId  id sales_payment_header
     * @param  int|null  $bankAccountId  akun Bank/Cash pilihan user, null = pakai rule BANK
     * @param  int|null  $userId
     * @return \App\Models\Transaction\JournalHeader
     *
     * @throws JournalValidationException
     */
    public function postFromCustomerPayment($paymentId, $bankAccountId = null, $userId = null)
    {
        $payment = SalesPaymentHeader::find($paymentId);
        if (empty($payment)) {
            throw new JournalValidationException('Customer payment tidak ditemukan.');
        }

        // Idempotent: pembayaran yang sudah memiliki jurnal aktif tidak digandakan.
        $existing = $this->getActiveJournal($payment->id);
        if (! empty($existing)) {
            return $existing;
        }

        $amount = $this->validator->toAmount($payment->net_amount);
        if ($amount <= JournalValidator::EPSILON) {
            throw new JournalValidationException(
                'Jumlah diterima pada pembayaran ' . $payment->payment_code
                . ' bernilai 0, jurnal tidak dapat dibuat.'
            );
        }

        $accountBank = $this->resolveBankAccount($bankAccountId);
        $accountAr = $this->resolver->resolve(self::TRANSACTION_TYPE, self::ROLE_AR);

        $details = [
            [
                'line_no' => 1,
                'account_id' => $accountBank,
                'description' => 'Penerimaan pembayaran ' . $payment->payment_code,
                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'line_no' => 2,
                'account_id' => $accountAr,
                'description' => 'Piutang pelanggan ' . $payment->payment_code,
                'debit' => 0,
                'credit' => $amount,
            ],
        ];

        $header = [
            'journal_date' => $payment->payment_date,
            'reference_type' => self::REFERENCE_TYPE,
            'reference_id' => $payment->id,
            'description' => 'Customer Payment ' . $payment->payment_code,
            'details' => $details,
        ];

        $journal = $this->journalService->create($header, true);

        return $this->postingService->post($journal->id, $userId);
    }

    /**
     * Jurnal yang masih aktif untuk pembayaran ini, yaitu DRAFT atau POSTED.
     * Jurnal REVERSED tidak dihitung karena pengaruhnya sudah dinetralkan.
     */
    public function getActiveJournal($paymentId)
    {
        if (empty($paymentId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $paymentId)
            ->whereIn('status', [JournalService::STATUS_DRAFT, JournalService::STATUS_POSTED])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Akun Bank/Cash dari pilihan user di form pembayaran.
     * Bila tidak ada pilihan, falls back ke rule CUSTOMER_PAYMENT / BANK supaya
     * pembayaran yang diconfirm tanpa membuka form tetap mendapat jurnal.
     */
    protected function resolveBankAccount($bankAccountId)
    {
        if (! empty($bankAccountId) && (int) $bankAccountId > 0) {
            return $this->assertUsableAccount((int) $bankAccountId, true);
        }

        return $this->assertUsableAccount(
            $this->resolver->resolve(self::TRANSACTION_TYPE, self::ROLE_BANK),
            false
        );
    }

    /**
     * Akun wajib ada, bukan header, dan aktif.
     */
    protected function assertUsableAccount($accountId, $fromUserSelection)
    {
        $account = DB::table('accounts')
            ->select(['id', 'code', 'name', 'is_header', 'is_active'])
            ->where('id', $accountId)
            ->whereNull('deleted_at')
            ->first();

        $source = $fromUserSelection ? 'pilihan Akun Bank/Cash di form pembayaran' : 'Account Mapping Rules';

        if (empty($account)) {
            throw new JournalValidationException(
                'Akun Bank/Cash dari ' . $source . ' tidak ditemukan atau sudah dihapus.'
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

        return (int) $account->id;
    }
}
