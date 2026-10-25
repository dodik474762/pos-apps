# PRD: Landing Page Distribution Management System (DMS)

> Dokumen ide PRD untuk landing page produk SaaS berlangganan bagi distributor.

---

## 1. Ringkasan

**Produk:** Platform SaaS berlangganan untuk distributor (FMCG, farmasi, sparepart, bahan bangunan, dll.) yang mengelola order, stok, tagihan, sales lapangan, dan pengiriman dalam satu sistem.

**Tujuan landing page:** Mengubah distributor yang masih memakai Excel, WhatsApp, dan nota manual menjadi *demo request* atau *free trial*.

---

## 2. Masalah dan Target Pengguna

| Persona | Masalah utama |
|---|---|
| **Owner distributor** | Tidak punya visibilitas omzet, stok, dan piutang secara real-time |
| **Admin gudang / order** | Input order manual, stok tidak sinkron, sering salah kirim |
| **Sales / kanvaser** | Order lewat WhatsApp, tidak ada catatan kunjungan, sulit cek stok dan harga |
| **Finance** | Piutang toko sulit dilacak, penagihan telat |

---

## 3. Tujuan dan Metrik Keberhasilan

| Tujuan | Metrik | Target awal |
|---|---|---|
| Menghasilkan leads | Conversion rate (visitor → demo/trial) | 3–5% |
| Kualitas leads | % leads yang sesuai kriteria (omzet, jumlah sales) | >40% |
| Engagement | Scroll depth ≥ 70%, waktu di halaman | >1,5 menit |
| Performa | LCP | <2,5 detik (penting untuk koneksi mobile) |

---

## 4. Value Proposition

- **Headline contoh:** *"Kelola Distribusi dari Order sampai Penagihan, dalam Satu Dashboard."*
- **Sub-headline:** *"Stok akurat, sales lebih produktif, piutang terkendali. Mulai dari Rp[xxx]/bulan, tanpa biaya setup."*
- **CTA utama:** "Jadwalkan Demo Gratis"
- **CTA sekunder:** "Coba Gratis 14 Hari"

---

## 5. Struktur Section Landing Page

1. **Navbar (sticky):** Logo, Fitur, Harga, Studi Kasus, FAQ, tombol CTA
2. **Hero:** Headline, sub-headline, CTA ganda, mockup dashboard, badge kepercayaan
3. **Social proof:** Logo klien dan angka ("200+ distributor, 1 juta+ transaksi/bulan")
4. **Problem section:** "Masih begini?" dengan ilustrasi Excel berantakan, chat WA order, stok selisih, dan piutang macet
5. **Solusi dan fitur utama** (kartu atau tab):
   - Order management (web, mobile, dan portal toko/B2B)
   - Inventory multi-gudang (batch, expired date, stok real-time)
   - Salesman app (kanvas, take order, rute kunjungan, GPS check-in)
   - Piutang dan penagihan (aging, reminder otomatis, giro/tempo)
   - Delivery dan armada (surat jalan, tracking, bukti terima)
   - Laporan dan analitik (omzet per sales, produk, area, dan toko)
   - Integrasi (akuntansi, e-Faktur, WhatsApp, marketplace)
6. **Cara kerja:** 3 langkah (Daftar → Import data → Mulai jualan)
7. **Manfaat terukur:** Misalnya "Input order 70% lebih cepat", "Selisih stok turun 90%"
8. **Pricing:** Tabel paket berlangganan (lihat bagian 6)
9. **Studi kasus / testimoni:** 2–3 cerita singkat dengan angka hasil
10. **FAQ:** Migrasi data, keamanan, jumlah user, kontrak, dan dukungan
11. **CTA penutup + form lead:** Nama, perusahaan, no. WhatsApp, jenis usaha, jumlah sales
12. **Footer:** Kontak, kebijakan privasi, syarat layanan, media sosial

---

## 6. Ide Model Pricing (Subscription)

| | **Starter** | **Growth** | **Enterprise** |
|---|---|---|---|
| Target | Distributor kecil | Menengah | Besar / multi-cabang |
| User | s.d. 5 | s.d. 25 | Unlimited |
| Gudang | 1 | 3 | Unlimited |
| Salesman app | Basic | Lengkap + GPS | Lengkap + custom |
| Integrasi | – | Akuntansi, WA | API, ERP, SSO |
| Support | Email | Chat prioritas | Dedicated account manager |
| Harga | Rp xxx/bln | Rp xxx/bln | Hubungi sales |

Tambahkan toggle **bulanan/tahunan** (diskon 15–20%) dan label **"Paling Populer"** pada paket tengah.

---

## 7. Kebutuhan Fungsional Landing Page

- Form lead dengan validasi, terhubung ke CRM/Google Sheets/email notifikasi
- Integrasi WhatsApp click-to-chat (kanal utama di Indonesia)
- Booking demo terjadwal (Calendly atau sejenisnya)
- Toggle harga bulanan/tahunan dan kalkulator ROI sederhana (opsional)
- Video demo singkat (60–90 detik) atau tur produk interaktif
- Tracking: GA4, Meta Pixel, event CTA, form submit, dan UTM
- Multi-bahasa (ID default, EN opsional)

---

## 8. Kebutuhan Non-Fungsional

- **Mobile-first** (mayoritas pemilik usaha membuka via HP)
- **Ringan:** gambar WebP, lazy load, CDN
- **SEO:** kata kunci seperti "aplikasi distributor", "software distribusi", "sistem manajemen distribusi"
- **Keamanan:** HTTPS, proteksi spam form (reCAPTCHA), kepatuhan UU PDP
- **Aksesibilitas dasar:** kontras, ukuran font, alt text

---

## 9. Strategi Konten dan Copy

- **Tone:** Profesional, praktis, dan lugas, dengan bahasa yang dekat dengan dunia distribusi ("tagihan", "kanvas", "tempo", "retur")
- **Bukti nyata:** Gunakan screenshot dan angka, bukan klaim umum
- **Hilangkan keraguan:** "Data Anda bisa diimpor dari Excel", "Training gratis", "Batalkan kapan saja"

---

## 10. Roadmap Pengerjaan (±4 minggu)

| Minggu | Aktivitas |
|---|---|
| 1 | Riset kompetitor, finalisasi copy, wireframe |
| 2 | Desain UI (hero, fitur, pricing) |
| 3 | Development, integrasi form dan tracking |
| 4 | QA, uji kecepatan, soft launch, mulai A/B test |

---

## 11. Risiko dan Mitigasi

| Risiko | Mitigasi |
|---|---|
| Traffic tinggi tapi leads tidak berkualitas | Tambahkan pertanyaan kualifikasi di form |
| Pengunjung bingung memilih paket | Tombol "Bantu pilih paket" via WhatsApp |
| Konversi rendah | A/B test headline, CTA, dan posisi pricing |
| Kepercayaan rendah (brand baru) | Tampilkan testimoni, sertifikasi keamanan, dan garansi trial |

---

## 12. A/B Test yang Disarankan

- Headline berbasis masalah vs. berbasis manfaat
- CTA "Demo Gratis" vs. "Coba Gratis 14 Hari"
- Form pendek (3 field) vs. form lengkap (6 field)

##
- lihat juga all modul di project ini
- buat semuanya static jangan ada yang ambil dari database
- cukup buatkan dulu fitur landing pagenya
- jangan ubah modul atau logic yang sudah jalan
- buatkan modul landing page sendiri