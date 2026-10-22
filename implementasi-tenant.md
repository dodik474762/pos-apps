# Implementasi Tenant (company_id) — Rencana & Progres

Tanggal: 22 Oktober 2026
Status: **Fase 3 selesai** (Fase 1 & 2 selesai)

## 1. Hasil pengecekan

| Item | Status |
|---|---|
| DB live `pos2` | ✅ **118/131 tabel** punya kolom `company_id BIGINT UNSIGNED NOT NULL DEFAULT 1` + index (`idx_company` / `idx_company_*`) dan unique key per-tenant (`uq_c_*`). 13 tabel tanpa kolom: `company`, `dictionary`, `menu`, `migrations`, `pulse_*`, `region`, `telescope_*` (bukan tabel transaksi). Tidak ada pekerjaan DB tersisa. |
| Kode aplikasi | 🔄 **91 file** sudah memuat referensi `company_id` (Fase 1–3). Sisa **204 file** tanpa referensi: 146 file relevan baru akan disentuh di Fase 4–5, sisanya memang tidak menyentuh tabel tenant. |
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
// G. Helper setCompanyId($model, $company_id) — dipakai untuk MENULIS company_id
//    ke model Eloquent. Semua kolom company_id di DB = NOT NULL DEFAULT 1,
//    jadi menulis NULL akan menggagalkan INSERT/UPDATE. Helper ini hanya
//    menempelkan company_id bila company aktif diketahui; bila kosong kolom
//    dibiarkan memakai DEFAULT database (perilaku sama seperti sebelum
//    scoping tenant → nol regresi).
// H. Raw SQL: filter tenant disisipkan sebagai integer hasil (int) $company_id,
//    Tiap scope/subquery memakai alias sendiri (f/l, pup/pu/m2, pod/po/pu_pod)
//    agar filter tidak bocor ke scope lain.
```

## 2b. Pola penulisan company_id yang aman

```php
// Tulis ke model Eloquent — pakai helper, bukan assignment langsung
setCompanyId($roles, $company_id);           // company_id hanya dipasang bila tidak kosong

// Tulis ke array insert query builder — company_id NOT NULL, jadi jangan pernah
// menulis null; key ditambahkan hanya bila company aktif diketahui
$insert = [ 'product' => $product, /* ... */ ];
if ($company_id !== null && $company_id !== '') {
    $insert['company_id'] = $company_id;
}
DB::table('product_stock')->insert($insert);
```

## 3. Fase eksekusi

- [x] **Fase 1 — Accounting / Journal Engine**
  - `app/Services/Accounting/*` (JournalService, JournalPostingService, JournalValidator, AccountingPeriodService, AccountPeriodBalanceService, GeneralLedgerService, TrialBalanceService, AccountMappingResolver, Ap/ArSubledgerService, *JournalService, ProductCostResolver)
  - `app/Services/ClosingPeriodQuery.php`
  - Controller api+web: Journals, AccountingPeriods, Accounts, Coa, AccountMappingRules (web), Ap/ArSubledger, report GeneralLedger
  - `app/Helper/NoGenerator.php` (`generateNoJournal` dkk)
- [x] **Fase 2 — AP/AR, Purchasing, Penjualan** — controller api+web: PurchaseOrder, PurchaseInvoice, PurchaseReturn, VendorBill, SalesInvoice, SalesPayment, TerimaUang, CreditNote, PLTagihan, ReturnConsigment + `NoGenerator` (`generateNoPO/SI/SP/CN/PL/Payment`)
- [x] **Fase 3 — Inventory / Product** — Product, Item, ProductType, PromoItem (api+web), ProductDiscount/FreeGood, ClosingStock, ProductAdjustmentStock, GoodReceipt, ReportStock, InventoryValuation
- [ ] **Fase 4 — Delivery / Packing** — api+web DeliveryOrder, PackingList, PackingListReturn, PackingListSalesman
- [ ] **Fase 5 — Master & sisanya** — Branch, CustomerCategory, CustomerLimitTop, Dayoff, RoutingApproval, Karyawan, Users, Document, DailyVisit, Presence, Sessions (mobile_session), Dashboard, ReportPenjualan

## 4. Catatan risiko yang sudah diantisipasi

- **Model ganda**: `general_ledgers` punya 2 model (`Models/Transaction/GeneralLedger` + `Models/Accounting/GeneralLedger`); promo item punya varian `Master/*` dan `Transaction/*` — keduanya wajib disesuaikan.
- **Idempotency journal** (cek `reference_type` + `reference_id`) ikut di-scope `company_id` supaya tidak ada duplikat lintas tenant.
- **Tabel tanpa model/referensi kode** (`inventory_balances`, `inventory_stock_mutations`, `retur_delivery_order_*`, `customer_deposit`, `employee`, `price_list`) — dilewati, tidak ada yang diubah.
- Gaya `->when($company_id !== null)` dipilih agar bila `session('id_company')` kosong (route tanpa login / sesi kadaluarsa) perilaku **persis seperti sebelumnya** (tanpa filter) → nol regresi.
- **Kolom `company_id` = `NOT NULL DEFAULT 1`** (diverifikasi Fase 3). Menulis `null` akan menggagalkan INSERT/UPDATE, jadi assignment model memakai helper `setCompanyId()` dan insert query builder hanya menambahkan key `company_id` bila company aktif diketahui. Bila company kosong → kolom dibiarkan memakai DEFAULT (nilai 1), sama seperti sebelum ada scoping tenant.
- **Raw SQL** (ClosingStock, ReportStock, InventoryValuation) memakai string konkatenasi, bukan binding. Filter tenant disisipkan sebagai `(int) $company_id` sehingga aman dari injeksi, dan **tiap scope/subquery memakai alias sendiri** (`f`/`l`, `pup`/`pu`/`m2`, `pod`/`po`/`pu_pod`) agar filter tidak bocor ke scope lain.

## 5. Verifikasi tiap fase

1. `php -l` semua file yang diubah (tanpa error).
2. `php artisan route:list` tetap jalan.
3. Audit grep: setiap file pada fase itu kini memuat referensi `company_id`.
4. Smoke test: login web → list tiap modul masih menampilkan data (company 1 tetap terlihat); submit 1 transaksi API mobile → baris tersimpan dengan `company_id`.

### Hasil verifikasi Fase 3 (22 Oktober 2026)

| No | Pemeriksaan | Hasil |
|---|---|---|
| 1 | `php -l` 17 file yang diubah | ✅ tidak ada error sintaks |
| 2 | `php artisan route:list` | ✅ 625 route tetap terdaftar |
| 3 | Cek kolom `company_id` di DB (`information_schema`) | ✅ 118/131 tabel punya kolom `NOT NULL DEFAULT 1` + index; 13 tabel tanpa (hanya `company`, `dictionary`, `menu`, `migrations`, `pulse_*`, `region`, `telescope_*`) |
| 4 | Audit statement belum ter-scope (script statis, per-statement) | ✅ 0 statement tersisa; 10 temuan terakhir adalah false positive (lookup by PK + insert bersyarat `company_id`) |
| 5 | Audit urutan `$company_id` dipakai sebelum di-assign | ✅ 0 masalah |
| 6 | Smoke test jalur baca (43 method controller/report) | ✅ 43 OK / 0 FAIL |
| 7 | Smoke test jalur tulis di dalam transaksi (submit + confirmDelete: Product, Item, ProductType, PromoItem, AdjustmentStock, GoodReceipt) | ✅ 8 OK / 0 FAIL — `company_id` = 1 terisi di header + detail, rollback bersih (row count kembali seperti semula) |
| 8 | Smoke test method mutasi (updatePrice, updateHargaRetail, removeUomPrice, removeDiscStrata, removeItemDiscFree, removeUom, closing, recalculateFrom) | ✅ 9 OK / 0 FAIL |
| 9 | Sisa data uji di DB setelah rollback | ✅ 0 baris sisa (`PROD-TEST-FASE3`, `ITEM-TEST-FASE3`, `TYPES-TEST`, `PROMO-TEST-FASE3`, dst.) |
| 10 | `vendor/bin/pint --test` | ℹ️ 17 file yang sama sudah gagal Pint sebelum perubahan (style issues warisan) — tidak diformat ulang agar diff tetap fokus pada tenant |

## 6. Progres

- [x] Pengecekan DB: 89/89 tabel sudah punya `company_id` (diverifikasi via `SHOW COLUMNS` ke `pos2`).
- [x] Fase 1 — Accounting / Journal Engine _(selesai)_
- [x] Fase 2 — AP/AR, Purchasing, Penjualan _(selesai)_
  - API: PurchaseOrder, PurchaseInvoice, PurchaseReturn, VendorBill, PLTagihan, TerimaUang, ReturnConsigment, CreditNote, SalesInvoice, SalesPayment — list/detail/submit/delete/posting/outstanding/GL sudah di-scope `company_id`; nomor dokumen per tenant.
  - Web: 10 controller modul yang sama di-scope (dropdown master, query detail, lookup cetak, generator nomor modul via `generateNoXX($company_id)`).
  - `NoGenerator`: seluruh generator + helper GL (`postingGL`, `cancelGL`, `cancelAllGL`, `updateAllGL`, `getGeneralLedger`), `stockUpdate`, `stockRollback`, `createAutoReturn`, `getMaxReturCustomer`, `getMaxReturKaryawan`, `canInvoiceProcessCL`, `checkCustomerCreditLimit`, serta helper `stock_cards` (`getMasterCostBase`, `getOriginalDoCost`, `recalculateFrom`, `monthlyReportWithRangeDate`) menerima `$company_id` (default `session('id_company')`).
  - Bug lama diperbaiki: `CreditNoteController` (referensi GL & lookup detail invoice), web `PurchaseInvoiceController::ubah()` (`$company_id` undefined), web VendorBill tax filter, generator `generateNoPO()` yang salah dipakai di modul lain.
  - Deferred (bukan scope Fase 2): `SalesOrderController::saveInvoice()` (dipanggil dari SalesInvoice API), service `app/Services/Accounting/*` dan `api/Transaction/JournalsController`.
- [x] Fase 3 — Inventory / Product _(selesai)_
  - **API master**: `ProductController` (getData, getDataProduct, getDataProductMobile, getProductCatalog, getProductMasterCatalog, submit_import, submit_stock, submit, confirmDelete, removeUom, removeUomPrice, removeDiscStrata, removeItemDiscFree, getDetailData, addItemPrice/DiscStrata/DiscFreeGood, updatePrice, updateHargaRetail), `ItemController`, `ProductTypeController`, `PromoItemController` (header + detail/free/syarat) — list/detail/submit/delete di-scope `company_id`; `company_id` ditulis ke `product`, `items`, `items_log`, `product_type`, `product_log`, `product_uom`, `product_uom_price`, `product_uom_price_log`, `product_uom_cost`, `product_discount`, `product_free_good`, `product_promo_item*`, `product_stock`, `product_stock_move`.
  - **Web master**: `ProductController` (dropdown product_type/unit/vendor/customer_category, list uom/price/disc strata/free good, log, stock, cost, form ubah/detail, helper `generateStock()` & `updateProduct()`), `ItemController` (dropdown product_type/unit + item log), `PromoItemController` (detail promo/free/syarat).
  - **API transaction**: `ClosingStockController` (getData + getStockCardLogDetail + closing + recalculate — termasuk raw SQL `stock_cards`), `ProductAdjustmentStockController` (getData, submit, confirmDelete, getDetailData, getDataProduct, getListVendor), `GoodReceiptController` (getData, submit, confirmDelete, getDetailData, delete, showModalPostJurnal, postJurnal, getListItemOutstandingPO) — `GoodReceiptJournalService` sudah ikut ter-scope lewat `company_id` header.
  - **Web transaction**: `ClosingStockController`, `ProductAdjustmentStockController` (dropdown warehouse + detail adjustment), `GoodReceiptController` (dropdown vendor/warehouse/tax/PO, detail GR, GL print, cetak PDF).
  - **Report**: `ReportStockController` (getData, getDataStock, getDataStockDetail — raw SQL `product_stock_move`, subquery PO draft & harga), `InventoryValuationController` (getData, getSummary — raw SQL `stock_cards` + subquery opening/closing value).
  - **`NoGenerator`**: helper baru `setCompanyId($model, $company_id)`; `generateCodeProduct`, `generateNoAdjStock`, `generateGrNumber` menerima `$company_id`; helper konversi satuan (`getSmallestUnit`, `getSmallestUnitV2`, `getLargestUnit`, `getHargaSemuaUnit`, `konversiSatuan`, `normalisasiQtyUom`) di-scope `product_uom`; helper `mapPurchases/mapSales/mapCancelDo/mapAdjustments/mapReturnsIn` di-scope tenant; `costMovement` menerima `company_id` lewat array `$m`; `stockUpdate`/`stockRollback` insert `company_id` hanya bila company diketahui (kolom NOT NULL).
  - **Bug lama diperbaiki**: `ProductAdjustmentStockController::confirmDelete()` memakai `RoutingHeader` (kelas tidak di-import → fatal error) diganti `ProductAdjustmentStock`; `costMovement()` memakai `$company_id` yang tidak pernah terdefinisi; `submit_import()` memakai `$company_id` sebelum di-assign; `ClosingStockController` lookup `stock_closing` memakai `where('company_id', null)` (selalu kosong) → diganti `when()`.
- [ ] Fase 4 — Delivery / Packing
- [ ] Fase 5 — Master & sisanya