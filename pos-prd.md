# PRD: Modul Point of Sale (POS)

| | |
|---|---|
| **Versi** | 1.0 (Draft) |
| **Tanggal** | 28 Oktober 2026 |
| **Modul** | Sales > Point of Sale |
| **Status** | Draft untuk review |

---

## 1. Latar Belakang

Penjualan saat ini hanya bisa dicatat lewat form Sales Order (SO) yang dirancang untuk alur sales lapangan: pilih customer, pilih produk lewat modal, lalu proses ke invoice. Untuk penjualan langsung di toko/counter (walk-in), alur tersebut terlalu lambat dan tidak ramah scanner.

Dibutuhkan tampilan kasir (POS) yang cepat, tetapi tetap memakai mesin SO yang sudah ada (harga channel, promo, cek stok, penomoran, multi-company) agar tidak ada logika bisnis ganda.

## 2. Tujuan

1. Kasir dapat menyelesaikan satu transaksi (cari produk sampai bayar) dalam waktu singkat, dengan dukungan scanner barcode dan shortcut keyboard.
2. Transaksi POS tersimpan sebagai Sales Order dengan `type = 'pos'`, sehingga memakai ulang seluruh aturan bisnis SO.
3. Transaksi POS dapat dibedakan dari SO biasa di list, laporan, dan proses invoice.

### Bukan Tujuan (Out of Scope v1.0)

- Hold/resume transaksi
- Retur/refund POS
- Tabel pembayaran terpisah dan split payment
- Shift kasir (buka/tutup kas)
- Integrasi printer thermal ESC/POS dan EDC/QRIS
- Mode offline
- Laporan khusus POS

## 3. Pengguna

| Peran | Kebutuhan |
|---|---|
| **Kasir** | Input cepat, hitung kembalian, cetak struk |
| **Supervisor/Admin** | Melihat transaksi POS dan menelusuri ke SO/invoice |
| **Akuntansi** | Order POS tetap bisa dibuatkan invoice dan jurnal lewat alur yang ada |

## 4. Ruang Lingkup Fungsional

### 4.1 Pencarian Produk
- **FR-01** Input pencarian mendukung kode, nama, dan model number. Pencarian berjalan otomatis setelah minimal 2 karakter (debounce 300 ms).
- **FR-02** Tekan **Enter** pada pencarian: jika kode sama persis atau hasil tunggal, produk langsung masuk keranjang (mode scanner) dan kolom dikosongkan.
- **FR-03** Hasil tampil sebagai kartu berisi nama, kode, satuan, harga, dan stok. Satu produk dengan beberapa satuan tampil sebagai kartu terpisah per satuan.
- **FR-04** Hanya produk yang punya harga aktif (channel customer, fallback `RETAIL UMUM`) yang ditampilkan. Maksimal 40 hasil.
- **FR-05** Produk dengan stok 0 tetap tampil, tetapi tidak bisa ditambahkan ke keranjang.

### 4.2 Customer
- **FR-06** Customer default adalah customer walk-in (kode `CASH`), atau customer pertama dengan channel `RETAIL UMUM` jika `CASH` tidak ada.
- **FR-07** Customer dapat diganti lewat dropdown pencarian (kode/nama).
- **FR-08** Mengganti customer mengosongkan keranjang, karena harga channel dan promo bisa berubah.

### 4.3 Keranjang
- **FR-09** Menambah produk yang sama dengan satuan sama menaikkan qty, bukan menambah baris.
- **FR-10** Qty bisa diubah lewat tombol +/− atau input angka (mendukung desimal). Qty ≤ 0 lewat tombol − menghapus baris.
- **FR-11** Baris bisa dihapus, dan keranjang bisa dikosongkan dengan konfirmasi.
- **FR-12** Tampil rincian per baris: harga satuan, diskon (jika ada), dan subtotal.

### 4.4 Promo dan Perhitungan
- **FR-13** Setiap perubahan keranjang memicu pengecekan promo ke endpoint `checkDiscount` (debounce 500 ms). Hanya respons terbaru yang dipakai.
- **FR-14** Diskon item, diskon header (potong grand total), dan barang gratis ditampilkan di ringkasan.
- **FR-15** Pajak dihitung per baris sesuai `tax_rate` dan `type_tax` (include/exclude).
- **FR-16** Ringkasan menampilkan Subtotal, Pajak, Diskon Promo, dan **TOTAL** (dibulatkan).
- **FR-17** Angka di layar hanya **estimasi**. Angka final selalu dihitung ulang server saat submit.

### 4.5 Pembayaran
- **FR-18** Metode bayar: Tunai, Transfer, QRIS, Debit/Kartu.
- **FR-19** Untuk Tunai: input uang diterima, tombol cepat (Uang Pas, 50.000, 100.000, 200.000), dan tampilan kembalian atau kekurangan secara real-time.
- **FR-20** Tombol Bayar nonaktif jika keranjang kosong, atau jika metode Tunai dan uang diterima kurang dari total.
- **FR-21** Catatan opsional per transaksi.
- **FR-22** Info pembayaran (metode, uang diterima, kembalian, catatan) disimpan di kolom `remarks` dengan format `POS | <metode> | Bayar: x | Kembali: y | <catatan>`.

### 4.6 Penyimpanan Transaksi
- **FR-23** Bayar memanggil endpoint `SalesOrderController@submit` dengan tambahan parameter `type = 'pos'`. `type` hanya diterima `so` atau `pos` (selain itu dianggap `so`), dan hanya diset saat data baru.
- **FR-24** Tanggal transaksi hari ini, `payment_term = 0`, warehouse default 1, cabang mengikuti cabang kasir, salesman adalah user login.
- **FR-25** Response `submit` menyertakan `so_number` untuk struk.
- **FR-26** Cegah double submit: tombol dan shortcut diabaikan selama request berjalan.
- **FR-27** Jika server menolak (stok habis, harga channel tidak ditemukan, customer nonaktif, dan sebagainya), pesan error ditampilkan dan keranjang **tidak** dikosongkan.
- **FR-28** Setelah sukses: notifikasi, cetak struk (jika dicentang), lalu keranjang, hasil pencarian, dan kolom bayar di-reset serta fokus kembali ke pencarian.

### 4.7 Struk
- **FR-29** Struk berisi nomor transaksi, tanggal-jam, customer, daftar item (qty, satuan, harga, diskon), subtotal, pajak, diskon promo, total, metode bayar, jumlah bayar, kembalian, dan barang gratis.
- **FR-30** Struk dicetak lewat jendela cetak browser (lebar ±260 px, cocok printer thermal 58/80 mm). Opsi "Cetak struk otomatis" aktif secara default.

### 4.8 Shortcut Keyboard
| Tombol | Aksi |
|---|---|
| `F2` | Fokus ke pencarian produk |
| `F9` | Bayar |
| `Enter` | Cari / tambah ke keranjang (mode scanner) |

## 5. Perubahan Data

### 5.1 Database
```sql
ALTER TABLE dbo.sales_order_headers
ADD [type] VARCHAR(10) NOT NULL
    CONSTRAINT DF_sales_order_headers_type DEFAULT ('so');
```
Data lama otomatis bernilai `so`.

### 5.2 Endpoint

| Method | Path | Fungsi |
|---|---|---|
| GET | `transaksi/sales_pos` | Halaman POS |
| POST | `api/transaksi/sales_pos/searchProduct` | Cari produk + harga + stok |
| POST | `api/transaksi/sales_pos/searchCustomer` | Cari customer (Select2) |
| POST | `api/transaksi/sales_order/checkDiscount` | Estimasi promo (sudah ada) |
| POST | `api/transaksi/sales_order/submit` | Simpan transaksi (sudah ada, ditambah `type`) |

### 5.3 File

| File | Keterangan |
|---|---|
| `web/Transaction/SalesPosController.php` | Controller halaman |
| `api/Transaction/SalesPosController.php` | Controller pencarian produk dan customer |
| `resources/views/web/sales_pos/index.blade.php` | Tampilan |
| `public/assets/js/controllers/transaction/sales_pos.js` | Logika sisi klien |
| `api/Transaction/SalesOrderController.php` | Ubah `submit()` (set `type`, return `so_number`) dan `getData()` (filter `type = 'so'`) |

## 6. Aturan Bisnis

1. Harga mengikuti prioritas yang sudah ada di `submit`: harga channel, harga customer-product, harga motoris, lalu hasil promo. Harga dari frontend tidak dipercaya.
2. Stok dicek per produk di warehouse 1. Stok ≤ 0 ditolak.
3. Order POS bernomor SO standar (`generateNoSO`) dan terikat `company_id` session.
4. List Sales Order **tidak** menampilkan order `type = 'pos'`.
5. Order POS tetap bisa dibuatkan invoice lewat `getAllSalesNotInvoice`/`generateAll`, kecuali diputuskan lain (lihat Pertanyaan Terbuka).

## 7. Persyaratan Non-Fungsional

| Kode | Persyaratan |
|---|---|
| NFR-01 | Pencarian produk merespons < 1 detik pada data normal |
| NFR-02 | Seluruh teks dari server di-escape sebelum dirender (cegah XSS) |
| NFR-03 | Semua request memakai CSRF token dan mengikuti middleware/akses menu yang ada |
| NFR-04 | Layout responsif, layar minimal tablet landscape |
| NFR-05 | Pencarian harus tetap cepat saat tabel `product`, `product_uom_price`, dan `product_stock` besar, sehingga perlu index pada kolom join dan filter utamanya (`product`, `unit`, `channel`, `date_start`) |

## 8. Skenario Penerimaan (UAT)

| # | Skenario | Hasil yang Diharapkan |
|---|---|---|
| 1 | Scan kode persis, tekan Enter | Produk masuk keranjang, kolom pencarian kosong dan fokus |
| 2 | Scan kode yang sama dua kali | Qty menjadi 2, tidak ada baris baru |
| 3 | Klik produk stok 0 | Ditolak dengan pesan stok habis |
| 4 | Keranjang memenuhi syarat promo | Diskon/barang gratis muncul di ringkasan |
| 5 | Tunai, uang kurang dari total | Tombol Bayar nonaktif, label "Kurang" berwarna merah |
| 6 | Tunai, uang lebih | Kembalian tampil benar, transaksi tersimpan, struk tercetak |
| 7 | Bayar dua kali cepat (klik/F9) | Hanya satu transaksi tersimpan |
| 8 | Server menolak (misal stok berubah) | Pesan error tampil, keranjang tetap utuh |
| 9 | Ganti customer saat keranjang terisi | Keranjang dikosongkan, harga dimuat ulang sesuai channel |
| 10 | Buka list Sales Order | Transaksi POS tidak muncul |
| 11 | Cek `sales_order_headers` | Kolom `type = 'pos'`, `remarks` berisi info pembayaran |

## 9. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Estimasi promo di layar berbeda dari hasil server | Total struk berbeda dari total tersimpan | Endpoint promo yang sama dengan form SO; tampilkan total server setelah submit di rilis berikutnya |
| Pembayaran hanya di `remarks` | Sulit rekap kas per metode bayar | Fase 2: tabel `sales_payments` |
| Customer `CASH` belum dibuat | POS jatuh ke customer RETAIL UMUM pertama | Buat customer walk-in sebelum go-live |
| Nama kolom (`type_tax`, `model_number`) berbeda di skema | Query pencarian error | Verifikasi kolom saat implementasi |
| Popup struk diblokir browser | Struk tidak tercetak | Pesan peringatan; izinkan popup untuk domain aplikasi |

## 10. Pertanyaan Terbuka

1. Apakah order POS boleh masuk alur invoice (`generateAll`), atau dikecualikan dengan filter `type = 'so'`?
2. Apakah perlu customer walk-in per cabang?
3. Apakah kasir boleh mengubah harga atau memberi diskon manual?
4. Apakah warehouse POS selalu 1, atau mengikuti cabang kasir?
5. Apakah dibutuhkan laporan penjualan harian per kasir dan per metode bayar?

## 11. Rencana Rilis

| Fase | Isi |
|---|---|
| **v1.0** | Cari produk, keranjang, promo, bayar, simpan ke SO `type = 'pos'`, struk |
| **v1.1** | Hold/resume, hak akses khusus kasir, laporan harian |
| **v2.0** | Tabel pembayaran, retur POS, shift kasir, printer thermal langsung |