<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Terima Uang - {{ $salesman->nik }}</title>
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

        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
        }

        .logo {
            width: 90px;
        }

        /* 🔹 Ukuran font kecil untuk tabel detail barang */
        .table-detail th,
        .table-detail td {
            font-size: 10px;
            padding: 3px;
        }

        .table-detail th {
            background: #f8f8f8;
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
                <h4 style="margin:0; padding:0;">PENERIMAAN UANG TAGIHAN</h4>
                <br>
                {{-- QR Code (otomatis di-generate) --}}
                <div style="margin-top:5px;">
                    {{-- <img src="data:image/png;base64,{{ $qr }}" alt="" width="70" height="70"> --}}
                </div>
            </td>
        </tr>
    </table>

    {{-- INFORMASI PO --}}
    <table class="no-border" style="width:100%;margin-top:-5px;">
        <tr>
            <td><strong>NIK Salesman:</strong> {{ $salesman->nik }}</td>
            <td style="padding-left:40px;"><strong>Jabatan:</strong> {{ $salesman->jabatan }}</td>
        </tr>
        <tr>
            <td><strong>Nama Salesman:</strong> {{ $salesman->nama_lengkap ?? '-' }}</td>
        </tr>
    </table>

    <h4>Detail Penerimaan Uang</h4>
    <table class="table-detail">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Rute</th>
                <th>Invoice Number</th>
                <th>Tanggal Invoice</th>
                <th>DO Number</th>
                <th>Tanggal DO</th>
                <th>Customer</th>
                <th>Warehouse</th>
                <th>Tanggal Jatuh Tempo</th>
                <th>Status</th>
                <th>Outstanding</th>
                <th>Nilai Terima</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $total_terima = 0;
            @endphp
            @foreach ($invoices as $item)
                @php
                    //$out = $item->total_amount - $item->amount_paid;
                    $out = $item->total_amount;
                    $do_number = $item->do_number == '' ? $item->dohs_number : $item->do_number;
                    $do_date = $item->do_date == '' ? $item->dohs_date : $item->do_date;
                    $total_bayar = $item->total_terbayar_rph == '' ? $item->total_terbayar : $item->total_terbayar_rph;
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $tanggal_rute }}</td>
                    <td>{{ $item->invoice_number }}</td>
                    <td>{{ $item->invoice_date }}</td>
                    <td>{{ $do_date }}</td>
                    <td>{{ $do_number }}</td>
                    <td>{{ $item->customer_code }} - {{ $item->nama_customer }}</td>
                    <td>{{ $item->warehouse_name }}</td>
                    <td>{{ $item->due_date }}</td>
                    <td>{{ $item->status }}</td>
                    <td class="text-right">{{ number_format($out, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($total_bayar, 0, ',', '.') }}</td>
                </tr>
                @php
                    $total_terima += $total_bayar;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="11" class="text-right"><strong>Total Terima</strong></td>
                <td class="text-right"><strong>{{ number_format($total_terima, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
    <table class="no-border" style="width:100%;">
        <tr>
            <td></td>
        </tr>
    </table>

    <br><br>
    <table class="no-border" style="width:100%;">
        <tr>
            <td class="text-center">
                <br><br><br>
                <strong>Disetorkan Oleh</strong>
                <br><br><br>
                (__________________)
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Diterima Oleh</strong>
                <br><br><br>
                (__________________)
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Direview Oleh</strong>
                <br><br><br>
                (__________________)
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Diketahui Oleh</strong>
                <br><br><br>
                (__________________)
            </td>
        </tr>
    </table>
</body>

</html>
