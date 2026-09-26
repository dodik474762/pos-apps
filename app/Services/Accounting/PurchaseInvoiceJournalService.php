<?php

namespace App\Services\Accounting;

use App\Models\Transaction\JournalHeader;
use App\Models\Transaction\PurchaseInvoiceHeader;
use Illuminate\Support\Facades\DB;

/**
 * Integrasi Journal Engine dengan Purchase Invoice.
 *
 * Setiap baris detail invoice dipetakan ke akun debit berdasarkan flag
 * product.is_stock:
 *   1. product.is_stock <> 0  -> role INVENTORY (barang persediaan)
 *   2. product.is_stock = 0   -> role EXPENSE (beban / jasa)
 *
 * Nominal baris memakai purchase_invoice_detail.subtotal, yaitu nilai
 * qty x harga setelah diskon dan sebelum pajak. PPN Masukan tetap
 * ditangani oleh postingGL() legacy sehingga tidak ikut dijurnalkan di sini.
 *
 * Akun debit yang sama digabung menjadi satu baris detail. Account payable
 * dibuat sebagai satu baris credit dengan nominal total invoice.
 *
 * Jurnal yang dihasilkan:
 *   1. journal_headers  : 1 baris, reference ke Purchase Invoice
 *   2. journal_details  : N baris debit (gabungan INVENTORY / EXPENSE)
 *                         dan 1 baris credit AP
 */
class PurchaseInvoiceJournalService
{
    const REFERENCE_TYPE = 'PurchaseInvoice';
    const TRANSACTION_TYPE = 'PURCHASE_INVOICE';
    const ROLE_AP = 'AP';
    const ROLE_INVENTORY = 'INVENTORY';
    const ROLE_EXPENSE = 'EXPENSE';
    const EPSILON = 0.001;
    const TOLERANCE = 0.01;

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

    public function postFromPurchaseInvoice($invoiceId, $userId = null)
    {
        $invoice = PurchaseInvoiceHeader::find($invoiceId);
        if (empty($invoice) || ! empty($invoice->deleted)) {
            throw new JournalValidationException('Purchase invoice tidak ditemukan.');
        }

        $this->assertPostableStatus($invoice);

        // Idempotent: invoice yang sudah memiliki jurnal tidak digandakan.
        $existing = $this->getActiveJournal($invoice->id);
        if (! empty($existing)) {
            // Jurnal tertinggal DRAFT, misalnya posting sebelumnya gagal, lalu diposting ulang.
            if ($existing->status === JournalService::STATUS_DRAFT) {
                return $this->postingService->post($existing->id, $userId);
            }

            return $existing;
        }

        $lines = $this->getLines($invoice);
        if ($lines->isEmpty()) {
            throw new JournalValidationException(
                'Detail Purchase Invoice ' . $invoice->invoice_number . ' tidak ada, jurnal tidak dapat dibuat.'
            );
        }

        $details = $this->buildDetails($invoice, $lines);
        if (empty($details)) {
            throw new JournalValidationException(
                'Total Purchase Invoice ' . $invoice->invoice_number . ' bernilai 0, jurnal tidak dapat dibuat.'
            );
        }

        $header = [
            'journal_date' => $invoice->invoice_date,
            'reference_type' => self::REFERENCE_TYPE,
            'reference_id' => $invoice->id,
            'description' => 'Purchase Invoice ' . $invoice->invoice_number,
            'details' => $details,
        ];

        $journal = $this->journalService->create($header, true);

        return $this->postingService->post($journal->id, $userId);
    }

    /**
     * Jurnal terakhir yang merujuk Purchase Invoice, dipakai untuk validasi hapus.
     * Mengembalikan null apabila invoice belum pernah dijurnalkan.
     */
    public function getJournalForPurchaseInvoice($invoiceId)
    {
        if (empty($invoiceId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $invoiceId)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Jurnal yang masih aktif, yaitu DRAFT atau POSTED. Jurnal REVERSED tidak
     * dihitung karena pengaruhnya di general ledger sudah dinetralkan.
     */
    public function getActiveJournal($invoiceId)
    {
        if (empty($invoiceId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $invoiceId)
            ->whereIn('status', [JournalService::STATUS_DRAFT, JournalService::STATUS_POSTED])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Hanya Purchase Invoice berstatus draft yang boleh dijurnalkan.
     */
    protected function assertPostableStatus($invoice)
    {
        if (! in_array($invoice->status, self::STATUS_POSTABLE, true)) {
            throw new JournalValidationException(
                'Purchase Invoice ' . $invoice->invoice_number . ' berstatus ' . $invoice->status
                . '. Hanya Purchase Invoice berstatus draft yang dapat dijurnalkan.'
            );
        }
    }

    /**
     * Baris detail invoice beserta flag is_stock dari master product.
     */
    protected function getLines($invoice)
    {
        return DB::table('purchase_invoice_detail as d')
            ->select([
                'd.id',
                'd.product',
                'd.subtotal',
                'p.id as product_id',
                'p.name as product_name',
                'p.is_stock',
            ])
            ->leftJoin('product as p', 'p.id', '=', 'd.product')
            ->where('d.purchase_invoice_id', $invoice->id)
            ->whereNull('d.deleted')
            ->orderBy('d.id', 'asc')
            ->get();
    }

    /**
     * Menyusun detail jurnal: satu baris debit per akun hasil resolusi mapping,
     * digabung bila beberapa baris invoice memakai akun yang sama, ditambah satu
     * baris credit untuk account payable.
     */
    protected function buildDetails($invoice, $lines)
    {
        $groups = [];
        $cache = [];
        $totalDebit = 0;

        foreach ($lines as $line) {
            $amount = $this->validator->toAmount($line->subtotal);
            if ($amount <= self::EPSILON) {
                continue;
            }

            if (empty($line->product_id)) {
                throw new JournalValidationException(
                    'Baris detail Purchase Invoice ' . $invoice->invoice_number
                    . ' dengan product #' . $line->product . ' tidak memiliki data product,'
                    . ' jurnal tidak dapat dibuat.'
                );
            }

            $role = $this->resolveRole($line);
            $accountId = $this->resolveAccount($cache, $role);

            if (! isset($groups[$accountId])) {
                $groups[$accountId] = [
                    'role' => $role,
                    'amount' => 0,
                ];
            }
            $groups[$accountId]['amount'] += $amount;
            $totalDebit += $amount;
        }

        $totalDebit = $this->validator->toAmount($totalDebit);
        $totalAp = $this->validator->toAmount($invoice->total_amount);

        if ($totalAp <= self::EPSILON) {
            throw new JournalValidationException(
                'Total Purchase Invoice ' . $invoice->invoice_number . ' bernilai 0, jurnal tidak dapat dibuat.'
            );
        }

        // Mencegah jurnal tidak balanced karena selisih pembulatan atau total header yang salah input.
        if (abs($totalDebit - $totalAp) > self::TOLERANCE) {
            throw new JournalValidationException(
                'Total detail Purchase Invoice ' . $invoice->invoice_number . ' sebesar '
                . $this->formatAmount($totalDebit) . ' tidak sama dengan total invoice sebesar '
                . $this->formatAmount($totalAp) . ', jurnal tidak dapat dibuat.'
            );
        }

        $accountAp = $this->resolveAccount($cache, self::ROLE_AP);

        $details = [];
        $lineNo = 1;
        foreach ($groups as $accountId => $group) {
            $amount = $this->validator->toAmount($group['amount']);
            if ($amount <= self::EPSILON) {
                continue;
            }

            $details[] = [
                'line_no' => $lineNo,
                'account_id' => $accountId,
                'description' => $this->debitDescription($group['role'], $invoice),
                'debit' => $amount,
                'credit' => 0,
            ];
            $lineNo++;
        }

        $details[] = [
            'line_no' => $lineNo,
            'account_id' => $accountAp,
            'description' => 'Hutang pembelian ' . $invoice->invoice_number,
            'debit' => 0,
            'credit' => $totalAp,
        ];

        return $details;
    }

    /**
     * is_stock decides per item: 0 berarti beban, selain itu termasuk persediaan.
     * Nilai NULL mengikuti default kolom yaitu persediaan.
     */
    protected function resolveRole($line)
    {
        return (int) $line->is_stock === 0 ? self::ROLE_EXPENSE : self::ROLE_INVENTORY;
    }

    /**
     * Resolusi akun per role, disimpan agar tidak berulang untuk tiap baris.
     */
    protected function resolveAccount(&$cache, $role)
    {
        if (! isset($cache[$role])) {
            $cache[$role] = $this->resolver->resolve(
                self::TRANSACTION_TYPE,
                $role,
                null,
                null
            );
        }

        return $cache[$role];
    }

    protected function debitDescription($role, $invoice)
    {
        if ($role === self::ROLE_EXPENSE) {
            return 'Beban pembelian non stock ' . $invoice->invoice_number;
        }

        return 'Persediaan pembelian ' . $invoice->invoice_number;
    }

    protected function formatAmount($value)
    {
        return number_format((float) $value, 2, ',', '.');
    }
}
