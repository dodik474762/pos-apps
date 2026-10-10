# PRD — Modul Accounting (Phase 1–9)

Dokumen ini merangkum seluruh keputusan desain, rules bisnis, dan DDL yang telah disepakati untuk modul Accounting, dari Chart of Accounts sampai General Ledger.

## Roadmap

| Phase | Nama | Status |
| --- | --- | --- |
| 1 | Chart of Accounts (COA) | Selesai |
| 2 | Account Mapping | Selesai |
| 3 | Journal Engine | Selesai |
| 4 | AR Subledger | Selesai |
| 5 | Purchase / AP Subledger | Selesai |
| 6 | Returns | Selesai (tanpa tabel baru) |
| 7 | Inventory Accounting | Selesai (integrasi ke stock\_cards existing) |
| 8 | Cash & Bank | Di-skip (pakai manual journal) |
| 9 | General Ledger | Selesai |

---

## Phase 1 — Chart of Accounts (COA)

Mendefinisikan struktur akun dan tipe akun dasar untuk seluruh sistem.

```sql
CREATE TABLE `account_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `normal_balance` varchar(10) NOT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `account_type_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `level` tinyint unsigned NOT NULL DEFAULT '1',
  `normal_balance` varchar(10) NOT NULL,
  `is_header` tinyint(1) NOT NULL DEFAULT '0',
  `is_control_account` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounts_code_unique` (`code`),
  KEY `accounts_account_type_id_foreign` (`account_type_id`),
  KEY `accounts_parent_id_foreign` (`parent_id`),
  CONSTRAINT `accounts_account_type_id_foreign` FOREIGN KEY (`account_type_id`) REFERENCES `account_types` (`id`),
  CONSTRAINT `accounts_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

`parent_id` dipakai untuk hierarki akun (self-reference). `is_header` menandai akun grup yang tidak boleh dipakai langsung di jurnal. `normal_balance` bernilai `debit` atau `credit`, divalidasi di level aplikasi.

---

## Phase 2 — Account Mapping

Menghubungkan jenis transaksi ERP ke akun yang sesuai, tanpa hardcode di kode program.

```sql
CREATE TABLE `account_mapping_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_type` varchar(50) NOT NULL,
  `account_role` varchar(50) NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `product_category_id` bigint unsigned DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_mapping_rules_account_id_foreign` (`account_id`),
  KEY `account_mapping_rules_lookup_index` (`transaction_type`,`account_role`,`is_active`),
  CONSTRAINT `account_mapping_rules_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

`AccountMappingResolver` mencari rule berdasarkan `transaction_type` + `account_role`, dengan rule yang punya `product_category_id` spesifik menang atas rule default (`product_category_id IS NULL`).

---

## Phase 3 — Journal Engine

### Definisi

Journal Engine adalah core accounting yang mengubah transaksi ERP menjadi jurnal double-entry tervalidasi dan dapat diposting — penghubung Account Mapping dengan General Ledger.

### Lifecycle

- `DRAFT` → dapat dibuat dan diedit.
- `POSTED` → tervalidasi dan diposting, immutable.
- `REVERSED` → jurnal posted yang dibatalkan melalui jurnal reversal baru.

### Validation Rules

1. Total debit harus sama dengan total credit.
2. Minimal terdapat 2 detail valid.
3. Debit dan credit tidak boleh negatif.
4. Satu detail tidak boleh memiliki debit dan credit sekaligus > 0.
5. Account harus aktif dan bukan header account.
6. Journal date harus berada pada accounting period OPEN.
7. Jurnal otomatis harus memiliki reference\_type dan reference\_id.
8. POSTED tidak dapat diedit langsung.

### Journal Posting

Proses atomic dalam DB transaction: validasi header → validasi period → validasi detail/account → hitung debit & credit → pastikan balance → update status POSTED → simpan posted\_at & posted\_by → commit.

### Journal Reversal

Jurnal POSTED tidak dihapus. Sistem membuat jurnal reversal baru dengan debit/credit dibalik, memiliki `reversal_of_id`, jurnal asli menjadi `REVERSED`, tetap mengikuti validasi period, dan tidak boleh dilakukan dua kali untuk jurnal yang sama.

### Accounting Period Closing

Closing bersifat **hard block**: sistem menolak menutup periode kalau masih ada journal `DRAFT` bertanggal di periode itu (daftar journal yang menghalangi ditampilkan ke user). Setelah closing: `status='CLOSED'`, `closed_at`, `closed_by` terisi; tidak ada jurnal baru (DRAFT maupun POSTED) yang boleh memakai `journal_date` di periode itu lagi.

```sql
CREATE TABLE `accounting_periods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `year` smallint unsigned NOT NULL,
  `month` tinyint unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'OPEN',
  `closed_at` datetime DEFAULT NULL,
  `closed_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_periods_year_month_unique` (`year`,`month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `journal_headers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `journal_no` varchar(50) NOT NULL,
  `journal_date` date NOT NULL,
  `accounting_period_id` bigint unsigned NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'DRAFT',
  `posted_at` datetime DEFAULT NULL,
  `posted_by` bigint unsigned DEFAULT NULL,
  `reversal_of_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `journal_headers_journal_no_unique` (`journal_no`),
  CONSTRAINT `journal_headers_accounting_period_id_foreign` FOREIGN KEY (`accounting_period_id`) REFERENCES `accounting_periods` (`id`),
  CONSTRAINT `journal_headers_reversal_of_id_foreign` FOREIGN KEY (`reversal_of_id`) REFERENCES `journal_headers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `journal_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `journal_id` bigint unsigned NOT NULL,
  `line_no` int unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `debit` decimal(18,2) NOT NULL DEFAULT '0.00',
  `credit` decimal(18,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `journal_details_journal_id_line_no_unique` (`journal_id`,`line_no`),
  CONSTRAINT `journal_details_journal_id_foreign` FOREIGN KEY (`journal_id`) REFERENCES `journal_headers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `journal_details_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Mapping Route per Transaction Type

| # | Transaksi | Jurnal | Karakteristik |
| --- | --- | --- | --- |
| 1 | Sales Invoice | DR AR / CR Sales | Sepasang tetap |
| 2 | Delivery/DO | DR COGS / CR Inventory | Sepasang tetap, value dari Inventory Accounting |
| 3 | Customer Payment | DR Bank-Cash / CR AR | AR via mapping, Bank/Cash dipilih user langsung |
| 4 | Purchase Invoice | DR Inventory/Expense / CR AP atau GRNI | Bisa multi-baris debit; DR GRNI kalau item sudah pernah di-Receiving |
| 5 | Receiving | DR Inventory / CR GRNI | Clearing account, dipasangkan dengan Purchase Invoice |
| 6 | Supplier Payment | DR AP / CR Bank-Cash | Mirror Customer Payment |
| 7 | Sales Return | DR Sales Return + Inventory / CR AR + COGS | Reverse 2 transaksi asal sekaligus, jadi credit note di AR Subledger |
| 8 | Purchase Return | DR AP/GRNI / CR Inventory-Expense | Percabangan tergantung status invoice, jadi credit note di AP Subledger |
| 9 | Cash/Bank | DR/CR sesuai direction | Kedua akun dipilih bebas user, pakai manual journal generik (tidak ada fitur khusus) |

Referensi jurnal retur (poin 7 & 8) harus di-update **setelah** journal POSTED, menunjuk ke `ArCreditNote`/`ApCreditNote` yang baru terbentuk — bukan ke dokumen retur yang tidak pernah ada tabelnya.

### Urutan Implementasi

1. AccountingPeriodService (termasuk validasi closing + reject kalau ada DRAFT).
2. JournalValidator.
3. JournalService untuk DRAFT.
4. JournalPostingService.
5. JournalReversalService.
6. API period dan journal.
7. Integrasi pertama ke Sales Invoice, lalu transaksi lain menyusul.

---

## Phase 4 — AR Subledger

Lapisan detail piutang per-customer dan per-invoice di bawah akun AR control account. Total outstanding di sini harus selalu sama dengan saldo akun AR di GL.

### Entity

- `ar_invoices` — 1 baris per Sales Invoice POSTED.
- `ar_payments` — sisi subledger dari Customer Payment.
- `ar_credit_notes` — sisi subledger dari Sales Return.
- `ar_allocations` — polymorphic, menghubungkan satu `ar_payments`/`ar_credit_notes` ke satu/lebih `ar_invoices`.

### Lifecycle

- Invoice: `OPEN` → `PARTIAL` → `PAID`, atau `VOID`. `OVERDUE` adalah flag turunan dari `due_date`, bukan status tersimpan.
- Source (payment/credit note): `UNALLOCATED` → `PARTIALLY_ALLOCATED` → `FULLY_ALLOCATED`.

### Validation Rules — Allocation

1. `allocated_amount` per baris > 0.
2. Total alokasi satu source ≤ amount total source.
3. Alokasi ke satu invoice ≤ outstanding\_amount invoice saat itu.
4. Source dan invoice wajib milik customer yang sama.
5. Tidak boleh alokasi ke invoice `PAID`/`VOID`.
6. Alokasi bersifat **manual** — user pilih sendiri invoice & nominalnya, tidak ada FIFO otomatis.
7. Alokasi tidak membuat jurnal baru — murni operasi subledger.
8. Update invoice & source wajib dalam satu DB transaction.

Sales Return diperlakukan sebagai **credit note terpisah** yang bisa dialokasikan ke invoice manapun, bukan langsung mengurangi outstanding invoice asal.

```sql
CREATE TABLE `ar_invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sales_invoice_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `invoice_amount` decimal(18,2) NOT NULL,
  `paid_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `outstanding_amount` decimal(18,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'OPEN',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ar_invoices_sales_invoice_id_unique` (`sales_invoice_id`),
  UNIQUE KEY `ar_invoices_invoice_no_unique` (`invoice_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ar_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_payment_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `allocated_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) NOT NULL DEFAULT 'UNALLOCATED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ar_payments_customer_payment_id_unique` (`customer_payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ar_credit_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sales_return_id` bigint unsigned NOT NULL COMMENT 'merujuk ke journal_headers.id jurnal SALES_RETURN, bukan dokumen retur',
  `customer_id` bigint unsigned NOT NULL,
  `credit_note_date` date NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `allocated_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) NOT NULL DEFAULT 'UNALLOCATED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ar_credit_notes_sales_return_id_unique` (`sales_return_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ar_allocations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `source_type` varchar(20) NOT NULL COMMENT 'PAYMENT atau CREDIT_NOTE',
  `source_id` bigint unsigned NOT NULL,
  `ar_invoice_id` bigint unsigned NOT NULL,
  `allocation_date` date NOT NULL,
  `allocated_amount` decimal(18,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
  `reversed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ar_allocations_source_index` (`source_type`,`source_id`),
  CONSTRAINT `ar_allocations_ar_invoice_id_foreign` FOREIGN KEY (`ar_invoice_id`) REFERENCES `ar_invoices` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Customer Balance** = `SUM(outstanding_amount) FROM ar_invoices WHERE customer_id=X AND status IN (OPEN, PARTIAL)` — live query, bukan tabel cache.

### Urutan Implementasi

1. ArInvoiceService (auto-create saat Sales Invoice POSTED)
2. ArPaymentService (auto-create saat Customer Payment POSTED)
3. ArCreditNoteService (auto-create saat Sales Return POSTED)
4. AllocationValidator
5. AllocationService (alokasi manual)
6. AllocationReversalService
7. Query Invoice Outstanding & Customer Balance
8. API endpoints

---

## Phase 5 — Purchase / AP Subledger

Cerminan Phase 4 dari sisi hutang. Entity: `ap_invoices`, `ap_payments`, `ap_credit_notes`, `ap_allocations` — struktur dan rules identik, konteks debit/kredit dibalik (AP credit-normal).

Perbedaan penting: **hanya Purchase Invoice** yang menghasilkan `ap_invoices` — Receiving tidak, karena Receiving hanya menyentuh GRNI (clearing sementara di Journal Engine), bukan AP sungguhan.

```sql
CREATE TABLE `ap_invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_invoice_id` bigint unsigned NOT NULL,
  `supplier_id` bigint unsigned NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `invoice_amount` decimal(18,2) NOT NULL,
  `paid_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `outstanding_amount` decimal(18,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'OPEN',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ap_invoices_purchase_invoice_id_unique` (`purchase_invoice_id`),
  UNIQUE KEY `ap_invoices_invoice_no_unique` (`invoice_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ap_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_payment_id` bigint unsigned NOT NULL,
  `supplier_id` bigint unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `allocated_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) NOT NULL DEFAULT 'UNALLOCATED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ap_payments_supplier_payment_id_unique` (`supplier_payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ap_credit_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_return_id` bigint unsigned NOT NULL COMMENT 'merujuk ke journal_headers.id jurnal PURCHASE_RETURN',
  `supplier_id` bigint unsigned NOT NULL,
  `credit_note_date` date NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `allocated_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) NOT NULL DEFAULT 'UNALLOCATED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ap_credit_notes_purchase_return_id_unique` (`purchase_return_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ap_allocations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `source_type` varchar(20) NOT NULL COMMENT 'PAYMENT atau CREDIT_NOTE',
  `source_id` bigint unsigned NOT NULL,
  `ap_invoice_id` bigint unsigned NOT NULL,
  `allocation_date` date NOT NULL,
  `allocated_amount` decimal(18,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
  `reversed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `ap_allocations_ap_invoice_id_foreign` FOREIGN KEY (`ap_invoice_id`) REFERENCES `ap_invoices` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Supplier Balance** = `SUM(outstanding_amount) FROM ap_invoices WHERE supplier_id=X AND status IN (OPEN, PARTIAL)`.

### Urutan Implementasi

Sama persis dengan Phase 4, cerminan: ApInvoiceService, ApPaymentService, ApCreditNoteService, AllocationValidator (reuse), AllocationService, AllocationReversalService.

---

## Phase 6 — Returns

**Tidak ada tabel/dokumen Sales Return atau Purchase Return tersendiri.** Phase ini murni orkestrasi tipis yang menyambungkan Journal Engine (Phase 3) dengan Credit Note (Phase 4/5).

### Alur

1. `SalesReturnService->create()` menerima input (customer, nilai retur sisi AR, nilai HPP/inventory, keterangan) — TANPA menyimpan sebagai dokumen.
2. Panggil `JournalService`/`JournalPostingService` dengan `transaction_type='SALES_RETURN'` (DR Sales Return + Inventory / CR AR + COGS).
3. Setelah journal `POSTED`, panggil `ArCreditNoteService` untuk membuat `ar_credit_notes`, dengan `sales_return_id` **diisi `journal_headers.id`**.
4. `PurchaseReturnService->create()` sama persis polanya untuk `PURCHASE_RETURN` → `ApCreditNoteService`.
5. Kalau journal gagal validasi (period closed, akun tidak aktif, dll) — proses berhenti total, credit note tidak pernah dibuat.
6. Reversal retur = reversal jurnal biasa + pembatalan alokasi kalau credit note sudah pernah dialokasikan.

### Urutan Implementasi

1. SalesReturnService (orkestrasi: Journal → ArCreditNote)
2. PurchaseReturnService (orkestrasi: Journal → ApCreditNote)
3. API endpoint retur

---

## Phase 7 — Inventory Accounting

**Tidak membuat tabel stock card baru** — terintegrasi ke tabel `stock_cards` yang sudah berjalan, dengan menambahkan kemampuan valuasi di atasnya.

### Keputusan desain

- Metode valuasi: **Weighted Average (Moving Average)**.
- `stock_cards` existing sudah punya kolom `nominal_value` (belum terisi, selalu 0) — dipakai sebagai **net value movement** baris itu (sejajar dengan `$movement` qty yang sudah ada: `qty_in - qty_out + qty_adjust - qty_transfer_out + qty_transfer_in + qty_return_in`).
- Tambahan 2 kolom baru yang mencerminkan pola `opening_balance`/`closing_balance` yang sudah ada:

```sql
ALTER TABLE stock_cards
  ADD COLUMN opening_value decimal(18,2) NOT NULL DEFAULT '0.00' AFTER opening_balance,
  ADD COLUMN closing_value decimal(18,2) NOT NULL DEFAULT '0.00' AFTER closing_balance;
```

- **Tidak ada tabel cache average cost terpisah** — average cost saat ini selalu diturunkan dari `closing_value ÷ closing_balance` baris `stock_cards` paling akhir untuk item tsb.

### Sumber unit cost per tipe movement

| Tipe | Sumber unit cost | Nominal value |
| --- | --- | --- |
| `qty_in` (GoodReceipt) | `product_uom_cost`, match product+uom+vendor, `date_start<=trans_date`, `is_active=1`, ambil terbaru | `qty_in × cost` |
| `qty_out` (DO) | average cost berjalan | `qty_out × average_cost` |
| `qty_return_in` | dari baris DO asal (reference\_type/reference\_id): `nominal_value ÷ qty_out` baris itu | `qty_return_in × unit_cost_asal` |
| `qty_transfer_in` | dari baris `qty_transfer_out` gudang asal | `qty_transfer_in × unit_cost_asal` |
| `qty_transfer_out` | average cost berjalan | `qty_transfer_out × average_cost` |
| `qty_adjust` positif (surplus) | `price` input user (ProductAdjustmentStockDtl.price) | `qty_adjust × price` |
| `qty_adjust` negatif (shortage) | average cost berjalan (bukan price user) | `\|qty_adjust\| × average_cost` |

### Titik integrasi

Ada **2 jalur** yang membuat baris `stock_cards`, keduanya perlu disentuh:

1. **Real-time** (`stockUpdate()`) — dipanggil dari Receiving/DO/Adjustment/Transfer. Tambahkan logic valuasi setelah insert baris, baca `closing_balance`/`closing_value` baris terakhir item tsb sebagai starting point.
2. **Batch recalculation** (`recalculateFrom()`, dipakai proses "closing stock") — menghapus dan membangun ulang baris dalam rentang tanggal, lalu `propagateBalanceAfter()` merambatkan saldo ke baris setelahnya. Perlu `$runningValue` paralel dengan `$runningBalance` yang sudah ada, dihitung ulang mengikuti urutan histori (TIDAK boleh pakai cache "saat ini", karena sedang membangun ulang masa lalu).

**Keterbatasan yang diterima**: `propagateValueAfter()` (versi value dari `propagateBalanceAfter()`) **tidak dibuat** — kalau recalculate dipanggil untuk rentang parsial (bukan sampai hari ini) dan ada baris setelah `toDate`, nominal\_value baris-baris itu bisa jadi tidak presisi sampai ada recalculate berikutnya yang mencakup rentang itu juga. Risiko ini diterima, bukan dianggap bug.

`value_out` dari baris `qty_out` (DO) inilah yang dipakai sebagai nilai HPP untuk DR COGS di Journal Engine — menggantikan nilai manual yang sebelumnya jadi placeholder.

### Urutan Implementasi

1. Inventarisir semua titik insert ke `stock_cards` (stockUpdate() dan recalculateFrom()).
2. Migration `opening_value`/`closing_value` (dijalankan sendiri).
3. CostResolverService (lookup product\_uom\_cost untuk GoodReceipt).
4. Tambahkan logic valuasi ke `stockUpdate()`.
5. Tambahkan `$runningValue` paralel ke `recalculateFrom()`.
6. Lookup unit\_cost untuk Return & Transfer dari baris asal.
7. Uji manual end-to-end satu produk (Receiving → DO → Return → Transfer → Adjustment).

---

## Phase 8 — Cash & Bank

**Di-skip** — tidak ada modul Cash & Bank terpisah. Kebutuhannya sudah tercakup oleh kemampuan dasar Journal Engine: manual journal DRAFT, di mana user memilih sendiri 2 akun (debit & kredit) + nominal, tanpa `reference_type`/`reference_id` (Validation Rule 7 hanya mewajibkan referensi untuk jurnal **otomatis**, bukan manual).

Tidak ada logic tambahan yang perlu dibangun. Kalau nanti modul Cash & Bank dibuat, tinggal tambahkan `transaction_type='CASH_BANK_TRANSACTION'` dan panggil `JournalService` yang sudah ada.

---

## Phase 9 — General Ledger

**Tidak ada transaksi baru** — GL murni membaca `journal_headers`/`journal_details`/`accounts`. Fokus desain ada pada efisiensi query saldo awal per periode.

### Komponen

1. **GeneralLedgerService->getLedger(accountId, periodId)** — mutasi akun per periode + saldo berjalan (sesuai `normal_balance`).
2. **TrialBalanceService->generate(periodId)** — ringkasan semua akun non-header, kolom Debit/Kredit sesuai tanda saldo, total harus seimbang.
3. **Snapshot saldo per periode** — dibuat otomatis sebagai bagian dari `AccountingPeriodService->close()`, untuk **semua akun di COA** (termasuk yang tidak bermutasi di periode itu), supaya laporan periode yang sudah closed tidak perlu dihitung ulang dari nol.

```sql
CREATE TABLE `account_period_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint unsigned NOT NULL,
  `accounting_period_id` bigint unsigned NOT NULL,
  `opening_balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_debit` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_credit` decimal(18,2) NOT NULL DEFAULT '0.00',
  `closing_balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_period_balances_account_period_unique` (`account_id`,`accounting_period_id`),
  CONSTRAINT `account_period_balances_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  CONSTRAINT `account_period_balances_period_id_foreign` FOREIGN KEY (`accounting_period_id`) REFERENCES `accounting_periods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Live vs Snapshot

- Periode **OPEN** → Trial Balance dihitung **live** (agregasi langsung dari `journal_details` WHERE status='POSTED').
- Periode **CLOSED** → Trial Balance dibaca langsung dari `account_period_balances` (snapshot immutable, tidak dihitung ulang).
- Saldo awal periode OPEN diambil dari `closing_balance` snapshot periode sebelumnya (kalau sudah closed) — bukan dihitung ulang dari seluruh histori.

**Prasyarat penting**: closing periode harus berurutan tanpa lompat (periode sebelumnya harus sudah closed dulu), karena `snapshotPeriod()` bergantung penuh pada `closing_balance` periode sebelumnya yang sudah final.

### Urutan Implementasi

1. Model & migration AccountPeriodBalance.
2. AccountPeriodBalanceService->snapshotPeriod() — loop semua akun, hitung opening/debit/credit/closing.
3. Sisipkan panggilan snapshot ke `AccountingPeriodService->close()` yang sudah ada, dalam transaction yang sama.
4. GeneralLedgerService->getLedger().
5. TrialBalanceService->generate() dengan cabang Live vs Snapshot.
6. API endpoint GL per akun & Trial Balance per periode.

---

## Lampiran — Catatan Lintas Phase

- **Account Role yang perlu didukung AccountMappingResolver**: `AR`, `SALES`, `COGS`, `INVENTORY`, `AP`, `GRNI`, `EXPENSE`, `SALES_RETURN`.
- **Prinsip rekonsiliasi**: total `ar_invoices`/`ap_invoices` outstanding harus selalu sama dengan saldo akun AR/AP di GL; total `inventory` value (dari `stock_cards.closing_value`) harus sama dengan saldo akun Inventory di GL.
- Modul `accounts` (COA) menjadi pola rujukan gaya penulisan Model/Service/Controller untuk seluruh phase di atas.
