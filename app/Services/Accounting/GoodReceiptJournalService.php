<?php

namespace App\Services\Accounting;

use App\Models\Transaction\GoodReceipt;
use App\Models\Transaction\JournalHeader;
use Illuminate\Support\Facades\DB;

/**
 * Integrasi Journal Engine dengan Receiving (Good Receipt).
 *
 * Pengakuan barang masuk gudang. Nilai barang dihitung dari harga di Purchase
 * Order karena invoice supplier belum tentu ada saat barang tiba, sehingga
 * akun liability ke supplier TIDAK diakui di sini. Kewajiban hutang diakui
 * saat Purchase Invoice diposting lewat PurchaseInvoiceJournalService, yang
 * mendebet GRNI untuk item yang sudah diterima.
 *
 * Akun debit Inventory dan akun credit GRNI diambil dari
 * account_mapping_rules dengan transaction_type = GOODS_RECEIPT. Akun debit
 * yang sama digabung menjadi satu baris detail.
 *
 * Jurnal yang dihasilkan:
 *   1. journal_headers  : 1 baris, reference ke Receiving
 *   2. journal_details  : N baris debit Inventory (gabungan per akun)
 *                         dan 1 baris credit GRNI
 */
class GoodReceiptJournalService
{
    const REFERENCE_TYPE = 'Receiving';
    const TRANSACTION_TYPE = 'GOODS_RECEIPT';
    const ROLE_INVENTORY = 'INVENTORY';
    const ROLE_GRNI = 'GRNI';
    const EPSILON = 0.001;
    const TOLERANCE = 0.01;

    const STATUS_POSTABLE = ['open'];

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

    public function postFromGoodReceipt($grId, $userId = null)
    {
        $gr = GoodReceipt::find($grId);
        if (empty($gr) || ! empty($gr->deleted)) {
            throw new JournalValidationException('Penerimaan barang tidak ditemukan.');
        }

        $this->assertPostableStatus($gr);

        // Idempotent: penerimaan yang sudah memiliki jurnal tidak digandakan.
        $existing = $this->getActiveJournal($gr->id);
        if (! empty($existing)) {
            // Jurnal tertinggal DRAFT, misalnya posting sebelumnya gagal, lalu diposting ulang.
            if ($existing->status === JournalService::STATUS_DRAFT) {
                return $this->postingService->post($existing->id, $userId);
            }

            return $existing;
        }

        $lines = $this->getLines($gr);
        if ($lines->isEmpty()) {
            throw new JournalValidationException(
                'Detail penerimaan barang ' . $gr->gr_number . ' tidak ada, jurnal tidak dapat dibuat.'
            );
        }

        $details = $this->buildDetails($gr, $lines);
        if (empty($details)) {
            throw new JournalValidationException(
                'Total penerimaan barang ' . $gr->gr_number . ' bernilai 0, jurnal tidak dapat dibuat.'
            );
        }

        $header = [
            'journal_date' => $gr->received_date,
            'reference_type' => self::REFERENCE_TYPE,
            'reference_id' => $gr->id,
            'description' => 'Penerimaan Barang ' . $gr->gr_number,
            'details' => $details,
        ];

        $journal = $this->journalService->create($header, true);

        return $this->postingService->post($journal->id, $userId);
    }

    /**
     * Jurnal terakhir yang merujuk penerimaan barang, dipakai untuk validasi hapus.
     * Mengembalikan null apabila penerimaan belum pernah dijurnalkan.
     */
    public function getJournalForGoodReceipt($grId)
    {
        if (empty($grId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $grId)
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
    public function getActiveJournal($grId)
    {
        if (empty($grId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $grId)
            // Jurnal reversal mewarisi reference_type dan reference_id, jadi harus dikecualikan.
            // Kalau tidak, reversal akan dianggap jurnal aktif sehingga hapus tetap terkunci
            // dan posting ulang justru mengembalikan jurnal reversal.
            ->whereNull('reversal_of_id')
            ->whereIn('status', [JournalService::STATUS_DRAFT, JournalService::STATUS_POSTED])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Hanya penerimaan barang berstatus open yang boleh dijurnalkan.
     */
    protected function assertPostableStatus($gr)
    {
        if (! in_array($gr->status, self::STATUS_POSTABLE, true)) {
            throw new JournalValidationException(
                'Penerimaan barang ' . $gr->gr_number . ' berstatus ' . $gr->status
                . '. Hanya penerimaan barang berstatus open yang dapat dijurnalkan.'
            );
        }
    }

    /**
     * Baris detail penerimaan. Nominal diambil dari harga di Purchase Order
     * (purchase_order_detail.purchase_price x qty_received) karena invoice
     * supplier belum tentu ada. Bila baris PO tidak ditemukan, nilai
     * subtotal yang tersimpan di dipakai sebagai cadangan.
     */
    protected function getLines($gr)
    {
        return DB::table('goods_receipt_detail as d')
            ->select([
                'd.id',
                'd.product',
                'd.purchase_order_detail',
                'd.qty_received',
                'd.subtotal',
                'pod.purchase_price',
                'p.id as product_id',
                'p.name as product_name',
            ])
            ->leftJoin('purchase_order_detail as pod', 'pod.id', '=', 'd.purchase_order_detail')
            ->leftJoin('product as p', 'p.id', '=', 'd.product')
            ->where('d.goods_receipt_header', $gr->id)
            ->whereNull('d.deleted')
            ->orderBy('d.id', 'asc')
            ->get();
    }

    /**
     * Menyusun detail jurnal: satu baris debit Inventory per akun hasil
     * resolusi mapping, digabung bila beberapa baris memakai akun yang sama,
     * ditambah satu baris credit GRNI.
     */
    protected function buildDetails($gr, $lines)
    {
        $groups = [];
        $cache = [];
        $totalDebit = 0;

        foreach ($lines as $line) {
            $amount = $this->resolveAmount($line);
            if ($amount <= self::EPSILON) {
                continue;
            }

            if (empty($line->product_id)) {
                throw new JournalValidationException(
                    'Baris detail penerimaan barang ' . $gr->gr_number
                    . ' dengan product #' . $line->product . ' tidak memiliki data product,'
                    . ' jurnal tidak dapat dibuat.'
                );
            }

            $accountId = $this->resolveAccount($cache, self::ROLE_INVENTORY, $gr->warehouse);

            if (! isset($groups[$accountId])) {
                $groups[$accountId] = ['amount' => 0];
            }
            $groups[$accountId]['amount'] += $amount;
            $totalDebit += $amount;
        }

        $totalDebit = $this->validator->toAmount($totalDebit);
        $totalGrni = $this->validator->toAmount($gr->total_amount);

        if ($totalGrni <= self::EPSILON) {
            throw new JournalValidationException(
                'Total penerimaan barang ' . $gr->gr_number . ' bernilai 0, jurnal tidak dapat dibuat.'
            );
        }

        // Mencegah jurnal tidak balanced karena selisih pembulatan atau total header yang salah input.
        if (abs($totalDebit - $totalGrni) > self::TOLERANCE) {
            throw new JournalValidationException(
                'Total detail penerimaan barang ' . $gr->gr_number . ' sebesar '
                . $this->formatAmount($totalDebit) . ' tidak sama dengan total header sebesar '
                . $this->formatAmount($totalGrni) . ', jurnal tidak dapat dibuat.'
            );
        }

        $accountGrni = $this->resolveAccount($cache, self::ROLE_GRNI, $gr->warehouse);

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
                'description' => 'Persediaan pembelian ' . $gr->gr_number,
                'debit' => $amount,
                'credit' => 0,
            ];
            $lineNo++;
        }

        $details[] = [
            'line_no' => $lineNo,
            'account_id' => $accountGrni,
            'description' => 'GRNI ' . $gr->gr_number,
            'debit' => 0,
            'credit' => $totalGrni,
        ];

        return $details;
    }

    /**
     * Nilai barang diterima berdasarkan harga di Purchase Order.
     * Cadangan memakai subtotal yang tersimpan bila baris PO tidak ada atau
     * harganya 0.
     */
    protected function resolveAmount($line)
    {
        $price = $this->validator->toAmount($line->purchase_price);
        $qty = $this->validator->toAmount($line->qty_received);

        if ($price > self::EPSILON && $qty > self::EPSILON) {
            return $this->validator->toAmount($price * $qty);
        }

        return $this->validator->toAmount($line->subtotal);
    }

    /**
     * Resolusi akun per role, disimpan agar tidak berulang untuk tiap baris.
     */
    protected function resolveAccount(&$cache, $role, $warehouseId = null)
    {
        $key = $role.'|'.$warehouseId;
        if (! isset($cache[$key])) {
            $cache[$key] = $this->resolver->resolve(
                self::TRANSACTION_TYPE,
                $role,
                null,
                $warehouseId
            );
        }

        return $cache[$key];
    }

    protected function formatAmount($value)
    {
        return number_format((float) $value, 2, ',', '.');
    }
}
