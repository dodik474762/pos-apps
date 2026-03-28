<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Multiple Invoice</title>

    <style>
        @page {
            margin: 4mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        /* ✅ Wrapper baris: 2 invoice per baris */
        .invoice-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            page-break-after: always;
        }

        .invoice-row:last-child {
            page-break-after: avoid;
        }

        /* ✅ Tiap kolom = 1 invoice, lebar 50% */
        .invoice-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 1mm 2mm;
            border-right: 1px dashed #aaa;
        }

        .invoice-col:last-child {
            border-right: none;
        }

        /* scale down */
        .scale-down {
            font-size: 8px !important;
        }

        .scale-down table,
        .scale-down td,
        .scale-down th {
            font-size: 7px !important;
            padding: 2px !important;
        }

        .scale-down h4 {
            font-size: 8px !important;
            margin: 2px 0 !important;
        }

        .page-info {
            text-align: center;
            font-size: 7px;
            color: #555;
            margin-top: 2px;
        }
    </style>
</head>

<body>

@php
    $chunks = $invoices->chunk(2);
@endphp

@foreach ($chunks as $chunkIndex => $chunk)
<div class="invoice-row">

    @foreach ($chunk as $data)
    @php
        $qr = '';
        $do = empty($data->do) ? [] : $data->do;
        $so = empty($data->do) ? $data->so : $do->so;
        $salesman_name = $so->salesman->nama_lengkap ?? '-';
    @endphp

    <div class="invoice-col">
        <div class="scale-down">
            @include('web.sales_invoice.print.po-printa5', [
                'data'         => $data,
                'company'      => $company,
                'qr'           => $qr,
                'so'           => $so,
                'salesman_name'=> $salesman_name
            ])
        </div>
        <div class="page-info">
            Invoice {{ $loop->parent->index * 2 + $loop->iteration }} / {{ $invoices->count() }}
        </div>
    </div>

    @endforeach

</div>
@endforeach

</body>
</html>