# PRD — Phase 9: General Ledger

Dokumen ini adalah pecahan khusus dari PRD Modul Accounting (Phase 1–9), fokus ke Phase 9 — General Ledger. Untuk konteks phase-phase lain, lihat PRD utama.

## Definisi

General Ledger (GL) **tidak memproses transaksi baru** — GL murni membaca data yang sudah ada di `journal_headers`, `journal_details`, dan `accounts`. Fokus desain Phase 9 ada pada 2 hal: bagaimana menyajikan mutasi per akun dengan benar, dan bagaimana membuat laporan periode yang sudah closed tetap cepat dibuka tanpa menghitung ulang dari nol setiap saat.

## Komponen

### 1. GeneralLedgerService->getLedger(accountId, periodId)

- Ambil semua `journal_details` dari `journal_headers` berstatus `POSTED` dengan `accounting_period_id` sesuai, untuk `account_id` tsb, urut `journal_date` lalu `id`.
- Saldo berjalan dihitung sesuai `normal_balance` akun: Debit-normal → `running += debit - credit`; Credit-normal → `running += credit - debit`.
- Baris "Saldo Awal" = saldo akun tsb di akhir periode sebelumnya (lihat bagian Snapshot).
- Baris "Saldo Akhir" = saldo awal + seluruh mutasi periode berjalan.

### 2. TrialBalanceService->generate(periodId)

- Untuk tiap akun non-header (`is_header=0`) yang aktif: hitung saldo akhir periode itu.
- Saldo ditampilkan di kolom Debit **atau** Kredit sesuai tanda saldonya — bukan sesuai `normal_balance` akun. Kalau akun Debit-normal tapi saldonya negatif, tetap ditampilkan di kolom Kredit, begitu juga sebaliknya.
- Total kolom Debit harus selalu sama dengan total kolom Kredit — inilah validasi utama sebelum periode boleh ditutup.

### 3. Snapshot saldo per periode (account\_period\_balances)

Dibuat otomatis sebagai bagian dari `AccountingPeriodService->close()`, untuk **semua akun di COA** — termasuk yang tidak ada mutasinya sama sekali di periode itu (nilainya 0, tapi baris tetap ada supaya rantai opening\_balance ke periode berikutnya tidak putus).

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

Baris snapshot ini **immutable** setelah dibuat, sejalan dengan prinsip "periode closed = final". Karena syarat closing sudah mewajibkan tidak ada jurnal `DRAFT`, baris ini dijamin tidak berubah lagi kecuali periode di-reopen (kasus khusus di luar alur normal).

## Live vs Snapshot

| Status Periode | Sumber Trial Balance | Karakteristik |
| --- | --- | --- |
| `OPEN` | Live — agregasi langsung dari `journal_details` | Bisa berubah tiap dibuka, selalu representasi data terkini |
| `CLOSED` | Snapshot — baca langsung dari `account_period_balances` | Immutable, sangat cepat (1 query tanpa agregasi) |

Saldo awal periode yang masih `OPEN` diambil dari `closing_balance` snapshot periode sebelumnya (kalau sudah closed) — bukan dihitung ulang dari seluruh histori jurnal sejak awal.

## Prasyarat Penting

Closing periode **harus berurutan tanpa lompat** — periode sebelumnya wajib sudah closed dulu sebelum periode berikutnya bisa ditutup. Ini karena `snapshotPeriod()` bergantung penuh pada `closing_balance` periode sebelumnya yang sudah final dan benar. Kalau ada periode yang di-skip closing-nya, rantai `opening_balance` ke periode-periode setelahnya akan putus dan salah.

## Urutan Implementasi

1. **Model & migration `AccountPeriodBalance`** — migration dijalankan sendiri, model dengan relasi `belongsTo` ke `Account` dan `AccountingPeriod`.
2. **`AccountPeriodBalanceService->snapshotPeriod(periodId)`** — loop semua baris `accounts`, untuk tiap akun: ambil `opening_balance` dari `closing_balance` akun itu di snapshot periode sebelumnya (0 kalau periode pertama), hitung `total_debit`/`total_credit` dari `journal_details` periode ini, hitung `closing_balance` sesuai `normal_balance`, insert 1 baris. Dalam satu DB transaction.
3. **Sisipkan panggilan snapshot ke `AccountingPeriodService->close()`** yang sudah ada — setelah status berhasil di-update jadi `CLOSED`, panggil `snapshotPeriod()` di transaction yang sama, sebelum commit. Kalau snapshot gagal, status `CLOSED` juga ikut rollback.
4. **`GeneralLedgerService->getLedger()`** — saldo awal dari snapshot periode sebelumnya (atau snapshot periode itu sendiri kalau sudah closed), mutasi tetap live dari `journal_details`.
5. **`TrialBalanceService->generate()`** — cabang logic: `CLOSED` → baca `account_period_balances`; `OPEN` → hitung live (reuse logic yang sama dengan `snapshotPeriod()`, cuma tidak disimpan).
6. **API endpoint** — GL per akun (drill-down dari Trial Balance) dan Trial Balance per periode. Response GL per akun menyertakan baris "Saldo Awal" di depan dan "Saldo Akhir" di akhir.

## Catatan Implementasi

- Query dasar yang sudah tersedia (`accounts`, `journal_headers`, `journal_details`, `accounting_periods`) sudah cukup untuk membangun versi **Live** Trial Balance (langkah 4-5) tanpa menunggu tabel `account_period_balances` selesai — bisa dites lebih dulu secara terpisah.
- Filter `accounting_periods WHERE status='OPEN'` yang mungkin sudah dipakai di tempat lain **perlu dilonggarkan** untuk kebutuhan GL — dropdown period selector dan Trial Balance historis butuh menampilkan periode `CLOSED` juga.
- Langkah 3 (sisip ke `close()`) adalah titik paling kritis — sebaiknya diuji dulu di periode dengan data dummy/sedikit, pastikan rollback benar-benar membatalkan status `CLOSED` kalau snapshot gagal di tengah loop.
