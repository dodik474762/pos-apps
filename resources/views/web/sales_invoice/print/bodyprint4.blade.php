 @php
     $taxRate = (float) ($ppn_val ?? 11);
     $subtotalInclude = $data->subtotal;
     $discountInclude = $data->discount_amount;
     $subtotalDpp = $subtotalInclude / (1 + $taxRate / 100);
     $discountDpp = $discountInclude / (1 + $taxRate / 100);
     $dppAfterDiscount = $subtotalDpp - $discountDpp;
     $taxAmount = $dppAfterDiscount * ($taxRate / 100);
     $grandTotal = $dppAfterDiscount + $taxAmount;

     $so_number = isset($data->do->so->so_number) ? $data->do->so->so_number : $data->so->so_number;
     $so_date = isset($data->do->so->so_date) ? $data->do->so->so_date : $data->so->so_date;
 @endphp

 {{-- ============================================================
     FAKTUR COPY 1 (atas)
     ============================================================ --}}
 <div class="faktur-block" style="margin-top:-25px;">

     {{-- HEADER --}}
     <table class="header-outer">
         <tr>
             {{-- Kiri: logo + nama company + alamat --}}
             <td style="vertical-align:middle;">
                 <table style="border-collapse:collapse; width:100%;">
                     <tr>
                         <td style="border:none; padding:0; width:50pt; vertical-align:middle;">
                             <img src="{{ public_path('assets/images/logo-main-app.png') }}" class="logo">
                         </td>
                         <td style="border:none; padding:0 0 0 3pt; vertical-align:middle;">
                             <div class="company-name">{{ $company->nama_company }}</div>
                             <div class="company-address">{!! $company->alamat !!}</div>
                         </td>
                     </tr>
                 </table>
             </td>

             {{-- Kanan: judul + kotak info --}}
             <td style="width:160pt; vertical-align:top; padding-left:4pt;;">
                 <div style="font-size:10pt; font-weight:bold; text-align:right; margin-bottom:1mm;">Faktur Penjualan
                 </div>
                 <table style="width:100%; border-collapse:collapse; font-size:7.5pt;">
                     <tr>
                         <td style="border:1px solid #000; padding:1.5pt 2pt; width:50%;">Tanggal</td>
                         <td style="border:1px solid #000; padding:1.5pt 2pt; width:50%; border-left:none;">Nomor
                         </td>
                     </tr>
                     <tr>
                         <td style="border:1px solid #000; border-top:none; padding:1.5pt 2pt;">
                             {{ date('d M Y', strtotime($data->invoice_date)) }}
                         </td>
                         <td
                             style="border:1px solid #000; border-top:none; border-left:none; padding:1.5pt 2pt;font-size:13px;">
                             {{ $data->invoice_number }}
                         </td>
                     </tr>
                     <tr>
                         <td style="border:1px solid #000; border-top:none; padding:1.5pt 2pt;">
                             Tgl. Jatuh Tempo
                         </td>
                         <td style="border:1px solid #000; border-top:none; border-left:none; padding:1.5pt 2pt;">
                             <strong>{{ date('d/m/Y', strtotime($data->due_date)) }}</strong>
                         </td>
                     </tr>
                     <tr>
                         <td style="border:1px solid #000; border-top:none; padding:1.5pt 2pt;">Syarat Pembayaran
                         </td>
                         <td style="border:1px solid #000; border-top:none; border-left:none; padding:1.5pt 2pt;">PO
                             No</td>
                     </tr>
                     <tr>
                         <td style="border:1px solid #000; border-top:none; padding:1.5pt 2pt;">
                             {{ $data->customers->top->remarks ?? 'CASH' }}
                         </td>
                         <td style="border:1px solid #000; border-top:none; border-left:none; padding:1.5pt 2pt;">
                             {{ $so_number }}
                         </td>
                     </tr>
                     <tr>
                         <td colspan="2" style="border:1px solid #000; border-top:none; padding:1.5pt 2pt;">
                             Penjual: <strong>{{ $salesman_name ?? '-' }}</strong>
                         </td>
                     </tr>
                 </table>
             </td>
         </tr>
     </table>

     {{-- KEPADA --}}
     <div class="kepada-section">
         <strong>Kepada :</strong><br>
         {{ $data->customers->code ?? '-' }} - {{ $data->customers->nama_customer ?? '-' }}<br>
         {{ $data->customers->address ?? '-' }}<br>
         {{ $kecamatan_name ?? '-' }}, {{ $kabupaten_name ?? '-' }}
     </div>

     {{-- TABEL BARANG --}}
     <table class="tabel-barang">
         <thead>
             <tr>
                 <th class="text-center" style="width:3%;">No</th>
                 <th style="width:30%;" class="text-left">Nama Barang</th>
                 <th style="width:10%;" class="text-center">Satuan.</th>
                 <th style="width:5%;" class="text-center">Qty</th>
                 <th style="width:19%;" class="text-right">@Harga (Excl. PPN)</th>
                 <th style="width:9%;" class="text-right">PPN</th>
                 <th style="width:9%;" class="text-right">Diskon</th>
                 <th style="width:15%;" class="text-right">Total Harga</th>
             </tr>
         </thead>
         <tbody>
             @php
                 // $taxRate = $ppn_val ?? 11;

                 $totalDpp = 0;
                 $totalPpn = 0;
             @endphp
             @foreach ($data->items as $i => $item)
                 @php
                     $subtotal = (float) $item->subtotal;
                     $price = (float) $item->price;
                     $taxRate = (float) ($ppn_val ?? 11);

                     $dpp = $subtotal / (1 + $taxRate / 100);
                     $ppn = $subtotal - $dpp;
                     $hargaExcl = $price / (1 + $taxRate / 100);

                     $harga_channel = '';
                     if ($item->so_detail->has_channel_price == 1) {
                         $harga_channel = ' (Disc. Cnl)';
                     }
                     if ($item->so_detail->has_customer_product == 1) {
                         $harga_channel = ' (Disc. Cust)';
                     }

                     $totalDpp += $dpp;
                     $totalPpn += $ppn;
                 @endphp
                 <tr>
                     <td class="text-center">{{ $i + 1 }}</td>
                     <td>{{ $item->products->name ?? '-' }}</td>
                     <td class="text-center">{{ $item->so_detail->units->name ?? '-' }}</td>
                     <td class="text-center">{{ number_format($item->qty, 0, ',', '.') }}</td>
                     <td class="text-right">{{ $subtotal <= 0 ? 0 : number_format($hargaExcl, 0, ',', '.') }}
                         {{ $harga_channel }}</td>
                     <td class="text-right">{{ number_format($ppn, 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format($item->discount, 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format($subtotal, 0, ',', '.') }}</td>
                 </tr>
             @endforeach
         </tbody>
     </table>

     {{-- TERBILANG --}}
     @php
         // Fungsi terbilang tidak tersedia default, tampilkan saja angkanya
         // Jika punya helper terbilang, ganti ini
         $terbilang = '-';
         if (function_exists('terbilang')) {
             $terbilang = ucfirst(terbilang($grandTotal));
         }
     @endphp
     @if ($terbilang !== '-')
         <table style="width:100%; border-collapse:collapse; margin-top:0;">
             <tr class="terbilang-row">
                 <td style="width:55pt; border:1px solid #000; padding:1.5pt 3pt;"><strong>Terbilang :</strong></td>
                 <td
                     style="border:1px solid #000; border-left:none; padding:1.5pt 3pt; font-style:italic; font-size:7pt;">
                     {{ $terbilang }}
                 </td>
             </tr>
         </table>
     @endif

     {{-- FOOTER: TTD kiri + Summary kanan --}}
     <div class="footer-section" style="margin-top:1.5mm;">

         {{-- Kiri: TTD + Bank --}}
         <div class="footer-left">
             <div style="display:table; width:100%;">
                 <div style="display:table-cell; text-align:center; font-size:7.5pt; width:50%;">
                     Dibuat Oleh - Driver<br><br><br>
                     <div style="border-top:1px solid #000; margin: 0 8pt;"></div>
                     <small>Tgl</small>
                 </div>
                 <div style="display:table-cell; text-align:center; font-size:7.5pt; width:50%;">
                     Supervisor - BOM<br><br><br>
                     <div style="border-top:1px solid #000; margin: 0 8pt;"></div>
                     <small>Tgl</small>
                 </div>
                 <div style="display:table-cell; text-align:center; font-size:7.5pt; width:50%;">
                     Pelanggan<br><br><br>
                     <div style="border-top:1px solid #000; margin: 0 8pt;"></div>
                     <small>Tgl.</small>
                 </div>
             </div>
             <div class="bank-info" style="margin-top:2mm;">
                 PEMBAYARAN HANYA DIAKUI MELALUI :<br>
                 {{ $company->akun_bank }}: {{ $company->akun_bank_number }}<br>
                 Atasnama : {{ $company->akun_bank_name }}
             </div>
         </div>

         {{-- Kanan: Summary --}}
         <div class="footer-right">
             <table class="summary-table">
                 <tr>
                     <td style="text-align:left; width:55%;">Sub Total</td>
                     <td class="text-right">
                         {{ number_format($subtotalDpp + ($subtotalDpp * $taxRate) / 100, 0, ',', '.') }}</td>
                 </tr>
                 @foreach ($promo as $v)
                     @php
                         $promoInclude = $v->total_potongan;
                     @endphp
                     <tr>
                         <td>{{ $v->promo_name }}</td>
                         <td class="text-right" style="color:#c00;">-
                             {{ number_format($promoInclude, 0, ',', '.') }}</td>
                     </tr>
                 @endforeach
                 <tr>
                     <td>Diskon</td>
                     <td class="text-right">{{ number_format($discountInclude, 0, ',', '.') }}</td>
                 </tr>
                 <tr>
                     <td>PPN ({{ $taxRate }}%)</td>
                     <td class="text-right">{{ number_format($taxAmount, 0, ',', '.') }}</td>
                 </tr>
                 <tr>
                     <td><strong>Total</strong></td>
                     <td class="text-right" style="font-size:14px !important;">
                         <strong>{{ number_format($grandTotal, 0, ',', '.') }}</strong>
                     </td>
                 </tr>
             </table>
         </div>

     </div>
 </div>
 {{-- end faktur-block 1 --}}
