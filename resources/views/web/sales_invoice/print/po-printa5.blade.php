<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Invoice - {{ $data->code }}</title>

    <style>
        @page {
            size: A5 landscape; /* 📌 Ukuran setengah A4 */
            margin: 5mm;        /* 📌 Margin kecil khas dot matrix */
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        .no-border td {
            border: none;
            padding: 2px;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .header-table td {
            border: none;
            vertical-align: middle;
            padding: 2px;
        }

        .logo {
            width: 60px; /* diperkecil agar muat A5 */
        }

        .table-detail th,
        .table-detail td {
            font-size: 10px;
            padding: 3px;
        }
    </style>
</head>

<body>
    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td style="width:70px;">
                <img src="{{ public_path('assets/images/logo-main-app.png') }}" class="logo">
            </td>
            <td>
                <strong>{{ $company->nama_company }}</strong><br>
                <small>{!! $company->alamat !!}</small>
            </td>
            <td style="text-align:right;">
                <strong>FAKTUR PENJUALAN</strong><br>
                <small>No: {{ $data->invoice_number }}</small>
                <br>
                {{-- <img src="data:image/png;base64,{{ $qr }}" width="55" height="55"> --}}
            </td>
        </tr>
    </table>

    {{-- INFO PO --}}
    <table class="no-border">
        <tr>
            <td><strong>Kode Pesanan:</strong> {{ isset($data->do->so->so_number) ? $data->do->so->so_number : $data->so->so_number }}</td>
            <td style="padding-left:20px;"><strong>Tanggal Pesanan:</strong> {{ isset($data->do->so->so_date) ? date('d/m/Y', strtotime($data->do->so->so_date)) : date('d/m/Y', strtotime($data->so->so_date)) }}</td>
        </tr>
        <tr>
            <td><strong>Pelanggan:</strong> {{ $data->customers->nama_customer ?? '-' }}</td>
            <td style="padding-left:20px;"><strong>Gudang:</strong> {{ $data->warehouses->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Syarat Pembarayan:</strong> {{ $data->customers->top->code ?? '-' }}</td>
            <td style="padding-left:20px;"><strong>Penjual:</strong> {{ $salesman_name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Nomor Faktur:</strong> {{ $data->invoice_number }}</td>
            <td style="padding-left:20px;"><strong>No. Kiriman:</strong> {{ isset($data->do->do_number) ? $data->do->do_number : '-' }}</td>
        </tr>
    </table>

    <h4 style="margin-top:6px;">Detail Barang</h4>

    <table class="table-detail">
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Satuan</th>
                <th>Qty</th>
                <th>Diskon</th>
                <th>Note</th>
                <th>Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->products->name ?? '-' }}</td>
                    <td>{{ $item->so_detail->units->name ?? '-' }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-center">{{ $item->discount }}</td>
                    <td class="text-center">{{ $item->so_detail->free_for == '' ? '' : 'FREE GOOD' }}</td>
                    <td class="text-center">{{ $item->subtotal }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><strong>Sub Total</strong></td>
                <td class="text-right"><strong>{{ number_format($data->subtotal, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right"><strong>PPN {{ $data->tax_base }} %</strong></td>
                <td class="text-right"><strong>{{ number_format($data->tax_amount, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right"><strong>Grand Total</strong></td>
                <td class="text-right"><strong>{{ number_format($data->total_amount, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <table class="no-border">
        <tr>
            <td>Note: Barang telah diterima dengan cukup dan baik, pembayaran transfer hanya diakui melalui rekening :<br/>{{ $company->akun_bank }} - {{ $company->akun_bank_number }} <br/>{{ $company->akun_bank_name }}</td>
        </tr>
    </table>

    <table class="no-border">
        <tr>
            <td class="text-center">
                <strong>Disetujui Oleh</strong><br><br><br>
                (__________________)
            </td>
            <td class="text-center">
                <strong>Dibuat Oleh</strong><br><br><br>
                (__________________)
            </td>
        </tr>
    </table>

</body>
</html>
