# PRD: Subscription & Billing (Langkah 7)

| | |
|---|---|
| **Status** | Draft |
| **Stack** | Laravel (PHP), multi-tenant (`company_id`), queue + scheduler |
| **Prasyarat** | Isolasi data tenant (langkah 2) sudah lulus uji, termasuk laporan, export, dan unduh file |
| **Tanggal** | 27 Oktober 2026 |

---

## 1. Ringkasan

Menambahkan sistem langganan berbayar per company (tenant): paket dengan batas pemakaian dan fitur, siklus tagihan otomatis, integrasi payment gateway, penegakan akses berdasarkan status langganan, dan panel super admin untuk mengelola semuanya.

## 2. Tujuan

- Setiap company memiliki satu langganan dengan status yang jelas dan dapat ditelusuri.
- Akses fitur dan kuota dikendalikan oleh paket, bukan oleh kode yang tersebar.
- Penagihan, pengingat, perpanjangan, dan suspend berjalan otomatis.
- Pembayaran dari gateway diproses idempoten dan dapat diaudit.
- Super admin dapat melihat dan menindak langganan tanpa akses database langsung.

## 3. Non-tujuan (di luar lingkup langkah ini)

- Proration saat upgrade/downgrade di tengah periode (dibahas di Open Questions).
- Multi-currency dan pajak yang kompleks (PPN dihitung sederhana atau ditunda).
- Metered billing (tagihan berdasarkan pemakaian aktual).
- Self-service refund; refund ditangani manual lewat super admin/gateway.

## 4. Keputusan default (dapat diubah)

| Parameter | Nilai default |
|---|---|
| Grace period setelah jatuh tempo | 7 hari |
| Perilaku saat `SUSPENDED` | Read-only, data tetap aman |
| Trial | 14 hari, tanpa kartu |
| Pembuatan tagihan | H-7 sebelum jatuh tempo |
| Pengingat | H-7, H-3, H-0, dan setelah lewat tempo |

Semua nilai di atas disimpan di config (`config/billing.php`), bukan hardcode.

## 5. Persona

- **Owner/Admin company**: melihat paket, tagihan, membayar, upgrade/downgrade.
- **Super admin (internal)**: memantau semua company, memperpanjang, suspend, impersonate.
- **Sistem**: scheduler, queue worker, dan webhook gateway.

## 6. Model Data

### 6.1 `plans` (global, tidak ber-`company_id`)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint | PK |
| `code` | string, unique | mis. `starter`, `pro` |
| `name` | string | |
| `price_monthly` | decimal | |
| `price_yearly` | decimal | |
| `limits` | json | mis. `{"users": 5, "warehouses": 2, "invoices_per_month": 500}`; `null` = tak terbatas |
| `features` | json | mis. `["salesforce", "multi_warehouse"]` |
| `is_active` | bool | paket nonaktif tidak bisa dipilih baru |

### 6.2 `subscriptions`

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint | PK |
| `company_id` | FK, unique aktif | satu langganan aktif per company |
| `plan_id` | FK | |
| `status` | enum | `TRIAL`, `ACTIVE`, `PAST_DUE`, `SUSPENDED`, `CANCELED` |
| `billing_cycle` | enum | `MONTHLY`, `YEARLY` |
| `trial_ends_at` | datetime, nullable | |
| `current_period_start` | datetime | |
| `current_period_end` | datetime | tanggal jatuh tempo |
| `grace_ends_at` | datetime, nullable | diisi saat masuk `PAST_DUE` |
| `canceled_at` | datetime, nullable | |
| `cancel_at_period_end` | bool | |

### 6.3 `subscription_invoices`

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint | PK |
| `company_id`, `subscription_id` | FK | |
| `number` | string, unique | nomor tagihan |
| `amount` | decimal | |
| `period_start`, `period_end` | date | periode yang ditagih |
| `due_date` | date | |
| `status` | enum | `PENDING`, `PAID`, `EXPIRED`, `FAILED`, `VOID` |
| `gateway` | string | |
| `gateway_reference` | string, unique, nullable | ID transaksi/order di gateway |
| `payment_url` | text, nullable | |
| `paid_at` | datetime, nullable | |

### 6.4 `payment_webhook_logs`

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint | PK |
| `gateway` | string | |
| `event_id` | string, nullable, unique per gateway | untuk deteksi duplikat |
| `payload` | json/longtext | payload mentah, apa adanya |
| `signature_valid` | bool | |
| `status` | enum | `RECEIVED`, `PROCESSED`, `IGNORED`, `FAILED` |
| `error` | text, nullable | |
| `received_at`, `processed_at` | datetime | |

### 6.5 Tambahan

- `subscription_events` (opsional tapi disarankan): riwayat perubahan status (`from`, `to`, `reason`, `actor`), untuk debugging dan audit.
- Audit log yang sudah ada dipakai untuk aksi super admin.

## 7. State Machine Status

```
TRIAL ──(bayar)──────────────► ACTIVE
TRIAL ──(trial habis)────────► SUSPENDED   (tidak ada grace; data tetap aman)
ACTIVE ──(lewat jatuh tempo)─► PAST_DUE
PAST_DUE ──(bayar)───────────► ACTIVE      (periode diperpanjang)
PAST_DUE ──(grace habis)─────► SUSPENDED
SUSPENDED ──(bayar)──────────► ACTIVE
ACTIVE/PAST_DUE/SUSPENDED ──(batalkan)──► CANCELED
```

Aturan:
- Semua perpindahan status melalui satu service (`SubscriptionStateService`) agar tercatat di `subscription_events`.
- Transisi di luar tabel di atas ditolak dan dilog.

## 8. Kebutuhan Fungsional

### 8.1 Penegakan akses

**FR-1. Middleware `EnsureActiveSubscription`** dipasang pada semua route tenant.

| Status | Perilaku |
|---|---|
| `TRIAL`, `ACTIVE` | Akses penuh sesuai paket |
| `PAST_DUE` | Akses penuh + banner peringatan dengan sisa grace period |
| `SUSPENDED` | **Read-only**: request `GET/HEAD` diizinkan; `POST/PUT/PATCH/DELETE` ditolak dan diarahkan ke halaman pembayaran |
| `CANCELED` | Sama seperti `SUSPENDED` sampai akhir periode berbayar, lalu read-only permanen |

Pengecualian (selalu boleh): login/logout, halaman pembayaran dan tagihan, route webhook, serta **export data milik sendiri** agar tenant tidak terkunci dari datanya.

> Catatan: rancangan awal menyebut `SUSPENDED` diarahkan ke halaman pembayaran. PRD ini memakai read-only sebagai perilaku utama (sesuai keputusan default), dengan redirect hanya untuk aksi tulis. Konfirmasi di Open Questions.

**FR-2. `QuotaService`** dipanggil sebelum menambah user, gudang, atau invoice.
- Pemakaian dihitung langsung dari tabel (`COUNT` dengan scope `company_id`), tanpa counter terpisah pada tahap awal.
- Jika batas tercapai, tolak dengan pesan jelas dan tautan upgrade.
- Batas `null` berarti tak terbatas.
- Kuota bulanan (mis. invoice) dihitung berdasarkan `current_period_start`–`current_period_end`.

**FR-3. Feature flag** melalui helper, mis. `tenant()->can('salesforce')`, yang membaca `plans.features` dari langganan aktif. Fitur yang tidak ada di paket: menu disembunyikan dan route/policy tetap menolak (tidak hanya disembunyikan di UI).

### 8.2 Otomasi

**FR-4. Job harian** (scheduler, dijalankan idempoten):
1. Buat `subscription_invoices` untuk langganan yang jatuh tempo dalam 7 hari dan belum punya tagihan periode berikutnya.
2. `ACTIVE` → `PAST_DUE` untuk langganan yang lewat `current_period_end` tanpa tagihan lunas; set `grace_ends_at = current_period_end + 7 hari`.
3. `PAST_DUE` → `SUSPENDED` untuk langganan yang lewat `grace_ends_at`.
4. `TRIAL` → `SUSPENDED` untuk trial yang habis tanpa pembayaran.
5. Tandai tagihan `PENDING` yang kedaluwarsa sebagai `EXPIRED`.

**FR-5. Job notifikasi** (email dan WhatsApp) pada H-7, H-3, H-0, dan setelah lewat tempo. Setiap notifikasi dicatat agar tidak terkirim dobel pada run ulang. Notifikasi trial: H-3 dan H-0 sebelum trial habis.

**FR-6. Webhook gateway**:
1. Simpan payload mentah ke `payment_webhook_logs` **sebelum** diproses.
2. Validasi signature; jika tidak valid, simpan dengan `signature_valid = false`, balas 4xx, jangan proses.
3. Proses idempoten: cari tagihan lewat `gateway_reference`; jika sudah `PAID`, abaikan (status `IGNORED`) dan tetap balas 200. Pemrosesan dibungkus transaksi DB dengan row lock agar webhook paralel tidak memperpanjang dua kali.
4. Jika pembayaran sukses: tandai tagihan `PAID`, perpanjang `current_period_*`, set status `ACTIVE`, kosongkan `grace_ends_at`.
5. Webhook gagal diproses dapat diulang dari panel super admin atau lewat perintah artisan.

### 8.3 Sisi pelanggan (owner/admin)

**FR-7.** Halaman Langganan: paket saat ini, status, periode, sisa trial/grace, pemakaian vs batas, daftar tagihan, tombol bayar.

**FR-8.** Pilih/ganti paket dan siklus. Upgrade berlaku setelah pembayaran; downgrade berlaku di akhir periode dan ditolak jika pemakaian saat ini melebihi batas paket tujuan (dengan penjelasan apa yang harus dikurangi).

**FR-9.** Batalkan langganan (`cancel_at_period_end`).

### 8.4 Panel super admin

**FR-10.** Daftar company dengan paket, status, periode berakhir, dan pemakaian; filter berdasarkan status dan pencarian nama.

**FR-11.** Aksi, semuanya wajib mengisi alasan dan tercatat di audit log (siapa, kapan, company, sebelum/sesudah):
- Perpanjang manual (tambah hari atau periode).
- Suspend dan aktifkan kembali.
- Ganti paket.
- Impersonate company (ada banner yang jelas, batas waktu sesi, dan setiap aksi selama impersonate ditandai).

**FR-12.** Lihat dan ulang `payment_webhook_logs`.

## 9. Kebutuhan Non-Fungsional

- **Isolasi tenant**: semua tabel ber-`company_id` memakai global scope yang sama dengan modul lain. Tabel `plans` bersifat global dan hanya bisa diubah super admin.
- **Keamanan**: endpoint webhook tanpa session/CSRF tetapi wajib signature; rate limit; secret gateway di `.env`; panel super admin dilindungi guard/role terpisah dan sebaiknya 2FA.
- **Idempotensi**: semua job dan webhook aman dijalankan ulang.
- **Zona waktu**: simpan UTC, hitung batas hari berdasarkan zona waktu yang ditetapkan di config (default `Asia/Jakarta`).
- **Observabilitas**: log terstruktur untuk setiap transisi status dan kegagalan webhook; alert bila ada webhook gagal berulang atau job harian tidak berjalan.
- **Performa**: pengecekan langganan di middleware di-cache per request (dan singkat per company), dan di-invalidate saat status berubah.

## 10. Rencana Implementasi

1. Migrasi, model, dan seeder `plans`.
2. `SubscriptionStateService` + `subscription_events`.
3. Middleware `EnsureActiveSubscription`, `QuotaService`, dan helper feature flag.
4. Integrasi gateway: pembuatan tagihan, `payment_url`, webhook + log.
5. Scheduler dan queue: job tagihan, status, dan notifikasi.
6. UI pelanggan (halaman langganan dan tagihan).
7. Panel super admin + audit log + impersonate.
8. Backfill: buat langganan `TRIAL` atau `ACTIVE` untuk semua company yang sudah ada **sebelum** middleware diaktifkan.
9. Rollout bertahap dengan feature flag, mulai dari mode "hanya log" (tanpa blokir), lalu aktifkan penegakan.

## 11. Kriteria Penerimaan

- [ ] Company baru otomatis mendapat langganan `TRIAL` 14 hari tanpa input kartu.
- [ ] Trial habis tanpa bayar → `SUSPENDED`; data tetap bisa dibaca dan di-export.
- [ ] Tagihan dibuat H-7; pengingat terkirim di H-7, H-3, H-0, dan setelah lewat tempo, masing-masing sekali.
- [ ] Lewat jatuh tempo → `PAST_DUE`; lewat 7 hari → `SUSPENDED`.
- [ ] Saat `SUSPENDED`, request tulis ditolak/redirect ke pembayaran; request baca tetap berjalan; login dan bayar tetap bisa.
- [ ] Webhook yang dikirim dua kali (atau paralel) hanya memperpanjang periode satu kali.
- [ ] Webhook dengan signature salah tidak mengubah data apa pun dan tercatat di log.
- [ ] Menambah user/gudang/invoice melewati batas paket ditolak dengan pesan upgrade.
- [ ] Fitur di luar paket tidak bisa diakses lewat URL langsung.
- [ ] Downgrade ditolak bila pemakaian melebihi batas paket tujuan.
- [ ] Setiap aksi super admin (perpanjang, suspend, ganti paket, impersonate) muncul di audit log dengan alasan.
- [ ] Company A tidak dapat melihat langganan, tagihan, atau webhook log company B (termasuk lewat laporan, export, dan unduh file).
- [ ] Menjalankan job harian dua kali berturut-turut tidak menghasilkan tagihan atau notifikasi ganda.

## 12. Rencana Pengujian

- **Unit**: transisi state machine, hitungan periode dan grace, `QuotaService`.
- **Feature**: middleware per status, webhook (valid, invalid, duplikat, paralel), halaman pelanggan, aksi super admin.
- **Time travel** (`Carbon::setTestNow`): simulasi H-7 sampai suspend.
- **Isolasi**: uji lintas tenant untuk seluruh endpoint baru.
- **Staging**: uji end-to-end dengan sandbox gateway.

## 13. Risiko

| Risiko | Mitigasi |
|---|---|
| Webhook terlambat/hilang sehingga pelanggan yang sudah bayar tersuspend | Rekonsiliasi harian: cek status tagihan `PENDING` langsung ke API gateway sebelum suspend |
| Perpanjangan ganda dari webhook duplikat | Idempotensi lewat `gateway_reference` + row lock + unique constraint |
| Company lama terkunci saat middleware diaktifkan | Backfill dan rollout mode "log only" lebih dulu |
| Impersonate disalahgunakan | Audit log wajib, alasan wajib, sesi terbatas, role khusus |
| Job harian gagal diam-diam | Heartbeat monitoring dan alert |

## 14. Open Questions

1. Gateway mana yang dipakai (Midtrans, Xendit, lainnya) dan metode bayar apa saja?
2. Apakah `SUSPENDED` murni read-only, atau tetap ada redirect penuh ke halaman pembayaran untuk semua halaman?
3. Proration saat upgrade di tengah periode: ditunda, atau dihitung?
4. PPN: sudah termasuk dalam harga atau ditambahkan terpisah?
5. Setelah `SUSPENDED` berapa lama data disimpan sebelum boleh diarsipkan/dihapus?
6. Notifikasi WhatsApp lewat provider apa?
7. Apakah perlu paket gratis permanen selain trial?
