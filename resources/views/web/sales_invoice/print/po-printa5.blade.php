<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Invoice - {{ $data->code }}</title>

    <style>
        @page {
            /* size: A5 landscape;  */
            /* ðŸ“Œ Ukuran setengah A4 */
            /* margin: 5mm;  */
            /* ðŸ“Œ Margin kecil khas dot matrix */
            /* margin: 8mm 10mm; */

            size: 215.9mm auto;
            /* 8.5 inch lebar, tinggi otomatis */
            margin: 5mm 11mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        th {
            /* background: #f0f0f0; */
            background: #ffffff;
        }

        .no-border td {
            border: none;
            padding: 2px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
            padding: 2px;
        }

        .logo {
            width: 80px;
            /* diperkecil agar muat A5 */
        }

        .table-detail th,
        .table-detail td {
            font-size: 12px;
            padding: 2px 3px;
            /* kurangi padding */
        }
    </style>
</head>

<body>
    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td style="width:80px;height:80px;">
                <img src="{{ public_path('assets/images/logo-main-app.png') }}" class="logo">
            </td>
            <td>
                <strong style="font-size:12px !important;">{{ $company->nama_company }}</strong><br>
                <small style="font-size:12px  !important;">{!! $company->alamat !!}</small>
            </td>
            {{-- âœ… lebar cukup, nowrap supaya tidak wrap --}}
            <td style="width:100px; text-align:right; white-space:nowrap;">
                <strong style="font-size:12px;">FAKTUR PENJUALAN</strong><br>
                <small>No: {{ $data->invoice_number }}</small>
            </td>
        </tr>
    </table>

    {{-- INFO PO --}}
    <table class="no-border" style="font-size:12px;">
        <tr>
            <td style="font-size:12px !important;"><strong>Kode Pesanan:</strong>
                {{ isset($data->do->so->so_number) ? $data->do->so->so_number : $data->so->so_number }}</td>
            <td style="padding-left:20px;font-size:12px !important;"><strong>Tanggal Pesanan:</strong>
                {{ isset($data->do->so->so_date) ? date('d/m/Y', strtotime($data->do->so->so_date)) : date('d/m/Y', strtotime($data->so->so_date)) }}
            </td>
        </tr>
        <tr>
            <td style="font-size:12px !important;"><strong>Pelanggan:</strong>
                {{ $data->customers->nama_customer ?? '-' }}</td>
            <td style="padding-left:20px;font-size:12px !important;"><strong>Nomor Faktur:</strong>
                {{ $data->invoice_number }}</td>
        </tr>
        <tr>
            <td style="font-size:12px !important;"><strong>Syarat Pembayaran:</strong>
                {{ $data->customers->top->remarks ?? '-' }}</td>
            <td style="padding-left:20px;font-size:12px !important;"><strong>Tanggal Faktur:</strong>
                {{ date('d/m/Y', strtotime($data->invoice_date)) }}</td>
        </tr>
        <tr>
            <td style="font-size:12px !important;"><strong>Alamat:</strong> {{ $data->customers->address ?? '-' }}</td>
            <td style="padding-left:20px;font-size:12px !important;"><strong>Tanggal Jatuh Tempo:</strong>
                {{ date('d/m/Y', strtotime($data->due_date)) }}</td>
        </tr>
        <tr>
            <td style="font-size:12px !important;"><strong>Penjual:</strong> {{ $salesman_name ?? '-' }}</td>
            <td style="padding-left:20px;font-size:12px !important;"><strong>No. Kiriman:</strong>
                {{ isset($data->do->do_number) ? $data->do->do_number : '-' }}</td>
        </tr>
    </table>

    <h4 style="margin-top:6px;font-size:12px !important;">Detail Barang</h4>

    <table class="table-detail" style="width:100%; table-layout:fixed;">
        <thead>
            <tr>
                <th class="text-center" style="width:3%;">No</th>
                <th class="text-center" style="width:22%;">Produk</th>
                <th class="text-center" style="width:10%;">Satuan</th>
                <th class="text-center" style="width:7%;">Qty</th>
                <th class="text-center" style="width:20%;">Harga (Excl. PPN)</th>
                <th class="text-center" style="width:10%;">PPN</th>
                <th class="text-center" style="width:10%;">Diskon</th>
                <!-- <th class="text-center" style="width:6%;">Note</th> -->
                <th class="text-center" style="width:18%;">Total</th>
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

                    $harga_channel = "";
                    if($item->so_detail->has_channel_price == 1){
                        $harga_channel = " (Disc. Cnl)";
                    }
                    if($item->so_detail->has_customer_product == 1){
                        $harga_channel = " (Disc. Cust)";
                    }

                    $totalDpp += $dpp;
                    $totalPpn += $ppn;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->products->name ?? '-' }}</td>
                    <td class="text-center">{{ $item->so_detail->units->name ?? '-' }}</td>
                    <td class="text-center">{{ number_format($item->qty, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($hargaExcl, 0, ',', '.') }} {{ $harga_channel }}</td>
                    <td class="text-right">{{ number_format($ppn, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->discount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        {{-- ================= FOOTER ================= --}}
        @php
            // Ambil dari header
            $subtotalInclude = $data->subtotal;
            $discountInclude = $data->discount_amount;

            // Convert ke DPP
            $subtotalDpp = $subtotalInclude / (1 + $taxRate / 100);
            $discountDpp = $discountInclude / (1 + $taxRate / 100);

            $dppAfterDiscount = $subtotalDpp - $discountDpp;
            $taxAmount = $dppAfterDiscount * ($taxRate / 100);
            $grandTotal = $dppAfterDiscount + $taxAmount;
        @endphp

        <tfoot>
            <tr>
                <td colspan="7" class="text-right"><strong>Sub Total (DPP)</strong></td>
                <td class="text-right"><strong>{{ number_format($subtotalDpp, 0, ',', '.') }}</strong></td>
            </tr>
            @foreach ($promo as $v)
                @php
                    $taxRate = (float) ($ppn_val ?? 11);

                    // nilai promo masih include PPN
                    $promoInclude = $v->total_potongan;

                    // convert ke DPP
                    $promoDpp = $promoInclude / (1 + $taxRate / 100);
                @endphp

                <tr>
                    <td colspan="7" class="text-right">
                        <strong>{{ $v->promo_name }}</strong>
                    </td>
                    <td class="text-right" style="color: #c00;">
                        <strong>- {{ number_format($promoDpp, 0, ',', '.') }}</strong>
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="7" class="text-right"><strong>PPN {{ $taxRate }} %</strong></td>
                <td class="text-right"><strong>{{ number_format($taxAmount, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td colspan="7" class="text-right"><strong>Grand Total</strong></td>
                <td class="text-right"><strong>{{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <table class="no-border">
        <tr>
            <td style="font-size:12px !important;">
                Note: Barang telah diterima dengan cukup dan baik, pembayaran transfer hanya diakui melalui rekening
                :<br />
                {{ $company->akun_bank }} - {{ $company->akun_bank_number }}<br />
                {{ $company->akun_bank_name }}
            </td>
        </tr>
    </table>

    <table class="no-border">
        <tr>
            <td class="text-center">
                <strong style="font-size:12px;">Diterima Oleh</strong><br><br><br>
                (__________________)
            </td>
            <td class="text-center">
                <strong style="font-size:12px;">Disetujui Oleh</strong><br><br><br>
                (__________________)
            </td>
            <td class="text-center">
                <strong style="font-size:12px;">Dibuat Oleh</strong><br><br><br>
                (__________________)
            </td>
        </tr>
    </table>

</body>

</html>
