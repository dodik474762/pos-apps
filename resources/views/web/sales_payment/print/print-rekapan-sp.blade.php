<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rekapan Sales Payment - {{ $data['date'] }}</title>
    <style>
        @page {
            margin: 5mm 4mm;
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
                <h4 style="margin:0; padding:0;">REKAPAN PEMBAYARAN PELANGGAN</h4>
                <small>Tanggal: {{ $data['date'] }}</small>
                <br>
                {{-- QR Code (otomatis di-generate) --}}
                <div style="margin-top:5px;">
                    {{-- <img src="data:image/png;base64,{{ $qr }}" alt="" width="70" height="70"> --}}
                </div>
            </td>
        </tr>
    </table>
    <h4 style="margin-top:-16px; margin-bottom:5px;">Detail {{ $user_group == 5 ? $salesmans->nik : '' }} </h4>
    <table class="table-detail">
        <thead>
            <tr>
                <th>No</th>
                <th>Sales</th>
                <th>Invoice</th>
                <th>Pelanggan</th>
                <th>Kec.</th>
                <th>TOP</th>
                <th>Bayar</th>
                <th>Tgl Inv</th>
                <th>Jth Tempo</th>
                <th>Status</th>
                <th>Total</th>
                <th>Sisa</th>
                <th>Dibayar</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
                $no = 1;
            @endphp
            @if (!empty($data['data_payment']))
                @foreach ($data['data_payment'] as $item)
                    @php
                        $item = (object) $item;
                        $sales_return = !empty($data['data_return'])
                            ? collect($data['data_return'])->where('invoice_id', $item->invoice_id)->first()
                            : [];
                    @endphp
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $item->salesman_name }}</td>
                        <td>{{ $item->invoice_number }}</td>
                        <td>{{ $item->customer_code }} - {{ $item->nama_customer }}</td>
                        <td>{{ $item->kecamatan_name ?? '-' }}</td>
                        <td>{{ $item->top_name }}</td>
                        <td>{{ $item->payment_method }}</td>
                        <td>{{ $item->invoice_date }}</td>
                        <td>{{ $item->due_date }}</td>
                        <td>{{ $item->status }}</td>
                        <td>{{ number_format($item->total_amount, 0, ',', '.') }}</td>
                        <td>{{ number_format($item->total_amount - $item->amount_paid, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->amount_paid, 0, ',', '.') }}</td>
                    </tr>
                    @if (!empty($sales_return))
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $item->salesman_name }}</td>
                            <td>{{ $sales_return->return_number }}</td>
                            <td>{{ $item->customer_code }} - {{ $item->nama_customer }}</td>
                            <td>{{ $item->kecamatan_name ?? '-' }}</td>
                            <td>{{ $item->top_name }}</td>
                            <td>RETURN</td>
                            <td>{{ $item->invoice_date }}</td>
                            <td>{{ $item->due_date }}</td>
                            <td>{{ $item->status }}</td>
                            <td>{{ $sales_return->refund_amount }}</td>
                            <td>0</td>
                            <td class="text-right">0</td>
                        </tr>
                    @endif
                    @php
                        if (!empty($sales_return)) {
                            $total += $item->amount_paid - $sales_return->refund_amount;
                        } else {
                            $total += $item->amount_paid;
                        }
                    @endphp
                @endforeach
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="12" class="text-right"><strong>Sub Total</strong></td>
                <td class="text-right"><strong>{{ number_format($total, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <br><br>
    <table class="no-border" style="width:100%;">
        <tr>
            <td class="text-center">
                <br><br><br>
                <strong>Dibuat Oleh</strong>
                <br><br><br>
                (__________________)
                <br>
                &nbsp;
            </td>
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
                Admin AR
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Disetujui Oleh</strong>
                <br><br><br>
                (__________________)
                <br>
                BOD
            </td>
        </tr>
    </table>
</body>

</html>
