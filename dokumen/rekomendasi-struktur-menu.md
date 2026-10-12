# Rekomendasi Struktur Menu

Tujuannya: mempermudah pembagian modul, scaling, dan maintain. Jangan ubah sekarang, cukup rekomendasi.

## Struktur Saat Ini (Ringkas)

Top level utama:
- Dashboard (10)
- Master (1) → banyak master umum
- Users & Permissions (3)
- Data (6) - kosong
- Transaksi (14) → campur (OP/AR/AP/HR/Finance)
- Approval (55) → approval per modul
- Salesman (121) → modul salesman
- Attandance (66) - kosong
- Report (36) → laporan + subledger + GL/TB
- Wilayah (73) - kosong

## Permasalahan

- Transaksi (14) terlalu besar: berisi transaksi operasional, HR (presensi/cuti/perdin), finance (invoice/reimbursement), hingga "Journals" (accounting) dan "Closing Period" (monitoring) - mixing concern.
- Report (36) juga campur: operasional (penjualan, piutang, stok), finance accounting (GL, TB, AR/AP Subledger).
- Accounting terkait (Accounts, Account Types, Account Mapping, Accounting Periods, Journals, Closing Period, GL, TB, AR/AP Subledger) tersebar di Master/Transaksi/Report.

## Rekomendasi Usulan Struktur

Usulan pembagian lebih tegas:

1. **Master Data** (tetap) → data referensi non-transaksi: Perusahaan, Customer/Vendor, Produk, Wilayah (kota/kec/kel), COA-related master (Account Types, Accounts, Account Mapping Rules), User/Role/Permission.
2. **Purchasing** → PO, GR, Purchase Invoice, Vendor Bill, Purchase Return
3. **Sales** → SO, DO, Sales Invoice, Sales Payment, Sales Return, Credit Note, Terima Uang, Return Consigment, Packing dll yang terkait penjualan
4. **Inventory** → Adjustment Stock, Closing Stock, Stock Card, Inventory Valuation (bisa dipindah ke Inventory Reports atau tetap)
5. **Human Resource / Attendance** → Presensi, Employee Leave, Perdin, dll (saat ini di Transaksi)
6. **Accounting / Finance** → **pemisahan eksplisit**
   - Master Accounting: Accounting Periods, Accounts, Account Types, Account Mapping Rules (saat ini ada di Master) — bisa jadi sub-menu "Accounting" di Master, atau buat top-level "Accounting" baru.
   - Transaction Accounting: Journals (Manual Journal), Closing Period (proses tutup periode) — lebih cocok di top-level **Accounting > Transactions** bukan di Transaksi umum.
   - Reports Accounting: General Ledger, Trial Balance, AR Subledger, AP Subledger — lebih cocok di **Accounting > Reports** ketimbang bercampur di Report umum.
7. **Reports** (umum/operasional) → pisahkan dari Accounting Reports. Contoh: Sales Report, Stock Report, Visit Report, Piutang/Umum non-GL.
8. **Approval** → tetap terpisah (cross-module)
9. **Settings/System** → Users & Permissions, Menu, Routing Approval, dll.

## Rekomendasi Konkret (minimal, low risk)

Biar nggak bikin breaking change besar, cukup **relokasi 3-4 menu accounting**:

- Pindahkan **Journals** (158) dari `Transaksi` ke `Accounting > Transactions` (buat parent baru)
- Pindahkan **Closing Period** (163) dari `Transaksi` ke `Accounting > Closing` atau `Accounting > Period` 
- Pindahkan **General Ledger** (164) & **Trial Balance** (165) dari `Report` ke `Accounting > Reports`
- Biarkan **AR Subledger** (159) & **AP Subledger** (160) di `Report` atau pindah ke `Accounting > Reports` (keduanya subledger akuntansi) — rekomendasi: pindah ke Accounting Reports biar konsisten

## Rekomendasi Penamaan Parent Baru

Tambahkan top-level baru: **Accounting** (mis. urutan setelah Approval/Salesman/Attendance atau sebelum Report). Atau jadikan sub-grouping.

Contoh struktur baru:

```
- Accounting (baru)
  ├── Master
  │   ├── Account Types
  │   ├── Accounts
  │   ├── Account Mapping Rules
  │   └── Accounting Periods
  ├── Transactions
  │   ├── Journals
  │   └── Closing Period
  └── Reports
      ├── General Ledger
      ├── Trial Balance
      ├── AR Subledger
      └── AP Subledger
```

Master-accounting items (154–157) bisa tetap di Master (parent 1) atau dipindah ke Accounting>Master. Keduanya valid; **rekomendasi minimal**: cukup buat top-level Accounting baru & relokasi 158,163,164,165 (+159,160) tanpa menghapus yang lama dulu (bisa soft-move via update `parent`).

## Catatan Implementasi (bukan sekarang)

Kalau mau dipindah, cukup update `menu` table:
- Tambah row `Accounting` (parent null, urutan wajar)
- Update `parent` untuk id 158,163,164,165 ke id Accounting baru
- Opsional: update `parent` 159,160 juga
- Tidak perlu ubah route/controller/view

**Rekomendasi final:** buat **top-level "Accounting"** terpisah, lalu grupkan Journals+Closing Period di bawahnya, dan **GL/TB/AR/AP Subledger** dipindah ke **Accounting > Reports**. Ini paling bersih untuk modul akuntansi Phase 9 ke depan.
