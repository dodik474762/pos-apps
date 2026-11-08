<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Terima Uang - {{ $salesman->nik }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20px 18px;
        }

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

        /* Tabel detail: fixed layout supaya pas lebar kertas portrait */
        .table-detail {
            table-layout: fixed;
            width: 100%;
        }

        .table-detail th,
        .table-detail td {
            font-size: 7.5px;
            padding: 2px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            vertical-align: middle;
        }

        .table-detail th {
            background: #f8f8f8;
            text-align: center;
        }

        /* Kolom tanggal & nomor: tidak boleh pecah baris */
        .nowrap {
            white-space: nowrap;
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
            <td style="text-align:right; width: 150px;">
                <h4 style="margin:0; padding:0;">PENERIMAAN UANG TAGIHAN</h4>
            </td>
        </tr>
    </table>

    {{-- INFORMASI SALESMAN --}}
    <table class="no-border" style="width:100%;margin-top:-5px;line-height: 0.5;">
        <tr>
            <td><strong>NIK Salesman:</strong> {{ $salesman->nik }}</td>
            <td style="padding-left:40px;"><strong>Jabatan:</strong> {{ $salesman->jabatan }}</td>
        </tr>
        <tr>
            <td><strong>Nama Salesman:</strong> {{ $salesman->nama_lengkap ?? '-' }}</td>
        </tr>
    </table>

    <h4 style="line-height: 0.5;">Detail Penerimaan Uang</h4>
    <table class="table-detail">
        <thead>
            <tr>
                <th style="width:3%;">No</th>
                <th style="width:7%;">Tanggal Rute</th>
                <th style="width:9%;">Invoice Number</th>
                <th style="width:7%;">Tanggal Invoice</th>
                <th style="width:9%;">DO Number</th>
                <th style="width:7%;">Tanggal DO</th>
                <th style="width:15%;">Customer</th>
                <th style="width:8%;">Warehouse</th>
                <th style="width:7%;">Tanggal Jatuh Tempo</th>
                <th style="width:6%;">Status</th>
                <th style="width:7%;">Outstanding</th>
                <th style="width:7%;">Tanggal Terima</th>
                <th style="width:7%;">Tipe Bayar</th>
                <th style="width:8%;">Nilai Terima</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $total_terima = 0;
            @endphp
            @foreach ($invoices as $item)
                @php
                    $item = (object) $item;
                    $out = $item->total_amount;
                    $do_number = $item->do_number == '' ? $item->dohs_number : $item->do_number;
                    $do_date = $item->do_date == '' ? $item->dohs_date : $item->do_date;
                    $total_bayar = $item->total_terbayar_rph == '' ? $item->total_terbayar : $item->total_terbayar_rph;
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="nowrap">{{ $tanggal_rute }}</td>
                    <td class="nowrap">{{ $item->invoice_number }}</td>
                    <td class="nowrap">{{ $item->invoice_date }}</td>
                    <td class="nowrap">{{ $do_number }}</td>
                    <td class="nowrap">{{ $do_date }}</td>
                    <td>{{ $item->customer_code }} - {{ $item->nama_customer }}</td>
                    <td>{{ $item->warehouse_name }}</td>
                    <td class="nowrap">{{ $item->due_date }}</td>
                    <td>{{ $item->status }}</td>
                    <td class="text-right nowrap">{{ number_format($out, 0, ',', '.') }}</td>
                    <td class="nowrap">{{ $item->receive_date ?? '-' }}</td>
                    <td class="nowrap">{{ $item->payment_type ?? '-' }}</td>
                    <td class="text-right nowrap">{{ number_format($total_bayar, 0, ',', '.') }}</td>
                </tr>
                @php
                    $total_terima += $total_bayar;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="13" class="text-right"><strong>Total Terima</strong></td>
                <td class="text-right nowrap"><strong>{{ number_format($total_terima, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <br><br>
    <table class="no-border" style="width:100%; page-break-inside: avoid;">
        <tr>
            <td class="text-center">
                <br><br><br>
                <strong>Disetorkan Oleh</strong>
                <br><br><br>
                (__________________)
                <br>
                &nbsp;
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Diterima Oleh</strong>
                <br><br><br>
                (__________________)
                <br>
                Kasir
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Direview Oleh</strong>
                <br><br><br>
                (__________________)
                <br>
                Admin AR
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Diketahui Oleh</strong>
                <br><br><br>
                (__________________)
                <br>
                BOD
            </td>
        </tr>
    </table>
</body>

</html>
