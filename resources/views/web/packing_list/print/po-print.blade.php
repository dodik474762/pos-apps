<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Packing List - {{ $data->code }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        .no-border td {
            border: none;
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
        }

        .logo {
            width: 90px;
        }

        .table-detail {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* penting */
        }

        .table-detail th,
        .table-detail td {
            font-size: 10px;
            /* kecilkan */
            padding: 3px;
            border: 1px solid #000;

            /* supaya text turun baris */
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;

            vertical-align: top;
        }

        .table-detail th {
            background: #f8f8f8;
        }

        /* PAGE BREAK CSS */
        .table-detail tr {
            page-break-inside: avoid;
        }

        .table-detail tr.force-break {
            page-break-after: always;
            page-break-inside: auto;
        }

        .page-break {
            page-break-before: always;
        }

        @page {
            margin: 40px 10px 60px 10px;
        }

        /* @page :first {
            margin-top: 20px;
        }*/

        .footer-page {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 10px;
            color: #333;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <table class="header-table" style="width:100%;">
        <tr>
            <td style="width: 90px;">
                <img src="{{ public_path('assets/images/logo-main-app.png') }}" class="logo">
            </td>
            <td style="text-align: left;">
                <h3 style="margin:0; padding:0;">{{ $company->nama_company }}</h3>
                <small>{!! $company->alamat !!}</small>
            </td>
            <td style="text-align:right;">
                <h3 style="margin:0; padding:0;">PACKING LIST</h3>
                <label style="font-size: 14px;">No: {{ $data->packing_list_no }}</label>
            </td>
        </tr>
    </table>

    {{-- INFORMASI --}}
    <table class="no-border" style="width:100%;line-height: 0.5;">
        <tr>
            <td>No. Kendaraan: {{ $data->vehicle_no }}</td>
            <td style="padding-left:40px;">Tanggal Packing:
                {{ date('d/m/Y', strtotime($data->packing_date)) }}</td>
        </tr>
        <tr>
            <td>Sopir: {{ $data->driver_name ?? '-' }}</td>
            <td style="padding-left:40px;">Ekspedisi: {{ $data->expedition_name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Keterangan: {{ $data->remarks ?? '-' }}</td>
        </tr>
    </table>

    {{-- ==================== DATA DO ==================== --}}
    <h4 style="line-height: 0.5;">Detail Delivery Order</h4>

    <table class="table-detail">
        <thead>
            <tr>
                <td style="width:4%;font-size: 10px;">No</td>
                <td style="width:7%;font-size: 10px;">DO</td>
                <td style="width:9%;font-size: 10px;">No. Faktur</td>
                <td style="width:8%;font-size: 10px;">Kode Cust</td>
                <td style="width:9%;font-size: 10px;">Customer</td>
                <td style="width:5%;font-size: 10px;">TOP</td>
                <td style="width:9%;font-size: 10px;">Jth Tempo</td>
                <td style="width:8%;font-size: 10px;">Total Faktur</td>
                <td style="width:9%;font-size: 10px;">Outstanding</td>
                <td style="width:8%;font-size: 10px;">Tunai</td>
                <td style="width:8%;font-size: 10px;">Transfer</td>
                <td style="width:7%;font-size: 10px;">Remark</td>
                <td style="width:9%;font-size: 10px;">WH Check</td>
            </tr>
        </thead>

        <tbody>
            @php $no = 1; @endphp
            @foreach ($details as $item)
                @php
                    $isPageBreak = $no % 15 == 0 && $no != count($details);
                @endphp
                <tr @if ($isPageBreak) class="force-break" @endif>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $item->do_number }}</td>
                    <td>{{ $item->invoice_number }}</td>
                    <td>{{ $item->customer_code }}</td>
                    <td>{{ $item->nama_customer }}</td>
                    <td>{{ $item->top_name }}</td>
                    <td>{{ date('d/m/Y', strtotime($item->due_date)) }}</td>
                    <td>{{ number_format($item->total_amount, 0, ',', '.') }}</td>
                    <td>{{ number_format($item->total_amount - $item->amount_paid, 0, ',', '.') }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="10" class="text-right"><strong>Total DO</strong></td>
                <td class="text-right" colspan="2">
                    <strong>{{ number_format(count($details), 0, ',', '.') }} Customer</strong>
                </td>
                <td>&nbsp;</td>
            </tr>
        </tfoot>
    </table>


    {{-- SIGN --}}
    <table class="no-border" style="width:100%;margin-top: -20px;">
        <tr>
            <td class="text-center">
                <br><br><br>
                <strong>Dibuat Oleh</strong>
                <br><br><br>
                (__________________)
                <br />
                &nbsp;
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Kolektor</strong>
                <br><br><br>
                (__________________)
                <br />
                &nbsp;
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Kasir</strong>
                <br><br><br>
                (__________________)
                <br />
                &nbsp;
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Admin AR</strong>
                <br><br><br>
                (__________________)
                <br />
                &nbsp;
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Disetujui Oleh</strong>
                <br><br><br>
                (AYU RIFZKITA)
                <br />
                BOD
            </td>
        </tr>
    </table>

    {{-- ==================== HITUNG TOTAL PRODUK ==================== --}}
    @php
        $totalProduk = 0;
        foreach ($details as $d) {
            if (!empty($d->detail)) {
                $totalProduk += count($d->detail);
            }
        }
    @endphp

    {{-- ==================== PAGE BREAK HANYA JIKA PRODUK > 10 ==================== --}}
    @if ($totalProduk > 10)
        <div class="page-break"></div>
    @endif


    {{-- ==================== DAFTAR PRODUK ==================== --}}
    <h4 style="margin-top: 5px;">Daftar Produk</h4>

    <table class="table-detail">
        <thead>
            <tr>
                <td style="width:5%">No</td>
                <td style="width:18%">Kode Produk</td>
                <td style="width:35%">Nama Produk</td>
                <td style="width:12%">Qty Pack</td>
                <td style="width:10%">Satuan</td>
                <td style="width:20%">Remark</td>
            </tr>
        </thead>

        <tbody>
            @php $p = 1; @endphp
            @foreach ($groupedItem as $d)
                @php
                    $isPageBreakProduk = $p % 22 == 0 && $p != count($groupedItem);
                @endphp
                <tr @if ($isPageBreakProduk) class="force-break" @endif>
                    <td class="text-center">{{ $p++ }}</td>

                    <td>{{ $d['product_code'] ?? '-' }}</td>
                    <td>{{ $d['product_name'] ?? '-' }}</td>

                    <td class="text-right">{{ number_format($d['conversion']['qty_in_largest_unit'], 2, ',', '.') }}
                    </td>

                    <td>{{ $d['conversion']['largest_unit_name'] ?? '-' }}</td>
                    <td>{!! $d['assembly'] == 1 ? '<strong>Assembly</strong>' : '' !!}
                        <br />
                        {{ $d['assembly_name'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $size = 9;
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";

            $y = $pdf->get_height() - 94;

            // x = 0, width = full page width, align = center
            $pdf->page_text(
                250,
                $y,
                $text,
                $font,
                $size,
                array(0, 0, 0),
                0.0,
                0.0,
                0.0,
                "center"
            );
        }
    </script>
</body>

</html>
