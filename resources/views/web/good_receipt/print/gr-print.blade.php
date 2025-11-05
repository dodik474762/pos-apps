<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Good Receipt - {{ $data->code }}</title>
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
                <h4 style="margin:0; padding:0;">GOODs RECEIPT</h4>
                <small>No: {{ $data->gr_number }}</small>
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
            <td><strong>Kode GR:</strong> {{ $data->gr_number }}</td>
            <td style="padding-left:40px;"><strong>Tanggal Terima:</strong> {{ date('d/m/Y', strtotime($data->received_date)) }}</td>
        </tr>
        <tr>
            <td><strong>Vendor:</strong> {{ $data->po->vendors->nama_vendor ?? '-' }}</td>
            <td style="padding-left:40px;"><strong>Gudang:</strong> {{ $data->po->warehouses->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Estimasi Diterima:</strong> {{ date('d/m/Y', strtotime($data->received_date)) }}</td>
            <td style="padding-left:40px;"><strong>Keterangan:</strong> {{ $data->remarks ?? '-' }}</td>
        </tr>
    </table>

    <h4>Detail Barang</h4>
    <table class="table-detail">
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Satuan</th>
                <th style="text-align: center;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->products->name ?? '-' }}</td>
                    <td>{{ $item->units->name ?? '-' }}</td>
                    <td class="text-center">{{ $item->qty_received }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br><br>
    <table class="no-border" style="width:100%;">
        <tr>
            <td class="text-center">
                <br><br><br>
                <strong>Diterima Oleh</strong>
                <br><br><br>
                (__________________)
            </td>
        </tr>
    </table>
</body>
</html>
