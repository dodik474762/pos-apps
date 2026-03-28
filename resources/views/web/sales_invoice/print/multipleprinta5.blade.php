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

        /* ✅ Wrapper 1 halaman = 2 invoice atas-bawah */
        .page-wrapper {
            page-break-after: always;
        }

        .page-wrapper:last-child {
            page-break-after: avoid;
        }

        /* ✅ Tiap invoice ambil ~50% tinggi halaman */
        .invoice-block {
            height: 48%;
            overflow: hidden;
            padding-bottom: 2mm;
        }

        /* Garis pemisah antar invoice */
        .invoice-divider {
            border-top: 1px dashed #aaa;
            margin: 2mm 0;
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
<div class="page-wrapper">

    @foreach ($chunk as $data)
    @php
        $qr = '';
        $do = empty($data->do) ? [] : $data->do;
        $so = empty($data->do) ? $data->so : $do->so;
        $salesman_name = $so->salesman->nama_lengkap ?? '-';
        $globalIndex = $chunkIndex * 2 + $loop->iteration;
    @endphp

    {{-- Garis pemisah (hanya antara invoice 1 dan 2, bukan sebelum yang pertama) --}}
    @if (!$loop->first)
        <div class="invoice-divider"></div>
    @endif

    <div class="invoice-block">
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
            Invoice {{ $globalIndex }} / {{ $invoices->count() }}
        </div>
    </div>

    @endforeach

</div>
@endforeach

</body>
</html>