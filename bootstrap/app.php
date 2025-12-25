<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
         // Tambahkan rute API publik Anda yang dikecualikan di sini
        $middleware->validateCsrfTokens(except: [
            'api_mobile/master/product/getDataProductMobile',
            'api_mobile/transaksi/sales_plan/getSalesRoutePlan',
            'api_mobile/transaksi/sales_plan/getSalesRoutePlan',
            'api_mobile/transaksi/sales_invoice/getOutstandingInvoice',
            'api_mobile/auth/login',
            'api_mobile/transaksi/presensi/getDataPresensi',
            'api_mobile/transaksi/presensi/submitPresensi',
            'api_mobile/transaksi/sales_order/sync',
            'api_mobile/transaksi/sales_order/getAverageTransaction',
            'api_mobile/transaksi/sales_order/closingOrder',
            'api_mobile/transaksi/sales_payment/sync',
            'api_mobile/transaksi/sales_return/sync',
            'api_mobile/master/customer/getCity',
            'api_mobile/master/customer/getKecamatan',
            'api_mobile/master/customer/getKelurahan',
            'api_mobile/master/customer/getProvinsi',
            'api_mobile/master/customer/submitNoo',
            'api_mobile/transaksi/packing_list/getData',
            'api_mobile/transaksi/packing_list/confirmDeliver',
            // Tambahkan URI webhook atau callback eksternal lainnya di sini
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
