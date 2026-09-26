<?php

namespace App\Services\Accounting;

use App\Models\Transaction\JournalHeader;
use App\Models\Transaction\SalesReturnDtl;
use App\Models\Transaction\SalesReturnHdr;
use Illuminate\Support\Facades\DB;

/**
 * Integrasi Journal Engine dengan Sales Return.
 *
 * Sales Return membalikkan dua sisi sekaligus:
 *   1. Sisi pendapatan, mengikuti Sales Invoice asal
 *        Dr SALES_RETURN (akun kontra pendapatan) / Cr AR
 *   2. Sisi persediaan, mengikuti Delivery Order asal
 *        Dr INVENTORY / Cr COGS
 *
 * Jurnal yang dihasilkan:
 *   1. journal_headers  : 1 baris, reference ke SalesReturn
 *   2. journal_details  : 2 baris sisi pendapatan (Dr kontra, Cr AR)
 *                         dan 2 baris sisi persediaan (Dr Inventory, Cr COGS)
 *
 * NILAI JUAL diambil dari sales_invoice_detail yang ditunjuk oleh
 * sales_return_detail.invoice_detail_id, diprorate terhadap qty_return sehingga
 * retur sebagian ikut terhitung benar. PPN tetap diproses oleh posting GL legacy
 * sehingga tidak ikut dalam jurnal ini, sama seperti Purchase Invoice.
 *
 * NILAI HPP TIDAK dihitung ulang. HPP ditelusuri ke Delivery Order asal melalui
 * sales_invoice_detail.so_detail_id -> delivery_order_detail, lalu memakai
 * product_uom_cost dengan urutan lookup yang sama persis dengan
 * DeliveryOrderJournalService, sehingga angkanya sama dengan yang diakui jurnal
 * DO. Jurnal DO yang sudah ada hanya dipakai sebagai pembanding, bukan sumber
 * angka, karena jurnal DO menggabungkan seluruh baris per pasangan akun dan
 * tidak menyimpan HPP per produk.
 *
 * RETUR BARANG RUSAK. Bila kondisi barang tidak layak jual lagi, barang tidak
 * kembali ke stok sehingga sisi persediaan tidak memakai INVENTORY melainkan
 * akun kerugian/write-off. Keputusan ini milik modul Sales Return lewat kolom
 * types pada header; Journal Engine hanya deliciosaikan.
 */
class SalesReturnJournalService
{
    const REFERENCE_TYPE = 'SalesReturn';
    const TRANSACTION_TYPE = 'SALES_RETURN';
    const ROLE_SALES_RETURN = 'SALES_RETURN';
    const ROLE_AR = 'AR';
    const ROLE_INVENTORY = 'INVENTORY';
    const ROLE_COGS = 'COGS';
    const ROLE_LOSS = 'LOSS';
    const EPSILON = 0.001;

    /**
     * Hanya sales return berstatus DRAFT yang boleh dijurnalkan.
     */
    const STATUS_POSTABLE = ['DRAFT'];

    /**
     * Nilai pada kolom sales_return.types yang menandai barang tidak layak jual
     * lagi. Selain nilai ini dianggap barang baik dan kembali ke stok.
     */
    const CONDITION_DAMAGED = [
        'damaged', 'damage', 'rusak', 'broken', 'reject', 'scrap',
        'writeoff', 'write_off', 'write-off', 'obsolete',
    ];

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

    public function postFromSalesReturn($returnId, $userId = null)
    {
        $return = SalesReturnHdr::find($returnId);
        if (empty($return) || ! empty($return->deleted)) {
            throw new JournalValidationException('Sales return tidak ditemukan.');
        }

        $this->assertPostableStatus($return);

        // Idempotent: sales return yang sudah punya jurnal aktif tidak digandakan.
        $existing = $this->getActiveJournal($return->id);
        if (! empty($existing)) {
            // Jurnal tertinggal DRAFT, misalnya posting sebelumnya gagal, lalu diposting ulang.
            if ($existing->status === JournalService::STATUS_DRAFT) {
                return $this->postingService->post($existing->id, $userId);
            }

            return $existing;
        }

        $lines = $this->getLines($return);
        if ($lines->isEmpty()) {
            throw new JournalValidationException(
                'Detail sales return ' . $return->return_number . ' tidak ada, jurnal tidak dapat dibuat.'
            );
        }

        $details = $this->buildDetails($return, $lines);
        if (empty($details)) {
            throw new JournalValidationException(
                'Total sales return ' . $return->return_number . ' bernilai 0, jurnal tidak dapat dibuat.'
            );
        }

        $header = [
            'journal_date' => $return->return_date,
            'reference_type' => self::REFERENCE_TYPE,
            'reference_id' => $return->id,
            'description' => 'Sales Return ' . $return->return_number,
            'details' => $details,
        ];

        $journal = $this->journalService->create($header, true);

        return $this->postingService->post($journal->id, $userId);
    }

    /**
     * Jurnal terakhir yang merujuk sales return, dipakai untuk validasi hapus.
     */
    public function getJournalForSalesReturn($returnId)
    {
        if (empty($returnId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $returnId)
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
    public function getActiveJournal($returnId)
    {
        if (empty($returnId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $returnId)
            // Jurnal reversal mewarisi reference_type dan reference_id, jadi harus dikecualikan.
            // Kalau tidak, reversal akan dianggap jurnal aktif sehingga hapus tetap terkunci
            // dan posting ulang justru mengembalikan jurnal reversal.
            ->whereNull('reversal_of_id')
            ->whereIn('status', [JournalService::STATUS_DRAFT, JournalService::STATUS_POSTED])
            ->orderByDesc('id')
            ->first();
    }

    protected function assertPostableStatus($return)
    {
        if (! in_array($return->status, self::STATUS_POSTABLE, true)) {
            throw new JournalValidationException(
                'Sales return ' . $return->return_number . ' berstatus ' . $return->status
                . '. Hanya sales return berstatus DRAFT yang dapat dijurnalkan.'
            );
        }
    }

    protected function getLines($return)
    {
        return SalesReturnDtl::where('return_id', $return->id)
            ->whereNull('deleted')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Menyusun detail jurnal dari dua sisi. Baris dikelompokkan berdasarkan akun
     * hasil resolusi mapping sehingga beberapa produk pada satu akun tetap
     * menjadi satu baris, dan total debit selalu sama dengan total credit.
     */
    protected function buildDetails($return, $lines)
    {
        $damaged = $this->isDamaged($return->types);

        $productNames = $this->getProductNames($lines);
        $cache = [];

        $revenue = [];
        $inventoryGood = [];
        $inventoryLoss = [];
        $totalRevenue = 0;
        $totalCogs = 0;

        foreach ($lines as $line) {
            $qty = $this->validator->toAmount($line->qty_return);
            if ($qty <= self::EPSILON) {
                continue;
            }

            $productId = (int) $line->product_id;
            $productName = $productNames[$productId] ?? ('product #' . $productId);

            // --- sisi pendapatan: mengikuti Sales Invoice asal ---
            $salesValue = $this->resolveSalesValue($return, $line, $productName);
            $accountSalesReturn = $this->resolveAccount($cache, self::ROLE_SALES_RETURN);
            $accountAr = $this->resolveAccount($cache, self::ROLE_AR);

            $this->accumulate(
                $revenue,
                $accountSalesReturn,
                $accountAr,
                $salesValue,
                'Sales Return ' . $return->return_number,
                'Piutang pelanggan ' . $return->return_number
            );
            $totalRevenue += $salesValue;

            // --- sisi persediaan: mengikuti Delivery Order asal ---
            $cogs = $this->resolveCogs($return, $line, $productName);
            $accountCogs = $this->resolveAccount($cache, self::ROLE_COGS);

            if ($damaged) {
                // Barang rusak tidak kembali ke stok, jadi debit ke akun kerugian.
                $accountLoss = $this->resolveAccount($cache, self::ROLE_LOSS);
                $this->accumulate(
                    $inventoryLoss,
                    $accountLoss,
                    $accountCogs,
                    $cogs,
                    'Rugi barang rusak ' . $return->return_number,
                    'HPP retur barang rusak ' . $return->return_number
                );
            } else {
                $accountInventory = $this->resolveAccount($cache, self::ROLE_INVENTORY);
                $this->accumulate(
                    $inventoryGood,
                    $accountInventory,
                    $accountCogs,
                    $cogs,
                    'Persediaan retur ' . $return->return_number,
                    'HPP retur ' . $return->return_number
                );
            }

            $totalCogs += $cogs;
        }

        $totalRevenue = $this->validator->toAmount($totalRevenue);
        $totalCogs = $this->validator->toAmount($totalCogs);

        if ($totalRevenue <= self::EPSILON) {
            throw new JournalValidationException(
                'Nilai jual sales return ' . $return->return_number
                . ' bernilai 0, jurnal tidak dapat dibuat.'
            );
        }
        if ($totalCogs <= self::EPSILON) {
            throw new JournalValidationException(
                'Nilai HPP sales return ' . $return->return_number
                . ' bernilai 0, jurnal tidak dapat dibuat.'
            );
        }

        $details = [];
        $lineNo = 1;
        $suffix = $damaged ? ' (barang rusak)' : '';

        foreach ([$revenue, $inventoryGood, $inventoryLoss] as $side) {
            foreach ($side as $debitAccount => $group) {
                $amount = $this->validator->toAmount($group['amount']);
                if ($amount <= self::EPSILON) {
                    continue;
                }

                $details[] = [
                    'line_no' => $lineNo,
                    'account_id' => $debitAccount,
                    'description' => $group['debitLabel'] . $suffix,
                    'debit' => $amount,
                    'credit' => 0,
                ];
                $lineNo++;

                $details[] = [
                    'line_no' => $lineNo,
                    'account_id' => $group['creditAccount'],
                    'description' => $group['creditLabel'] . $suffix,
                    'debit' => 0,
                    'credit' => $amount,
                ];
                $lineNo++;
            }
        }

        return $details;
    }

    /**
     * Menambahkan nominal ke kelompok debit/kredit, menggabungkan bila akun
     * debit sama.
     */
    protected function accumulate(
        &$side,
        $debitAccount,
        $creditAccount,
        $amount,
        $debitLabel = '',
        $creditLabel = ''
    ) {
        $amount = $this->validator->toAmount($amount);
        if ($amount <= self::EPSILON) {
            return;
        }

        if (! isset($side[$debitAccount])) {
            $side[$debitAccount] = [
                'creditAccount' => $creditAccount,
                'debitLabel' => $debitLabel,
                'creditLabel' => $creditLabel,
                'amount' => 0,
            ];
        }
        $side[$debitAccount]['amount'] += $amount;
    }

    /**
     * Kondisi barang. Kolom sales_return.types adalah flag yang dimiliki modul
     * Sales Return. Nilai apa pun di luar daftar CONDITION_DAMAGED dianggap
     * barang baik yang kembali ke stok, termasuk nilai default 'good'.
     */
    protected function isDamaged($types)
    {
        if (empty($types)) {
            return false;
        }

        return in_array(strtolower(trim($types)), self::CONDITION_DAMAGED, true);
    }

    /**
     * Nilai jual barang yang dikembalikan, diambil dari Sales Invoice asal.
     * Subtotal baris invoice diprorate terhadap qty_return agar retur sebagian
     * menghasilkan nilai yang proporsional.
     */
    protected function resolveSalesValue($return, $line, $productName)
    {
        $invoiceDetail = null;
        if (! empty($line->invoice_detail_id)) {
            $invoiceDetail = DB::table('sales_invoice_detail')
                ->where('id', $line->invoice_detail_id)
                // Baris retur harus menunjuk baris Sales Invoice yang sama dengan
                // header sales return, supaya nilai jual tidak diambil dari invoice lain.
                ->where('invoice_id', $return->invoice_id)
                ->whereNull('deleted')
                ->first();
        }

        if (! empty($invoiceDetail)) {
            $invoiceQty = $this->validator->toAmount($invoiceDetail->qty);
            $subtotal = $this->validator->toAmount($invoiceDetail->subtotal);
            $qtyReturn = $this->validator->toAmount($line->qty_return);

            if ($invoiceQty > self::EPSILON) {
                if ($qtyReturn > $invoiceQty + self::EPSILON) {
                    throw new JournalValidationException(
                        'Qty retur ' . $productName . ' pada sales return ' . $return->return_number
                        . ' (' . $qtyReturn . ') melebihi qty baris Sales Invoice asal ('
                        . $invoiceQty . '). Nilai jual tidak boleh negatif, jurnal tidak dapat dibuat.'
                    );
                }

                $unitValue = $this->validator->toAmount($subtotal / $invoiceQty);

                return $this->validator->toAmount($unitValue * $qtyReturn);
            }
        }

        if (! empty($line->invoice_detail_id) && empty($invoiceDetail)) {
            throw new JournalValidationException(
                'Baris retur ' . $productName . ' pada sales return ' . $return->return_number
                . ' menunjuk baris Sales Invoice #'.$line->invoice_detail_id
                . ' yang tidak ditemukan atau bukan milik Sales Invoice #'.$return->invoice_id
                . '. Jurnal tidak dapat dibuat.'
            );
        }

        // Cadangan memakai nilai yang tersimpan di baris retur.
        $stored = $this->validator->toAmount($line->return_line_amount);
        if ($stored > self::EPSILON) {
            return $stored;
        }

        $unitPrice = $this->validator->toAmount($line->unit_price);
        if ($unitPrice > self::EPSILON) {
            return $this->validator->toAmount($unitPrice * $this->validator->toAmount($line->qty_return));
        }

        throw new JournalValidationException(
            'Nilai jual ' . $productName . ' pada sales return ' . $return->return_number
            . ' tidak dapat ditentukan karena baris Sales Invoice asal tidak ditemukan'
            . ' maupun nilai tersimpan. Jurnal tidak dapat dibuat.'
        );
    }

    /**
     * Nilai HPP barang yang dikembalikan.
     *
     * Baris DO asal dicari lewat sales_invoice_detail.so_detail_id yang menunjuk
     * delivery_order_detail. Bila DO tidak ditemukan, proses dihentikan karena
     * HPP tidak boleh ditebak.
     */
    protected function resolveCogs($return, $line, $productName)
    {
        $doDetail = $this->findDeliveryOrderLine($line, $productName, $return);
        $qty = $this->validator->toAmount($line->qty_return);
        $cost = $this->resolveCost($doDetail->product_id, $doDetail->uom, $productName);

        return $this->validator->toAmount($cost * $qty);
    }

    /**
     * Baris DO yang mengirim produk pada Sales Invoice asal.
     */
    protected function findDeliveryOrderLine($line, $productName, $return)
    {
        if (empty($line->invoice_detail_id)) {
            throw new JournalValidationException(
                'Baris retur ' . $productName . ' pada sales return ' . $return->return_number
                . ' tidak terhubung ke Sales Invoice asal, HPP tidak dapat ditentukan.'
            );
        }

        $soDetailId = DB::table('sales_invoice_detail')
            ->where('id', $line->invoice_detail_id)
            ->whereNull('deleted')
            ->value('so_detail_id');

        if (empty($soDetailId)) {
            throw new JournalValidationException(
                'Baris retur ' . $productName . ' pada sales return ' . $return->return_number
                . ' tidak memiliki so_detail_id, HPP tidak dapat ditentukan.'
            );
        }

        $doDetail = DB::table('delivery_order_detail as d')
            ->join('delivery_order_header as h', 'h.id', '=', 'd.do_id')
            ->select(['d.id', 'd.product_id', 'd.uom', 'd.qty', 'd.do_id'])
            ->where('d.so_detail_id', $soDetailId)
            ->whereNull('d.deleted')
            ->whereNull('h.deleted')
            ->where('h.status', '!=', 'CANCELED')
            ->orderBy('d.id', 'asc')
            ->first();

        if (empty($doDetail)) {
            throw new JournalValidationException(
                'Delivery Order asal untuk ' . $productName . ' pada sales return ' . $return->return_number
                . ' tidak ditemukan, HPP tidak dapat ditentukan.'
            );
        }

        return $doDetail;
    }

    /**
     * HPP per unit dari product_uom_cost, urutan lookup sama persis dengan
     * DeliveryOrderJournalService::resolveCost supaya angkanya sama dengan yang
     * diakui jurnal DO.
     */
    protected function resolveCost($productId, $uom, $productName = '')
    {
        $row = DB::table('product_uom_cost')
            ->where('product', $productId)
            ->where('unit_id', $uom)
            ->where('is_active', 1)
            ->orderByDesc('date_start')
            ->orderByDesc('id')
            ->first();

        if (empty($row)) {
            $row = DB::table('product_uom_cost')
                ->where('product', $productId)
                ->where('is_active', 1)
                ->orderByDesc('date_start')
                ->orderByDesc('id')
                ->first();
        }

        if (empty($row) || $this->validator->toAmount($row->cost) <= self::EPSILON) {
            throw new JournalValidationException(
                'HPP untuk ' . $productName . ' (unit ' . $uom . ') belum tersedia, jurnal tidak dapat dibuat. '
                . 'Lengkapi data product_uom_cost untuk produk tersebut.'
            );
        }

        return $this->validator->toAmount($row->cost);
    }

    /**
     * Resolusi akun per role, disimpan agar tidak berulang untuk tiap baris.
     * Account mapping rules tidak punya kolom product_category_id pada tabel
     * produk, sehingga scope kategori diteruskan apa adanya oleh resolver.
     */
    protected function resolveAccount(&$cache, $role)
    {
        if (! isset($cache[$role])) {
            $cache[$role] = $this->resolver->resolve(self::TRANSACTION_TYPE, $role);
        }

        return $cache[$role];
    }

    protected function getProductNames($lines)
    {
        $ids = [];
        foreach ($lines as $line) {
            $ids[] = (int) $line->product_id;
        }

        $rows = DB::table('product')
            ->select(['id', 'name'])
            ->whereIn('id', array_values(array_unique($ids)))
            ->get();

        $names = [];
        foreach ($rows as $row) {
            $names[$row->id] = $row->name;
        }

        return $names;
    }
}
