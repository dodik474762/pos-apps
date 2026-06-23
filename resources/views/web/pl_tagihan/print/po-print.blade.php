<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>PL Tagihan - {{ $salesman->nik }}</title>
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
        .table-detail {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table-detail th,
        .table-detail td {
            border: 1px solid #000;

            font-size: 8px;
            padding: 2px 3px;

            white-space: normal;
            word-break: break-word;
            overflow-wrap: break-word;

            vertical-align: top;
        }

        .table-detail th {
            background: #f8f8f8;
            text-align: center;
        }

        .table-detail td.text-right {
            text-align: right;
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
                <h4 style="margin:0; padding:0;">PACKING LIST TAGIHAN</h4>
                <br>
                {{-- QR Code (otomatis di-generate) --}}
                <div style="margin-top:5px;">
                    {{-- <img src="data:image/png;base64,{{ $qr }}" alt="" width="70" height="70"> --}}
                </div>
            </td>
        </tr>
    </table>

    <br>

    {{-- INFORMASI PO --}}
    <table class="no-border" style="width:100%;">
        <tr>
            <td><strong>NIK Salesman:</strong> {{ $salesman_name }}</td>
            <td style="padding-left:40px;"><strong>Jabatan:</strong> SALESMAN</td>
        </tr>
        <tr>
            <td><strong>Nama Salesman:</strong> {{ $salesman_name ?? '-' }}</td>
        </tr>
    </table>

    <h4>Detail Tagihan</h4>
    <table class="table-detail">
        <thead>
            <tr>
                <th style="width:5%">No</th>

                <th style="width:7%">Tanggal<br>Rute</th>

                <th style="width:10%">Invoice</th>

                <th style="width:8%">Tgl<br>Invoice</th>
                <th style="width:8%">Jatuh<br>Tempo</th>

                <th style="width:8%">DO</th>

                <th style="width:10%">Customer</th>

                <th style="width:7%">Warehouse</th>

                <th style="width:6%">Status</th>

                <th style="width:6%">Nilai Faktur</th>
                <th style="width:9%">Outstanding</th>

                <th style="width:4%">TOP</th>

                <th style="width:6%">Tunai</th>

                <th style="width:8%">Transfer</th>

                <th style="width:7%">Remark</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $outstandin = 0;
            @endphp
            @foreach ($invoices as $item)
                @php
                    $out = $item->total_amount - $item->amount_paid;
                    $do_number = $item->do_number == '' ? $item->dohs_number : $item->do_number;
                    $do_date = $item->do_date == '' ? $item->dohs_date : $item->do_date;
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $tanggal_rute }}</td>
                    <td>{{ $item->invoice_number }}</td>
                    <td>{{ $item->invoice_date }}</td>
                    <td>{{ $item->due_date }}</td>
                    <td>{{ $do_number }}</td>
                    <td>{{ $item->customer_code }} - {{ $item->nama_customer }}</td>
                    <td>{{ $item->warehouse_name }}</td>
                    <td>{{ $item->status }}</td>
                    <td class="text-right">{{ number_format($item->total_amount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($out, 0, ',', '.') }}</td>
                    <td>{{ $item->top_name }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @php
                    $outstandin += $out;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="13" class="text-right"><strong>Sub Total</strong></td>
                <td class="text-right" colspan="2"><strong>{{ number_format($outstandin, 0, ',', '.') }}</strong>
                </td>
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
            <td class="text-center">
                <br><br><br>
                <strong>Ditagihkan Oleh</strong>
                <br><br><br>
                (__________________)
            </td>
        </tr>
    </table>
</body>

</html>
