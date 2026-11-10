# Alur & Status Invoice

Dokumen ini merangkum hasil review modul invoice di repo ini: di mana status invoice diubah, modul apa saja yang memicunya, dan catatan temuan. Tidak ada perubahan logic pada kode saat dokumen ini dibuat.

> Catatan: repo memiliki dua domain invoice yang berbeda dan tidak saling terkait:
> 1. **Invoice transaksi** (`sales_invoice_header`, `purchase_invoice_header`) — invoice penjualan/pembelian ERP.
> 2. **Invoice langganan/billing** (`subscription_invoices`) — tagihan berlangganan tenant.

---

## 1. Invoice Penjualan (Sales Invoice)

Tabel: `sales_invoice_header`
Model: `app/Models/Transaction/SalesInvoiceHeader.php`

### 1.1 Daftar status

| Status | Arti | Ditulis oleh |
| --- | --- | --- |
| `DRAFT` | Invoice dibuat / generate dari SO | `SalesInvoiceController::submit()`, `SalesOrderController::saveInvoice()` |
| `PACKED` | Invoice sudah masuk Packing List | `PackingListController::submit()` (bukan modul invoice) |
| `POSTED` | Invoice sudah diposting ke jurnal | `SalesInvoiceController::posted()` |
| `PARTIAL PAID` | Sebagian sudah dibayar | `SalesPaymentController`, `PackingListController` |
| `PAID` | Lunas | `SalesPaymentController`, `PackingListController` |
| `CANCELED` | Invoice dibatalkan/dihapus/koreksi | `SalesInvoiceController::confirmDelete()`, alur koreksi |

Status lain yang dipakai di query/filter: `PACKED`, `POSTED`, `PARTIAL PAID`, `PAID`, `DRAFT`, `CANCELED`. Beberapa modul juga membaca `PAID`/`PARTIAL PAID` untuk menentukan invoice outstanding.

### 1.2 Titik update status

| Status | Lokasi | Keterangan |
| --- | --- | --- |
| `DRAFT` | `app/Http/Controllers/api/Transaction/SalesInvoiceController.php:662` | Saat create invoice baru (`submit()`). |
| `DRAFT` | `app/Http/Controllers/api/Transaction/SalesOrderController.php:4979` | Saat generate invoice dari SO (`saveInvoice()`). |
| `PACKED` | `app/Http/Controllers/api/Transaction/PackingListController.php:605` | Saat submit Packing List. Lihat bagian 1.3. |
| `POSTED` | `app/Http/Controllers/api/Transaction/SalesInvoiceController.php:1202` | `posted()`; setelah jurnal + AR subledger berhasil dibuat. |
| `PAID` | `app/Http/Controllers/api/Transaction/SalesPaymentController.php:450` | Saat pembayaran penuh. |
| `PARTIAL PAID` | `app/Http/Controllers/api/Transaction/SalesPaymentController.php:452` | Saat pembayaran sebagian. |
| `PAID` / `PARTIAL PAID` | `app/Http/Controllers/api/Transaction/PackingListController.php:1686` | Pembayaran invoice via Packing List/mobile. |
| `POSTED` | `app/Http/Controllers/api/Transaction/SalesPaymentController.php:865` | Cancel payment; `amount_paid` jadi 0. |
| `PARTIAL PAID` | `app/Http/Controllers/api/Transaction/SalesPaymentController.php:867` | Cancel payment; masih ada sisa pembayaran. |
| `CANCELED` | `app/Http/Controllers/api/Transaction/SalesInvoiceController.php:1104` | `confirmDelete()` (soft delete invoice). |
| `CANCELED` | `app/Http/Controllers/api/Transaction/SalesInvoiceController.php:1045` | Alur koreksi invoice; invoice lama dibatalkan. |
| status lama | `app/Http/Controllers/api/Transaction/SalesInvoiceController.php:1042` | Invoice hasil koreksi mewarisi status invoice lama. |

Catatan alur koreksi (`SalesInvoiceController::submit()` saat `id` terisi dan invoice sudah `POSTED`/`PAID`):
1. SO lama diubah ke `correction`.
2. SO baru dibuat dan di-generate menjadi invoice baru.
3. Invoice baru mewarisi status invoice lama (`:1042`).
4. Invoice lama di-set `CANCELED` dan di-soft-delete (`:1045`).

### 1.3 Khusus status `PACKED`

`PACKED` pada invoice **hanya** ditulis di modul Packing List, tepatnya di dalam `PackingListController::submit()`:

- `app/Http/Controllers/api/Transaction/PackingListController.php:597` — DO di-set `PACKED`.
- `app/Http/Controllers/api/Transaction/PackingListController.php:600-605` — cari `SalesInvoiceHeader` berdasarkan:
  - `do_id = $do->id`, atau
  - `sales_order = $do->so_id`,
  lalu `$so->status = 'PACKED'` (variabel `$so` sebenarnya adalah invoice header; namanya menyesatkan).

Semua referensi `PACKED` lainnya hanya **membaca/filter**, tidak menulis:
- `app/Http/Controllers/web/DashboardController.php:14`
- `app/Http/Controllers/web/Transaction/SalesPaymentController.php:290`
- `app/Http/Controllers/api/Transaction/SalesPaymentController.php:947`
- `app/Http/Controllers/web/Transaction/TerimaUangController.php:231`
- `app/Http/Controllers/web/Transaction/PLTagihanController.php:257`
- `app/Http/Controllers/api/Transaction/CreditNoteController.php:98`
- `app/Http/Controllers/api/Transaction/SalesReturnController.php:173`
- `app/Http/Controllers/api/Transaction/SalesInvoiceController.php:745`, `:802`, `:1138` (dipakai untuk cek apakah stock/DO perlu dikembalikan).

Catatan/risiko:
- Tidak ada null-check sebelum `$so->status = 'PACKED'`. Jika invoice untuk DO/SO tersebut tidak ditemukan, berpotensi error `Attempt to read property on null`.
- Overwrite tanpa guard: invoice yang sudah `POSTED`, `PARTIAL PAID`, atau `PAID` bisa berubah kembali menjadi `PACKED` saat Packing List disimpan/diedit.
- Saat DO di-remove dari Packing List (`:580`) atau Packing List di-`confirmDelete` (`:790-796`), hanya DO yang dikembalikan ke `CONFIRMED`; status invoice **tidak** di-reset dari `PACKED`.
- `submitSr()` di file yang sama memakai variabel `$do` untuk `SalesReturnHdr` lalu men-set `PACKED` (`:718-724`). Itu status Sales Return, bukan invoice.

### 1.4 Status invoice tidak diubah oleh modul return/credit note

- `SalesReturnController` hanya mengubah `return_qty` pada detail invoice.
- `CreditNoteController` hanya mengubah `credit_note_amount` pada detail invoice.

Status header `sales_invoice_header` tidak disentuh oleh kedua modul tersebut.

### 1.5 Alur ringkas

```
SO / saveInvoice ──> DRAFT ──> [Packing List] ──> PACKED
                                   │
                                   └──> POSTED ──> PARTIAL PAID ──> PAID
                                                 └───────────────> PAID

Invoice ──> CANCELED (hapus / koreksi)
Payment dibatalkan ──> POSTED / PARTIAL PAID
```

---

## 2. Subledger Piutang (`ar_invoices`)

Subledger AR punya status sendiri yang **terpisah** dari `sales_invoice_header`:

| Status | Arti |
| --- | --- |
| `OPEN` | Belum dibayar |
| `PARTIALLY_PAID` | Dibayar sebagian |
| `PAID` | Lunas |
| `REVERSED` | Invoice direversal |

Update status dilakukan di `app/Services/Accounting/ArSubledgerService.php`:
- `:244` — dibuat `OPEN` saat sync dari jurnal.
- `:259` — dihitung ulang saat sync.
- `:527` / `:555` — `applyToInvoiceBalance()` / `restoreInvoiceBalance()`.
- `:639` — `reverseInvoice()` menjadi `REVERSED`.
- `:724` — `resolveInvoiceStatus()` menentukan `OPEN`/`PARTIALLY_PAID`/`PAID`.

Subledger AR dibentuk dari jurnal yang sudah `POSTED` (`syncFromJournal()` dipanggil di `SalesInvoiceController::posted()`), lalu diperbarui oleh pembayaran/credit note.

---

## 3. Invoice Pembelian (Purchase Invoice)

Tabel: `purchase_invoice_header`
Status: `draft`, `posted`, `partial`, `paid`.

| Status | Lokasi | Keterangan |
| --- | --- | --- |
| `draft` | `app/Http/Controllers/api/Transaction/PurchaseInvoiceController.php:147` | Di-set saat submit create **dan edit**. |
| `posted` | `app/Http/Controllers/api/Transaction/PurchaseInvoiceController.php:469` | Setelah jurnal berhasil diposting. |
| `paid` / `posted` | `app/Http/Controllers/api/Transaction/VendorBillController.php:185` | Saat pembayaran vendor disimpan. |
| `posted` / `partial` / `paid` | `app/Http/Controllers/api/Transaction/VendorBillController.php:323-327` | Rollback saat pembayaran vendor dihapus. |

Subledger hutang `ap_invoices` dikelola `app/Services/Accounting/ApSubledgerService.php` dengan status yang sama seperti AR (`OPEN`, `PARTIALLY_PAID`, `PAID`, `REVERSED`).

Catatan/risiko:
- `PurchaseInvoiceController::submit()` men-set `status = 'draft'` tanpa syarat, termasuk saat edit invoice yang sudah `posted`/`paid`, sehingga berpotensi menurunkan status.

---

## 4. Invoice Langganan (Subscription/Billing)

Tabel: `subscription_invoices`
Model: `app/Models/Billing/SubscriptionInvoice.php`
Status: `PENDING`, `PAID`, `EXPIRED`, `FAILED`, `VOID`.

Semua penulisan status terpusat di `app/Services/Billing/BillingInvoiceService.php`:

| Status | Lokasi | Pemicu |
| --- | --- | --- |
| `PENDING` | `BillingInvoiceService.php:53` | `createForSubscription()` — dipanggil `BillingDailyJob` (invoice perpanjangan, `BillingDailyJob.php:51`) dan `SubscriptionService::choosePlan` (invoice upgrade, `SubscriptionService.php:96`). |
| `PAID` | `BillingInvoiceService.php:127` | `markPaid()`, dipanggil dari: <br>- `WebhookService::processLog()` `:132` (webhook gateway / replay) <br>- `web/billing/admin/InvoiceController::markPaid()` `:74` (super admin) <br>- `web/billing/SubscriptionController::invoicePay()` `:209` (konfirmasi manual tenant) |
| `EXPIRED` | `BillingInvoiceService.php:154` | `expirePending()` — hanya dipanggil `BillingDailyJob.php:106`, dijalankan scheduler `billing:daily`. |
| `FAILED` | — | Konstanta/label/filter ada, tetapi **tidak pernah ditulis** oleh kode mana pun. |
| `VOID` | — | Konstanta/label/filter ada, tetapi **tidak pernah ditulis** oleh kode mana pun. |

Catatan/risiko:
- Webhook hanya memproses status `PAID`; event gateway non-`PAID` di-`IGNORED` dan tidak mengubah invoice, sehingga `FAILED` praktis tidak pernah tersimpan.
- `markPaid()` hanya mencegah invoice yang sudah `PAID`; tidak ada transition map eksplisit, sehingga `EXPIRED`/`FAILED`/`VOID` bisa langsung menjadi `PAID`.
- `applyPaidInvoice()` selalu mencatat actor `'webhook'`, termasuk saat sumbernya admin atau konfirmasi manual (`BillingInvoiceService.php:136`).
- Belum ada flow void/cancel invoice.
- `expirePending()` melakukan bulk update tanpa audit log/event.

---

## 5. Ringkasan modul yang mengubah status invoice

### Sales Invoice (`sales_invoice_header`)
- `api/Transaction/SalesInvoiceController.php` — `DRAFT`, `POSTED`, `CANCELED`, pewarisan status koreksi.
- `api/Transaction/SalesPaymentController.php` — `PAID`, `PARTIAL PAID`, `POSTED`.
- `api/Transaction/PackingListController.php` — `PACKED`, `PAID`, `PARTIAL PAID`.
- `api/Transaction/SalesOrderController.php` — `DRAFT` saat generate invoice.
- `SalesReturnController` / `CreditNoteController` — tidak mengubah status header.

### Purchase Invoice (`purchase_invoice_header`)
- `api/Transaction/PurchaseInvoiceController.php` — `draft`, `posted`.
- `api/Transaction/VendorBillController.php` — `paid`, `partial`, `posted`.

### Subscription Invoice (`subscription_invoices`)
- `Services/Billing/BillingInvoiceService.php` — satu-satunya penulis status.
- Pemicu: `Jobs/Billing/BillingDailyJob.php`, `Services/Billing/WebhookService.php`, `web/billing/admin/InvoiceController.php`, `web/billing/SubscriptionController.php`.

---

## 6. Status di Transaksi/Sales Payment

Tabel: `sales_payment_header`
Model: `app/Models/Transaction/SalesPaymentHeader.php`

Enum status pada schema (`dokumen/backup/pos2.sql`): `PENDING`, `POSTED`, `CANCELLED`, `NOT PAID`.
Selain itu ada status tampilan **virtual** `WAITING` yang tidak disimpan di kolom `status`.

### 6.1 Daftar status & lokasi update

| Status | Lokasi | Keterangan |
| --- | --- | --- |
| `PENDING` | `app/Http/Controllers/api/Transaction/SalesPaymentController.php:302` | Saat create payment baru via `submit()`. Edit tidak me-reset status. |
| `PENDING` | `app/Http/Controllers/api/Transaction/SalesPaymentController.php:640` | Payment dari mobile (`sync()`). |
| `NOT PAID` | `app/Http/Controllers/api/Transaction/SalesPaymentController.php:533` | `sync()` ketika `alasan_tidak_bayar` terisi. |
| `POSTED` | `app/Http/Controllers/api/Transaction/SalesPaymentController.php:1064` | `posted()`; set `post_date`/`post_by`, lalu membuat jurnal + sync AR subledger. |
| `POSTED` | `app/Http/Controllers/api/Transaction/SalesPaymentController.php:1139` | `confirmAll()` (bulk); hanya set `status` + `updated_by`, **tanpa** `post_date` dan tanpa jurnal. |
| `CANCELLED` | `app/Http/Controllers/api/Transaction/SalesPaymentController.php:883` | `confirmDelete()`; juga set `deleted`/`deleted_by`. |
| `WAITING` (virtual) | `app/Http/Controllers/api/Transaction/SalesPaymentController.php:79-80` | Filter: `status = PENDING` dan `verificator_id IS NOT NULL`. |
| `WAITING` (virtual) | `app/Http/Controllers/web/Transaction/SalesPaymentController.php:1073-1074` | Halaman `confirmPayment` memfilter `PENDING` + `verificator_id NOT NULL`. |

Catatan: `submit()` selalu mengisi `verificator_id`/`verificator_date` saat create (`:324-329`), sehingga payment dari web langsung masuk kategori `WAITING`. `PENDING` tanpa verificator umumnya berasal dari `sync()` mobile.

### 6.2 Efek ke status Sales Invoice

| Status invoice | Lokasi | Keterangan |
| --- | --- | --- |
| `PAID` / `PARTIAL PAID` | `SalesPaymentController.php:450` / `:452` | `submit()`; keputusan memakai `outstanding_amount` yang dikirim client. |
| `PAID` / `PARTIAL PAID` | `SalesPaymentController.php:677` | `sync()`; hanya jika `user_group != 5` (salesman). |
| `POSTED` / `PARTIAL PAID` | `SalesPaymentController.php:865` / `:867` | `confirmDelete()` saat `amount_paid` kembali 0 / masih ada sisa. |

`posted()` **tidak** mengubah `sales_invoice_header`; yang diperbarui adalah subledger AR (`ar_payments`/`ar_invoices`) melalui `ArSubledgerService::syncFromJournal()`.

### 6.3 Temuan/risiko

- `confirmAll()` menandai `POSTED` tanpa `post_date` dan tanpa jurnal, berbeda dari `posted()`. Karena guard `posted()` memakai `post_date` (bukan status), payment bisa "di-posted" lagi dan baru dibuatkan jurnal, sementara UI bisa menganggapnya sudah posted.
- `submit()` sudah memanggil `postingGL()` (GL legacy) pada saat status masih `PENDING`, lalu `posted()` memanggil `CustomerPaymentJournalService` (journal engine). Berpotensi double posting.
- `confirmDelete()` hanya memblokir status `CANCELLED`. Payment berstatus `POSTED` boleh dibatalkan, tetapi hanya `cancelAllGL()` legacy yang dipanggil; jurnal dari `posted()` tidak direversal.
- Edit payment `POSTED`: status dan `post_date` tidak di-reset; GL lama di-cancel lalu di-posting ulang di `submit()`.
- `sync()` memakai `$invoice->outstanding_amount` (`:674`), padahal kolom itu tidak ada di tabel `sales_invoice_header` (outstanding hanya alias `total_amount - amount_paid`). Akibatnya nilainya `null` dan `outstanding_after <= 0`, sehingga invoice bisa selalu di-set `PAID` walau pembayaran sebagian (untuk `user_group != 5`).
- `submit()` menentukan `PAID`/`PARTIAL PAID` berdasarkan `outstanding_amount` kiriman client (`:358`, `:449`), bukan hitungan server.
- Penulisan status tidak konsisten kapitalisasinya: `CANCELLED` (payment) vs `CANCELED` (invoice). Tampilan JS mengizinkan hapus untuk `PENDING`/`POSTED`; `NOT PAID` tidak ditangani.

