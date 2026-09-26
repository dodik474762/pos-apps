<?php

namespace App\Services\Accounting;

use App\Models\Transaction\AccountMappingRules;
use App\Models\Transaction\JournalHeader;
use App\Models\Transaction\SalesInvoiceHeader;
use Illuminate\Support\Facades\DB;

class SalesInvoiceJournalService
{
    const REFERENCE_TYPE = 'SALES_INVOICE';
    const TRANSACTION_TYPE = 'SALES';
    const ROLE_AR = 'AR';
    const ROLE_REVENUE = 'REVENUE';
    const ROLE_TAX = 'TAX_PAYABLE';

    protected $journalService;
    protected $postingService;
    protected $validator;

    public function __construct()
    {
        $this->journalService = new JournalService();
        $this->postingService = new JournalPostingService();
        $this->validator = new JournalValidator();
    }

    /**
     * Integrasi pertama kali Journal Engine dengan Sales Invoice.
     * Mem journalskan tagihan menjadi jurnal double entry:
     *   Dr AR (total) / Cr Revenue (total - pajak) / Cr Tax Payable (pajak)
     */
    public function postFromSalesInvoice($invoiceId, $userId = null)
    {
        $invoice = SalesInvoiceHeader::find($invoiceId);
        if (empty($invoice)) {
            throw new JournalValidationException('Sales invoice tidak ditemukan.');
        }

        // Idempotent: invoice yang sudah memiliki jurnal aktif tidak digandakan.
        $existing = JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $invoice->id)
            ->whereIn('status', [JournalService::STATUS_DRAFT, JournalService::STATUS_POSTED])
            ->first();
        if (! empty($existing)) {
            return $existing;
        }

        $total = $this->validator->toAmount($invoice->total_amount);
        if ($total <= JournalValidator::EPSILON) {
            return null;
        }

        $taxAmount = $this->validator->toAmount($invoice->tax_amount);
        $revenueAmount = round($total - $taxAmount, 2);

        $accountAr = $this->resolveAccount(self::ROLE_AR);
        $accountRevenue = $this->resolveAccount(self::ROLE_REVENUE);

        $details = [];
        $details[] = [
            'line_no' => 1,
            'account_id' => $accountAr,
            'description' => 'Piutang penjualan ' . $invoice->invoice_number,
            'debit' => $total,
            'credit' => 0,
        ];

        $lineNo = 2;
        if ($taxAmount > JournalValidator::EPSILON) {
            $accountTax = $this->resolveAccount(self::ROLE_TAX);
            $details[] = [
                'line_no' => $lineNo,
                'account_id' => $accountTax,
                'description' => 'Pajak penjualan ' . $invoice->invoice_number,
                'debit' => 0,
                'credit' => $taxAmount,
            ];
            $lineNo++;
        }

        $details[] = [
            'line_no' => $lineNo,
            'account_id' => $accountRevenue,
            'description' => 'Pendapatan penjualan ' . $invoice->invoice_number,
            'debit' => 0,
            'credit' => $revenueAmount,
        ];

        $header = [
            'journal_date' => $invoice->invoice_date,
            'reference_type' => self::REFERENCE_TYPE,
            'reference_id' => $invoice->id,
            'description' => 'Sales Invoice ' . $invoice->invoice_number,
            'details' => $details,
        ];

        $journal = $this->journalService->create($header, true);

        return $this->postingService->post($journal->id, $userId);
    }

    /**
     * Mengambil account_id dari Account Mapping berdasarkan transaction_type dan account_role.
     */
    public function resolveAccount($role)
    {
        $rule = AccountMappingRules::where('transaction_type', self::TRANSACTION_TYPE)
            ->where('account_role', $role)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->first();

        if (empty($rule)) {
            throw new JournalValidationException(
                'Account Mapping untuk SALES / ' . $role . ' belum tersedia. '
                . 'Tambahkan di menu Master > Account Mapping Rules '
                . '(Transaction Type SALES, Role ' . $role . ') sebelum memposting invoice.'
            );
        }

        $account = DB::table('accounts')
            ->select(['id', 'code', 'name', 'is_header', 'is_active'])
            ->where('id', $rule->account_id)
            ->whereNull('deleted_at')
            ->first();

        if (empty($account)) {
            throw new JournalValidationException('Akun untuk SALES / ' . $role . ' tidak ditemukan.');
        }
        if ((int) $account->is_header === 1) {
            throw new JournalValidationException('Akun ' . $account->code . ' untuk SALES / ' . $role . ' adalah header account.');
        }
        if ((int) $account->is_active !== 1) {
            throw new JournalValidationException('Akun ' . $account->code . ' untuk SALES / ' . $role . ' tidak aktif.');
        }

        return $account->id;
    }
}
