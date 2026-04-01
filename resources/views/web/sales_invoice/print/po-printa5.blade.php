<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Invoice - {{ $data->code }}</title>

    <style>
        @page {
            /* size: A5 landscape;  */
            /* 📌 Ukuran setengah A4 */
            /* margin: 5mm;  */
                   /* 📌 Margin kecil khas dot matrix */
            /* margin: 8mm 10mm; */

            size: 215.9mm auto;  /* 8.5 inch lebar, tinggi otomatis */
            margin: 5mm 11mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
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
            padding: 2px 3px; /* kurangi padding */
        }
    </style>
</head>

<body>
    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td style="width:60px;">
                <img src="{{ public_path('assets/images/logo-main-app.png') }}" class="logo">
            </td>
            <td>
                <strong>{{ $company->nama_company }}</strong><br>
                <small>{!! $company->alamat !!}</small>
            </td>
             {{-- ✅ lebar cukup, nowrap supaya tidak wrap --}}
            <td style="width:100px; text-align:right; white-space:nowrap;">
                <strong style="font-size:10px;">FAKTUR PENJUALAN</strong><br>
                <small>No: {{ $data->invoice_number }}</small>
            </td>
        </tr>
    </table>

   {{-- INFO PO --}}
    <table class="no-border" style="font-size:10px;">
        <tr>
            <td style="font-size:10px !important;"><strong>Kode Pesanan:</strong> {{ isset($data->do->so->so_number) ? $data->do->so->so_number : $data->so->so_number }}</td>
            <td style="padding-left:20px;font-size:10px !important;"><strong>Tanggal Pesanan:</strong> {{ isset($data->do->so->so_date) ? date('d/m/Y', strtotime($data->do->so->so_date)) : date('d/m/Y', strtotime($data->so->so_date)) }}</td>
        </tr>
        <tr>
            <td style="font-size:10px !important;"><strong>Pelanggan:</strong> {{ $data->customers->nama_customer ?? '-' }}</td>
            <td style="padding-left:20px;font-size:10px !important;"><strong>Nomor Faktur:</strong> {{ $data->invoice_number }}</td>
        </tr>
        <tr>
            <td style="font-size:10px !important;"><strong>Syarat Pembayaran:</strong> {{ $data->customers->top->remarks ?? '-' }}</td>            
            <td style="padding-left:20px;font-size:10px !important;"><strong>Tanggal Faktur:</strong> {{ date('d/m/Y', strtotime($data->invoice_date)) }}</td>
        </tr>
        <tr>            
            <td style="font-size:10px !important;"><strong>Gudang:</strong> {{ $data->warehouses->name ?? '-' }}</td>            
            <td style="padding-left:20px;font-size:10px !important;"><strong>Tanggal Jatuh Tempo:</strong> {{ date('d/m/Y', strtotime($data->due_date)) }}</td>
        </tr>
        <tr>
            <td style="font-size:10px !important;"><strong>Penjual:</strong> {{ $salesman_name ?? '-' }}</td>        
            <td style="padding-left:20px;font-size:10px !important;"><strong>No. Kiriman:</strong> {{ isset($data->do->do_number) ? $data->do->do_number : '-' }}</td>                
        </tr>
    </table>

    <h4 style="margin-top:6px;font-size:10px !important;">Detail Barang</h4>

  <table class="table-detail" style="width:100%; table-layout:fixed;">
    <thead>
        <tr>
             <th class="text-center" style="width:3%;">No</th>
            <th class="text-center" style="width:32%;">Produk</th>
            <th class="text-center" style="width:10%;">Satuan</th>
            <th class="text-center" style="width:7%;">Qty</th>
            <th class="text-center" style="width:16%;">Harga</th>
            <th class="text-center" style="width:11%;">Diskon</th>
            <th class="text-center" style="width:6%;">Note</th>
            <th class="text-center" style="width:15%;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data->items as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td style="overflow:hidden; white-space:nowrap;">{{ $item->products->name ?? '-' }}</td>
                <td class="text-center">{{ $item->so_detail->units->name ?? '-' }}</td>
                <td class="text-center">{{ $item->qty }}</td>
                <td class="text-right">{{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->discount, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->so_detail->free_for == '' ? '' : 'FREE' }}</td>
                <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" class="text-right"><strong>Sub Total</strong></td>
            <td class="text-right"><strong>{{ number_format($data->subtotal, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td colspan="7" class="text-right"><strong>Discount {{ $data->so->discount_percent == '0' ? '' : $data->so->discount_percent.' %' }}</strong></td>
            <td class="text-right"><strong>{{ number_format($data->so->discount_amount, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td colspan="7" class="text-right"><strong>PPN {{ $data->tax_base }} %</strong></td>
            <td class="text-right"><strong>0</strong></td>
        </tr>
        <tr>
            <td colspan="7" class="text-right"><strong>Grand Total</strong></td>
            <td class="text-right"><strong>{{ number_format($data->total_amount, 0, ',', '.') }}</strong></td>
        </tr>
    </tfoot>
</table>

    <table class="no-border">
        <tr>
            <td style="font-size:10px !important;">
                Note: Barang telah diterima dengan cukup dan baik, pembayaran transfer hanya diakui melalui rekening :<br/>
                {{ $company->akun_bank }} - {{ $company->akun_bank_number }}<br/>
                {{ $company->akun_bank_name }}
            </td>
        </tr>
    </table>

    <table class="no-border">
        <tr>
            <td class="text-center">
                <strong>Diterima Oleh</strong><br><br><br>
                (__________________)
            </td>
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
