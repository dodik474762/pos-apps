<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Multiple Invoice</title>

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
            height: auto;
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
            font-size: 8pt;
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

        .page-wrapper {
            page-break-after: always;
        }

        .page-wrapper:last-child {
            page-break-after: avoid;
        }

        .page-info {
            text-align: center;
            font-size: 10px;
            color: #555;
            margin-top: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    @foreach ($invoices as $globalIndex => $data)
        {{-- <div class="page-wrapper"> --}}
        @php
            $qr = '';
            $do = empty($data->do) ? [] : $data->do;
            $so = empty($data->do) ? $data->so : $do->so;
            $salesman_name = $so->salesman->nama_lengkap ?? '-';
        @endphp

        @include('web.sales_invoice.print.bodyprint4', [
            'data' => $data,
            'company' => $company,
            'qr' => $qr,
            'so' => $so,
            'salesman_name' => $salesman_name,
            'ppn_val' => $data->ppn_value,
            'promo' => $data->promo ?? collect(),
            'promo_item' => $data->promo_item ?? collect(),
        ])

        {{-- <div class="page-info">
                Invoice {{ $globalIndex + 1 }} / {{ $invoices->count() }}
            </div>
        </div> --}}
    @endforeach

</body>

</html>
