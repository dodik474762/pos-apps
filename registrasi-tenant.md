# Dokumentasi Registrasi dan Langganan Tenant

Dokumen ini menjelaskan alur registrasi tenant, berlangganan, pembayaran, trial, dan operasional billing di aplikasi POS.

## 1. Ringkasan

Saat ini aplikasi **belum memiliki halaman registrasi publik/self-signup**. Tenant/company dibuat oleh Super Admin melalui menu **Master Data > Perusahaan**. Setelah company dibuat, sistem otomatis membuat langganan **TRIAL**.

Alur besarnya:

```text
Company dibuat
  -> TRIAL (default 14 hari)
  -> Customer memilih paket
  -> Invoice dibuat
  -> Pembayaran dikonfirmasi
  -> ACTIVE
  -> Invoice perpanjangan dibuat H-7
  -> Pembayaran memperpanjang periode
```

Jika pembayaran terlambat:

```text
ACTIVE -> PAST_DUE -> masa tenggang 7 hari -> SUSPENDED
```

## 2. Aktor

| Aktor | Tanggung jawab |
|---|---|
| Super Admin | Membuat company, mengatur paket, memantau langganan, menandai pembayaran manual, suspend/aktifkan, dan impersonate. |
| Owner/Admin Tenant | Melihat langganan, memilih paket, membayar tagihan, dan membatalkan langganan. |

## 3. Alur Registrasi Tenant

1. Super Admin login.
2. Buka **Master Data > Perusahaan**.
3. Tambahkan company baru.
4. Setelah company tersimpan, observer otomatis membuat subscription:
   - Status: `TRIAL`
   - Paket default: `starter`
   - `trial_ends_at`: sekarang + `BILLING_TRIAL_DAYS`
   - `current_period_end`: sama dengan akhir trial
5. Buat/berikan akun user tenant.
6. User tenant login dan membuka menu **Langganan**.

Catatan:

- Trial hanya dibuat otomatis untuk **company baru**.
- Company lama yang sudah ada saat implementasi tidak otomatis menjadi trial.
- Company lama yang belum memiliki subscription dapat dibuatkan trial dengan command backfill.

## 4. Alur Berlangganan Customer

### 4.1 Melihat Langganan

Menu:

- **Langganan > Langganan Saya**
- URL: `/billing/subscription`

Informasi yang ditampilkan:

- Paket aktif
- Status langganan
- Periode berjalan
- Tanggal trial berakhir
- Masa tenggang
- Pemakaian kuota
- Riwayat status
- Tagihan terbaru

### 4.2 Memilih Paket

Menu:

- **Langganan > Langganan Saya > Pilih / Ganti Paket**
- URL: `/billing/subscription/plans`

Langkah:

1. Pilih paket: **Starter**, **Pro**, atau **Business**.
2. Pilih siklus: **Bulanan** atau **Tahunan**.
3. Klik **Pilih Paket**.

Perilaku sistem:

| Kondisi | Hasil |
|---|---|
| Masih trial | Invoice dibuat. Paket baru aktif setelah pembayaran. |
| Upgrade | Invoice dibuat. Paket baru aktif setelah pembayaran. |
| Downgrade | Dijadwalkan berlaku saat periode berjalan berakhir. |
| Paket sama | Tidak ada perubahan. |
| Pemakaian melebihi batas paket tujuan | Downgrade ditolak. |

### 4.3 Pembayaran

Menu:

- **Langganan > Tagihan Saya**
- URL: `/billing/invoices`

Langkah:

1. Buka detail invoice.
2. Periksa nomor invoice, periode, jumlah, dan status.
3. Lakukan pembayaran sesuai gateway yang tersedia.

Untuk gateway `manual`:

- Tombol **Konfirmasi Pembayaran Manual** hanya muncul jika `BILLING_ALLOW_MANUAL_CONFIRM=true`.
- Default lokal/testing: aktif.
- Default production: nonaktif.
- Jika tombol tidak tersedia, Super Admin dapat menandai lunas dari panel admin.

### 4.4 Aktivasi Setelah Pembayaran

Setelah invoice berstatus `PAID`:

- Subscription berubah menjadi `ACTIVE`.
- Paket diterapkan.
- Periode berjalan diperpanjang sesuai periode invoice.
- `trial_ends_at` dan grace dibersihkan.
- Invoice tidak dapat diproses dua kali.

Webhook gateway bersifat idempoten:

- Event yang sama tidak memperpanjang langganan dua kali.
- Reference invoice yang sama tidak membuat pembayaran ganda.

### 4.5 Perpanjangan Otomatis

Scheduler harian membuat invoice perpanjangan **7 hari sebelum periode berakhir**.

Setelah invoice dibayar:

- Periode diperpanjang satu bulan atau satu tahun sesuai siklus.
- Status kembali/tetap `ACTIVE`.

### 4.6 Keterlambatan Pembayaran

1. Periode berakhir dan invoice belum dibayar.
2. Status menjadi `PAST_DUE`.
3. Masa tenggang default 7 hari.
4. Jika tetap tidak dibayar, status menjadi `SUSPENDED`.

Jika enforcement aktif:

- `PAST_DUE`: aplikasi masih bisa dipakai, muncul banner peringatan.
- `SUSPENDED`: aplikasi menjadi read-only.
- Data tetap dapat dibaca dan di-export.

### 4.7 Downgrade

Downgrade tidak langsung berlaku.

- Sistem menyimpan `pending_plan_id`, `pending_billing_cycle`, dan `pending_effective_at`.
- Perubahan diterapkan saat periode berjalan berakhir.
- Jika pemakaian saat ini melebihi batas paket tujuan, permintaan ditolak.

### 4.8 Pembatalan

Customer dapat membatalkan langganan dari halaman **Langganan Saya**.

- Status menjadi `CANCELED`.
- `cancel_at_period_end` diset.
- Jika enforcement aktif, aplikasi menjadi read-only.
- Data tetap dapat dibaca dan di-export.
- Pembatalan saat ini bersifat final dari UI; belum ada tombol reaktivasi untuk status `CANCELED`.

## 5. Panel Super Admin

Menu **Super Admin Billing**:

| Menu | URL | Fungsi |
|---|---|---|
| Langganan Company | `/admin/billing/subscriptions` | Daftar company, status, paket, pemakaian, dan masa grace. |
| Paket Langganan | `/admin/billing/plans` | Tambah/edit paket dan batas kuota. |
| Tagihan Langganan | `/admin/billing/invoices` | Melihat invoice dan menandai lunas. |
| Webhook Log | `/admin/billing/webhooks` | Melihat payload webhook dan replay. |

### 5.1 Detail Company

Di halaman detail company, Super Admin dapat:

- Melihat status langganan.
- Melihat pemakaian vs batas paket.
- Melihat riwayat status.
- Melihat invoice.
- Melihat audit log.
- Memperpanjang manual beberapa hari.
- Suspend langganan.
- Mengaktifkan langganan.
- Mengganti paket secara paksa.
- Impersonate company.

Semua aksi admin wajib menyertakan alasan dan dicatat di audit log.

### 5.2 Menandai Pembayaran Manual

1. Buka **Super Admin Billing > Tagihan Langganan**.
2. Cari invoice.
3. Klik aksi **Tandai Lunas**.
4. Sistem menerapkan pembayaran dan memperpanjang langganan.

Gunakan ini sebagai fallback jika gateway belum terintegrasi.

## 6. Setup Trial

### 6.1 Konfigurasi

Trial diatur melalui `.env`, bukan melalui menu aplikasi.

```env
BILLING_TRIAL_DAYS=14
BILLING_DEFAULT_PLAN=starter
BILLING_INVOICE_LEAD_DAYS=7
BILLING_GRACE_DAYS=7
BILLING_TIMEZONE=Asia/Jakarta
```

Keterangan:

| Variabel | Default | Fungsi |
|---|---|---|
| `BILLING_TRIAL_DAYS` | `14` | Lama trial untuk company baru. |
| `BILLING_DEFAULT_PLAN` | `starter` | Paket awal saat trial. |
| `BILLING_INVOICE_LEAD_DAYS` | `7` | Invoice perpanjangan dibuat berapa hari sebelum periode berakhir. |
| `BILLING_GRACE_DAYS` | `7` | Masa tenggang setelah jatuh tempo. |
| `BILLING_TIMEZONE` | `Asia/Jakarta` | Zona waktu proses billing. |

Jika konfigurasi di-cache, jalankan:

```bash
php artisan config:clear
```

### 6.2 Alur Trial

1. Company baru disimpan.
2. `CompanySubscriptionObserver` memanggil `startTrial()`.
3. Subscription dibuat dengan status `TRIAL`.
4. Customer dapat memakai aplikasi selama masa trial.
5. Reminder dikirim H-3 dan H-0.
6. Saat trial berakhir, scheduler harian mengubah status menjadi `SUSPENDED`.
7. Customer dapat memilih paket dan membayar untuk menjadi `ACTIVE`.

Trial bersifat **global per aplikasi**, bukan per paket. Tabel `plans` belum memiliki kolom `trial_days`.

### 6.3 Trial untuk Company Lama

Company lama yang belum memiliki subscription:

```bash
php artisan billing:backfill --plan=starter --trial
```

Company lama yang sudah memiliki subscription tidak diubah oleh command tersebut.

Saat implementasi awal, company yang sudah ada di-backfill sebagai:

- Status: `ACTIVE`
- Paket: `business`

Jika ingin mereset company yang sudah memiliki subscription menjadi trial, saat ini belum tersedia tombol di UI. Perlu penanganan manual atau penambahan fitur admin.

## 7. Paket Default

Seeder menyediakan tiga paket:

| Paket | Harga Bulanan | Harga Tahunan | Users | Gudang | Invoice/Bulan |
|---|---:|---:|---:|---:|---:|
| Starter | Rp 99.000 | Rp 990.000 | 5 | 2 | 500 |
| Pro | Rp 299.000 | Rp 2.990.000 | 20 | 10 | 5.000 |
| Business | Rp 999.000 | Rp 9.990.000 | Tak terbatas | Tak terbatas | Tak terbatas |

Fitur default:

- Starter: `multi_warehouse`
- Pro: `salesforce`, `multi_warehouse`, `advanced_report`
- Business: `salesforce`, `multi_warehouse`, `advanced_report`, `priority_support`

Paket dapat diubah melalui **Super Admin Billing > Paket Langganan**.

## 8. Scheduler

Billing harian berjalan pukul **01:00 Asia/Jakarta**.

Command manual:

```bash
php artisan billing:daily
```

Scheduler server:

```cron
* * * * * cd /path/pos-apps && php artisan schedule:run >> /dev/null 2>&1
```

Tanpa scheduler, trial tidak otomatis menjadi suspended dan invoice perpanjangan tidak dibuat otomatis.

## 9. Status Langganan

| Status | Akses aplikasi | Keterangan |
|---|---|---|
| `TRIAL` | Penuh | Masa uji coba. |
| `ACTIVE` | Penuh | Langganan aktif dan sudah dibayar. |
| `PAST_DUE` | Penuh | Lewat jatuh tempo, masih dalam grace. |
| `SUSPENDED` | Read-only | Grace habis atau trial habis. |
| `CANCELED` | Read-only | Dibatalkan oleh customer. |

Akses read-only hanya berlaku jika enforcement diaktifkan. Default saat ini enforcement masih mati.

## 10. Konfigurasi Enforcement

```env
BILLING_ENFORCE=false
BILLING_ENFORCE_LOG_ONLY=true
BILLING_ENFORCE_QUOTA=false
BILLING_ENFORCE_FEATURES=false
```

| Variabel | Fungsi |
|---|---|
| `BILLING_ENFORCE` | Mengaktifkan pembatasan akses berdasarkan status langganan. |
| `BILLING_ENFORCE_LOG_ONLY` | Jika `true`, pelanggaran hanya dicatat, tidak diblokir. |
| `BILLING_ENFORCE_QUOTA` | Mengaktifkan pembatasan users, gudang, dan invoice. |
| `BILLING_ENFORCE_FEATURES` | Mengaktifkan pembatasan fitur berdasarkan paket. |

Urutan aktivasi yang disarankan:

1. `BILLING_ENFORCE=true`
2. `BILLING_ENFORCE_LOG_ONLY=true`
3. Pantau log
4. `BILLING_ENFORCE_LOG_ONLY=false`
5. Aktifkan quota
6. Aktifkan fitur

## 11. Gateway Pembayaran

Saat ini hanya tersedia gateway `manual`.

```env
BILLING_GATEWAY=manual
BILLING_WEBHOOK_SECRET=
BILLING_ALLOW_MANUAL_CONFIRM=true
```

Webhook:

- Endpoint: `POST /billing/webhook/{gateway}`
- Signature: HMAC-SHA256 dari raw body
- Header signature: `X-Billing-Signature`
- Header event ID: `X-Event-Id`
- Signature tidak valid: HTTP `401`
- Event duplikat: HTTP `200` dengan status `IGNORED`

Untuk production, diperlukan implementasi gateway nyata dan pengisian `BILLING_WEBHOOK_SECRET`.

## 12. Komponen Teknis

| Komponen | Lokasi |
|---|---|
| Konfigurasi billing | `config/billing.php` |
| Observer trial otomatis | `app/Observers/CompanySubscriptionObserver.php` |
| State machine | `app/Services/Billing/SubscriptionStateService.php` |
| Pemilihan paket | `app/Services/Billing/SubscriptionService.php` |
| Invoice | `app/Services/Billing/BillingInvoiceService.php` |
| Quota | `app/Services/Billing/QuotaService.php` |
| Webhook | `app/Services/Billing/WebhookService.php` |
| Job harian | `app/Jobs/Billing/BillingDailyJob.php` |
| Middleware akses | `app/Http/Middleware/EnsureActiveSubscription.php` |
| UI customer | `resources/views/web/billing/` |
| UI admin | `resources/views/web/billing/admin/` |
| Routes | `routes/web.php` |
| Scheduler | `routes/console.php` |

Tabel utama:

- `plans`
- `subscriptions`
- `subscription_invoices`
- `subscription_events`
- `subscription_notifications`
- `payment_webhook_logs`
- `audit_logs`

## 13. Checklist Onboarding Tenant

1. Pastikan paket billing sudah tersedia.
2. Buat company di **Master Data > Perusahaan**.
3. Pastikan subscription `TRIAL` otomatis terbentuk.
4. Buat user tenant dan berikan akses login.
5. Minta tenant membuka **Langganan > Langganan Saya**.
6. Tenant memilih paket dan siklus.
7. Tenant membayar invoice.
8. Super Admin memverifikasi status menjadi `ACTIVE`.
9. Pastikan scheduler berjalan untuk perpanjangan dan reminder.
10. Pantau company di **Super Admin Billing > Langganan Company**.

## 14. Batasan Saat Ini

- Belum ada registrasi publik/self-signup tenant.
- Gateway pembayaran baru `manual`.
- Trial bersifat global, belum per paket.
- Belum ada tombol admin untuk memulai ulang trial pada company yang sudah memiliki subscription.
- Enforcement default masih mati.
- Pembatalan oleh customer belum memiliki alur reaktivasi dari UI.
