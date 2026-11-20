# Report Penjualan (`/report/report_penjualan`)

Dokumen ini merangkum halaman **Report Penjualan**: filter, keempat tab, endpoint API, cara kuantitas ditampilkan, serta kolom **QTY (IN PCS)** dan **QTY (IN KARTON)** yang ditambahkan belakangan.

---

## 1. File terkait

| Bagian | File |
| --- | --- |
| Route halaman | `routes/web.php:432` → `web\report\ReportPenjualanController::index` |
| Route API | `routes/web.php:912-914` (`getData`, `getDataDetailDiskon`, `getDataPenjualanPerProduct`) |
| Controller halaman | `app/Http/Controllers/web/report/ReportPenjualanController.php` |
| Controller API | `app/Http/Controllers/api/report/ReportPenjualanController.php` (di-alias `ReportReportPenjualanController` di routes) |
| View utama | `resources/views/web/report_penjualan/index.blade.php` |
| Partial tab | `report_per_penjual.blade.php`, `report_per_barang.blade.php`, `report_detail_diskon.blade.php` |
| JS | `public/assets/js/controllers/report/report_penjualan.js` |

---

## 2. Filter

| Field | ID | Keterangan |
| --- | --- | --- |
| Tanggal mulai | `#filter-start-date` | Default hari ini |
| Tanggal akhir | `#filter-end-date` | Default hari ini |
| Principal | `#filter-principal` | Berisi vendor; **terkunci** bila `session('vendor_id')` terisi (akun principal hanya melihat datanya sendiri) |
| Tipe Report | `#select-option-report` | `PENJUALAN`, `PENJUALAN PER PENJUAL`, `PENJUALAN PER BARANG`, `PENJUALAN DETAIL DISKON` |
| Satuan | `#filter-satuan` | `default` (unit jual), `terkecil`, `terbesar`; hanya tampil saat tab **Per Barang** aktif |

Tombol **Filters** memanggil `ReportPenjualan.filter()`, yang memilih fungsi DataTable sesuai tipe report. Saat halaman dimuat (`$(function(){...})`) keempat tabel sudah di-inisialisasi sekaligus, jadi tab yang belum dibuka pun sudah mengambil data.

Basis query `getData` dan `getDataPenjualanPerProduct`:

- `sales_invoice_detail` → `sales_order_details` → `sales_order_headers` → `sales_invoice_header`
- `sih.invoice_date BETWEEN date_start AND date_end`, `sih.deleted IS NULL`, `m.deleted IS NULL`, `m.total_amount > 0`
- `sid.deleted IS NULL`, `sid.flag_cancel IS NULL`
- `m.company_id = session('id_company')` bila session terisi
- `p.vendor = principal` bila principal dipilih

Baris digabung per **invoice + SO + produk + satuan** (`GROUP BY sid.invoice_id, sod.sales_order_id, sod.product_id, sod.unit`), sehingga satu produk yang dijual dalam dua satuan berbeda muncul sebagai dua baris.

Status invoice **tidak** difilter di laporan ini (beda dengan card dashboard yang memakai `STATUS_INVOICE`).

---

## 3. Tab dan endpoint

| Tab | Tabel | Endpoint | Controller |
| --- | --- | --- | --- |
| All Report Penjualan | `#table-data` | `POST api/report/report_penjualan/getData` | `getData()` |
| Report Penjualan Per Penjual | `#table-data-per-penjual` | `POST api/report/report_piutang/getData` (`types=per-penjual`) | `ReportPiutangController` |
| Report Penjualan Per Barang | `#table-data-per-barang` | `POST api/report/report_penjualan/getDataPenjualanPerProduct` | `getDataPenjualanPerProduct()` |
| Report Penjualan Detail Diskon | `#table-data-detail-diskon` | `POST api/report/report_penjualan/getDataDetailDiskon` | `getDataDetailDiskon()` |

Catatan: tab **Per Penjual** sebenarnya menampilkan laporan piutang per penjual (kolom total piutang, dibayar, sisa), bukan penjualan.

`getDataDetailDiskon()` memanggil `getData()` lalu memperkaya baris dengan `line_discount_1..5` berdasarkan `sales_order_promo` / `product_promo_item`:

- Beban `PRINCIPAL` + kategori `DISC PROMO` → line 1
- `PRINCIPAL` + kategori syarat/strata → line 2
- `PRINCIPAL` + `DISC CHANNEL` atau ada additional disc → line 3
- Beban distributor + additional disc → line 4
- Beban distributor lainnya → line 5
- Diskon produk (`sid.discount`) ditambahkan ke line 3

Kolom `LINE DISCOUNT 4` dan `LINE DISCOUNT 5` hanya ada di tab Detail Diskon.

---

## 4. Konvensi kuantitas

### 4.1 `QTYSOLD` (pecahan per level satuan)

`qty_sold` dibentuk di SQL berdasarkan jumlah level `product_uom` per produk, dipisah titik:

| Level satuan | Format | Contoh |
| --- | --- | --- |
| 4 | `karton.renteng.pack.pcs` | `1.2.3.4` |
| 3 | `karton.pack.pcs` | `0.1.0` |
| 2 | `karton.pcs` | `2.5` |
| 1 | `pcs` | `12` |

Contoh nyata: `20.0.0` berarti 20 karton; `0.1.0` berarti 1 pack.

### 4.2 Kolom pecahan `KARTON`, `RENTENG`, `PACK`, `PCS`

Kolom-kolom ini **memecah** string `qty_sold`, bukan total:

| Kolom | Isi |
| --- | --- |
| `KARTON` | Bagian pertama (jumlah karton utuh) |
| `RENTENG` | Bagian kedua, hanya untuk produk 4 level |
| `PACK` | Bagian pack |
| `PCS` | Sisa dalam satuan terkecil **setelah** dikurangi karton/pack — bukan total pcs |

### 4.3 Kolom baru `QTY (IN PCS)` dan `QTY (IN KARTON)`

Ditambahkan setelah `PCS` pada tab **All** dan **Detail Diskon** (header `<th>` juga ditambah di `index.blade.php` dan `report_detail_diskon.blade.php`). Keduanya menampilkan **total** kuantitas:

| Kolom | Field API | Rumus |
| --- | --- | --- |
| `QTY (IN PCS)` | `qty_pcs` | `ROUND(qty × nilai_konversi_terkecil satuan jual, 2)` |
| `QTY (IN KARTON)` | `qty_karton` | `ROUND(qty_pcs ÷ konversi level terbesar, 2)` |

Detail implementasi di `ReportPenjualanController::getData()`:

```php
$kartonConvSql = "CASE uomf.total_level
        WHEN 4 THEN uomf.l4
        WHEN 3 THEN uomf.l3
        WHEN 2 THEN uomf.l2
    END";
$qtyPcsSql = "CASE WHEN uomu.conv IS NULL THEN NULL ELSE ROUND($q, 2) END as qty_pcs";
$qtyKartonSql = "IFNULL(ROUND($q / NULLIF($kartonConvSql, 0), 2), 0) as qty_karton";
```

- `$q = (g.qty * uomu.conv)`; `uomu` = konversi satuan yang dijual ke satuan terkecil, `uomf` = level konversi per produk.
- Level terbesar dianggap **karton**, mengikuti urutan `qty_sold`. Produk 1 level tidak punya karton → `QTY (IN KARTON)` = 0 (sama seperti kolom `KARTON` lama).
- Field tambahan `karton_conv` ikut dikirim sebagai dasar konversi (dipakai untuk baris retur), tidak ditampilkan di tabel.
- Nilai dikirim sebagai angka (bukan string berformat) agar tetap numerik saat diekspor ke Excel. DataTables menampilkannya apa adanya, mis. `1000` atau `0.33`.

Baris retur (bila muncul) diberi nilai negatif, konsisten dengan `qty_sold` dan `gross_amount`:

```php
$returnRow['qty_pcs']    = -1 * (float) $value['qty_return'];
$returnRow['qty_karton'] = !empty($value['karton_conv'])
    ? -1 * round((float) $value['qty_return'] / (float) $value['karton_conv'], 2)
    : 0;
```

### 4.4 Contoh hasil uji (data lokal)

| Produk | `QTYSOLD` | Konversi karton | `QTY (IN PCS)` | `QTY (IN KARTON)` |
| --- | --- | --- | --- | --- |
| POTATO-Q KOREAN BBQ 5x10 | `20.0.0` | 50 | 1.000 | 20 |
| Kinder Joy T24 | `0.1.0` | 72 | 24 | 0,33 |
| Produk level 4 (conv 192) | `0.1.0.0` | 192 | 24 | 0,13 |

Konsistensi `pcs ÷ konversi = karton` diperiksa untuk seluruh baris 1–20 November 2026 (3.926 baris): tidak ada selisih di luar pembulatan 2 desimal.

---

## 5. Kolom tabel utama

Urutan kolom yang benar-benar dirender berasal dari definisi `columns` di JS (atribut `title` menimpa `<th>`):

1. No
2. `PRINCIPAL`, `VENDOR`, `KODE PRODUK`, `KODE PRODUK PRINCIPAL`, `NAMA PRODUK`
3. `KATEGORI`, `BRAND`, `SUB BRAND`, `PACKAGING` (selalu kosong)
4. `KODE CUSTOMER`, `NAMA CUSTOMER`, `KECAMATAN`, `KABUPATEN/KOTA`, `KELURAHAN`, `ALAMAT`, `NAMA PASAR`, `CHANNEL`
5. `KODE SALESMAN` (`users.name`), `NAMA SALESMAN` (`karyawan.nama_lengkap`), `CYCLE KUNJUNGAN` (`daily_visit.cicle_type`), `DAY KUNJUNGAN`, `TANGGAL`, `BULAN`, `TAHUN`
6. `NOMOR INVOICE`, `QTYSOLD`, `KARTON`, `RENTENG`, `PACK`, `PCS`, **`QTY (IN PCS)`**, **`QTY (IN KARTON)`**, `TIPE TRANS` (`sales`/`retur`)
7. `GROSSAMOUNT` (`SUM(sid.qty × sid.price)`), `LINE DISCOUNT 1-3`, `TOTAL DISCOUNT`, `NET AMOUNT`, `NO FAKTUR PAJAK`, `TGL FAKTUR PAJAK`, `COGS`

Tabel memakai `fixedColumns.leftColumns = 4`, tombol export Excel (`ReportPenjualan` / `ReportPenjualanDetailDiskon`), dan `deferRender`.

Tab **Per Barang** memakai konversi berbeda: `qty_terkecil`, `qty_terbesar`, `unit_terkecil`, `unit_terbesar`, `price_terkecil`, `price_terbesar`, dan kolom `KUANTITAS` mengikuti pilihan filter satuan. Tab ini **tidak** diubah pada penambahan kolom in-pcs/in-karton.

---

## 6. Catatan & limitasi

- **"PCS" bukan selalu pcs.** Satuan terkecil (level 1) bisa botol, sachet, dsb. `QTY (IN PCS)` = total dalam satuan level 1.
- **Produk tanpa level karton** (hanya 1 level) → `QTY (IN KARTON)` = 0, sedangkan `QTYSOLD` menampilkan total pcs.
- **Produk dengan jumlah level tak lazim** (data lokal: 1 produk punya 9 baris `product_uom`) → format `QTYSOLD` kosong (`CASE` tanpa `ELSE`) dan `QTY (IN KARTON)` = 0; `QTY (IN PCS)` tetap terisi bila konversi satuan jual ditemukan.
- **Satuan jual tanpa baris `product_uom`** → `qty_pcs` NULL dan ditampilkan 0 (kolom lama juga kosong).
- **Baris retur jarang muncul.** `getData()` memfilter `sales_return.types = 'good'` dan `return_type = 'RETURN'`, sementara seluruh retur 2026 di data lokal bertipe `REFUND`, sehingga baris retur tidak tampil. Perilaku ini lama dan tidak diubah.
- **Retur tidak mengurangi baris penjualan**, hanya menambah baris negatif bila lolos filter.
- **`GROSSAMOUNT` = `SUM(sid.qty × sid.price)`** (qty invoice), sedangkan `QTYSOLD`/kolom pcs-karton memakai `SUM(sod.qty)` (qty SO). Perbedaan sumber qty ini memang sudah ada sejak awal.
- **Tanpa baris total.** Semua angka per baris; rekap dilakukan lewat Excel export.
- **COGS, NO FAKTUR PAJAK, TGL FAKTUR PAJAK** dirender kosong di JS (`render` selalu `""`), bukan diambil dari query.

---

## 7. Verifikasi

```bash
php -l app/Http/Controllers/api/report/ReportPenjualanController.php
node --check public/assets/js/controllers/report/report_penjualan.js
```

Uji endpoint tanpa membuka browser (butuh session & `$_POST`):

```php
session(['id_company' => 1]);
$_POST = [
    'date_start' => '2026-11-01', 'date_end' => '2026-11-20',
    'principal' => '', 'draw' => 1, 'length' => 5, 'start' => 0,
];
$res = json_decode(app(App\Http\Controllers\api\report\ReportPenjualanController::class)->getData(), true);
foreach ($res['data'] as $row) {
    echo $row['qty_sold'] . ' | ' . $row['qty_pcs'] . ' | ' . $row['qty_karton'] . PHP_EOL;
}
```

Cek silang konversi per produk:

```sql
SELECT level, unit_tujuan, nilai_konversi_terkecil, state
FROM product_uom WHERE product = 96 AND deleted IS NULL ORDER BY level;
-- L1 conv 1, L2 conv 12, L3 conv 24, L4 conv 192 (state large = karton)
```

---

## 8. Riwayat perubahan

- **Kolom `QTY (IN PCS)` dan `QTY (IN KARTON)`** ditambahkan pada tab All dan Detail Diskon. Perubahan hanya menambah field `qty_pcs`, `qty_karton`, `karton_conv` di `getData()`, dua definisi kolom di `report_penjualan.js`, dan dua `<th>` di tiap view. Kolom lama, filter, dan endpoint lain tidak diubah.
