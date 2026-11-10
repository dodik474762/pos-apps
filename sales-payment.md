# PRD: Modul Sales Payment (Penerimaan Pembayaran Penjualan)

| | |
|---|---|
| **Versi** | 1.0 (Draft) |
| **Tanggal** | 9 November 2026 |
| **Modul** | Transaksi > Sales Payment |
| **Status** | Draft untuk review — mendokumentasikan kondisi saat ini (as-is) + gap |
| **Stack** | Laravel (PHP), multi-tenant (`company_id`), web + mobile API |

---

## 1. Latar Belakang

Sales Payment mencatat penerimaan uang dari customer atas invoice penjualan (`sales_invoice_header`). Modul ini mengalokasikan pembayaran ke satu atau beberapa invoice, memperbarui sisa tagihan, membentuk jurnal kas/piutang, dan menyinkronkan subledger AR.

Saat ini alur tersedia lewat web (single, bulk, konfirmasi) dan mobile (`sync`). Karena historisnya sempat memakai posting GL langsung lalu bermigrasi ke Journal Engine, ada beberapa jalur yang tumpang tindih. Dokumen ini merangkum perilaku aktual dan menandai area yang perlu diputuskan/diperbaiki.

## 2. Tujuan

1. Mencatat pembayaran customer secara single maupun bulk, dengan alokasi per invoice.
2. Menjaga konsistensi `amount_paid` dan status invoice (`PAID` / `PARTIAL PAID`).
3. Membentuk jurnal double-entry dan subledger AR yang dapat diaudit.
4. Menyediakan alur konfirmasi/posting, pembatalan, cetak, dan rekapan.
5. Mendukung pencatatan "tidak bayar" dari lapangan beserta alasannya.

## 3. Ruang Lingkup

- Input payment manual (web): single dan bulk per customer.
- Input payment dari mobile (`api_mobile/transaksi/sales_payment/sync`).
- Konfirmasi/posting payment dan pembentukan jurnal.
- Pembatalan payment.
- Cetak bukti pembayaran dan rekapan sales/driver.
- Sinkronisasi subledger AR (alokasi FIFO).

### Non-Tujuan

- Split payment per baris invoice (satu detail = satu invoice).
- Refund/overpayment handling otomatis.
- Multi-currency (implementasi memakai IDR).
- Rekonsiliasi bank otomatis.
- Perhitungan pajak pada penerimaan (pajak sudah ada di invoice).

## 4. Pengguna & Hak Akses

| Peran | Kebutuhan |
|---|---|
| **Admin/Keuangan** | Input, edit, posting, batalkan payment, cetak |
| **Salesman (user_group 5)** | Input pembayaran dari mobile; status invoice tidak langsung diubah |
| **Supervisor** | Konfirmasi payment yang masuk (termasuk bulk) |
| **Akuntansi** | Melihat jurnal dan subledger AR yang terbentuk |

## 5. Definisi Status

### 5.1 Status header `sales_payment_header`

Enum schema: `PENDING`, `POSTED`, `CANCELLED`, `NOT PAID`.

| Status | Arti | Ditulis di |
|---|---|---|
| `PENDING` | Payment tersimpan, belum diposting | `submit()` :302, `sync()` :640 |
| `POSTED` | Payment sudah diposting/dikonfirmasi | `posted()` :1064, `confirmAll()` :1139 |
| `CANCELLED` | Payment dibatalkan | `confirmDelete()` :883 |
| `NOT PAID` | Kunjungan lapangan tanpa pembayaran (ada alasan) | `sync()` :533 |
| `WAITING` (virtual) | Tampilan saja: `PENDING` + `verificator_id` terisi | Filter `getData()` :79-80, `confirmPayment` web :1073-1074 |

Catatan: `submit()` selalu mengisi `verificator_id`/`verificator_date` saat create (:324-329), sehingga payment dari web langsung tampil sebagai `WAITING`. `PENDING` tanpa verificator umumnya berasal dari mobile.

### 5.2 Efek ke status invoice (`sales_invoice_header`)

| Status invoice | Ditulis di | Kapan |
|---|---|---|
| `PAID` | `submit()` :450 | Outstanding (dari client) menjadi 0 |
| `PARTIAL PAID` | `submit()` :452 | Outstanding masih > 0 |
| `PAID` / `PARTIAL PAID` | `sync()` :677 | Hanya jika `user_group != 5` |
| `POSTED` | `confirmDelete()` :865 | `amount_paid` kembali 0 |
| `PARTIAL PAID` | `confirmDelete()` :867 | Masih ada sisa pembayaran lain |

`posted()` tidak mengubah status `sales_invoice_header`; yang diperbarui adalah subledger AR.

### 5.3 Subledger AR

`ar_payments` / `ar_invoices` dikelola `ArSubledgerService`:

| Entitas | Status |
|---|---|
| `ar_invoices` | `OPEN`, `PARTIALLY_PAID`, `PAID`, `REVERSED` |
| `ar_payments` | `UNALLOCATED`, `PARTIALLY_ALLOCATED`, `ALLOCATED`, `REVERSED` |
| `ar_allocations` | `ACTIVE`, `REVERSED` |

Alokasi payment ke invoice terbuka dilakukan FIFO berdasarkan `due_date`, `invoice_date`, lalu `id` (`ArSubledgerService` :415-447).

## 6. Model Data

### 6.1 `sales_payment_header`

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint | PK |
| `payment_code` | varchar(100) | Nomor payment (`generateNoSP`) |
| `payment_date` | date | |
| `customer_id` | varchar(20) | Relasi ke `customer` |
| `payment_method` | enum | `CASH`, `BANK`, `GIRO`, `TRANSFER`, `RETURN`, `OFFSET` |
| `total_amount` | decimal(18,2) | Gross (termasuk diskon) |
| `discount_amount` | decimal(18,2) | Diskon pembayaran |
| `net_amount` | decimal(18,2) | Uang yang diterima |
| `reference_no` | varchar(50) | Referensi eksternal / id mobile |
| `remarks` | text | |
| `status` | enum | `PENDING`, `POSTED`, `CANCELLED`, `NOT PAID` |
| `coa_kas` | int | Akun kas/bank legacy |
| `bulk` | int | Penanda input bulk |
| `platform` | varchar(100) | `mobile` untuk sync |
| `verificator_id` / `verificator_date` / `last_verificator` | | Jejak verifikasi |
| `post_date` / `post_by` | | Diisi hanya oleh `posted()` |
| `deleted` / `deleted_by` | | Soft delete |

### 6.2 `sales_payment_detail`

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint | PK |
| `payment_id` | varchar(20) | FK ke header |
| `invoice_id` | varchar(20) | FK ke `sales_invoice_header` |
| `allocated_amount` | decimal(18,2) | Nominal yang dialokasikan |
| `outstanding_amount` | decimal(18,2) | Outstanding invoice saat pembayaran (snapshot) |
| `line_no` | int | |
| `deleted` / `deleted_by` | | Soft delete |

## 7. Alur Bisnis

### 7.1 Input single (web) — `submit()`
1. Validasi mapping akun `SALES_PAYMENT` (piutang usaha, diskon bayar) dan akun kas/bank.
2. Buat header baru dengan status `PENDING` dan `verificator_id` = user saat ini; atau load header jika edit.
3. Simpan detail per invoice; validasi `allocated_amount <= outstanding`.
4. Hitung diskon pembayaran (hanya pembayaran pertama untuk invoice tersebut).
5. Update `amount_paid` invoice dan set `PAID` / `PARTIAL PAID`.
6. Posting GL legacy lewat `postingGL()` (piutang, kas, diskon).
7. Commit.

### 7.2 Edit — `submit()` dengan `id`
- GL lama di-`cancelAllGL()` (:336) lalu di-posting ulang.
- Status header tidak di-reset; jika sudah `POSTED`, tetap `POSTED` termasuk `post_date`.

### 7.3 Input bulk — `submitBulk()`
- Detail dikelompokkan per `customer_id`, lalu `submit()` dipanggil per customer.
- Header dibuat baru dengan `bulk = 1`, status `PENDING`.

### 7.4 Input mobile — `sync()`
- Jika `alasan_tidak_bayar` terisi: buat header `NOT PAID`, tanpa detail/posting.
- Jika normal: buat header `PENDING` (`platform = mobile`), simpan detail, tambah `amount_paid`.
- Status invoice hanya diubah jika `user_group != 5` (salesman tidak mengubah status, menunggu verifikasi).
- Posting GL langsung (`postingGL()`).

### 7.5 Posting/konfirmasi — `posted()`
1. Guard: tolak jika `post_date` sudah terisi.
2. Set `status = POSTED`, `post_date`, `post_by`.
3. `CustomerPaymentJournalService::postFromCustomerPayment()` membuat jurnal.
4. `ArSubledgerService::syncFromJournal()` membentuk `ar_payments` dan alokasi FIFO ke invoice terbuka.
5. Commit; response menyertakan `journal_no`.

### 7.6 Konfirmasi massal — `confirmAll()`
- Ambil payment `PENDING` sesuai id terpilih, set `status = POSTED` dan `updated_by`.
- Tidak mengisi `post_date` dan tidak membentuk jurnal.

### 7.7 Pembatalan — `confirmDelete()`
1. Tolak jika sudah `CANCELLED`.
2. Kembalikan `amount_paid` invoice dan set `POSTED` (jika 0) atau `PARTIAL PAID`.
3. Soft delete detail.
4. `cancelAllGL(reference)` (GL legacy).
5. Set header `CANCELLED` + `deleted`/`deleted_by`.

### 7.8 Cetak & rekapan
- Cetak bukti pembayaran per payment (`cetak`).
- Rekapan sales/driver (`cetakRekapan`) menggabungkan invoice, payment, retur, dan receive payment.
- Halaman `confirmPayment` menampilkan daftar payment `PENDING` + `verificator_id NOT NULL`.

## 8. Aturan Bisnis & Perhitungan

- **BR-01** Satu detail payment hanya untuk satu invoice.
- **BR-02** `allocated_amount` tidak boleh melebihi outstanding invoice; jika melebihi, transaksi ditolak.
- **BR-03** `allocated_amount` tidak boleh lebih kecil dari diskon pembayaran pada baris tersebut.
- **BR-04** Diskon pembayaran hanya dihitung pada pembayaran pertama/kedua untuk invoice (sesuai hitungan `jumlahInvoicePayment`).
- **BR-05** `net_amount = Σ(allocated_amount - discount)`; `total_amount = Σ allocated_amount`.
- **BR-06** Status invoice menjadi `PAID` bila outstanding hasil alokasi = 0, selain itu `PARTIAL PAID`.
- **BR-07** Posting jurnal resmi (Journal Engine) hanya dilakukan di `posted()`, memakai akun `bank_account_id`.
- **BR-08** Subledger AR mengalokasikan payment secara FIFO ke invoice `OPEN`/`PARTIALLY_PAID`.
- **BR-09** Payment yang sudah `CANCELLED` tidak boleh dibatalkan ulang.
- **BR-10** Semua operasi tulis memakai `DB::transaction()`.

## 9. Functional Requirements (kondisi saat ini)

| ID | Requirement | Status |
|---|---|---|
| FR-01 | Input payment single dengan alokasi ke satu/lebih invoice | Ada (`submit`) |
| FR-02 | Input bulk per customer | Ada (`submitBulk`) |
| FR-03 | Input dari mobile termasuk alasan tidak bayar | Ada (`sync`) |
| FR-04 | Status `PENDING` saat dibuat, `POSTED` saat diposting | Ada |
| FR-05 | Update status invoice menjadi `PAID`/`PARTIAL PAID` | Ada (dengan gap, lihat §10) |
| FR-06 | Jurnal double-entry saat posting | Ada (`posted`) |
| FR-07 | Subledger AR + alokasi FIFO | Ada (`syncFromJournal`) |
| FR-08 | Pembatalan payment + rollback status invoice | Ada (`confirmDelete`) |
| FR-09 | Konfirmasi massal | Ada (`confirmAll`, tanpa jurnal) |
| FR-10 | Cetak bukti & rekapan | Ada |

## 10. Gap Analysis / Temuan

Prioritas: **P0** = berisiko data/jurnal salah, **P1** = inkonsistensi status, **P2** = perbaikan kualitas.

| ID | Temuan | Dampak | Prioritas |
|---|---|---|---|
| GAP-01 | `confirmAll()` set `POSTED` tanpa `post_date` dan tanpa jurnal (`:1139`) | Status `POSTED` tanpa jurnal; `posted()` masih bisa dipanggil karena guard memakai `post_date` | P0 |
| GAP-02 | `submit()` sudah `postingGL()` (legacy) lalu `posted()` membentuk jurnal lagi | Potensi double posting GL vs Journal Engine | P0 |
| GAP-03 | `confirmDelete()` hanya `cancelAllGL()` legacy; jurnal hasil `posted()` tidak direversal | Pembayaran `CANCELLED` tetapi jurnal masih aktif | P0 |
| GAP-04 | `sync()` memakai `$invoice->outstanding_amount` (`:674`) yang tidak ada di tabel `sales_invoice_header` | Nilai `null` → invoice bisa selalu `PAID` walau bayar sebagian | P0 |
| GAP-05 | `submit()` menentukan `PAID`/`PARTIAL PAID` dari `outstanding_amount` kiriman client (`:358`, `:449`) | Status dapat dimanipulasi / tidak akurat | P1 |
| GAP-06 | Edit payment `POSTED` tidak me-reset status/`post_date`, tetapi GL di-cancel dan diposting ulang | Status dan jurnal bisa tidak sinkron | P1 |
| GAP-07 | `submit()` mengisi `verificator_id` saat create sehingga web langsung `WAITING`; `PENDING` hanya dari mobile | Semantik status verifikasi kabur | P2 |
| GAP-08 | Ejaan tidak konsisten: `CANCELLED` (payment) vs `CANCELED` (invoice) | Risiko query/filter lintas modul | P2 |
| GAP-09 | `NOT PAID` tidak ditangani di aksi UI hapus/konfirmasi tertentu | Data lapangan berstatus khusus tidak terkelola seragam | P2 |

## 11. State Machine

```
                 submit()/sync()
                       │
                       ▼
                  ┌─────────┐   posted()        ┌────────┐
                  │ PENDING │ ────────────────> │ POSTED │
                  └─────────┘                   └────────┘
                       │                         │
      sync() alasan    │                         │ confirmDelete()
      tidak bayar      ▼                         ▼
                  ┌─────────┐                ┌───────────┐
                  │ NOT PAID│                │ CANCELLED │
                  └─────────┘                └───────────┘

  confirmAll(): PENDING ──> POSTED (tanpa post_date/jurnal)
```

## 12. Acceptance Criteria

- **AC-01** Payment baru tersimpan dengan status `PENDING` dan detail alokasi benar.
- **AC-02** Setelah `posted()`, status `POSTED`, `post_date` terisi, jurnal terbentuk, dan `ar_payments` teralokasi FIFO.
- **AC-03** Invoice yang teralokasi penuh menjadi `PAID`; sebagian menjadi `PARTIAL PAID`.
- **AC-04** Pembatalan payment mengembalikan `amount_paid` dan status invoice, serta membatalkan jurnal terkait.
- **AC-05** Payment `CANCELLED` tidak dapat dibatalkan dua kali.
- **AC-06** Nilai `allocated_amount` tidak boleh melebihi outstanding maupun lebih kecil dari diskon.
- **AC-07** Payment mobile dengan alasan tidak bayar tersimpan sebagai `NOT PAID` tanpa jurnal.
- **AC-08** Tidak ada double posting GL dan status `POSTED` selalu konsisten dengan keberadaan jurnal.

## 13. Open Questions

1. Apakah `postingGL()` legacy di `submit()`/`sync()` masih diperlukan, atau seluruh posting harus lewat Journal Engine di `posted()`?
2. Apakah `confirmAll()` seharusnya memanggil alur `posted()` (dengan jurnal dan `post_date`)?
3. Apakah pembatalan payment `POSTED` wajib membuat jurnal reversal resmi?
4. Bagaimana seharusnya `NOT PAID` diperlakukan pada proses verifikasi dan laporan?
5. Apakah `WAITING` perlu dijadikan status tersimpan, bukan filter virtual?
6. Apakah status invoice untuk pembayaran salesman (`user_group = 5`) harus tetap berubah atau menunggu verifikasi?

## 14. Referensi Implementasi

- Controller API: `app/Http/Controllers/api/Transaction/SalesPaymentController.php`
- Controller web: `app/Http/Controllers/web/Transaction/SalesPaymentController.php`
- Model: `app/Models/Transaction/SalesPaymentHeader.php`, `SalesPaymentDtl.php`
- Jurnal: `app/Services/Accounting/CustomerPaymentJournalService.php`
- Subledger AR: `app/Services/Accounting/ArSubledgerService.php`
- Schema: `dokumen/backup/pos2.sql` (`sales_payment_header` :400666, `sales_payment_detail` :395237)
- View: `resources/views/web/sales_payment/*`
- JS: `public/assets/js/controllers/transaction/sales_payment.js`
- Route: `routes/web.php:780-789` (API), `:364-371` (web)
