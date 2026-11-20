# Dashboard Utama (`/dashboard`)

Dokumen ini merangkum halaman dashboard utama: dari mana datanya, bagaimana tiap kartu dihitung, dan apa yang diubah pada kartu **Total Laba Bruto**. Dashboard retur (`/dashboard/return`) punya controller dan view sendiri, jadi tidak dibahas di sini.

---

## 1. File terkait

| Bagian | File |
| --- | --- |
| Route halaman | `routes/web.php:164` → `Route::get('/dashboard', [DashboardController::class, 'index'])` |
| Route API | `routes/web.php:441-443` (`getGrafikPenjualan`, `getInvoiceOutstanding`, `getMapVisit`) |
| Controller | `app/Http/Controllers/web/DashboardController.php` |
| View halaman | `resources/views/web/dashboard/index.blade.php` |
| Partial kartu | `resources/views/web/dashboard/card_summary.blade.php` |
| Partial tabel SO | `resources/views/web/dashboard/list_sales_order.blade.php` |
| JS | `public/assets/js/controllers/dashboard.js`, `notification.js` |
| Layout | `resources/views/web/template/main.blade.php` |

---

## 2. Alur render

`DashboardController::index()`:

1. `$year` diambil dari query string `?year=`, default tahun berjalan.
2. Mengisi data pendukung: `username`, `akses`, `data_province`, `data_salesman`.
3. Menghitung kartu:
   - `summary_po` ← `getSummaryPO($year)`
   - `summary_so` ← `getSummarySO($year)`
   - `summary_invoice` ← `getSummaryInvoice($year)`
   - `summary_gross_profit` ← `getSummaryGrossProfit($year)`
4. Render `web.dashboard.index`.

Di view:

- Role `partnership` hanya melihat kartu sambutan (tanpa kartu KPI, grafik, peta, tabel).
- Role lain melihat: header + pemilih tahun → `card_summary` → `list_sales_order` → grafik penjualan → peta kunjungan salesman → tabel invoice outstanding.
- Kartu KPI, tabel invoice outstanding, dan grafik disembunyikan untuk role selain `superadmin` (`d-none`).

---

## 3. Basis data yang dipakai bersama

Semua kartu penjualan memakai konstanta yang sama:

```php
const STATUS_INVOICE = ['PACKED', 'POSTED', 'PARTIAL PAID', 'PAID', 'DRAFT'];
```

Filter lain yang konsisten dipakai:

- `sales_invoice_header.deleted IS NULL`
- `YEAR(invoice_date) = $year`
- `company_id = session('id_company')` (bila session terisi)
- `sales_invoice_detail.deleted IS NULL` dan `flag_cancel IS NULL` untuk perhitungan HPP.

Tujuannya agar angka antar kartu sebanding: Total Penjualan, Laba Bruto, grafik, dan daftar outstanding memakai populasi invoice yang sama. Invoice `CANCELED`/terhapus tidak ikut.

---

## 4. Perhitungan tiap kartu

### 4.1 Total Pesanan — `getSummarySO()`

- Basis: `sales_order_headers` yang **sudah terbit invoice** pada tahun tersebut.
- Filter: `total_amount > 0`, `deleted IS NULL`, status `confirmed`/`draft`, dan `EXISTS` invoice pada `sales_invoice_header` dengan `sales_order = sales_order_headers.id`.
- Nilai: `SUM(total_amount)` untuk nominal, `COUNT(*)` untuk jumlah pesanan.
- Status `correction` tidak dihitung.

### 4.2 Total Penjualan — `getSummaryInvoice()`

Semua query memakai basis `STATUS_INVOICE`.

| Field | Rumus | Dipakai di |
| --- | --- | --- |
| `summary_gross` | `SUM(subtotal)` | Angka utama kartu (dasar DPP, sebelum PPN) |
| `summary_netto` | `SUM(total_amount)` | Meta kartu ("Netto") |
| `jumlah` | `COUNT(*)` | Meta kartu ("N invoice") |
| `summary` | `SUM(total_amount - amount_paid)` untuk invoice yang sisanya > 0 | Kartu Tagihan Outstanding |
| `jumlah_outstanding` | jumlah invoice dengan sisa > 0 | Meta kartu outstanding |

Catatan: `getSummaryInvoice()` masih menghitung `total_cogs`, tetapi hasilnya **tidak lagi dipakai** kartu mana pun (lihat bagian 5).

### 4.3 Tagihan Outstanding — `getSummaryInvoice()` + `getInvoiceOutstanding()`

- Header: invoice dengan `(total_amount - amount_paid) > 0`.
- Tabel: `getInvoiceOutstanding()` memakai filter identik, di-join ke `customer`, mendukung pencarian DataTables (customer code/nama, status, tanggal) dan urut berdasarkan `id`.

### 4.4 Total Laba Bruto — `getSummaryGrossProfit()`

Rumus:

```
laba bruto = penjualan neto − HPP
penjualan neto = SUM(total_amount) − SUM(tax_amount WHERE type_tax = 'include')
```

Alasannya: `total_amount` adalah nilai invoice setelah diskon header, dan bila PPN menempel di harga (`type_tax = 'include'`) maka PPN harus dikeluarkan karena HPP tidak memuat PPN. Untuk `type_tax = 'exclude'`, PPN memang tidak ada di `total_amount`, jadi tidak perlu dikoreksi.

Rincian per baris (`sales_invoice_detail`):

```sql
COALESCE(
    SELECT puc.cost / COALESCE(NULLIF(pucu.nilai_konversi_terkecil, 0), 1)
    FROM product_uom_cost puc
    LEFT JOIN product_uom AS pucu ON pucu.id = puc.product_uom
    WHERE puc.product = sid.product_id AND puc.is_active = 1 [AND puc.company_id = ?]
    ORDER BY (puc.date_start IS NOT NULL AND puc.date_start <= sih.invoice_date) DESC,
             puc.date_start DESC, puc.id DESC
    LIMIT 1
, 0)
```

Poin penting:

1. **Cost efektif per tanggal invoice.** Cost yang `date_start <= invoice_date` diutamakan lewat `ORDER BY` pertama.
2. **Fallback cost terbaru.** Bila belum ada cost pada tanggal invoice (cost baru diinput belakangan), dipakai cost terbaru sehingga baris tidak dihitung HPP = 0. Ini penyebab utama angka lama terlalu besar: 18.390 baris (revenue ±2,15 M) sebelumnya tidak punya cost efektif.
3. **Konversi satuan.** `puc.cost` dibagi `product_uom.nilai_konversi_terkecil` milik satuan cost, lalu dikali `nilai_konversi_terkecil` satuan yang dijual (`sod.unit`). Ini memperbaiki 42 baris cost yang tersimpan di satuan kecil (contoh: product 21 — cost 30.536 di unit 4 vs 366.439 di unit 1).
4. **LEFT JOIN.** `sales_order_details` dan `product_uom` di-left-join supaya baris penjualan tidak hilang dari perhitungan; `COALESCE(..., 1)` menjaga konversi tetap 1 bila relasi tidak ditemukan.
5. **PPN dipisah** dari `SUM(tax_amount)` dengan syarat `type_tax = 'include'`.

Output:

```php
[
    'revenue_net'  => $summary_netto - $tax_include,
    'total_cogs'   => $total_cogs,
    'gross_profit' => $revenue_net - $total_cogs,
]
```

Di kartu (`card_summary.blade.php`):

```
Angka utama : IDR {gross_profit}
Meta        : Penjualan neto {revenue_net} · HPP {total_cogs}
```

---

## 5. Riwayat perbaikan kartu Laba Bruto

Sebelumnya kartu memakai `$gross_profit = summary_so['summary'] - summary_po['summary_po']`, yaitu total SO dikurangi total PO. Basisnya tidak sebanding: SO bisa belum menjadi invoice, PO bukan HPP, dan PPN ikut terhitung.

| Aspek | Sebelum | Sesudah |
| --- | --- | --- |
| Sumber | `summary_so − summary_po` | `getSummaryGrossProfit($year)` |
| Basis | SO vs PO (campur dokumen) | Invoice aktif (`STATUS_INVOICE`) |
| HPP | Tidak ada HPP eksplisit | HPP per baris invoice |
| PPN | Ikut terhitung | Dikeluarkan (`type_tax = 'include'`) |
| Cost hilang | — | Fallback ke cost terbaru |
| Konversi satuan | — | `product_uom.nilai_konversi_terkecil` |

Hasil ukur data lokal tahun 2026 saat perbaikan (6.907 invoice aktif, 36.838 baris detail):

| Metrik | Nilai |
| --- | --- |
| Penjualan neto (tanpa PPN) | Rp 4.185.748.218,85 |
| HPP | Rp 4.015.887.972,58 |
| **Laba bruto** | **Rp 169.860.246,27** |
| Angka rumus lama | Rp 2.419.606.792 |

Sebagai pembanding data mentah 2026: `SUM(subtotal)` = 4.317.219.259, `SUM(tax_amount)` = 111.281.189, `SUM(total_amount)` = 4.297.029.406. Waktu eksekusi query ±0,52 detik.

Tidak ada logic lain yang diubah: `getSummarySO()`, `getSummaryPO()`, `getSummaryInvoice()`, grafik, peta, dan tabel outstanding tetap seperti sebelumnya.

---

## 6. Catatan & limitasi

- **Retur penjualan belum dikurangkan.** `sales_invoice_detail.return_qty` tidak dikurangkan dari qty penjualan maupun HPP, agar basis laba bruto sama dengan kartu Total Penjualan. Bila ingin laba neto retur, kurangi qty dengan `return_qty` (dan nilai penjualan dengan porsi retur).
- **Credit note** (`credit_note_amount`) juga belum memengaruhi laba bruto, sama seperti kartu penjualan lain.
- **Kualitas HPP bergantung master cost.** Fallback cost terbaru membuat angka tidak nol, tetapi bila cost master berubah setelah invoice, HPP historis ikut memakai cost baru. Untuk laporan akuntansi final, cocokkan dengan jurnal HPP di GL (`summary_journal_engine_2026-09-26.md`).
- **Invoice `DRAFT` dihitung** karena masuk `STATUS_INVOICE`. Bila ingin hanya invoice yang sudah dikirim/dibayar, ubah konstanta — tetapi kartu penjualan lain juga akan berubah.
- **PPN.** Hanya `type_tax = 'include'` yang dikeluarkan. Bila ada data lama dengan `tax_amount` terisi namun `type_tax` kosong, PPN tersebut masih ikut di `total_amount`.
- **Performa.** Subquery cost dijalankan per baris detail (±0,5 detik untuk setahun data lokal). Bila volume tumbuh jauh, pertimbangkan tabel snapshot HPP per invoice.

---

## 7. Cara verifikasi

```bash
php -l app/Http/Controllers/web/DashboardController.php

php artisan tinker
>>> session(['id_company' => 1]); // atau null untuk semua company
>>> app(App\Http\Controllers\web\DashboardController::class)->getSummaryGrossProfit(2026);
```

Cek silang manual:

```sql
-- penjualan neto tanpa PPN (header), sama seperti di controller
SELECT
  (SELECT SUM(total_amount) FROM sales_invoice_header
    WHERE deleted IS NULL AND YEAR(invoice_date) = 2026
      AND status IN ('PACKED','POSTED','PARTIAL PAID','PAID','DRAFT')) AS netto,
  (SELECT SUM(sid.tax_amount) FROM sales_invoice_detail sid
     JOIN sales_invoice_header sih ON sih.id = sid.invoice_id
    WHERE sih.deleted IS NULL AND YEAR(sih.invoice_date) = 2026
      AND sih.status IN ('PACKED','POSTED','PARTIAL PAID','PAID','DRAFT')
      AND sid.deleted IS NULL AND sid.flag_cancel IS NULL
      AND sid.type_tax = 'include') AS tax_include;
-- revenue_net = netto - tax_include
```

Lalu bandingkan `gross_profit` dengan tampilan kartu di `/dashboard?year=2026`.
