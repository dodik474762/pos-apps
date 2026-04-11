<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Multiple Invoice</title>

    <style>
        @page {
            size: 215.9mm auto;  /* 8.5 inch lebar, tinggi otomatis */
            margin: 5mm 11mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
        }

        .page-wrapper {
            page-break-after: always;
        }

        .page-wrapper:last-child {
            page-break-after: avoid;
        }

        .page-info {
            text-align: center;
            font-size: 10px;
            color: #555;
            margin-top: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>

@foreach ($invoices as $globalIndex => $data)
<div class="page-wrapper">
    @php
        $qr = '';
        $do = empty($data->do) ? [] : $data->do;
        $so = empty($data->do) ? $data->so : $do->so;
        $salesman_name = $so->salesman->nama_lengkap ?? '-';
    @endphp

    @include('web.sales_invoice.print.po-printa5', [
        'data'         => $data,
        'company'      => $company,
        'qr'           => $qr,
        'so'           => $so,
        'salesman_name'=> $salesman_name,
        'promo'        => $data->promo ?? collect(),
        'promo_item'   => $data->promo_item ?? collect()
    ])

    <div class="page-info">
        Invoice {{ $globalIndex + 1 }} / {{ $invoices->count() }}
    </div>
</div>
@endforeach

</body>
</html>