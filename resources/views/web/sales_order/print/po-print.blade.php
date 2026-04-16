<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Order - {{ $data->code }}</title>
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
                <h4 style="margin:0; padding:0;">SALES ORDER</h4>
                <small>No: {{ $data->so_number }}</small>
                <br>
                {{-- QR Code (otomatis di-generate) --}}
                <div style="margin-top:5px;">
                    {{-- <img src="{{ $qr }}" alt="" width="70" height="70"> --}}
                </div>
            </td>
        </tr>
    </table>

    <br>

    {{-- INFORMASI PO --}}
    <table class="no-border" style="width:100%;">
        <tr>
            <td><strong>Kode SO:</strong> {{ $data->so_number }}</td>
            <td style="padding-left:40px;"><strong>Tanggal SO:</strong> {{ date('d/m/Y', strtotime($data->so_date)) }}</td>
        </tr>
        <tr>
            <td><strong>Pelanggan:</strong> {{ $data->customers->nama_customer ?? '-' }}</td>
            <td style="padding-left:40px;"><strong>Keterangan:</strong> {{ $data->remarks ?? '-' }}</td>
            <td style="padding-left:40px;"><strong>Channel Outlet:</strong> {{ $data->customers->channel_outlet ?? '-' }}</td>
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
            <th>Harga (Excl. PPN)</th>
            <th>Disc (%)</th>
            <th>Disc (Rp)</th>
            <th>Keterangan</th>
            <th>PPN</th>
            <th>Subtotal (Incl. PPN)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data->items as $i => $item)
            @php
                $taxRate = $ppn_val ?? 11;
                $subtotalBeforeTax = $item->subtotal; // sudah include PPN
                $dpp = $subtotalBeforeTax / (1 + $taxRate / 100); // harga exclude PPN
                $ppn = $subtotalBeforeTax - $dpp;
                $hargaExcl = $item->unit_price / (1 + $taxRate / 100);

                // Cari promo item untuk produk ini
                $promoItems = $promo_item->where('sales_order_detail_id', $item->id);
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    {{ $item->products->name ?? '-' }}
                    @if($promoItems->count() > 0)
                        <br>
                        @foreach($promoItems as $pi)
                            <small style="color: #c00; font-style: italic;">
                                🎁 {{ $pi->promo_name }}
                                @if($pi->discount_percent > 0)
                                    ({{ $pi->discount_percent }}%)
                                @endif
                                @if($pi->discount_amount > 0)
                                    - Rp {{ number_format($pi->discount_amount, 0, ',', '.') }}
                                @endif
                            </small><br>
                        @endforeach
                    @endif
                </td>
                <td>{{ $item->units->name ?? '-' }}</td>
                <td class="text-center">{{ $item->qty }}</td>
                <td class="text-right">{{ number_format($hargaExcl, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->discount_percent }}</td>
                <td class="text-right">{{ number_format($item->discount_amount, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->free_for == '' ? '' : 'FREE GOOD' }}</td>
                <td class="text-right">{{ number_format($ppn, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="9" class="text-right"><strong>Total</strong></td>
            <td class="text-right"><strong>{{ number_format($data->total_amount, 0, ',', '.') }}</strong></td>
        </tr>
        @foreach($promo as $v)
            <tr>
                <td colspan="9" class="text-right"><strong>{{ $v->promo_name }}</strong></td>
                <td class="text-right" style="color: #c00;">
                    <strong>- {{ number_format($v->total_potongan, 0, ',', '.') }}</strong>
                </td>
            </tr>
        @endforeach
        <tr>
            @php
                $subtotalAfterPromo = $data->total_amount - $data->discount_amount;
                $taxAmount = $subtotalAfterPromo - ($subtotalAfterPromo / (1 + ($data->tax_base / 100)));
            @endphp
            <td colspan="9" class="text-right"><strong>PPN {{ $ppn_val }}%</strong></td>
            <td class="text-right"><strong>{{ number_format($taxAmount, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td colspan="9" class="text-right"><strong>Grand Total</strong></td>
            <td class="text-right"><strong>{{ number_format($subtotalAfterPromo, 0, ',', '.') }}</strong></td>
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
