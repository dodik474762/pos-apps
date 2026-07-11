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

        .page-break {
            page-break-before: always;
        }

        @page {
            margin: 100px 25px 60px 25px;
        }

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
    <table class="header-table" style="width:100%;margin-top: -100px;">
        <tr>
            <td style="width: 90px;">
                <img src="{{ public_path('assets/images/logo-main-app.png') }}" class="logo">
            </td>
            <td style="text-align: left;">
                <h3 style="margin:0; padding:0;">{{ $company->nama_company }}</h3>
                <small>{!! $company->alamat !!}</small>
            </td>
            <td style="text-align:right;">
                <h4 style="margin:0; padding:0;">PACKING LIST</h4>
                <small>No: {{ $data->packing_list_no }}</small>
            </td>
        </tr>
    </table>

    {{-- INFORMASI --}}
    <table class="no-border" style="width:100%;line-height: 0.5;">
        <tr>
            <td><strong>No. Kendaraan:</strong> {{ $data->vehicle_no }}</td>
            <td style="padding-left:40px;"><strong>Tanggal Packing:</strong>
                {{ date('d/m/Y', strtotime($data->packing_date)) }}</td>
        </tr>
        <tr>
            <td><strong>Sopir:</strong> {{ $data->driver_name ?? '-' }}</td>
            <td style="padding-left:40px;"><strong>Ekspedisi:</strong> {{ $data->expedition_name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Keterangan:</strong> {{ $data->remarks ?? '-' }}</td>
        </tr>
    </table>

    {{-- ==================== DATA DO ==================== --}}
    <h4 style="line-height: 0.5;">Detail Delivery Order</h4>

    <table class="table-detail">
        <thead>
            <tr>
                <th style="width:6%">No</th>
                <th style="width:7%">DO</th>
                <th style="width:10%">No. Faktur</th>
                <th style="width:8%">Kode Cust</th>
                <th style="width:9%">Nama Customer</th>
                <th style="width:5%">TOP</th>
                <th style="width:10%">Jth Tempo</th>
                <th style="width:8%">Total Faktur</th>
                <th style="width:7%">Outstanding</th>
                <th style="width:6%">Tunai</th>
                <th style="width:8%">Transfer</th>
                <th style="width:9%">Remark</th>
                <th style="width:7%">WH Check</th>
            </tr>
        </thead>

        <tbody>
            @php $no = 1; @endphp
            @foreach ($details as $item)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $item->do_number }}</td>
                    {{-- <td>{{ $item->do_date }}</td> --}}
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
    <table class="no-border" style="width:100%;">
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
    <h4>Daftar Produk</h4>

    <table class="table-detail">
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:18%">Kode Produk</th>
                <th style="width:35%">Nama Produk</th>
                <th style="width:12%">Qty Pack</th>
                <th style="width:10%">Satuan</th>
                <th style="width:20%">Remark</th>
            </tr>
        </thead>

        <tbody>
            @php $p = 1; @endphp
            @foreach ($groupedItem as $d)
                <tr>
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

            {{-- @foreach ($grouped as $item)
                @foreach ($item as $items)
                    @foreach ($items as $d)
                        @php
                            $assembly = '';
                            if(isset($productLargest[$d->product->code]) && strtolower($d->deliveryDetail->units->name) != 'karton' && strtolower($d->deliveryDetail->units->name) != 'box'){
                                if ($productLargest[$d->product->code] >= 1) {
                                    $assembly = 'Assembly '. number_format($productLargest[$d->product->code], 0).' Karton/Box';
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $p++ }}</td>

                            <td>{{ $d->product->code ?? '-' }}</td>
                            <td>{{ $d->product->name ?? '-' }}</td>

                            <td class="text-right">{{ number_format($d->qty_do, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($d->qty_packed, 2, ',', '.') }}</td>

                            <td>{{ $d->deliveryDetail->units->name ?? '-' }}</td>
                            <td>{{ $d->remark ?? '-' }} {{ $assembly }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach --}}

            {{-- @foreach ($packingListDetail as $d)
                    <tr>
                        <td>{{ $p++ }}</td>
                        <td>{{ $d->do_number }}</td>

                        <td>{{ $d->product->code ?? '-' }}</td>
                        <td>{{ $d->product->name ?? '-' }}</td>

                        <td class="text-right">{{ number_format($d->qty_do, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($d->qty_packed, 2, ',', '.') }}</td>

                        <td>{{ $d->deliveryDetail->units->name ?? '-' }}</td>
                        <td>{{ $d->remark ?? '-' }}</td>
                    </tr>
            @endforeach --}}
        </tbody>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $size = 9;
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";

            $y = $pdf->get_height() - 40;

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
