<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $data->code }}</title>
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
                <h4 style="margin:0; padding:0;">PURCHASE ORDER</h4>
                <small>No: {{ $data->code }}</small>
                <br>
                {{-- QR Code (otomatis di-generate) --}}
                <div style="margin-top:5px;">
                    {{-- {!! base64_encode(QrCode::format('png')->size(70)->generate($data->code)) !!} --}}
                </div>
            </td>
        </tr>
    </table>

    <br>

    {{-- INFORMASI PO --}}
    <table class="no-border" style="width:100%;">
        <tr>
            <td><strong>Kode PO:</strong> {{ $data->code }}</td>
            <td style="padding-left:40px;"><strong>Tanggal PO:</strong> {{ date('d/m/Y', strtotime($data->po_date)) }}</td>
        </tr>
        <tr>
            <td><strong>Vendor:</strong> {{ $data->vendors->nama_vendor ?? '-' }}</td>
            <td style="padding-left:40px;"><strong>Gudang:</strong> {{ $data->warehouses->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Estimasi Diterima:</strong> {{ date('d/m/Y', strtotime($data->est_received_date)) }}</td>
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
                <th>Qty</th>
                <th>Harga Beli</th>
                <th>Disc (%)</th>
                <th>Disc (Rp)</th>
                <th>Pajak</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->products->name ?? '-' }}</td>
                    <td>{{ $item->units->name ?? '-' }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right">{{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->diskon_persen }}</td>
                    <td class="text-right">{{ number_format($item->diskon_nominal, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $item->tax_rate }}%/{{ number_format($item->tax_amount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" class="text-right"><strong>Total</strong></td>
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
