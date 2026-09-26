<?php

namespace App\Services\Accounting;

use App\Models\Transaction\DeliveryOrderHeader;
use App\Models\Transaction\JournalHeader;
use Illuminate\Support\Facades\DB;

/**
 * Integrasi Journal Engine dengan Delivery Order.
 *
 * HPP (harga pokok penjualan) dihitung per baris detail DO lewat
 * ProductCostResolver. product_uom_cost menyimpan cost dalam satuan besar,
 * sehingga cost dibagi dengan rantai konversi product_uom bila baris DO
 * memakai satuan yang lebih kecil. Bila cost atau rantai konversinya tidak
 * ada, proses berhenti.
 *
 * Jurnal yang dihasilkan:
 *   1. journal_headers  : 1 baris, status DRAFT, reference ke DO
 *   2. journal_details  : baris 1 debit akun COGS, baris 2 credit akun INVENTORY
 */
class DeliveryOrderJournalService
{
    const REFERENCE_TYPE = 'DELIVERY_ORDER';
    const TRANSACTION_TYPE = 'DELIVERY_ORDER';
    const ROLE_COGS = 'COGS';
    const ROLE_INVENTORY = 'INVENTORY';
    const EPSILON = 0.001;

    const STATUS_POSTABLE = ['CONFIRMED', 'PACKED'];

    protected $journalService;
    protected $postingService;
    protected $validator;
    protected $resolver;
    protected $costResolver;

    public function __construct()
    {
        $this->journalService = new JournalService();
        $this->postingService = new JournalPostingService();
        $this->validator = new JournalValidator();
        $this->resolver = new AccountMappingResolver();
        $this->costResolver = new ProductCostResolver();
    }

    public function postFromDeliveryOrder($doId, $userId = null)
    {
        $do = DeliveryOrderHeader::find($doId);
        if (empty($do)) {
            throw new JournalValidationException('Delivery order tidak ditemukan.');
        }

        $this->assertPostableStatus($do);

        // Idempotent: DO yang sudah memiliki jurnal aktif tidak digandakan.
        $existing = JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $do->id)
            ->whereIn('status', [JournalService::STATUS_DRAFT, JournalService::STATUS_POSTED])
            ->first();
        if (! empty($existing)) {
            return $existing;
        }

        $lines = DB::table('delivery_order_detail')
            ->select(['id', 'product_id', 'uom', 'qty', 'line_no'])
            ->where('do_id', $do->id)
            ->whereNull('deleted')
            ->orderBy('line_no', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if (empty($lines)) {
            throw new JournalValidationException(
                'Detail DO ' . $do->do_number . ' tidak ada, jurnal tidak dapat dibuat.'
            );
        }

        $details = $this->buildDetails($do, $lines);
        if (empty($details)) {
            throw new JournalValidationException(
                'Total HPP DO ' . $do->do_number . ' bernilai 0, jurnal tidak dapat dibuat.'
            );
        }

        $header = [
            'journal_date' => $do->do_date,
            'reference_type' => self::REFERENCE_TYPE,
            'reference_id' => $do->id,
            'description' => 'Delivery Order ' . $do->do_number,
            'details' => $details,
        ];

        $journal = $this->journalService->create($header, true);

        return $this->postingService->post($journal->id, $userId);
    }

    /**
     * Jurnal terakhir yang merujuk DO, dipakai untuk validasi hapus.
     * Mengembalikan null apabila DO belum pernah dijurnalkan.
     */
    public function getJournalForDeliveryOrder($doId)
    {
        if (empty($doId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $doId)
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
    public function getActiveJournal($doId)
    {
        if (empty($doId)) {
            return null;
        }

        return JournalHeader::where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $doId)
            // Jurnal reversal mewarisi reference_type dan reference_id, jadi harus dikecualikan.
            // Kalau tidak, reversal akan dianggap jurnal aktif sehingga hapus tetap terkunci
            // dan posting ulang justru mengembalikan jurnal reversal.
            ->whereNull('reversal_of_id')
            ->whereIn('status', [JournalService::STATUS_DRAFT, JournalService::STATUS_POSTED])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * DO hanya boleh dijurnalkan pada status CONFIRMED atau PACKED.
     */
    protected function assertPostableStatus($do)
    {
        if (! in_array($do->status, self::STATUS_POSTABLE, true)) {
            throw new JournalValidationException(
                'DO ' . $do->do_number . ' berstatus ' . $do->status
                . '. Hanya DO berstatus CONFIRMED atau PACKED yang dapat dijurnalkan.'
            );
        }
    }

    /**
     * Menghitung HPP seluruh baris lalu menyusun detail jurnal.
     * Baris dikelompokkan berdasarkan pasangan akun COGS dan INVENTORY hasil
     * resolusi mapping, sehingga normally tetap menghasilkan 2 baris detail.
     */
    protected function buildDetails($do, $lines)
    {
        $productNames = $this->getProductNames($lines);

        $groups = [];
        foreach ($lines as $line) {
            $qty = $this->validator->toAmount($line->qty);
            if ($qty <= self::EPSILON) {
                continue;
            }

            $productId = (int) $line->product_id;
            $cost = $this->resolveCost($productId, $line->uom, $productNames);
            $amount = $this->validator->toAmount($qty * $cost);

            $accountCogs = $this->resolver->resolve(
                self::TRANSACTION_TYPE,
                self::ROLE_COGS,
                null,
                $do->warehouse_id
            );
            $accountInventory = $this->resolver->resolve(
                self::TRANSACTION_TYPE,
                self::ROLE_INVENTORY,
                null,
                $do->warehouse_id
            );

            $key = $accountCogs . '|' . $accountInventory;
            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'cogs' => $accountCogs,
                    'inventory' => $accountInventory,
                    'amount' => 0,
                ];
            }
            $groups[$key]['amount'] += $amount;
        }

        $details = [];
        $lineNo = 1;
        foreach ($groups as $group) {
            $amount = $this->validator->toAmount($group['amount']);
            if ($amount <= self::EPSILON) {
                continue;
            }

            $details[] = [
                'line_no' => $lineNo,
                'account_id' => $group['cogs'],
                'description' => 'HPP DO ' . $do->do_number,
                'debit' => $amount,
                'credit' => 0,
            ];
            $lineNo++;

            $details[] = [
                'line_no' => $lineNo,
                'account_id' => $group['inventory'],
                'description' => 'Persediaan keluar DO ' . $do->do_number,
                'debit' => 0,
                'credit' => $amount,
            ];
            $lineNo++;
        }

        return $details;
    }

    /**
     * HPP per baris, didelegasikan ke ProductCostResolver.
     *
     * product_uom_cost menyimpan cost dalam satuan besar, sehingga cost harus
     * dibagi dengan rantai konversi product_uom bila baris dokumen memakai
     * satuan yang lebih kecil. Tidak ada lagi fallback ke cost satuan lain
     * tanpa konversi karena itu membuat HPP kelipatan dari satuan sebenarnya.
     */
    protected function resolveCost($productId, $uom, $productNames = [])
    {
        return $this->costResolver->resolve($productId, $uom, $productNames);
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
