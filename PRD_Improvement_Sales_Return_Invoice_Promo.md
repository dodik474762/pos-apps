# Product Requirements Document (PRD)

## Improvement Sales Return, Sales Invoice, dan Report Promo

**Status:** Draft\
**Versi:** 1.0\
**Tanggal:** 12 November 2026\
**Modul:** Sales Return, Sales Invoice, Report Promo

------------------------------------------------------------------------

## 1. Latar Belakang

Diperlukan improvement pada modul Sales Return, Sales Invoice, dan
Report Promo untuk melengkapi informasi transaksi, memperjelas
keterkaitan dokumen penjualan, serta mengurangi risiko ketidaksesuaian
data stok dan penjualan.

Temuan utama: 1. Informasi dokumen sumber seperti Invoice, Delivery
Order (DO), dan Packing List (PL) belum ditampilkan secara lengkap pada
daftar Sales Return. 2. Sales Invoice membutuhkan informasi tanggal DO
dan tanggal PL. 3. Koreksi transaksi setelah barang di-DO dan dibuatkan
PL belum memiliki pencatatan koreksi invoice yang memadai. Kondisi ini
berisiko menyebabkan barang yang sudah keluar gudang dan berstatus
terjual tidak tercatat secara konsisten. 4. Report Promo belum
menyediakan riwayat promo yang pernah dibuat beserta periode berlakunya.

## 2. Tujuan

-   Memudahkan pengguna menelusuri hubungan Sales Return dengan Invoice,
    DO, dan PL.
-   Menampilkan tanggal dokumen pengiriman pada modul Sales Invoice.
-   Memastikan setiap koreksi transaksi setelah proses DO dan PL
    tercatat dan dapat ditelusuri.
-   Menyediakan riwayat promo beserta tanggal berlakunya.
-   Mengurangi risiko selisih antara data penjualan, dokumen pengiriman,
    dan stok barang.

## 3. Ruang Lingkup

  -----------------------------------------------------------------------
  No.                     Modul                   Improvement
  ----------------------- ----------------------- -----------------------
  1                       Dashboard Sales Return            Penambahan kolom nomor
                                                  dan tanggal Invoice,
                                                  DO, serta PL

  2                       Sales Invoice           Penambahan kolom
                                                  Tanggal DO dan Tanggal
                                                  PL

  3                       Kontrol koreksi         Pencatatan dan
                          transaksi               penelusuran koreksi
                                                  invoice setelah
                                                  transaksi memiliki DO
                                                  dan PL

  4                       Report Promo            Penambahan tab History
                                                  Promo berisi promo yang
                                                  pernah dibuat dan
                                                  tanggal berlakunya
  -----------------------------------------------------------------------

## 4. Kebutuhan Fungsional

### 4.1 Sales Return

**Kebutuhan:** Menambahkan informasi dokumen referensi pada daftar Sales
Return.

  -----------------------------------------------------------------------
  No.                     Nama Kolom              Keterangan
  ----------------------- ----------------------- -----------------------
  1                       No. Invoice             Nomor Sales Invoice
                                                  yang terkait dengan
                                                  transaksi return

  2                       Tanggal Invoice         Tanggal penerbitan
                                                  Sales Invoice

  3                       No. DO                  Nomor Delivery Order
                                                  yang terkait

  4                       Tanggal DO              Tanggal dokumen
                                                  Delivery Order

  5                       No. PL                  Nomor Packing List yang
                                                  terkait

  6                       Tanggal PL              Tanggal dokumen Packing
                                                  List
  -----------------------------------------------------------------------

**Aturan kebutuhan:** - Informasi harus mengacu pada dokumen transaksi
yang terkait dengan Sales Return. - Jika dokumen terkait belum tersedia,
sistem menampilkan nilai kosong atau penanda yang konsisten. - Kolom
membantu pengguna menelusuri dokumen asal transaksi return.

**Kriteria penerimaan:** - \[ \] Daftar Sales Return menampilkan keenam
kolom. - \[ \] Nomor dan tanggal Invoice, DO, serta PL sesuai dengan
dokumen sumber. - \[ \] Data tetap dapat ditampilkan ketika salah satu
dokumen belum terbentuk. - \[ \] Penambahan kolom tidak mengubah proses
transaksi Sales Return yang sudah berjalan.

### 4.2 Sales Invoice

**Kebutuhan:** Menambahkan informasi tanggal dokumen pengiriman pada
daftar Sales Invoice.

  -----------------------------------------------------------------------
  No.                     Nama Kolom              Keterangan
  ----------------------- ----------------------- -----------------------
  1                       Tanggal DO              Tanggal Delivery Order
                                                  yang terkait dengan
                                                  Sales Invoice

  2                       Tanggal PL              Tanggal Packing List
                                                  yang terkait dengan
                                                  Sales Invoice
  -----------------------------------------------------------------------

**Aturan kebutuhan:** - Tanggal DO dan PL harus merujuk pada dokumen
yang berhubungan dengan Sales Invoice. - Apabila satu Sales Invoice
memiliki lebih dari satu DO atau PL, aturan penyajian perlu ditetapkan
berdasarkan relasi transaksi yang berlaku. Pilihannya adalah menampilkan
seluruh tanggal terkait atau menyediakan detail dokumen. - Jika DO atau
PL belum dibuat, kolom menampilkan nilai kosong atau status yang sesuai.

**Kriteria penerimaan:** - \[ \] Daftar Sales Invoice menampilkan kolom
Tanggal DO dan Tanggal PL. - \[ \] Tanggal yang ditampilkan sesuai
dengan dokumen terkait. - \[ \] Kondisi belum memiliki DO/PL ditangani
tanpa menyebabkan error. - \[ \] Data Sales Invoice lama tetap dapat
ditampilkan dengan benar.

### 4.3 Kontrol Koreksi Sales Invoice Setelah DO dan PL

**Latar belakang masalah:**

Terdapat kondisi ketika Sales Invoice sudah diproses menjadi DO dan
Packing List (PL), kemudian toko mengajukan koreksi terhadap produk
tertentu yang memiliki diskon. Koreksi dilakukan oleh admin, tetapi
proses koreksi tersebut belum memiliki record koreksi invoice yang
memadai.

Hal ini menimbulkan risiko: - Nilai atau detail invoice tidak lagi
mencerminkan transaksi sebenarnya. - Barang yang sudah keluar gudang dan
berstatus terjual berisiko tidak tercatat secara konsisten. - Riwayat
perubahan transaksi sulit ditelusuri. - Data penjualan, dokumen
pengiriman, dan stok berpotensi tidak sinkron.

**Kebutuhan:** Sistem harus menyediakan pencatatan koreksi yang dapat
ditelusuri ketika perubahan dilakukan terhadap Sales Invoice yang sudah
memiliki DO dan PL.

**Kebutuhan minimum yang disarankan:** 1. Setiap koreksi menyimpan
referensi Sales Invoice asal. 2. Sistem menyimpan nilai/detail sebelum
koreksi dan sesudah koreksi. 3. Sistem mencatat item yang dikoreksi,
termasuk kuantitas, harga, diskon, dan nilai transaksi yang relevan. 4.
Sistem mencatat alasan koreksi, pengguna/admin yang melakukan koreksi,
serta tanggal dan waktu koreksi. 5. Riwayat koreksi dapat dilihat oleh
pengguna yang memiliki hak akses. 6. Koreksi tidak boleh menghapus atau
menimpa jejak transaksi sebelumnya. 7. Sistem menjaga konsistensi antara
Sales Invoice, DO, PL, status penjualan, dan pergerakan stok.

**Kontrol bisnis yang perlu diterapkan:** - Koreksi terhadap transaksi
yang sudah di-DO dan dibuatkan PL harus melalui mekanisme terkontrol. -
Sistem perlu menentukan apakah koreksi hanya berdampak pada nilai
invoice atau juga berdampak pada kuantitas/barang yang telah dikirim. -
Jika koreksi mengubah kuantitas atau barang yang sudah keluar gudang,
sistem harus mengarahkan pengguna ke proses pembatalan, retur, atau
dokumen penyesuaian yang sesuai. Perubahan stok tidak boleh dilakukan
diam-diam melalui edit invoice. - Hak akses koreksi perlu dibatasi
kepada role yang berwenang.

**Kriteria penerimaan:** - \[ \] Koreksi Sales Invoice setelah DO dan PL
menghasilkan record audit. - \[ \] Riwayat sebelum dan sesudah koreksi
dapat ditelusuri. - \[ \] Sistem menyimpan alasan, pelaksana, serta
waktu koreksi. - \[ \] Koreksi tidak menghilangkan histori invoice dan
dokumen pengiriman sebelumnya. - \[ \] Perubahan kuantitas/barang yang
telah keluar gudang mengikuti proses stok/retur/penyesuaian yang sah. -
\[ \] Status dan data stok tetap konsisten setelah koreksi. - \[ \]
Pengguna tanpa hak akses tidak dapat melakukan koreksi.

### 4.4 Report Promo -- History Promo

**Kebutuhan:** Menambahkan tab **History Promo** pada modul Report
Promo.

Informasi minimum yang ditampilkan:

  No.   Informasi               Keterangan
  ----- ----------------------- -----------------------------------------
  1     Nama/Identitas Promo    Nama atau kode promo yang pernah dibuat
  2     Tanggal Mulai Berlaku   Tanggal awal promo berlaku
  3     Tanggal Akhir Berlaku   Tanggal akhir promo berlaku

**Aturan kebutuhan:** - Tab menampilkan promo yang pernah dibuat,
termasuk promo yang sudah tidak aktif atau sudah berakhir, sepanjang
datanya tersedia. - Riwayat tidak hanya menampilkan promo yang sedang
aktif. - Pengguna dapat membedakan promo berdasarkan periode berlakunya.

**Kriteria penerimaan:** - \[ \] Tersedia tab History Promo pada Report
Promo. - \[ \] Tab menampilkan promo yang pernah dibuat. - \[ \] Periode
mulai dan akhir promo ditampilkan dengan benar. - \[ \] Promo yang sudah
berakhir tetap dapat ditemukan. - \[ \] Data riwayat tidak mengubah
promo yang sedang berjalan.

## 5. Kebutuhan Non-Fungsional

-   **Akurasi data:** Informasi yang ditampilkan harus bersumber dari
    dokumen transaksi terkait.
-   **Audit trail:** Perubahan Sales Invoice harus dapat ditelusuri,
    termasuk pengguna dan waktu perubahan.
-   **Hak akses:** Fitur koreksi hanya dapat digunakan oleh role yang
    berwenang.
-   **Kompatibilitas:** Improvement tidak boleh merusak proses transaksi
    dan laporan yang sudah berjalan.
-   **Performa:** Penambahan kolom dan tab riwayat tidak boleh
    menyebabkan penurunan performa yang signifikan.
-   **Konsistensi:** Relasi data Invoice, DO, PL, Sales Return, dan stok
    harus tetap terjaga.

## 6. Skenario Pengujian

  ------------------------------------------------------------------------
  ID                      Skenario                Hasil yang Diharapkan
  ----------------------- ----------------------- ------------------------
  TC-01                   Membuka daftar Sales    No. dan tanggal Invoice,
                          Return dengan dokumen   DO, serta PL tampil
                          lengkap                 sesuai sumber

  TC-02                   Membuka Sales Return    Kolom PL menampilkan
                          yang belum memiliki PL  nilai kosong/status yang
                                                  sesuai

  TC-03                   Membuka daftar Sales    Tanggal DO dan PL tampil
                          Invoice dengan DO dan   sesuai dokumen
                          PL                      

  TC-04                   Membuka Sales Invoice   Halaman tetap berjalan
                          tanpa DO atau PL        dan nilai yang belum
                                                  tersedia ditangani
                                                  dengan benar

  TC-05                   Melakukan koreksi       Sistem menyimpan audit
                          invoice setelah DO dan  trail koreksi
                          PL                      

  TC-06                   Melihat histori koreksi Nilai sebelum/sesudah,
                          invoice                 alasan, pelaksana, dan
                                                  waktu dapat ditelusuri

  TC-07                   Mengoreksi kuantitas    Sistem mewajibkan proses
                          barang yang sudah       stok/retur/penyesuaian
                          keluar gudang           yang sesuai

  TC-08                   Pengguna tanpa hak      Sistem menolak tindakan
                          akses mencoba melakukan 
                          koreksi                 

  TC-09                   Membuka tab History     Daftar promo yang pernah
                          Promo                   dibuat beserta periode
                                                  berlaku tampil

  TC-10                   Mencari promo yang      Promo tetap tersedia
                          sudah berakhir          dalam riwayat
  ------------------------------------------------------------------------

## 7. Prioritas Implementasi

  -----------------------------------------------------------------------
  Prioritas               Modul/Fitur             Alasan
  ----------------------- ----------------------- -----------------------
  High                    Kontrol koreksi Sales   Mengurangi risiko
                          Invoice setelah DO dan  transaksi dan stok
                          PL                      tidak tercatat

  High                    Kolom referensi dokumen Mempermudah penelusuran
                          pada Sales Return       transaksi retur

  Medium                  Tanggal DO dan PL pada  Mempermudah monitoring
                          Sales Invoice           dokumen pengiriman

  Medium                  History Promo           Mempermudah penelusuran
                                                  promo sebelumnya
  -----------------------------------------------------------------------

## 8. Asumsi dan Hal yang Perlu Dikonfirmasi

1.  **Relasi dokumen:** Apakah satu Sales Invoice dapat memiliki lebih
    dari satu DO atau PL? Jika ya, bagaimana format tampilan yang
    diinginkan?
2.  **Koreksi invoice:** Apakah koreksi dilakukan melalui revisi
    invoice, credit note, debit note, atau dokumen koreksi khusus?
3.  **Dampak koreksi:** Apakah koreksi hanya mengubah harga/diskon, atau
    dapat mengubah kuantitas dan item?
4.  **Barang sudah keluar gudang:** Proses resmi apa yang harus
    digunakan jika koreksi memengaruhi barang yang telah dikirim?
5.  **Audit trail:** Role mana yang boleh melakukan koreksi dan siapa
    yang menyetujui koreksi?
6.  **History Promo:** Apakah informasi cukup nama/kode dan tanggal
    berlaku, atau perlu ditambahkan jenis promo, status, serta detail
    ketentuan promo?

## 9. Indikator Keberhasilan

-   Informasi dokumen Invoice, DO, dan PL dapat ditelusuri dari halaman
    Sales Return dan Sales Invoice.
-   Koreksi transaksi setelah DO dan PL memiliki histori yang lengkap.
-   Tidak ada koreksi yang menghilangkan jejak transaksi atau
    menyebabkan perubahan stok tanpa proses yang sah.
-   Riwayat promo terdahulu dapat diakses berdasarkan periode
    berlakunya.
-   Risiko ketidaksesuaian data penjualan dan stok berkurang.

------------------------------------------------------------------------

**Catatan:** Dokumen ini merupakan PRD draft dan perlu dikonfirmasi pada
bagian asumsi sebelum dijadikan acuan final development.
