# Ringkasan Kerja — Journal Engine Transaksi

**Tanggal:** 26 September 2026
**Repositori:** pos-apps (branch `main`)
**Commit:** `916d9ab` s/d `d678d87` — 9 commit
**Skala:** 49 file berubah, +5033 / -34 baris

---

## 1. Tujuan

Menggantikan pencatatan akuntansi legacy per modul (`postingGL()` yang
ditulis ulang tiap modul, masing-masing dengan aturan sendiri) menjadi satu
**Journal Engine** terpusat. Setiap modul transaksi cukup memanggil service-nya,
dan seluruh aturan akuntansi berlaku seragam di satu tempat.

## 2. Komponen Inti (commit `916d9ab`)

| Komponen | File | Tugas |
|---|---|---|
| JournalService | `app/Services/Accounting/JournalService.php` | buat header + detail, generate `journal_no` |
| JournalValidator | `app/Services/Accounting/JournalValidator.php` | aturan 1–8 |
| JournalPostingService | `app/Services/Accounting/JournalPostingService.php` | `DRAFT → POSTED`, proyeksi ke `general_ledgers` |
| JournalReversalService | `app/Services/Accounting/JournalReversalService.php` | pembatalan jurnal |
| AccountingPeriodService | `app/Services/Accounting/AccountingPeriodService.php` | pastikan periode `OPEN` |
| AccountMappingResolver | `app/Services/Accounting/AccountMappingResolver.php` | `resolve(transaction_type, role)` |
| JournalValidationException | `app/Services/Accounting/JournalValidationException.php` | error validasi berbahasa Indonesia |
| Model | `JournalHeader`, `JournalDetail`, `AccountingPeriod` | |

### Aturan standar Journal Engine

1. Total debit harus sama dengan total credit.
2. Minimal 2 detail valid.
3. Debit dan credit tidak boleh negatif.
4. Satu detail tidak boleh punya debit **dan** credit sekaligus lebih dari 0.
5. Account harus **aktif** dan **bukan header account**.
6. `journal_date` harus berada pada accounting period berstatus `OPEN`.
7. Jurnal otomatis wajib punya `reference_type` dan `reference_id`.
8. Jurnal `POSTED` tidak dapat diedit langsung.

Jurnal yang sudah `POSTED` diproyeksikan ke `general_ledgers` satu baris per
detail, dengan `dc = D` atau `C`, sehingga Journal Engine menjadi penghubung
single source of truth antara Account Mapping dan General Ledger.

## 3. Modul Transaksi yang Terpasang

| Modul | `reference_type` | `transaction_type` | Entri Jurnal | Commit |
|---|---|---|---|---|
| Sales Invoice | `SALES_INVOICE` | `SALES` | Dr AR / Cr TAX_PAYABLE / Cr REVENUE | `916d9ab` |
| Delivery Order | `DELIVERY_ORDER` | `DELIVERY_ORDER` | Dr COGS / Cr INVENTORY | `d79db58` |
| Customer Payment | `CustomerPayment` | `CUSTOMER_PAYMENT` | Dr Bank / Cr AR | `74600da` |
| Purchase Invoice | `PurchaseInvoice` | `PURCHASE_INVOICE` | Dr INVENTORY atau Dr EXPENSE, Dr GRNI / Cr AP | `4d5959b` |
| Receiving | `Receiving` | `GOODS_RECEIPT` | Dr INVENTORY / Cr GRNI | `7583813` |
| Supplier Payment | `SupplierPayment` | `SUPPLIER_PAYMENT` | Dr AP / Cr Bank | `7583813` |
| Sales Return | `SalesReturn` | `SALES_RETURN` | Dr SALES_RETURN + Dr INVENTORY / Cr AR + Cr COGS | `c6733e8` |
| Purchase Return | `PurchaseReturn` | `PURCHASE_RETURN` | Dr AP atau Dr GRNI / Cr INVENTORY atau Cr EXPENSE | `a24b9e4` |

Setiap modul mendapat trigger **Post Jurnal** terpisah di halaman list, modal
konfirmasi, route, dan guard hapus. Alur `submit()` / `postingGL()` legacy
tetap dibiarkan utuh dan tidak disentuh.

### Sales Return (`c6733e8`)

Dua jalur, ditentukan `sales_return.types`:

- **good** — `Dr SALES_RETURN + Dr INVENTORY / Cr AR + Cr COGS`
- **damaged** — `Dr SALES_RETURN + Dr LOSS / Cr AR + Cr COGS`

Kata kunci `damaged`, `damage`, `rusak`, `broken`, `reject`, `scrap` dikenali
sebagai barang rusak; nilai default `good`.

### Purchase Return (`a24b9e4`)

- Sisi debit mengikuti sumber dokumen: `FROM_INVOICE` → **Dr AP**,
  `FROM_GR` → **Dr GRNI**.
- Sisi kredit mengikuti `product.is_stock` di master produk: `0` → **Cr EXPENSE**,
  selain itu → **Cr INVENTORY**.
- Sumber boleh **dicampur per detail** dalam satu header.
- Nominal diambil dari dokumen asal dan diprorate:
  - `FROM_INVOICE`: `purchase_invoice_detail.subtotal / qty x qty_retur`
  - `FROM_GR`: `goods_receipt_detail.subtotal / qty_received x qty_retur`,
    dengan fallback ke harga PO bila subtotal tidak tersedia.

## 4. Product Cost Resolver (`778508f`)

`app/Services/Accounting/ProductCostResolver.php` dipakai bersama oleh
`DeliveryOrderJournalService` dan `SalesReturnJournalService`, supaya
perhitungan HPP di kedua modul tidak berbeda.

- `product_uom_cost` disimpan dalam **satuan besar**, lalu dibagi dengan total
  konversi rantai `product_uom` menuju satuan transaksi.
- Rantai multi-hop, maximum 5 hop, melewati `x1` (alias satuan dasar).
- Menolak detail yang tidak punya `product_uom_cost` atau tidak punya jalur
  konversi. **Tidak ada fallback diam-diam ke satuan dasar** — missing cost
  harus terlihat, bukan tersembunyi di selisih pembukuan.

Hasil verifikasi:

- 341 dari 341 detail retur aktif berhasil dihitung.
- Produksi: `JV09260006` cocok, `483.197 x 3 = 1.449.591`.

## 5. Cash/Bank Transaction Journal Engine (`d678d87`)

`app/Services/Accounting/CashBankTransactionJournalService.php` — **engine
saja, belum tersambung ke modul.**

Kas/Bank membukukan perpindahan uang dengan dua akun, dipilih langsung user:

| `direction` | Entri |
|---|---|
| `IN` | Dr Bank/Cash / Cr akun lawan |
| `OUT` | Dr akun lawan / Cr Bank/Cash |

Berbeda dari modul lain, akun bank **tidak** diambil dari
`AccountMappingResolver`, karena bank dan akun lawannya bersifat transaksional.

Validasi khusus di atas aturan standar:

- akun Bank/Cash wajib berada di bawah header `1100 CASH & BANK` — divalidasi
  dengan menelusuri `parent_id`, bukan daftar kode hardcoded;
- akun lawan dan akun bank aktif serta bukan header account (aturan 5);
- kedua akun tidak boleh sama;
- `amount` lebih besar dari 0;
- `direction` hanya `IN` / `OUT`;
- akun lawan boleh memakai default `account_mapping_rules`
  (`CASH_BANK_TRANSACTION` + role per jenis transaksi), dan **pilihan user
  selalu menang** atas default tersebut.

Juga: idempoten (post dua kali mengembalikan jurnal yang sama), jurnal reversal
tidak dihitung sebagai jurnal aktif (`whereNull('reversal_of_id')`), dan
jurnal `DRAFT` yang tertinggal dari posting gagal dicoba di-post ulang.

### Verifikasi: 38 dari 38 lulus

- Alur `IN` dan `OUT`, termasuk arah huruf kecil.
- Proyeksi `general_ledgers` benar: Dr bank lalu Cr kontra.
- Idempotensi: tidak ada jurnal ganda.
- 13 kasus validasi ditolak sesuai harapan.
- Default mapping terbaca, dan pilihan user meng-override-nya.
- Reversal: terbentuk dengan debit/kredit terbalik dan `line_no`
  dipertahankan, reversal kedua ditolak, transaksi bisa dibukukan ulang.

## 6. Data Master

- `account_mapping_rules`: **28 rule**, mencakup 4 rule yang ditambahkan hari ini
  untuk Purchase Return — `AP`, `GRNI`, `INVENTORY`, `EXPENSE`.
- Periode `2026-09-01` s/d `2026-09-30` berstatus `OPEN`.
- Struktur COA untuk Kas/Bank sudah hierarki, sehingga tidak butuh kolom baru:
  `1000 ASSET` → `1100 CASH & BANK` → `1101 CASH`, `1102 BANK BCA`,
  `1103 BANK MANDIRI`.

Jurnal yang tercatat di DB setelah semua pekerjaan (**7 jurnal, semuanya
milik user**, hasil uji manual):

```
JV09260001  SALES_INVOICE    ref=5505  POSTED
JV09260002  DELIVERY_ORDER   ref=5646  POSTED
JV09260003  CustomerPayment  ref=4730  POSTED
JV09260004  PurchaseInvoice  ref=13    POSTED
JV09260005  Receiving        ref=115   POSTED
JV09260006  DELIVERY_ORDER   ref=5645  POSTED
JV09260007  PurchaseReturn   ref=16    POSTED
```

## 7. Insiden Residu DB — Sudah Dibersihkan

Perlu dicatat apa adanya sebagai peringatan untuk sesi berikutnya.

Skrip verifikasi Cash/Bank pertama **tidak pernah memanggil
`DB::beginTransaction()`**, sehingga `DB::rollBack()` di akhir skrip tidak
mengapa-apa. Akibatnya **7 jurnal CashBankTransaction + 14 baris
`general_ledgers` + 1 rule mapping tertinggal** di database produksi.

Deteksi dan pembersihan:

- Semua 7 jurnal teridentifikasi pasti milik verifikasi
  (`reference_type = CashBankTransaction`, `reference_id` 9999xx, 0 di luar
  daftar itu).
- Penghapusan awal gagal karena FK `journal_headers_reversal_of_id`; urutan
  benar adalah **hapus jurnal reversal lebih dulu**, baru jurnal asal.
- Setelah dibersihkan DB kembali ke 7 jurnal user, `general_ledgers` 25.825
  baris, nol rule `CASH_BANK_TRANSACTION`, akun non-aktif yang sempat
  dimatikan sudah aktif kembali.

Jalur rollback pada skrip verifikasi yang sudah diperbaiki **sudah diuji dan
terbukti mengembalikan seluruh baseline** (headers, details, GL, rules).

## 8. Blocker: Modul Kas/Bank Belum Ada

Engine Kas/Bank sudah jadi dan terverifikasi, tetapi **tidak ada tabel, menu,
controller, view, JS, maupun route** Kas/Bank di repo ini — sudah dicek di
`main` maupun semua branch, juga di 114 tabel dan tabel `menu`. Yang tersedia
baru 3 akun kas/bank di COA; modulnya sendiri belum pernah dibuat.

Akibatnya kalimat "Modul Kas/Bank memanggil JournalService" belum punya
pemanggil. Wiring tombol, form, dan route membutuhkan tabel baru, sedangkan
kebijakan yang berlaku melarang membuat migration. Keputusan yang diambil:
**berhenti di engine**, modul diserahkan ke user untuk dibuat.

Pekerjaan yang tersisa di sisi user:

1. Buat tabel `cash_bank_transaction` beserta CRUD-nya.
2. Panggil service dengan kontrak berikut:

```php
$journal = app(CashBankTransactionJournalService::class)->post([
    'reference_id'            => $row->id,               // wajib
    'bank_or_cash_account_id' => $row->bank_account_id,  // wajib, pilihan user
    'contra_account_id'       => $row->contra_account_id ?? null,
    'amount'                  => $row->amount,           // wajib, > 0
    'direction'               => $row->direction,        // wajib, IN|OUT
    'default_contra_role'     => $row->contra_role,      // opsional
    'journal_date'            => $row->tanggal,          // opsional
    'description'             => $row->keterangan,       // opsional
], $userId);
```

3. Guard hapus memakai `getJournalForCashBankTransaction($id)`, cek status
   dengan `getActiveJournal($id)`, dan pembatalan lewat
   `JournalReversalService::reverse($journalId, [...])`.
4. Dropdown akun kas/bank cukup memfilter anak dari header `1100` dengan
   `is_header = 0` dan `is_active = 1`.

> `default_contra_role` sengaja dibuat opsional karena daftar parameter yang
> disepakati tidak memuat "jenis transaksi". Tanpa itu, mapping default
> tidak punya kunci untuk dipilih.

### Catatan COA

Akun lawan generik yang lazim dipakai untuk Kas/Bank **belum ada** di COA:
Beban Administrasi Bank, Pendapatan Bunga, dan Modal Pemilik. Yang tersedia
baru 14 akun daun: `1101`, `1102`, `1103`, `1201`, `1301`, `1302`, `2100`,
`2101`, `2201`, `4100`, `4101`, `5100`, `5110`, `5200`. Penambahan akun
ditahan sebagai keputusan master data user.

## 9. Metode Verifikasi

Sesuai kebijakan, **tidak ada migration dan tidak ada file test** yang dibuat.
Verifikasi yang dipakai:

- `php -l` untuk seluruh file PHP.
- `node --check` untuk file JavaScript.
- `php artisan view:cache` dan `view:clear`.
- `php artisan route:list`.
- Query read-only ke database.
- Verifikasi perilaku memakai skrip sementara di luar repo
  (`/tmp/opencode`), dibungkus transaksi yang di-rollback, lalu dihapus.

## 10. Urutan Commit

```
916d9ab  08:29  update0transacrtioin-jurnal          fondasi Journal Engine
d79db58  09:18  pudayte-delivery-order-post-jurnal   Delivery Order
74600da  09:37  updayte-payment-jurnal                Customer Payment
4d5959b  10:25  udpayte-po-invoice-jurnal             Purchase Invoice
7583813  12:07  jdupayte-gr-dan0vendorbull            Receiving + Supplier Payment + Vendor Bill
c6733e8  12:33  update-sales-return                   Sales Return
778508f  12:49  updayte-product-cost-resolver         ProductCostResolver
a24b9e4  13:02  updatye-0posting-purchase-return      Purchase Return
d678d87  13:09  dupate                                Cash/Bank Journal Engine
```

## 11. Saran Berikutnya

1. Bangun modul Kas/Bank (butuh migration — perlu izin terpisah).
2. Tambahkan akun lawan generik ke COA bila diperlukan.
3. Perhatikan pembedaan status jurnal di formulir. Setelah pembatalan,
   `getActiveJournal()` mengembalikan `null`, sama seperti kondisi "belum pernah
   dibukukan". Keduanya tidak bisa dibedakan lewat satu pemanggilan itu. Formulir
   perlu memakai `getJournalForCashBankTransaction()`, yang mengembalikan jurnal
   asal-termasuk yang sudah `REVERSED`-supaya tombol "Post Jurnal" tahu
   bahwa transaksi pernah dibukukan lalu dibatalkan, bukan belum pernah
   dibukukan sama sekali.
