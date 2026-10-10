# Closing Periode & Account Period Balances (Phase 9 GL)

## Tujuan
Saat periode akuntansi ditutup, sistem membuat **snapshot saldo per akun** ke tabel `account_period_balances`. Snapshot ini digunakan agar:
- Laporan Trial Balance untuk periode `CLOSED` bisa dibuka cepat tanpa agregasi ulang.
- Rantai `opening_balance` → `closing_balance` antar periode tetap terjaga.
- Periode yang sudah `CLOSED` bersifat **immutable** (tidak berubah lagi) karena tidak boleh ada jurnal `DRAFT/POSTED` baru.

## Tabel `account_period_balances`

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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_period_balances_account_period_unique` (`account_id`,`accounting_period_id`),
  CONSTRAINT `account_period_balances_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  CONSTRAINT `account_period_balances_period_id_foreign` FOREIGN KEY (`accounting_period_id`) REFERENCES `accounting_periods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

- Tabel ini **hanya diisi saat close periode**, bukan saat post jurnal.
- Baris dibuat untuk **semua akun aktif** (`accounts.is_active=1 AND accounts.deleted_at IS NULL`), termasuk yang tidak punya mutasi di periode tsb (nilai 0). Hal ini penting agar rantai opening balance tidak putus.
- `updated_at` ada (snapshot bisa di-upsert jika dipanggil ulang) meski alur normal hanya 1x.

## Kapan `account_period_balances` terisi?

Hanya ketika `AccountingPeriodService->close($id)` dijalankan dan berhasil.

Alur `close()` (di `app/Services/Accounting/AccountingPeriodService.php`):

1. `DB::beginTransaction()`
2. Validasi periode ada
3. Validasi **tidak ada jurnal DRAFT** di rentang periode (`journal_headers.journal_date BETWEEN start_date AND end_date AND status='DRAFT'`) → jika ada, rollback & gagal
4. **Validasi berurutan**: cari periode sebelumnya (year<cur OR (year==cur AND month<cur)), pastikan berstatus `CLOSED`. Jika ada periode sebelumnya tapi belum `CLOSED`, close ditolak.
5. Update `accounting_periods`: `status = 'CLOSED'`, `closed_at = now()`, `closed_by = user_id` (dari session atau parameter)
6. **Snapshot**: panggil `AccountPeriodBalanceService->snapshotPeriod($period->id)` 
7. Commit jika semua sukses. Jika snapshot gagal → `throw` → rollback (status periode kembali `OPEN`)

## Cara kerja `snapshotPeriod()`

Lokasi: `app/Services/Accounting/AccountPeriodBalanceService.php`

Proses (dalam transaction):

1. Cek periode ada dan berstatus `CLOSED`
2. Cari periode sebelumnya (terbaru sebelum periode yg di-snapshot) untuk dapatkan `closing_balance`-nya
3. Ambil semua akun aktif: `Accounts::whereNull('deleted_at')->where('is_active',1)->orderBy('code')->get()`
4. Untuk tiap akun:
   - `opening_balance` = 0. Jika periode sebelumnya ada & sudah punya snapshot, ambil `account_period_balances.closing_balance` periode sebelumnya
   - Hitung `total_debit` & `total_credit` dari `journal_details jd JOIN journal_headers jh` dengan `jh.accounting_period_id = periodId`, `jh.status = POSTED`, `jd.account_id = akun.id` (agregasi SUM)
   - `closing_balance` dihitung berdasar `normal_balance` akun:
     - `normal_balance = 'DEBIT'` → `closing = opening + (total_debit - total_credit)`
     - `normal_balance = 'CREDIT'` → `closing = opening + (total_credit - total_debit)`
   - Upsert baris ke `account_period_balances` (berdasarkan `account_id + accounting_period_id`)

Catatan: tidak memfilter `is_header=0` saat snapshot. Snapshot dibuat untuk **semua akun aktif** agar struktur COA utuh & aman untuk periode berikutnya.

## Kapan TIDAK pakai snapshot?

- **Periode `OPEN`** → `TrialBalanceService->generate()` dan `GeneralLedgerService->getLedger()` **hitung live** dari `journal_details` + `journal_headers` berstatus `POSTED`.
- **Periode `CLOSED`** → 
  - `TrialBalanceService` membaca langsung `account_period_balances` (hanya akun `is_header=0`, non-zero ditampilkan, saldo ditampilkan di kolom Debit/Kredit **berdasarkan tanda** closing balance per PRD).
  - `GeneralLedgerService` tetap mengambil mutasi **live** dari jurnal periode tsb (urut `journal_date`, `id`). `opening_balance` diambil dari snapshot periode sebelumnya (jika sebelumnya CLOSED) atau dari snapshot periode itu sendiri sesuai konteks. Ini menjaga GL per akun tetap detail (baris jurnal) sekaligus konsisten dengan rantai saldo.

## Penting untuk diketahui

- **Sequential closing wajib**. Kalau periode bulan 8 CLOSED, bulan 9 baru bisa ditutup. Kalau ada yang terlewat (mis. bulan 8 masih OPEN), close bulan 9 akan ditolak. Ini mencegah `opening_balance` salah.
- **Rollback aman**. Karena snapshot & update status dilakukan dalam **satu transaction**, kalau snapshot gagal di tengah loop → seluruh close dibatalkan (periode tetap `OPEN`, tidak ada baris snapshot setengah jadi).
- **Data sumber GL/TB** tetap `journal_headers + journal_details` (bukan tabel `general_ledgers` legacy). Tabel legacy `general_ledgers` dipakai untuk tampilan "Informasi General Ledger" di form transaksi, terpisah dari Phase 9 GL report.
- **Reopen** saat ini **belum** menghapus snapshot. Sesuai PRD, reopen adalah kasus khusus di luar alur normal; penyesuaian bisa ditambahkan nanti jika diperlukan.

## File terkait

- Service: `app/Services/Accounting/AccountPeriodBalanceService.php`
- Service: `app/Services/Accounting/AccountingPeriodService.php` (method `close()`)
- Service: `app/Services/Accounting/GeneralLedgerService.php`
- Service: `app/Services/Accounting/TrialBalanceService.php`
- Migration: `database/migrations/2026_10_09_133634_create_account_period_balances_table.php`
- Model: `app/Models/Accounting/AccountPeriodBalance.php`
