<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Multiple Invoice</title>

    <style>
        @page {
            size: A5 landscape;
            margin: 4mm; /* diperkecil */
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px; /* lebih kecil */
        }

        /* halaman */
        .page-number {
            text-align: center;
            font-size: 8px;
            margin-top: 4px;
            color: #333;
        }

        /* supaya template utama ikut scaled down */
        .scale-down {
            font-size: 9px !important;
        }

        .scale-down table,
        .scale-down td,
        .scale-down th {
            font-size: 8px !important;
            padding: 2px !important;
        }

        .scale-down h4 {
            font-size: 10px !important;
            margin: 2px 0 !important;
        }
    </style>
</head>

<body>

@foreach ($invoices as $data)

    @php
        $qr = base64_encode(QrCode::format('png')->size(70)->generate($data->invoice_number));
        $do = $data->do;
        $so = $do->so;
        $salesman = $so->salesman;
        $salesman_name = $salesman->nama_lengkap ?? '-';

        $currentPage = $loop->iteration;
        $totalPage   = $loop->count;
    @endphp

    {{-- panggil template utama, dibungkus dengan class scale-down --}}
    <div class="scale-down">
        @include('web.sales_invoice.print.po-printa5', [
            'data' => $data,
            'company' => $company,
            'qr' => $qr,
            'so' => $so,
            'salesman_name' => $salesman_name
        ])
    </div>

    {{-- Info halaman --}}
    <div class="page-number">
        Halaman {{ $currentPage }} / {{ $totalPage }}
    </div>

    {{-- break page --}}
    @if (!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif

@endforeach

</body>
</html>
