<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Multiple Invoice</title>

    <style>
        @page {
            size: A5 landscape;
            margin: 5mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
    </style>
</head>

<body>

@foreach ($invoices as $data)

    @php
        $qr = base64_encode(QrCode::format('png')->size(80)->generate($data->invoice_number));
        $do = $data->do;
        $so = $do->so;
        $salesman = $so->salesman;
        $salesman_name = $salesman->nama_lengkap ?? '-';
    @endphp

    {{-- panggil template utama --}}
    @include('web.sales_invoice.print.po-printa5', [
        'data' => $data,
        'company' => $company,
        'qr' => $qr,
        'so' => $so,
        'salesman_name' => $salesman_name
    ])

    {{-- break page untuk invoice berikutnya --}}
    @if (!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif

@endforeach

</body>
</html>
