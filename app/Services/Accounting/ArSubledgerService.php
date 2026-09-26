<?php

namespace App\Services\Accounting;

use Illuminate\Support\Facades\DB;

/**
 * AR Subledger.
 *
 * Subledger ini adalah rincian (detail) dari akun kontrol AR, bukan sumber
 * data terpisah. Semua nominal subledger dibaca dari jurnal yang sudah
 * POSTED, bukan dihitung ulang dari dokumen, supaya angka subledger pasti
 * sama dengan yang masuk ke general_ledgers. Menghitung ulang berisiko
 * menghasilkan selisih senyap antara subledger dan GL.
 *
 * Alur:
 *   1. Modul memanggil syncFromJournal() tepat setelah jurnal di-post.
 *      Record hanya terbentuk bila jurnal sudah berstatus POSTED.
 *   2. Invoice membentuk tagihan AR dengan outstanding penuh.
 *   3. Payment dan Credit Note langsung dialokasikan ke invoice terbuka
 *      secara FIFO (due_date, lalu invoice_date, lalu id).
 *   4. Saat jurnal di-reversal, alokasi dibatalkan dan saldo invoice
 *      dikembalikan seperti semula.
 *
 * Catatan angka:
 *   - ar_invoices memakai nominal faktur termasuk PPN, karena itu yang
 *     masuk ke Dr AR di jurnal.
 *   - ar_credit_notes memakai bagian pendapatan saja, tanpa PPN, karena
 *     itu yang masuk ke Cr AR di jurnal.
 *
 * Kolom paid_amount pada ar_invoices berarti "sudah tertutup", bukan
 * khusus pembayaran. Credit note juga menambahnya, karena keduanya
 * mengurangi outstanding.
 */
class ArSubledgerService
{
    const EPSILON = 0.001;

    /**
     * reference_type jurnal yang membentuk subledger AR.
     */
    const REFERENCE_INVOICE = 'SALES_INVOICE';
    const REFERENCE_PAYMENT = 'CustomerPayment';
    const REFERENCE_CREDIT_NOTE = 'SalesReturn';

    /**
     * transaction_type untuk resolve akun kontrol AR.
     */
    const TRANSACTION_INVOICE = 'SALES';
    const TRANSACTION_PAYMENT = 'CUSTOMER_PAYMENT';
    const TRANSACTION_CREDIT_NOTE = 'SALES_RETURN';

    const ROLE_AR = 'AR';

    /**
     * source_type pada ar_allocations.
     */
    const SOURCE_PAYMENT = 'PAYMENT';
    const SOURCE_CREDIT_NOTE = 'CREDIT_NOTE';

    /**
     * Status ar_invoices.
     */
    const INVOICE_OPEN = 'OPEN';
    const INVOICE_PARTIALLY_PAID = 'PARTIALLY_PAID';
    const INVOICE_PAID = 'PAID';
    const INVOICE_REVERSED = 'REVERSED';

    /**
     * Status ar_payments dan ar_credit_notes.
     */
    const SOURCE_UNALLOCATED = 'UNALLOCATED';
    const SOURCE_PARTIALLY_ALLOCATED = 'PARTIALLY_ALLOCATED';
    const SOURCE_ALLOCATED = 'ALLOCATED';
    const SOURCE_REVERSED = 'REVERSED';

    /**
     * Status ar_allocations.
     */
    const ALLOCATION_ACTIVE = 'ACTIVE';
    const ALLOCATION_REVERSED = 'REVERSED';

    protected $resolver;
    protected $validator;

    public function __construct()
    {
        $this->resolver = new AccountMappingResolver();
        $this->validator = new JournalValidator();
    }

    /**
     * Titik masuk tunggal. Dipanggil modul setelah jurnal di-post.
     *
     * Jurnal yang belum POSTED diabaikan, sehingga subledger tidak pernah
     * terbentuk lebih dulu daripada jurnal.
     *
     * @param  \App\Models\Transaction\JournalHeader  $journal
     * @return bool  true bila subledger formed atau di-reverse
     */
    public function syncFromJournal($journal)
    {
        if (empty($journal) || empty($journal->id)) {
            return false;
        }

        // Jurnal reversal meniru reference_type dokumen asal. Subledger untuk
        // dokumen itu sudah ada dan harus dibatalkan, bukan dibuat ulang.
        if (! empty($journal->reversal_of_id)) {
            return $this->reverseFromJournal($journal);
        }

        if ($journal->status !== JournalService::STATUS_POSTED) {
            return false;
        }

        $referenceType = (string) $journal->reference_type;

        if ($referenceType === self::REFERENCE_INVOICE) {
            $this->formInvoice($journal);

            return true;
        }

        if ($referenceType === self::REFERENCE_PAYMENT) {
            $this->formPayment($journal);

            return true;
        }

        if ($referenceType === self::REFERENCE_CREDIT_NOTE) {
            $this->formCreditNote($journal);

            return true;
        }

        return false;
    }

    /**
     * Membatalkan subledger saat jurnal di-reversal.
     *
     * @param  \App\Models\Transaction\JournalHeader  $journal  jurnal reversal
     * @return bool
     */
    public function reverseFromJournal($journal)
    {
        $referenceType = (string) $journal->reference_type;
        $referenceId = (int) $journal->reference_id;

        if ($referenceId <= 0) {
            return false;
        }

        if ($referenceType === self::REFERENCE_INVOICE) {
            return $this->reverseInvoice($referenceId);
        }

        if ($referenceType === self::REFERENCE_PAYMENT) {
            return $this->reverseSource(self::SOURCE_PAYMENT, $referenceId);
        }

        if ($referenceType === self::REFERENCE_CREDIT_NOTE) {
            return $this->reverseSource(self::SOURCE_CREDIT_NOTE, $referenceId);
        }

        return false;
    }

    /**
     * Invoice membentuk tagihan AR.
     */
    protected function formInvoice($journal)
    {
        $invoiceId = (int) $journal->reference_id;

        $invoice = DB::table('sales_invoice_header')->where('id', $invoiceId)->first();

        if (empty($invoice)) {
            throw new JournalValidationException(
                'Subledger AR tidak dapat dibentuk karena sales invoice #' . $invoiceId . ' tidak ditemukan.'
            );
        }
        if ((int) $invoice->deleted === 1) {
            throw new JournalValidationException(
                'Subledger AR tidak dapat dibentuk karena sales invoice ' . $invoice->invoice_number . ' sudah dihapus.'
            );
        }

        $amount = $this->arAmountFromJournal($journal, self::TRANSACTION_INVOICE, 'debit');

        $existing = DB::table('ar_invoices')->where('sales_invoice_id', $invoiceId)->first();

        $dueDate = empty($invoice->due_date) ? $invoice->invoice_date : $invoice->due_date;

        $attributes = [
            'customer_id' => (int) $invoice->customer_id,
            'invoice_no' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date,
            'due_date' => $dueDate,
            'invoice_amount' => $amount,
            'updated_at' => now(),
        ];

        if (empty($existing)) {
            DB::table('ar_invoices')->insert($attributes + [
                'sales_invoice_id' => $invoiceId,
                'paid_amount' => 0,
                'outstanding_amount' => $amount,
                'status' => self::INVOICE_OPEN,
                'created_at' => now(),
            ]);

            return;
        }

        // Dokumen bisa di-post ulang setelah reversal, jadi outstanding
        // disetel ulang ke nominal penuh. Alokasi yang dibatalkan saat
        // reversal sudah mengembalikan paid_amount ke 0.
        $paid = $this->toAmount($existing->paid_amount);
        $outstanding = $amount - $paid;

        DB::table('ar_invoices')->where('id', $existing->id)->update($attributes + [
            'outstanding_amount' => max(0, $outstanding),
            'status' => $this->resolveInvoiceStatus($paid, max(0, $outstanding)),
        ]);
    }

    /**
     * Payment membentuk penerimaan kas dan langsung dialokasikan ke invoice.
     */
    protected function formPayment($journal)
    {
        $paymentId = (int) $journal->reference_id;

        $payment = DB::table('sales_payment_header')->where('id', $paymentId)->first();

        if (empty($payment)) {
            throw new JournalValidationException(
                'Subledger AR tidak dapat dibentuk karena payment customer #' . $paymentId . ' tidak ditemukan.'
            );
        }
        if ((int) $payment->deleted === 1) {
            throw new JournalValidationException(
                'Subledger AR tidak dapat dibentuk karena payment ' . $payment->payment_code . ' sudah dihapus.'
            );
        }

        $amount = $this->arAmountFromJournal($journal, self::TRANSACTION_PAYMENT, 'credit');

        $record = $this->upsertSourceRecord(
            'ar_payments',
            'customer_payment_id',
            $paymentId,
            [
                'customer_id' => (int) $payment->customer_id,
                'payment_date' => $payment->payment_date,
                'amount' => $amount,
            ]
        );

        $this->allocate(
            self::SOURCE_PAYMENT,
            $record['source_id'],
            $record['customer_id'],
            $record['amount'],
            $this->toAmount($record['allocated_amount']),
            $payment->payment_date
        );
    }

    /**
     * Sales Return membentuk kredit nota dan langsung dialokasikan ke invoice.
     */
    protected function formCreditNote($journal)
    {
        $returnId = (int) $journal->reference_id;

        $return = DB::table('sales_return')->where('id', $returnId)->first();

        if (empty($return)) {
            throw new JournalValidationException(
                'Subledger AR tidak dapat dibentuk karena sales return #' . $returnId . ' tidak ditemukan.'
            );
        }
        if ((int) $return->deleted === 1) {
            throw new JournalValidationException(
                'Subledger AR tidak dapat dibentuk karena sales return ' . $return->return_number . ' sudah dihapus.'
            );
        }

        $amount = $this->arAmountFromJournal($journal, self::TRANSACTION_CREDIT_NOTE, 'credit');

        $record = $this->upsertSourceRecord(
            'ar_credit_notes',
            'sales_return_id',
            $returnId,
            [
                'customer_id' => (int) $return->customer_id,
                'credit_note_date' => $return->return_date,
                'amount' => $amount,
            ]
        );

        $this->allocate(
            self::SOURCE_CREDIT_NOTE,
            $record['source_id'],
            $record['customer_id'],
            $record['amount'],
            $this->toAmount($record['allocated_amount']),
            $return->return_date
        );
    }

    /**
     * Menyiapkan baris ar_payments atau ar_credit_notes.
     *
     * Alokasi lama yang masih aktif dibuang lebih dulu, supaya post ulang
     * tidak menghasilkan alokasi ganda untuk sumber yang sama.
     *
     * @return array  source_id, customer_id, amount, allocated_amount
     */
    protected function upsertSourceRecord($table, $foreignKey, $foreignId, $attributes)
    {
        $existing = DB::table($table)->where($foreignKey, $foreignId)->first();

        $amount = $this->toAmount($attributes['amount']);
        $allocated = 0;

        if (! empty($existing)) {
            $this->releaseAllocationsFor(
                $this->sourceTypeFor($table),
                (int) $existing->id
            );
            $allocated = 0;

            DB::table($table)->where('id', $existing->id)->update($attributes + [
                'allocated_amount' => 0,
                'status' => self::SOURCE_UNALLOCATED,
                'updated_at' => now(),
            ]);

            $sourceId = (int) $existing->id;
        } else {
            $sourceId = (int) DB::table($table)->insertGetId($attributes + [
                $foreignKey => $foreignId,
                'allocated_amount' => 0,
                'status' => self::SOURCE_UNALLOCATED,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'source_id' => $sourceId,
            'customer_id' => (int) $attributes['customer_id'],
            'amount' => $amount,
            'allocated_amount' => $allocated,
        ];
    }

    /**
     * Alokasi FIFO ke invoice yang masih terbuka.
     *
     * @param  string  $sourceType  PAYMENT atau CREDIT_NOTE
     * @param  int  $sourceId  id baris ar_payments / ar_credit_notes
     * @param  int  $customerId
     * @param  float  $amount  nominal sumber
     * @param  float  $alreadyAllocated  alokasi yang sudah tercatat
     * @param  string  $allocationDate
     * @return float  total alokasi baru
     */
    protected function allocate($sourceType, $sourceId, $customerId, $amount, $alreadyAllocated = 0, $allocationDate = null)
    {
        $allocatedBefore = $this->toAmount($alreadyAllocated);
        $available = $amount - $allocatedBefore;

        if ($available <= self::EPSILON) {
            return 0;
        }

        $invoices = DB::table('ar_invoices')
            ->where('customer_id', $customerId)
            ->whereIn('status', [self::INVOICE_OPEN, self::INVOICE_PARTIALLY_PAID])
            ->where('outstanding_amount', '>', self::EPSILON)
            ->whereNull('deleted_at')
            ->orderBy('due_date', 'asc')
            ->orderBy('invoice_date', 'asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get();

        $allocationDate = empty($allocationDate) ? now()->format('Y-m-d') : $allocationDate;
        $total = 0;

        foreach ($invoices as $invoice) {
            if ($available - $total <= self::EPSILON) {
                break;
            }

            $outstanding = $this->toAmount($invoice->outstanding_amount);
            $take = min($outstanding, $available - $total);

            if ($take <= self::EPSILON) {
                continue;
            }

            DB::table('ar_allocations')->insert([
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'ar_invoice_id' => (int) $invoice->id,
                'allocation_date' => $allocationDate,
                'allocated_amount' => $take,
                'status' => self::ALLOCATION_ACTIVE,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->applyToInvoiceBalance((int) $invoice->id, $take);

            $total += $take;
        }

        $this->refreshSourceStatus($sourceType, $sourceId, $amount, $allocatedBefore + $total);

        return $total;
    }

    /**
     * Menutup outstanding invoice dan menaikkan paid_amount.
     *
     * Credit note memakai jalur yang sama karena sama-sama mengurangi
     * tagihan pelanggan.
     */
    protected function applyToInvoiceBalance($invoiceId, $amount)
    {
        $invoice = DB::table('ar_invoices')->where('id', $invoiceId)->lockForUpdate()->first();

        if (empty($invoice)) {
            return;
        }

        // Invoice yang sudah direversal tidak boleh disentuh lagi.
        if ($invoice->status === self::INVOICE_REVERSED) {
            return;
        }

        $paid = $this->toAmount($invoice->paid_amount) + $amount;
        $outstanding = max(0, $this->toAmount($invoice->outstanding_amount) - $amount);

        DB::table('ar_invoices')->where('id', $invoiceId)->update([
            'paid_amount' => $paid,
            'outstanding_amount' => $outstanding,
            'status' => $this->resolveInvoiceStatus($paid, $outstanding),
            'updated_at' => now(),
        ]);
    }

    /**
     * Mengembalikan outstanding invoice ketika alokasi dibatalkan.
     */
    protected function restoreInvoiceBalance($invoiceId, $amount)
    {
        $invoice = DB::table('ar_invoices')->where('id', $invoiceId)->lockForUpdate()->first();

        if (empty($invoice) || $invoice->status === self::INVOICE_REVERSED) {
            return;
        }

        $paid = max(0, $this->toAmount($invoice->paid_amount) - $amount);
        $outstanding = $this->toAmount($invoice->outstanding_amount) + $amount;

        DB::table('ar_invoices')->where('id', $invoiceId)->update([
            'paid_amount' => $paid,
            'outstanding_amount' => $outstanding,
            'status' => $this->resolveInvoiceStatus($paid, $outstanding),
            'updated_at' => now(),
        ]);
    }

    /**
     * Membatalkan reversal untuk payment atau credit note.
     */
    protected function reverseSource($sourceType, $referenceId)
    {
        $table = $sourceType === self::SOURCE_PAYMENT ? 'ar_payments' : 'ar_credit_notes';
        $foreignKey = $sourceType === self::SOURCE_PAYMENT ? 'customer_payment_id' : 'sales_return_id';

        $record = DB::table($table)->where($foreignKey, $referenceId)->first();

        if (empty($record)) {
            return false;
        }

        $this->releaseAllocationsFor($sourceType, (int) $record->id);

        DB::table($table)->where('id', $record->id)->update([
            'allocated_amount' => 0,
            'status' => self::SOURCE_REVERSED,
            'updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Membatalkan reversal untuk invoice.
     *
     * Invoice tidak boleh dihapus dari subledger karena general_ledger masih
     * menyimpan jejaknya, sehingga statusnya ditandai REVERSED.
     */
    protected function reverseInvoice($salesInvoiceId)
    {
        $invoice = DB::table('ar_invoices')->where('sales_invoice_id', $salesInvoiceId)->first();

        if (empty($invoice)) {
            return false;
        }

        $allocations = DB::table('ar_allocations')
            ->where('ar_invoice_id', (int) $invoice->id)
            ->where('status', self::ALLOCATION_ACTIVE)
            ->lockForUpdate()
            ->get();

        foreach ($allocations as $allocation) {
            DB::table('ar_allocations')->where('id', $allocation->id)->update([
                'status' => self::ALLOCATION_REVERSED,
                'reversed_at' => now(),
                'updated_at' => now(),
            ]);

            // Alokasi yang dibatalkan harus dikurangi lagi dari sumbernya,
            // supaya payment atau credit note tidak dianggap sudah tertutup.
            $this->reduceSourceAllocated(
                $allocation->source_type,
                (int) $allocation->source_id,
                $this->toAmount($allocation->allocated_amount)
            );
        }

        DB::table('ar_invoices')->where('id', $invoice->id)->update([
            'paid_amount' => 0,
            'outstanding_amount' => 0,
            'status' => self::INVOICE_REVERSED,
            'updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Melepas semua alokasi aktif milik satu sumber.
     */
    protected function releaseAllocationsFor($sourceType, $sourceId)
    {
        $allocations = DB::table('ar_allocations')
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('status', self::ALLOCATION_ACTIVE)
            ->lockForUpdate()
            ->get();

        foreach ($allocations as $allocation) {
            DB::table('ar_allocations')->where('id', $allocation->id)->update([
                'status' => self::ALLOCATION_REVERSED,
                'reversed_at' => now(),
                'updated_at' => now(),
            ]);

            $this->restoreInvoiceBalance(
                (int) $allocation->ar_invoice_id,
                $this->toAmount($allocation->allocated_amount)
            );
        }
    }

    /**
     * Mengurangi allocated_amount sumber ketika alokasinya dibatalkan.
     */
    protected function reduceSourceAllocated($sourceType, $sourceId, $amount)
    {
        $table = $sourceType === self::SOURCE_PAYMENT ? 'ar_payments' : 'ar_credit_notes';

        $record = DB::table($table)->where('id', $sourceId)->lockForUpdate()->first();

        if (empty($record)) {
            return;
        }

        $allocated = max(0, $this->toAmount($record->allocated_amount) - $amount);
        $total = $this->toAmount($record->amount);

        DB::table($table)->where('id', $sourceId)->update([
            'allocated_amount' => $allocated,
            'status' => $this->resolveSourceStatus($total, $allocated),
            'updated_at' => now(),
        ]);
    }

    /**
     * Menghitung ulang status sumber setelah alokasi berubah.
     */
    protected function refreshSourceStatus($sourceType, $sourceId, $amount, $allocated)
    {
        $table = $sourceType === self::SOURCE_PAYMENT ? 'ar_payments' : 'ar_credit_notes';

        DB::table($table)->where('id', $sourceId)->update([
            'allocated_amount' => $allocated,
            'status' => $this->resolveSourceStatus($amount, $allocated),
            'updated_at' => now(),
        ]);
    }

    /**
     * Status invoice dari saldo saat ini.
     */
    protected function resolveInvoiceStatus($paid, $outstanding)
    {
        if ($outstanding <= self::EPSILON) {
            return self::INVOICE_PAID;
        }

        if ($paid > self::EPSILON) {
            return self::INVOICE_PARTIALLY_PAID;
        }

        return self::INVOICE_OPEN;
    }

    /**
     * Status payment atau credit note dari porsi yang sudah dialokasikan.
     */
    protected function resolveSourceStatus($amount, $allocated)
    {
        if ($allocated <= self::EPSILON) {
            return self::SOURCE_UNALLOCATED;
        }

        if ($allocated >= $amount - self::EPSILON) {
            return self::SOURCE_ALLOCATED;
        }

        return self::SOURCE_PARTIALLY_ALLOCATED;
    }

    /**
     * Nominal movements pada akun kontrol AR di jurnal.
     *
     * Membaca dari jurnal, bukan dari dokumen, karena yang harus disimpan di
     * subledger adalah nominal yang benar-benar masuk ke general_ledgers.
     *
     * @param  string  $transactionType
     * @param  string  $side  debit atau credit
     */
    protected function arAmountFromJournal($journal, $transactionType, $side)
    {
        $accountId = $this->resolver->resolve($transactionType, self::ROLE_AR);

        $amount = $this->toAmount(
            DB::table('journal_details')
                ->where('journal_id', $journal->id)
                ->where('account_id', $accountId)
                ->sum($side)
        );

        if ($amount <= self::EPSILON) {
            throw new JournalValidationException(
                'Subledger AR tidak dapat dibentuk karena jurnal ' . $journal->journal_no
                . ' tidak memiliki nominal ' . $side . ' pada akun kontrol AR.'
            );
        }

        return $amount;
    }

    protected function sourceTypeFor($table)
    {
        return $table === 'ar_payments' ? self::SOURCE_PAYMENT : self::SOURCE_CREDIT_NOTE;
    }

    protected function toAmount($value)
    {
        return $this->validator->toAmount($value);
    }
}
