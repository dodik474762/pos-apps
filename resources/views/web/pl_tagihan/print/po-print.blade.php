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
            /* margin: 0 0 0 28px; */
            margin: 0;
            padding: 0;
            margin-left: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
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

            font-size: 10px;
            padding: 1px 2px;

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
    <table class="header-table" style="width:100%;margin-top: -30px;">
        <tr>
            <td style="width: 90px;">
                <img src="{{ public_path('assets/images/logo-main-app.png') }}" class="logo">
            </td>
            <td style="text-align: left; vertical-align: top; padding: 0;">
                <h3 style="margin: 0; padding: 0;">{{ $company->nama_company }}</h3>
                <small style="display: block; margin-top: -8px;">{!! $company->alamat !!}</small>
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

    {{-- INFORMASI PO --}}
    <table class="no-border" style="width:100%;line-height: 0.5;">
        <tr>
            <td><strong>NIK Salesman:</strong> {{ $salesman_name }}</td>
            <td style="padding-left:40px;"><strong>Jabatan:</strong> SALESMAN</td>
        </tr>
        <tr>
            <td><strong>Nama Salesman:</strong> {{ $salesman_name ?? '-' }}</td>
            <td style="padding-left:40px;"><strong>Tanggal Rute:</strong> {{ $tanggal_rute }}</td>
        </tr>
        <tr>
            <td><strong>Warehouse:</strong> {{ empty($invoices) ? '' : $invoices[0]->warehouse_name }}</td>
        </tr>
    </table>

    <h4>Detail Tagihan</h4>
    <table class="table-detail">
        <thead>
            <tr>
                <td style="width:5%;font-size: 14px;font-weight: normal;">No</td>

                <td style="width:8%;font-size: 14px;font-weight: normal;">Invoice</td>

                <td style="width:8%;font-size: 14px;font-weight: normal;">Tgl<br>Invoice</td>
                <td style="width:8%;font-size: 14px;font-weight: normal;">Jatuh<br>Tempo</td>

                <td style="width:8%;font-size: 14px;font-weight: normal;">DO</td>

                <td style="width:14%;font-size: 14px;font-weight: normal;">Customer</td>

                <td style="width:6%;font-size: 14px;font-weight: normal;">Status</td>

                <td style="width:4%;font-size: 14px;font-weight: normal;">TOP</td>

                <td style="width:10%;font-size: 14px;font-weight: normal;">Nilai Faktur</td>
                <td style="width:10%;font-size: 14px;font-weight: normal;">Outstanding</td>

                <td style="width:8%;font-size: 14px;font-weight: normal;">Tunai</td>

                <td style="width:8%;font-size: 14px;font-weight: normal;">Transfer</td>

                <td style="width:7%;font-size: 14px;font-weight: normal;">Remark</td>

                <td style="width:5%;font-size: 14px;font-weight: normal;">Retur</td>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $outstandin = 0;
                $nilaiFaktur = 0;
            @endphp
            @foreach ($invoices as $item)
                @php
                    $out = $item->total_amount - $item->amount_paid;
                    $do_number = $item->do_number == '' ? $item->dohs_number : $item->do_number;
                    $do_date = $item->do_date == '' ? $item->dohs_date : $item->do_date;
                @endphp
                <tr>
                    <td style="font-size: 14px;" class="text-center">{{ $no++ }}</td>
                    <td style="font-size: 14px;">{{ $item->invoice_number }}</td>
                    <td style="font-size: 14px;">{{ $item->invoice_date }}</td>
                    <td style="font-size: 14px;">{{ $item->due_date }}</td>
                    <td style="font-size: 14px;">{{ $do_number }}</td>
                    <td style="font-size: 14px;">{{ $item->customer_code }} - {{ $item->nama_customer }}</td>
                    <td style="font-size: 14px;">{{ $item->status }}</td>
                    <td style="font-size: 14px;">{{ $item->top_name }}</td>
                    <td style="font-size: 14px;" class="text-right">
                        {{ number_format($item->total_amount, 0, ',', '.') }}</td>
                    <td style="font-size: 14px;" class="text-right">{{ number_format($out, 0, ',', '.') }}</td>
                    <td style="font-size: 14px;">&nbsp;</td>
                    <td style="font-size: 14px;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @php
                    $outstandin += $out;
                    $nilaiFaktur += $item->total_amount;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" class="text-right" style="font-size: 14px;">Sub Total</td>
                <td class="" colspan="1" class="text-right" style="font-size: 14px;">
                    {{ number_format($nilaiFaktur, 0, ',', '.') }}
                </td>
                <td class="" colspan="1" class="text-right" style="font-size: 14px;">
                    {{ number_format($outstandin, 0, ',', '.') }}
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <br>
    <table class="no-border" style="width:100%;">
        <tr>
            <td class="text-center">
                <br><br><br>
                <strong>Dibuat Oleh</strong>
                <br><br><br>
                (__________________)
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Kolektor</strong>
                <br><br><br>
                (__________________)
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Kasir</strong>
                <br><br><br>
                (__________________)
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Admin AR</strong>
                <br><br><br>
                (__________________)
            </td>
            <td class="text-center">
                <br><br><br>
                <strong>Disetujui Oleh</strong>
                <br><br><br>
                (__________________)
            </td>
        </tr>
    </table>
</body>

</html>
