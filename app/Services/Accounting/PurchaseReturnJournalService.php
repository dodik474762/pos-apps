<?php

namespace App\Services\Accounting;

use App\Models\Transaction\JournalHeader;
use App\Models\Transaction\PurchaseReturn;
use App\Models\Transaction\PurchaseReturnDtl;
use Illuminate\Support\Facades\DB;

/**
 * Integrasi Journal Engine dengan Purchase Return.
 *
 * Purchase Return membalikkan sisi perolehan barang dari dokumen asal:
 *   Kasus A - invoice sudah masuk, AP sudah diakui
 *        Dr AP / Cr INVENTORY
 *   Kasus B - baru Receiving, AP belum diakui, barang masih di GRNI
 *        Dr GRNI / Cr INVENTORY
 *   Barang non stok yang sudah dibebankan sebagai Expense
 *        Dr AP atau Dr GRNI / Cr EXPENSE
 *
 * Sisi kredit mengikuti product.is_stock pada master produk, sama seperti
 * PurchaseInvoiceJournalService, sehingga entry ini benar-benar membalik
 * beban yang pernah diakui saat pembelian.
 *
 * Jurnal yang dihasilkan:
 *   1. journal_headers  : 1 baris, reference ke PurchaseReturn
 *   2. journal_details  : satu baris debit per akun AP/GRNI hasil resolusi
 *                         mapping (bisa lebih dari satu bila satu retur
 *                         mencakup invoice dan GR sekaligus), ditambah satu
 *                         baris credit per akun INVENTORY/EXPENSE.
 *                         Baris dengan akun sama digabung menjadi satu baris.
 *
 * NILAI perolehan diambil dari dokumen asal, diprorate terhadap qty yang
 * diretur, bukan dihitung ulang dari harga beli:
 *   - FROM_INVOICE : purchase_invoice_detail.subtotal / qty x qty_retur
 *   - FROM_GR      : goods_receipt_detail.subtotal / qty_received x qty_retur,
 *                    cadangan purchase_order_detail.purchase_price x qty_retur
 * Subtotal invoice sudah termasuk PPN dan PurchaseInvoiceJournalService
 * membukukannya di sisi persediaan, sehingga nilai yang sama dipakai di sini
 * agar entry pembelian benar-benar terbalik. PPN tidak dipisah di jurnal ini,
 * sama seperti Purchase Invoice.
 */
class PurchaseReturnJournalService
{
    const REFERENCE_TYPE = 'PurchaseReturn';
    const TRANSACTION_TYPE = 'PURCHASE_RETURN';
    const ROLE_AP = 'AP';
    const ROLE_GRNI = 'GRNI';
    const ROLE_INVENTORY = 'INVENTORY';
    const ROLE_EXPENSE = 'EXPENSE';
    const EPSILON = 0.001;
    const TOLERANCE = 0.01;

    /**
     * Nilai pada purchase_return.return_type dan purchase_return_detail.return_type
     * yang menunjuk dokumen asal. Nilai ini sudah ada di modul, jadi Journal
     * Engine tidak menebak status invoice.
     */
    const SOURCE_INVOICE = 'FROM_INVOICE';
    const SOURCE_GR = 'FROM_GR';

    /**
     * Hanya purchase return berstatus DRAFT yang boleh dijurnalkan. Status
     * APPROVED belum pernah dipakai modul Purchase Return.
     */
    const STATUS_POSTABLE = ['DRAFT'];

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

    public function postFromPurchaseReturn($returnId, $userId = null)
    {
        $return = PurchaseReturn::find($returnId);
        if (empty($return) || ! empty($return->deleted)) {
            throw new JournalValidationException('Purchase return tidak ditemukan.');
        }

        $this->assertPostableStatus($return);

        // Idempoten: retur yang sudah punya jurnal aktif tidak digandakan.
        $existing = $this->getActiveJournal($return->id);
        if (! empty($existing)) {
            // Jurnal tertinggal DRAFT, misalnya posting sebelumnya gagal, lalu dicoba lagi.
            if ($existing->status === JournalService::STATUS_DRAFT) {
                return $this->postingService->post($existing->id, $userId);
            }

            return $existing;
        }

        $lines = $this->getLines($return);
        if ($lines->isEmpty()) {
            throw new JournalValidationException(
                'Detail purchase return ' . $return->code . ' tidak ada, jurnal tidak dapat dibuat.'
            );
        }

        $details = $this->buildDetails($return, $lines);
        if (empty($details)) {
            throw new JournalValidationException(
                'Total purchase return ' . $return->code . ' bernilai 0, jurnal tidak dapat dibuat.'
            );
        }

        $header = [
            'journal_date' => $return->return_date,
            'reference_type' => self::REFERENCE_TYPE,
            'reference_id' => $return->id,
            'description' => 'Purchase Return ' . $return->code,
            'details' => $details,
        ];

        $journal = $this->journalService->create($header, true);

        return $this->postingService->post($journal->id, $userId);
    }

    /**
     * Jurnal terakhir yang merujuk purchase return, dipakai untuk validasi hapus.
     */
    public function getJournalForPurchaseReturn($returnId)
    {
        if (empty($returnId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $returnId)
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
    public function getActiveJournal($returnId)
    {
        if (empty($returnId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $returnId)
            ->whereNull('reversal_of_id')
            ->whereIn('status', [JournalService::STATUS_DRAFT, JournalService::STATUS_POSTED])
            ->orderByDesc('id')
            ->first();
    }

    protected function assertPostableStatus($return)
    {
        if (! in_array($return->status, self::STATUS_POSTABLE, true)) {
            throw new JournalValidationException(
                'Purchase return ' . $return->code . ' berstatus ' . $return->status
                . '. Hanya purchase return berstatus DRAFT yang dapat dijurnalkan.'
            );
        }
    }

    /**
     * Baris detail retur beserta flag is_stock dari master product.
     */
    protected function getLines($return)
    {
        return PurchaseReturnDtl::where('purchase_return_id', $return->id)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($line) {
                $product = DB::table('product')
                    ->where('id', $line->product)
                    ->whereNull('deleted')
                    ->first();

                $line->product_id = empty($product) ? null : (int) $product->id;
                $line->product_name = empty($product) ? null : $product->name;
                $line->is_stock = empty($product) ? null : (int) $product->is_stock;

                return $line;
            });
    }

    /**
     * Menyusun detail jurnal dari dua sisi. Sisi debit dikelompokkan per akun
     * AP atau GRNI, sisi credit per akun INVENTORY atau EXPENSE, sehingga
     * beberapa baris dengan akun sama tetap menjadi satu baris.
     */
    protected function buildDetails($return, $lines)
    {
        $debitSide = [];
        $creditSide = [];
        $cache = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as $line) {
            $qty = $this->validator->toAmount($line->qty);
            if ($qty <= self::EPSILON) {
                continue;
            }

            if (empty($line->product_id)) {
                throw new JournalValidationException(
                    'Baris detail purchase return ' . $return->code . ' dengan product #'
                    . $line->product . ' tidak memiliki data product, jurnal tidak dapat dibuat.'
                );
            }

            $productName = $line->product_name;
            $source = $this->resolveSource($return, $line);
            $amount = $this->resolveAmount($return, $line, $source, $productName);

            $debitRole = $source === self::SOURCE_INVOICE ? self::ROLE_AP : self::ROLE_GRNI;
            $creditRole = $this->resolveCreditRole($line, $productName);

            $debitAccount = $this->resolveAccount($cache, $debitRole);
            $creditAccount = $this->resolveAccount($cache, $creditRole);

            $debitLabel = ($debitRole === self::ROLE_AP ? 'Hutang pembelian ' : 'GRNI ')
                . $return->code;
            $creditLabel = ($creditRole === self::ROLE_EXPENSE
                ? 'Beban pembelian non stock '
                : 'Persediaan pembelian ') . $return->code;

            $this->addAmount($debitSide, $debitAccount, $debitLabel, $amount);
            $this->addAmount($creditSide, $creditAccount, $creditLabel, $amount);

            $totalDebit += $amount;
            $totalCredit += $amount;
        }

        $totalDebit = $this->validator->toAmount($totalDebit);
        $totalCredit = $this->validator->toAmount($totalCredit);

        if ($totalDebit <= self::EPSILON) {
            return [];
        }

        // Mencegah jurnal tidak balanced karena pembulatan per baris.
        if (abs($totalDebit - $totalCredit) > self::TOLERANCE) {
            throw new JournalValidationException(
                'Total purchase return ' . $return->code . ' tidak balanced, jurnal tidak dapat dibuat.'
            );
        }

        $details = [];
        $lineNo = 1;

        foreach ($debitSide as $accountId => $side) {
            $amount = $this->validator->toAmount($side['amount']);
            if ($amount <= self::EPSILON) {
                continue;
            }

            $details[] = [
                'line_no' => $lineNo,
                'account_id' => $accountId,
                'description' => $side['label'],
                'debit' => $amount,
                'credit' => 0,
            ];
            $lineNo++;
        }

        foreach ($creditSide as $accountId => $side) {
            $amount = $this->validator->toAmount($side['amount']);
            if ($amount <= self::EPSILON) {
                continue;
            }

            $details[] = [
                'line_no' => $lineNo,
                'account_id' => $accountId,
                'description' => $side['label'],
                'debit' => 0,
                'credit' => $amount,
            ];
            $lineNo++;
        }

        return $details;
    }

    /**
     * Menambahkan nominal ke salah satu sisi, dikelompokkan per akun hasil
     * resolusi mapping. Kedua sisi memakai nominal yang sama sehingga total
     * debit selalu sama dengan total credit.
     */
    protected function addAmount(&$side, $accountId, $label, $amount)
    {
        if (! isset($side[$accountId])) {
            $side[$accountId] = [
                'label' => $label,
                'amount' => 0,
            ];
        }

        $side[$accountId]['amount'] += $amount;
    }

    /**
     * Menentukan dokumen asal per baris. purchase_return_detail.return_type
     * dipakai lebih dulu karena satu retur boleh mencampur baris dari invoice
     * dan dari GR, lalu purchase_return.return_type sebagai cadangan.
     */
    protected function resolveSource($return, $line)
    {
        $source = strtoupper(trim((string) ($line->return_type ?: $return->return_type)));

        if ($source === self::SOURCE_INVOICE) {
            return self::SOURCE_INVOICE;
        }

        if ($source === self::SOURCE_GR) {
            return self::SOURCE_GR;
        }

        throw new JournalValidationException(
            'Baris ' . $line->product_name . ' pada purchase return ' . $return->code
            . ' tidak memiliki return_type yang dikenal (' . ($line->return_type ?: 'kosong')
            . '). Expected ' . self::SOURCE_INVOICE . ' atau ' . self::SOURCE_GR
            . ', jurnal tidak dapat dibuat.'
        );
    }

    /**
     * Sisi kredit mengikuti product.is_stock, sama seperti Purchase Invoice.
     * Barang non stok sudah langsung dibebankan sebagai Expense sehingga yang
     * dibalik adalah beban tersebut, bukan persediaan.
     */
    protected function resolveCreditRole($line, $productName)
    {
        if ((int) $line->is_stock === 0) {
            return self::ROLE_EXPENSE;
        }

        return self::ROLE_INVENTORY;
    }

    /**
     * Nilai perolehan barang yang dikembalikan, diambil dari dokumen asal
     * lalu diprorate terhadap qty retur. Tidak dihitung ulang dari harga beli.
     */
    protected function resolveAmount($return, $line, $source, $productName)
    {
        if (empty($line->reference_detail_id)) {
            throw new JournalValidationException(
                'Baris ' . $productName . ' pada purchase return ' . $return->code
                . ' tidak menunjuk baris dokumen asal, nilai perolehan tidak dapat ditentukan. '
                . 'Jurnal tidak dapat dibuat.'
            );
        }

        $qtyReturn = $this->validator->toAmount($line->qty);

        if ($source === self::SOURCE_INVOICE) {
            return $this->amountFromInvoice($return, $line, $productName, $qtyReturn);
        }

        return $this->amountFromReceiving($return, $line, $productName, $qtyReturn);
    }

    /**
     * Nilai dari Purchase Invoice asal. Subtotal invoice sudah termasuk PPN dan
     * PurchaseInvoiceJournalService membukukannya di sisi persediaan, sehingga
     * memakai nilai yang sama membuat entry pembelian terbalik utuh.
     */
    protected function amountFromInvoice($return, $line, $productName, $qtyReturn)
    {
        $detail = DB::table('purchase_invoice_detail')
            ->where('id', $line->reference_detail_id)
            // Baris retur harus menunjuk baris invoice yang sama dengan header
            // retur, supaya nilai diambil dari invoice yang benar.
            ->where('purchase_invoice_id', $return->reference_id)
            ->whereNull('deleted')
            ->first();

        if (empty($detail)) {
            throw new JournalValidationException(
                'Baris ' . $productName . ' pada purchase return ' . $return->code
                . ' menunjuk baris purchase invoice #' . $line->reference_detail_id
                . ' yang tidak ditemukan atau bukan milik purchase invoice #' . $return->reference_id
                . '. Jurnal tidak dapat dibuat.'
            );
        }

        $qty = $this->validator->toAmount($detail->qty);
        $subtotal = $this->validator->toAmount($detail->subtotal);

        if ($qty > self::EPSILON) {
            if ($qtyReturn > $qty + self::EPSILON) {
                throw new JournalValidationException(
                    'Qty retur ' . $productName . ' pada purchase return ' . $return->code . ' ('
                    . $qtyReturn . ') melebihi qty baris purchase invoice asal (' . $qty
                    . '). Nilai perolehan tidak boleh melebihi nilai yang pernah diakui,'
                    . ' jurnal tidak dapat dibuat.'
                );
            }

            if ($subtotal > self::EPSILON) {
                return $this->validator->toAmount(($subtotal / $qty) * $qtyReturn);
            }
        }

        // Tidak ada cadangan dari unit_price atau total_price baris retur.
        // Nilainya adalah harga sebelum diskon dan PPN, sedangkan yang pernah
        // dibukukan ke Inventory adalah subtotal, sehingga memakainya akan
        // menyisakan sisa di persediaan. Lebih baik berhenti daripada membalik
        // entry sebagian.
        throw new JournalValidationException(
            'Nilai perolehan ' . $productName . ' pada purchase return ' . $return->code
            . ' tidak dapat ditentukan karena baris purchase invoice asal tidak memiliki'
            . ' subtotal maupun qty yang valid. Jurnal tidak dapat dibuat.'
        );
    }

    /**
     * Nilai dari Receiving asal. Subtotal penerimaan dipakai lebih dulu karena
     * itu nilai yang pernah diakui saat penerimaan. Bila kosong, harga di
     * Purchase Order dipakai karena itu sumber yang sama dengan jurnal Receiving.
     */
    protected function amountFromReceiving($return, $line, $productName, $qtyReturn)
    {
        $detail = DB::table('goods_receipt_detail as d')
            ->leftJoin('purchase_order_detail as pod', 'pod.id', '=', 'd.purchase_order_detail')
            ->where('d.id', $line->reference_detail_id)
            ->where('d.goods_receipt_header', $return->reference_id)
            ->whereNull('d.deleted')
            ->first([
                'd.qty_received',
                'd.subtotal',
                'pod.purchase_price',
            ]);

        if (empty($detail)) {
            throw new JournalValidationException(
                'Baris ' . $productName . ' pada purchase return ' . $return->code
                . ' menunjuk baris penerimaan #' . $line->reference_detail_id
                . ' yang tidak ditemukan atau bukan milik penerimaan #' . $return->reference_id
                . '. Jurnal tidak dapat dibuat.'
            );
        }

        $qtyReceived = $this->validator->toAmount($detail->qty_received);
        $subtotal = $this->validator->toAmount($detail->subtotal);

        if ($qtyReceived > self::EPSILON) {
            if ($qtyReturn > $qtyReceived + self::EPSILON) {
                throw new JournalValidationException(
                    'Qty retur ' . $productName . ' pada purchase return ' . $return->code . ' ('
                    . $qtyReturn . ') melebihi qty baris penerimaan asal (' . $qtyReceived
                    . '). Nilai perolehan tidak boleh melebihi nilai yang pernah diakui,'
                    . ' jurnal tidak dapat dibuat.'
                );
            }

            if ($subtotal > self::EPSILON) {
                return $this->validator->toAmount(($subtotal / $qtyReceived) * $qtyReturn);
            }
        }

        $price = $this->validator->toAmount($detail->purchase_price);
        if ($price > self::EPSILON) {
            return $this->validator->toAmount($price * $qtyReturn);
        }

        throw new JournalValidationException(
            'Nilai perolehan ' . $productName . ' pada purchase return ' . $return->code
            . ' tidak dapat ditentukan karena baris penerimaan asal tidak memiliki subtotal'
            . ' maupun harga di Purchase Order. Jurnal tidak dapat dibuat.'
        );
    }

    /**
     * Resolusi akun per role, disimpan agar tidak berulang untuk tiap baris.
     * product.category pada tabel produk berupa varchar, bukan id numerik,
     * sehingga scope kategori diteruskan apa adanya oleh resolver.
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
}
