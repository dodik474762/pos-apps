<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Invoice - {{ $data->code }}</title>
    <style>
        @page {
            size: 210mm 297mm;
            /* A4 portrait */
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7pt;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* =========================================
           WRAPPER: Satu faktur = setengah A4
           ========================================= */
        .faktur-block {
            width: 194mm;
            height: 161.5mm;
            /* Tepat setengah A4 */
            padding: 5mm 8mm 4mm 8mm;
            overflow: hidden;
            page-break-inside: avoid;
        }

        /* Garis potong di tengah halaman */
        .cut-line {
            width: 210mm;
            border-top: 1px dashed #555;
            text-align: center;
            font-size: 6pt;
            color: #555;
            line-height: 0;
            height: 0;
        }

        /* =========================================
           HEADER
           ========================================= */
        .header-outer {
            width: 100%;
            display: table;
            border-collapse: collapse;
            margin-bottom: 2mm;
        }

        .header-outer td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .header-left {
            display: table;
            width: 100%;
        }

        .header-left td {
            border: none;
            padding: 0 2mm 0 0;
            vertical-align: middle;
        }

        .logo {
            width: 45pt;
        }

        .company-name {
            font-size: 9pt;
            font-weight: bold;
        }

        .company-address {
            font-size: 6pt;
            line-height: 1.3;
        }

        /* Kotak info faktur di kanan header */
        .faktur-info-box {
            border: 1px solid #000;
            width: 120pt;
            min-width: 120pt;
            font-size: 6pt;
        }

        .faktur-info-box table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .faktur-info-box td {
            border: 1px solid #000;
            padding: 1.5pt 2pt;
            font-size: 6pt;
        }

        .faktur-title {
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #000;
            padding: 2pt;
            border: none;
        }

        /* =========================================
           INFO PELANGGAN
           ========================================= */
        .kepada-section {
            font-size: 7pt;
            margin-bottom: 1mm;
            border-top: 1px solid #000;
            padding-top: 1mm;
        }

        /* =========================================
           TABEL BARANG
           ========================================= */
        table.tabel-barang {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1mm;
            font-size: 8pt;
        }

        table.tabel-barang th,
        table.tabel-barang td {
            border: 1px solid #000;
            padding: 1.5pt 2pt;
        }

        table.tabel-barang th {
            background: #fff;
            text-align: center;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        /* =========================================
           FOOTER FAKTUR
           ========================================= */
        .footer-section {
            display: table;
            width: 100%;
            margin-top: 2mm;
        }

        .footer-left {
            display: table-cell;
            vertical-align: top;
            width: 55%;
            font-size: 7pt;
        }

        .footer-right {
            display: table-cell;
            vertical-align: top;
            width: 45%;
        }

        table.summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
        }

        table.summary-table td {
            border: 1px solid #000;
            padding: 1.5pt 3pt;
        }

        /* Tanda tangan */
        .ttd-section {
            display: table;
            width: 100%;
            margin-top: 2mm;
        }

        .ttd-cell {
            display: table-cell;
            text-align: center;
            font-size: 6pt;
            padding: 0 4pt;
        }

        .ttd-line {
            border-top: 1px solid #000;
            margin: 10mm 4pt 0 4pt;
        }

        /* Terbilang */
        .terbilang-row td {
            font-size: 6pt;
            font-style: italic;
            border: 1px solid #000;
            padding: 1.5pt 3pt;
        }

        .bank-info {
            font-size: 6pt;
            line-height: 1.4;
        }
    </style>
</head>

<body>

    @include('web.sales_invoice.print.bodyprint4')

    {{-- ============================================================
     GARIS POTONG
     ============================================================ --}}
    {{-- <div style="width:210mm; border-top:1px dashed #888; font-size:0; line-height:0; height:0; margin:0;"></div> --}}

    {{-- ============================================================
     FAKTUR COPY 2 (bawah) — persis sama
     ============================================================ --}}
    {{-- <div class="faktur-block"> --}}

    {{-- HEADER --}}
    {{-- <table class="header-outer">
            <tr>
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
                <td style="width:130pt; vertical-align:top; padding-left:4pt;">
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
                            <td style="border:1px solid #000; border-top:none; border-left:none; padding:1.5pt 2pt;">
                                <strong>{{ $data->invoice_number }}</strong>
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

        <div class="kepada-section">
            <strong>Kepada :</strong><br>
            {{ $data->customers->nama_customer ?? '-' }}<br>
            {{ $data->customers->address ?? '-' }}
        </div>

        <table class="tabel-barang">
            <thead>
                <tr>
                    <th style="width:45%;" class="text-left">Nama Barang</th>
                    <th style="width:8%;" class="text-center">Kts.</th>
                    <th style="width:14%;" class="text-right">@Harga</th>
                    <th style="width:12%;" class="text-right">Diskon</th>
                    <th style="width:21%;" class="text-right">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->items as $item)
                    @php
                        $subtotal = (float) $item->subtotal;
                        $price = (float) $item->price;
                        $hargaExcl = $price / (1 + $taxRate / 100);
                    @endphp
                    <tr>
                        <td>{{ $item->products->name ?? '-' }}</td>
                        <td class="text-center">{{ number_format($item->qty, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($hargaExcl, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->discount, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

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

        <div class="footer-section" style="margin-top:1.5mm;">
            <div class="footer-left">
                <div style="display:table; width:100%;">
                    <div style="display:table-cell; text-align:center; font-size:7.5pt; width:50%;">
                        Disiapkan Oleh<br><br><br>
                        <div style="border-top:1px solid #000; margin: 0 8pt;"></div>
                        <small>Tgl</small>
                    </div>
                    <div style="display:table-cell; text-align:center; font-size:7.5pt; width:50%;">
                        Diterima Oleh<br><br><br>
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
                        <td class="text-right"><strong>{{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div> --}}

    {{-- </div> --}}

</body>

</html>
