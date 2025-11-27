<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Payment - {{ $data->code }}</title>
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
            padding: 5px;
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
                <h4 style="margin:0; padding:0;">PEMBAYARAN PELANGGAN</h4>
                <small>No: {{ $data->payment_code }}</small>
                <br>
                {{-- QR Code (otomatis di-generate) --}}
                <div style="margin-top:5px;">
                    <img src="data:image/png;base64,{{ $qr }}" alt="" width="70" height="70">
                </div>
            </td>
        </tr>
    </table>

    <br>

    {{-- INFORMASI PO --}}
    <table class="no-border" style="width:100%;">
        <tr>
            <td><strong>Kode Pelanggan:</strong> {{ $data->customers->code }}</td>
            <td style="padding-left:40px;"><strong>Tanggal Pembayaran:</strong> {{ date('d/m/Y', strtotime($data->payment_date)) }}</td>
        </tr>
        <tr>
            <td><strong>Pelanggan:</strong> {{ $data->customers->nama_customer ?? '-' }}</td>
            <td style="padding-left:40px;"><strong>Alamat:</strong> {{ $data->customers->address ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Nomor Pembayaran:</strong> {{ $data->payment_code }}</td>
            <td style="padding-left:40px;"><strong>Keterangan:</strong> {{ $data->remarks }}</td>
        </tr>
    </table>

    <h4>Detail</h4>
    <table class="table-detail">
        <thead>
            <tr>
                <th>No</th>
                <th>No. Invoice</th>
                <th>Tanggal Invoice</th>
                <th>Jumlah Belum Dibayar</th>
                <th>Jumlah Dibayar</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
            @endphp
            @foreach ($data->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->invoice->invoice_number }}</td>
                    <td>{{ $item->invoice->invoice_date }}</td>
                    <td>{{ $item->outstanding_amount }}</td>
                    <td class="text-right">{{ $item->allocated_amount }}</td>
                </tr>
                @php
                    $total += $item->allocated_amount ?? 0;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Sub Total</strong></td>
                <td class="text-right"><strong>{{ number_format($total, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <br><br>
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
