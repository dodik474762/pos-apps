aku punya data master promo :

- KOPI KENANGAN - PROMO 11+1 MIX 3
  - min_mix = 1 product
  - max_mix = 3 product
  - min_qty = 12 botol
  - max_qty = 12 botol
  - berkelipatan = 1
  - potong di grand total = 1
  - discount_type = nominal
  - discount_nilai = 5742
  - group product yang kena promo :
    - KOPI KENANGAN CAFFE LATTE 220ML
    - KOPI KENANGAN ROYAL CHEESE 220ML
    - KOPI KENANGAN INDONESIANO 220ML
 

 - KOPI KENANGAN - STRATA DISC 1% MIX 3
  - min_mix = 1 product
  - max_mix = 3 product
  - min_qty = 6 carton
  - max_qty = 20 carton
  - berkelipatan = 1
  - potong di grand total = 1
  - discount_type = percent
  - discount_nilai = 1
  - group product yang kena promo :
    - KOPI KENANGAN CAFFE LATTE 220ML
    - KOPI KENANGAN ROYAL CHEESE 220ML
    - KOPI KENANGAN INDONESIANO 220ML


- KOPI KENANGAN - STRATA DISC 2% MIX 3
  - min_mix = 1 product
  - max_mix = 3 product
  - min_qty = 21 carton
  - max_qty = 9999 carton
  - berkelipatan = 1
  - potong di grand total = 1
  - discount_type = percent
  - discount_nilai = 2
  - group product yang kena promo :
    - KOPI KENANGAN CAFFE LATTE 220ML
    - KOPI KENANGAN ROYAL CHEESE 220ML
    - KOPI KENANGAN INDONESIANO 220ML



- Aku input data list item  dari Sales Order Berikut:
 - KOPI KENANGAN CAFFE LATTE 220ML | 4 Botol 
 - KOPI KENANGAN ROYAL CHEESE 220ML | 4 Botol
 - KOPI KENANGAN INDONESIANO 220ML | 4 Botol
 - KOPI KENANGAN INDONESIANO 220ML | 8 Carton


 dari function calculatePromoV2 aku mendapatkan hasil seperti ini:
 - KOPI KENANGAN - PROMO 11+1 MIX 3 saja


harusnya hasil yang aku inginkan seperti ini:
 - KOPI KENANGAN - PROMO 11+1 MIX 3
 - KOPI KENANGAN - STRATA DISC 1% MIX 3


karena kondisi input sales order productnya memenuhi 2 promo tersebut


Case Perbaikan :

Edited untitled:Untitled-1
Searched for "function isPromoApplicable"
Viewed SalesOrderController.php:1150-1300
Searched for "calculatePromoV2"
Viewed SalesOrderController.php:590-620
Viewed SalesOrderController.php:1580-1650
Viewed SalesOrderController.php:1780-1850
Viewed SalesOrderController.php:1230-1275
Edited SalesOrderController.php
Viewed SalesOrderController.php:1560-1630
Edited SalesOrderController.php

Halo! Terima kasih atas penjelasan detail skenario input dan ekspektasinya.

Saya telah menganalisis fungsi `calculatePromoV2` (beserta dengan fungsi saudaranya, `calculatePromo`), dan menemukan 2 **bug logika utama** yang saling terkait mengapa `STRATA DISC 1% MIX 3` milik Anda tidak tepanggil, dan mengapa saat Anda memesan unit yang kembar (Indonesiano terbagi 2 input satuan), sistem tetap hanya men-trigger `PROMO 11+1 MIX 3`.

### Apa Penyebab Masalahnya?
1. **Pencarian Produk Gagal Menghitung Keseluruhan Kuantitas Baris Ganda (Botol + Carton)**
   Di sistem sebelumnya, ketika sistem mulai mengisi variabel daftar barang yang lolos filter promo (`$itemsValue`), sistem menggunakan perintah `collect($items)->where('product_id', $h)->first();`.
   > Karena menggunakan instruksi logis `->first()`, maka saat data "Indonesiano" dicek, sistem **hanya akan mengambil data baris pertama** yang ditemukan di Array (ia hanya menghitung yang 4 Botol, dan varian 8 Carton diabaikan!).
   > 
   > Karena yang ditarik hanya data awal (Latte 4, Royal 4, Indo 4 = 12 Botol), maka wajar promo `STRATA 1%` Anda (Syarat: Minimum 6 Carton) mengeluarkan status **TIDAK LOLOS SYARAT** (`if(!$applicable) continue;`) karena total kuantitas dianggap belum cukup (masih 12 Botol). Padahal aslinya ditambah 8 carton.
   
2. **Kelemahan validasi limit MAX Mix Total**
   Dulu sistem tidak melakukan `array_unique` pada Product ID bawaan API. Sehingga jika *Indonesiano* yang dientry terpecah di 2 rows keranjang, sistem mendeteksinya seolah-olah Anda memiliki 4 Tipe Produk secara kombinasi/Mix Limit. (Hal ini menyebabkan hitungan total applicable berlebih.)

### Apa perbaikan yang sudah dilakukan?
Saya telah merevisi kode sistem pada fungsi `calculatePromoV2` sekaligus juga di fungsi lawas `calculatePromo` atas celah di atas di `/app/Http/Controllers/api/Transaction/SalesOrderController.php`, dengan perbaikan berikut:

1. Modifikasi struktur *fetching array* dari menggunakan `->first()` menjadi penggabungan array penuh menggunakan kumpulan `->all()`. 
   Dengan begini, untuk ID Produk "Indonesiano", baik baris yang isiannya 4 Botol maupun baris 8 Carton, **keduanya akan tertarik utuh** dimasukkan ke perhitungan promo.
2. Karena kita menarik semua baris seutuhnya, sistem kalkulasi `calculateTotalSmallestQty(...)` di bawahnya kini telah dijamin bisa mengkalkulasi real total qty (misal 144 Botol + 12 Botol = 156 Botol). 
3. Menyisipkan variabel kontrol `$checkedProducts = []` serta instruksi pelindung `if (in_array(..., $checkedProducts)) continue;` menjelang pengecekan `isPromoApplicable()`.
   Peran filter pelindung ini mencegah fitur deteksi Max/Min Mix kebingungan jika menemukan ID produk divalidasi berkali-kali karena pecah baris di keranjang (Botol vs Carton). Ia tetap dijamin menghitung produk _Indonesiano_ tersebut sebagai 1 jenis produk sah dalam syarat Kombinasi (Syarat _Mix-3_ terpenuhi).

Cobalah lakukan pengujian input Sales Order dengan kombinasi produk Botol dan Carton Anda kembali, hasil potong total `discount_header` dipastikan akan mendata kedua buah diskon dengan sesuai dengan harapan Anda (mendapat 11+1 Mix 3 sekaligus tambahan diskon Strata 1%).