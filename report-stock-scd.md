# Report Stock SCD

## Tujuan
Menyesuaikan fitur SCD pada halaman `report/report_stock` agar perhitungan omset/SCD bersumber dari data penjualan, bukan dari mutasi stok (`product_stock_move`).

## File yang Diubah
- `app/Http/Controllers/api/report/ReportStockController.php` — method `getData()`

## Perubahan
- Menambahkan subquery agregat penjualan per `product` + `warehouse` dari:
  - `sales_invoice_detail` + `sales_invoice_header` (tanggal invoice, gudang, company)
  - `sales_order_details` untuk produk & satuan
  - `product_uom` untuk konversi qty ke satuan terkecil
- Mengurangi penjualan dengan `sales_return_detail` + `sales_return` (status selain `CANCELLED`) untuk mendapatkan penjualan bersih.
- Subquery di-join ke query utama sebagai `s3m`.

## Kolom yang Disesuaikan
| Kolom | Sebelum | Sesudah |
|---|---|---|
| Last 3 Month (`stok_3bln`) | `qty_in - qty_out` mutasi stok | Penjualan bersih 3 bulan / konversi satuan large |
| Avg Omset per Day (`avg_omset`) | `stok_3bln / 25` (mutasi stok) | Penjualan bersih 3 bulan / konversi / 25 |
| SCD Stock (`stock_scd`) | Stok tersedia / avg omset mutasi | Stok tersedia / avg omset penjualan |
| SCD Stock + Instransit (`stock_scd_and_intransit`) | (Stok + intransit) / avg omset mutasi | (Stok + intransit) / avg omset penjualan |

## Tidak Diubah
- Perhitungan Total Masuk, Total Keluar, Stok Tersedia, Stok Akan Datang, Total Stock + Intransit.
- Filter tanggal, principal/vendor, company, search, sorting, dan pagination.
- Method lain: `getDataStock()` dan `getDataStockDetail()`.

## Pengujian
- `php -l app/Http/Controllers/api/report/ReportStockController.php` — tidak ada error sintaks.
- Uji langsung `getData` dengan variasi tanggal, principal, keyword search, dan sorting — query berjalan dan data SCD tampil.
