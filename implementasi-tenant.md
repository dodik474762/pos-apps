# Implementasi Tenant (company_id) — Rencana & Progres

Tanggal: 19 Oktober 2026
Status: **Fase 1 berjalan**

## 1. Hasil pengecekan

| Item | Status |
|---|---|
| DB live `pos2` | ✅ **89/89 tabel** pada daftar ALTER TABLE sudah punya kolom `company_id BIGINT UNSIGNED NOT NULL DEFAULT 1` + index (`idx_company` / `idx_company_*`) dan unique key per-tenant (`uq_c_*`). Tidak ada pekerjaan DB tersisa. |
| Kode aplikasi | ❌ **146 file** mereferensikan tabel target tapi **nol** referensi `company_id`; **28 file** sudah di-scope sebagian (warisan commit `*-company`). |
| Sumber company_id aktif | Web → `session('id_company')` (di-set `LoginController::signIn` dari `karyawan.company`). Mobile/JWT → `Users::where('id', $userId)->value('company_id')`. |

## 2. Pola standar (additive-only — logic existing tidak diubah)

```php
// A. Baca (SELECT) — kondisional, nol risiko regresi jika company kosong
$company_id = session('id_company');                            // web
$company_id = Users::where('id', $userId)->value('company_id'); // JWT mobile
$datadb->when($company_id !== null && $company_id !== '',
    fn ($q) => $q->where('m.company_id', $company_id));

// B. Tulis via property assignment (paling umum di repo ini)
$model->company_id = $company_id;

// C. Tulis via array / insert()
DB::table('x')->insert([..., 'company_id' => $company_id]);

// D. Model dengan $fillable → tambahkan 'company_id'
// E. app/Helper/NoGenerator.php: 16 fungsi generateNo* → tambah param opsional
//    $company_id + ->when(...) — mengikuti pola yang sudah ada di generateNoSO().
// F. Journal engine: JANGAN ubah signature method.
//    Derive dari record sumber: $company_id = $header->company_id; lalu
//    propagate ke insert journal_headers / journal_details / general_ledgers /
//    account_period_balances + scope lookups (accounts, coa, product,
//    product_uom_cost) + scope idempotency check
//    JournalHeader::where('reference_type', 'reference_id').
```

## 3. Fase eksekusi

- [ ] **Fase 1 — Accounting / Journal Engine**
  - `app/Services/Accounting/*` (JournalService, JournalPostingService, JournalValidator, AccountingPeriodService, AccountPeriodBalanceService, GeneralLedgerService, TrialBalanceService, AccountMappingResolver, Ap/ArSubledgerService, *JournalService, ProductCostResolver)
  - `app/Services/ClosingPeriodQuery.php`
  - Controller api+web: Journals, AccountingPeriods, Accounts, Coa, AccountMappingRules (web), Ap/ArSubledger, report GeneralLedger
  - `app/Helper/NoGenerator.php` (`generateNoJournal` dkk)
- [ ] **Fase 2 — AP/AR, Purchasing, Penjualan** — controller api+web: PurchaseOrder, PurchaseInvoice, PurchaseReturn, VendorBill, SalesInvoice, SalesPayment, TerimaUang, CreditNote, PLTagihan, ReturnConsigment + `NoGenerator` (`generateNoPO/SI/SP/CN/PL/Payment`)
- [ ] **Fase 3 — Inventory / Product** — Product, Item, ProductType, PromoItem (api+web), ProductDiscount/FreeGood, ClosingStock, ProductAdjustmentStock, GoodReceipt, ReportStock, InventoryValuation
- [ ] **Fase 4 — Delivery / Packing** — api+web DeliveryOrder, PackingList, PackingListReturn, PackingListSalesman
- [ ] **Fase 5 — Master & sisanya** — Branch, CustomerCategory, CustomerLimitTop, Dayoff, RoutingApproval, Karyawan, Users, Document, DailyVisit, Presence, Sessions (mobile_session), Dashboard, ReportPenjualan

## 4. Catatan risiko yang sudah diantisipasi

- **Model ganda**: `general_ledgers` punya 2 model (`Models/Transaction/GeneralLedger` + `Models/Accounting/GeneralLedger`); promo item punya varian `Master/*` dan `Transaction/*` — keduanya wajib disesuaikan.
- **Idempotency journal** (cek `reference_type` + `reference_id`) ikut di-scope `company_id` supaya tidak ada duplikat lintas tenant.
- **Tabel tanpa model/referensi kode** (`inventory_balances`, `inventory_stock_mutations`, `retur_delivery_order_*`, `customer_deposit`, `employee`, `price_list`) — dilewati, tidak ada yang diubah.
- Gaya `->when($company_id !== null)` dipilih agar bila `session('id_company')` kosong (route tanpa login / sesi kadaluarsa) perilaku **persis seperti sebelumnya** (tanpa filter) → nol regresi.

## 5. Verifikasi tiap fase

1. `php -l` semua file yang diubah (tanpa error).
2. `php artisan route:list` tetap jalan.
3. Audit grep: setiap file pada fase itu kini memuat referensi `company_id`.
4. Smoke test: login web → list tiap modul masih menampilkan data (company 1 tetap terlihat); submit 1 transaksi API mobile → baris tersimpan dengan `company_id`.

## 6. Progres

- [x] Pengecekan DB: 89/89 tabel sudah punya `company_id` (diverifikasi via `SHOW COLUMNS` ke `pos2`).
- [x] Fase 1 — Accounting / Journal Engine _(selesai)_
- [ ] Fase 2 — AP/AR, Purchasing, Penjualan
- [ ] Fase 3 — Inventory / Product
- [ ] Fase 4 — Delivery / Packing
- [ ] Fase 5 — Master & sisanya
