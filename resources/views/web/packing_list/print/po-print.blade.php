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
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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

        /* TABLE DETAIL */
        .table-detail th,
        .table-detail td {
            font-size: 10px;
            padding: 5px;
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
                <h4 style="margin:0; padding:0;">PACKING LIST</h4>
                <small>No: {{ $data->packing_list_no }}</small>
            </td>
        </tr>
    </table>

    <br>

    {{-- INFORMASI --}}
    <table class="no-border" style="width:100%;">
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
    <h4>Detail Delivery Order</h4>

    <table class="table-detail">
        <thead>
            <tr>
                <th>No</th>
                <th>No. DO</th>
                <th>Tanggal DO</th>
                <th>Kode Customer</th>
                <th>Nama Customer</th>
            </tr>
        </thead>

        <tbody>
            @php $no = 1; @endphp
            @foreach ($details as $item)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->do_number }}</td>
                    <td>{{ $item->do_date }}</td>
                    <td>{{ $item->customer_code }}</td>
                    <td>{{ $item->nama_customer }}</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Total DO</strong></td>
                <td class="text-right">
                    <strong>{{ number_format(count($details), 0, ',', '.') }} Customer</strong>
                </td>
            </tr>
        </tfoot>
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
    @if($totalProduk > 10)
        <div class="page-break"></div>
    @endif


    {{-- ==================== DAFTAR PRODUK ==================== --}}
    <h4>Daftar Produk</h4>

    <table class="table-detail">
        <thead>
            <tr>
                <th>No</th>
                <th>No. DO</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Qty DO</th>
                <th>Qty Pack</th>
                <th>Remark</th>
            </tr>
        </thead>

        <tbody>
            @php $p = 1; @endphp

            @foreach ($details as $d)
                @if (!empty($d->detail))
                    @foreach ($d->detail as $prod)
                        <tr>
                            <td>{{ $p++ }}</td>
                            <td>{{ $d->do_number }}</td>

                            <td>{{ $prod->product->code ?? '-' }}</td>
                            <td>{{ $prod->product->name ?? '-' }}</td>

                            <td class="text-right">{{ number_format($prod->qty_do, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($prod->qty_packed, 2, ',', '.') }}</td>

                            <td>{{ $prod->remark ?? '-' }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>


    <br><br>

    {{-- SIGN --}}
    <table class="no-border" style="width:100%;">
        <tr>
            <td class="text-center">
                <br><br><br>
                <strong>Disetujui Oleh</strong>
                <br><br><br>
                (__________________)
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Dibuat Oleh</strong>
                <br><br><br>
                (__________________)
            </td>
        </tr>
    </table>

</body>

</html>
