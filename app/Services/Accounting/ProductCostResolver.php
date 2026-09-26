<?php

namespace App\Services\Accounting;

use Illuminate\Support\Facades\DB;

/**
 * Resolver HPP per satuan untuk Journal Engine.
 *
 * Konvensi data produk: product_uom_cost SELALU menyimpan cost dalam satuan
 * besar (satuan dengan level tertinggi pada rantai product_uom). Case cost
 * disimpan di satuan lain tidak boleh dipakai mentah, karena angkanya akan
 * menjadi kelipatan dari satuan yang dimaksud.
 *
 * product_uom menyimpan rantai konversi dari satuan terkecil ke terbesar:
 *   unit_dasar = 9 (PCS), unit_tujuan = 8 (RENCENG), nilai_konversi = 12
 *   artinya 1 PCS = 12 RENCENG
 * Karena itu untuk memakai cost satuan besar pada satuan yang lebih kecil,
 * rantai walked dari satuan yang diminta ke satuan cost, faktor konversi
 * dikalikan, lalu cost DIBAGI dengan hasil perkalian tersebut.
 *
 * Contoh produk 142 (KUAH BAKSO 8GR), cost 213.825 per CARTON, DO memakai RENCENG:
 *   1 PCS = 12 RENCENG, 1 RENCENG = 10 PACK, 1 PACK = 6 CARTON
 *   total konversi = 12 * 10 * 6 = 60
 *   cost per RENCENG = 213.825 / 60 = 3.563,75
 *
 * Bila tidak ada rantai konversi dari satuan yang diminta ke satuan cost,
 * proses dihentikan. HPP tidak boleh ditebak maupun dipakai tanpa konversi.
 */
class ProductCostResolver
{
    const EPSILON = 0.001;

    /**
     * Batas langkah penjelajahan rantai, mencegah loop tak berujung bila data
     * product_uom membentuk siklus.
     */
    const MAX_HOPS = 10;

    /**
     * Mengembalikan HPP per satuan $uom untuk $productId.
     *
     * @param  int  $productId
     * @param  int  $uom  id unit pada baris dokumen (delivery_order_detail.uom)
     * @param  array  $productNames  pemetaan product_id => nama untuk pesan error
     * @return float
     *
     * @throws JournalValidationException
     */
    public function resolve($productId, $uom, array $productNames = [])
    {
        $productId = (int) $productId;
        $uom = (int) $uom;

        if ($productId <= 0) {
            throw new JournalValidationException(
                'Produk pada baris dokumen tidak memiliki id, HPP tidak dapat dihitung.'
            );
        }

        $name = $productNames[$productId] ?? ('produk #' . $productId);

        if ($uom <= 0) {
            throw new JournalValidationException(
                'Satuan pada baris ' . $name . ' tidak valid, HPP tidak dapat dihitung.'
            );
        }

        $rows = $this->loadCostRows($productId);

        if (count($rows) === 0) {
            throw new JournalValidationException(
                'product_uom_cost untuk ' . $name . ' belum ada, HPP tidak dapat dihitung. '
                . 'Tambahkan cost satuan besar produk tersebut di Master > Product > Cost.'
            );
        }

        $errors = [];
        $fallback = null;

        foreach ($rows as $row) {
            $cost = (float) $row->cost;

            if ($cost <= self::EPSILON) {
                $errors[] = 'unit ' . $row->unit_id . ' bers_cost '
                    . $row->cost . ' (nol/kosong)';
                continue;
            }

            // Satuan sama dengan satuan cost: tidak perlu konversi.
            if ((int) $row->unit_id === $uom) {
                return $this->round($cost);
            }

            $factor = $this->conversionFactor($productId, $uom, (int) $row->unit_id);

            if ($factor === null) {
                $errors[] = 'unit ' . $row->unit_id . ' tidak punya jalur konversi dari unit ' . $uom;
                continue;
            }

            // Baris pertama yang punya jalur konversi dipakai, sesuai urutan
            // tanggal terbaru dari loadCostRows().
            if ($fallback === null) {
                $fallback = $this->round($cost / $factor);
            }
        }

        if ($fallback !== null) {
            return $fallback;
        }

        throw new JournalValidationException(
            'HPP ' . $name . ' untuk satuan ' . $uom . ' tidak dapat dihitung karena: '
            . (empty($errors) ? 'tidak ada cost aktif yang cocok' : implode('; ', $errors))
            . '. Lengkapi product_uom_cost (satuan besar) dan rantai konversi product_uom '
            . 'untuk produk tersebut, atau ubah satuan pada dokumen.'
        );
    }

    /**
     * Baris cost aktif untuk produk, diurutkan tanggal terbaru.
     * Dengan begitu cost yang diakui adalah cost terakhir yang berlaku.
     */
    protected function loadCostRows($productId)
    {
        return DB::table('product_uom_cost')
            ->where('product', $productId)
            ->where('is_active', 1)
            ->orderByDesc('date_start')
            ->orderByDesc('id')
            ->get(['unit_id', 'cost', 'date_start']);
    }

    /**
     * Total faktor konversi dari $fromUom naik ke $toUom.
     * Mengembalikan null bila tidak ada rantai.
     */
    protected function conversionFactor($productId, $fromUom, $toUom)
    {
        if ((int) $fromUom === (int) $toUom) {
            return 1.0;
        }

        $chain = $this->loadChain($productId);
        $factor = 1.0;
        $current = (int) $fromUom;

        for ($hop = 0; $hop < self::MAX_HOPS; $hop++) {
            if ($current === (int) $toUom) {
                return $factor;
            }

            $step = $this->pickStep($chain, $current);

            if ($step === null) {
                return null;
            }

            $factor *= (float) $step->nilai_konversi;
            $current = (int) $step->unit_tujuan;
        }

        return $current === (int) $toUom ? $factor : null;
    }

    /**
     * Memilih satu langkah dari $current ke unit yang lebih besar.
     *
     * product_uom menyimpan baris level 1 berupa self-loop (unit_dasar sama
     * dengan unit_tujuan, konversi 1) yang hanya menandai satuan terkecil.
     * Baris semacam itu dilewati, dan konversi 1 ke unit lain juga dilewati
     * karena bukan konversi satuan melainkan penanda alias.
     */
    protected function pickStep($chain, $current)
    {
        if (! isset($chain[$current])) {
            return null;
        }

        foreach ($chain[$current] as $step) {
            if ((int) $step->unit_tujuan === $current) {
                continue;
            }
            if ((float) $step->nilai_konversi <= 1) {
                continue;
            }

            return $step;
        }

        return null;
    }

    /**
     * Rantai product_uom dikelompokkan per unit_dasar. Urutan level lalu id
     * membuat pemilihan langkah konsisten antar permintaan.
     */
    protected function loadChain($productId)
    {
        return DB::table('product_uom')
            ->where('product', $productId)
            ->whereNull('deleted')
            ->orderBy('level')
            ->orderBy('id')
            ->get(['unit_dasar', 'unit_tujuan', 'nilai_konversi', 'level', 'id'])
            ->groupBy('unit_dasar')
            ->all();
    }

    protected function round($value)
    {
        return round((float) $value, 2);
    }
}
