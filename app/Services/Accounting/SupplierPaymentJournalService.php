<?php

namespace App\Services\Accounting;

use App\Models\Transaction\JournalHeader;
use App\Models\Transaction\VendorBillHeader;
use Illuminate\Support\Facades\DB;

/**
 * Integrasi Journal Engine dengan Vendor Payment (Pembayaran Supplier).
 *
 * Jurnal yang dihasilkan:
 *   1. journal_headers  : 1 baris, reference ke Vendor Payment
 *   2. journal_details  : baris 1 debit AP dari account mapping
 *                         baris 2 credit Bank/Cash
 *
 * PENTING: kolom account_id pada vendor_payment_header menunjuk ke tabel legacy
 * `coa`, bukan ke tabel `accounts` milik Journal Engine. Kedua tabel itu tidak
 * selaras, id dan kode akunnya berbeda, sehingga account_id tidak boleh dipakai
 * sebagai akun jurnal. Contoh konsekuensinya, coa.id = 4 adalah "1120 Bank
 * Mandiri" sementara accounts.id = 4 adalah "1102 BANK BCA".
 *
 * Karena itu akun Bank/Cash untuk jurnal diambil dari pilihan user pada modal
 * posting, dengan fallback ke rule SUPPLIER_PAYMENT / BANK. Akun AP tetap dari
 * rule SUPPLIER_PAYMENT / AP.
 *
 * submit() dan postingGL() legacy tetap berjalan berdampingan. Jurnal ini
 * hanya ditambah sebagai jurnal double entry dan tidak mengubah logic lama.
 */
class SupplierPaymentJournalService
{
    const REFERENCE_TYPE = 'SupplierPayment';
    const TRANSACTION_TYPE = 'SUPPLIER_PAYMENT';
    const ROLE_AP = 'AP';
    const ROLE_BANK = 'BANK';
    const EPSILON = 0.001;
    const TOLERANCE = 0.01;

    /**
     * Hanya vendor payment berstatus draft yang boleh dijurnalkan. Status posted
     * berarti jurnal sudah dibuat, cancelled berarti transaksi dibatalkan.
     */
    const STATUS_POSTABLE = ['draft'];

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
     * Membuat dan mempost jurnal pembayaran ke supplier.
     *   Dr AP (dari mapping SUPPLIER_PAYMENT / AP)
     *   Cr Bank/Cash (pilihan user di modal posting)
     *
     * Nominal memakai total_payment pada header. Total itu divalidasi terhadap
     * penjumlahan amount_paid di detail supaya hutang yang dihapus selalu
     * sebesar yang benar-benar dialokasikan ke invoice.
     *
     * @param  int  $paymentId  id vendor_payment_header
     * @param  int|null  $bankAccountId  akun accounts.id pilihan user
     * @param  int|null  $userId
     * @return \App\Models\Transaction\JournalHeader
     *
     * @throws JournalValidationException
     */
    public function postFromVendorPayment($paymentId, $bankAccountId = null, $userId = null)
    {
        $payment = VendorBillHeader::with('details')->find($paymentId);
        if (empty($payment) || ! empty($payment->deleted)) {
            throw new JournalValidationException('Vendor payment tidak ditemukan.');
        }

        $this->assertPostableStatus($payment);

        // Idempotent: pembayaran yang sudah memiliki jurnal aktif tidak digandakan.
        $existing = $this->getActiveJournal($payment->id);
        if (! empty($existing)) {
            // Jurnal tertinggal DRAFT, misalnya posting sebelumnya gagal, lalu diposting ulang.
            if ($existing->status === JournalService::STATUS_DRAFT) {
                return $this->postingService->post($existing->id, $userId);
            }

            return $existing;
        }

        $amount = $this->validator->toAmount($payment->total_payment);
        if ($amount <= self::EPSILON) {
            throw new JournalValidationException(
                'Total pembayaran ' . $payment->payment_number
                . ' bernilai 0, jurnal tidak dapat dibuat.'
            );
        }

        $this->assertAllocations($payment, $amount);

        $accountAp = $this->resolver->resolve(self::TRANSACTION_TYPE, self::ROLE_AP);
        $accountBank = $this->resolveBankAccount($bankAccountId);

        $details = [
            [
                'line_no' => 1,
                'account_id' => $accountAp,
                'description' => 'Hutang usaha ' . $payment->payment_number,
                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'line_no' => 2,
                'account_id' => $accountBank,
                'description' => 'Pembayaran supplier ' . $payment->payment_number,
                'debit' => 0,
                'credit' => $amount,
            ],
        ];

        $header = [
            'journal_date' => $payment->payment_date,
            'reference_type' => self::REFERENCE_TYPE,
            'reference_id' => $payment->id,
            'description' => 'Vendor Payment ' . $payment->payment_number,
            'details' => $details,
        ];

        $journal = $this->journalService->create($header, true);

        return $this->postingService->post($journal->id, $userId);
    }

    /**
     * Jurnal terakhir yang merujuk vendor payment, dipakai untuk validasi hapus.
     * Mengembalikan null apabila pembayaran belum pernah dijurnalkan.
     */
    public function getJournalForVendorPayment($paymentId)
    {
        if (empty($paymentId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $paymentId)
            // Jurnal reversal memakai reference yang sama, sehingga yang dicari di sini
            // adalah jurnal asal transaksi, bukan jurnal pembalikannya.
            ->whereNull('reversal_of_id')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Jurnal yang masih aktif, yaitu DRAFT atau POSTED. Jurnal REVERSED tidak
     * dihitung karena pengaruhnya di general ledger sudah dinetralkan.
     */
    public function getActiveJournal($paymentId)
    {
        if (empty($paymentId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $paymentId)
            // Jurnal reversal mewarisi reference_type dan reference_id, jadi harus dikecualikan.
            // Kalau tidak, reversal akan dianggap jurnal aktif sehingga hapus tetap terkunci
            // dan posting ulang justru mengembalikan jurnal reversal.
            ->whereNull('reversal_of_id')
            ->whereIn('status', [JournalService::STATUS_DRAFT, JournalService::STATUS_POSTED])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Hanya vendor payment berstatus draft yang boleh dijurnalkan.
     */
    protected function assertPostableStatus($payment)
    {
        if (! in_array($payment->status, self::STATUS_POSTABLE, true)) {
            throw new JournalValidationException(
                'Vendor payment ' . $payment->payment_number . ' berstatus ' . $payment->status
                . '. Hanya vendor payment berstatus draft yang dapat dijurnalkan.'
            );
        }
    }

    /**
     * Detail alokasi wajib ada, invoice yang dirujuk harus masih valid, dan
     * jumlah amount_paid harus sama dengan total_payment pada header.
     */
    protected function assertAllocations($payment, $amount)
    {
        $details = $payment->details;
        if ($details->isEmpty()) {
            throw new JournalValidationException(
                'Detail vendor payment ' . $payment->payment_number
                . ' tidak ada, jurnal tidak dapat dibuat.'
            );
        }

        $invoiceIds = $details->pluck('purchase_invoice_id')->unique()->values();
        $validInvoices = DB::table('purchase_invoice_header')
            ->whereIn('id', $invoiceIds)
            ->whereNull('deleted')
            ->pluck('id');

        $missing = $invoiceIds->diff($validInvoices);
        if ($missing->isNotEmpty()) {
            throw new JournalValidationException(
                'Invoice pada vendor payment ' . $payment->payment_number
                . ' tidak ditemukan atau sudah dihapus: #' . $missing->implode(', #')
                . '. Jurnal tidak dapat dibuat.'
            );
        }

        $allocated = $this->validator->toAmount($details->sum(function ($detail) {
            return $this->validator->toAmount($detail->amount_paid);
        }));

        if (abs($allocated - $amount) > self::TOLERANCE) {
            throw new JournalValidationException(
                'Total detail vendor payment ' . $payment->payment_number . ' sebesar '
                . $this->formatAmount($allocated) . ' tidak sama dengan total header sebesar '
                . $this->formatAmount($amount) . ', jurnal tidak dapat dibuat.'
            );
        }
    }

    /**
     * Akun Bank/Cash dari pilihan user pada modal posting.
     *
     * Nilai di sini adalah id tabel accounts, BUKAN account_id milik
     * vendor_payment_header yang menunjuk ke tabel coa legacy.
     *
     * Bila tidak ada pilihan, falls back ke rule SUPPLIER_PAYMENT / BANK.
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

        $source = $fromUserSelection
            ? 'pilihan Akun Bank/Cash di modal posting jurnal'
            : 'Account Mapping Rules';

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

    protected function formatAmount($value)
    {
        return number_format((float) $value, 2, ',', '.');
    }
}
