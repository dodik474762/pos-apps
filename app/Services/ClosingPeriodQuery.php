<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ClosingPeriodQuery
{
    /**
     * Konfigurasi tab monitoring closing period.
     * Dipakai oleh web controller (render label kolom) & api controller (search/order).
     * Urutan `columns` harus sama dengan urutan <th> di blade dan kolom di JS.
     */
    public static function tabs(): array
    {
        $tabs = [
            'gr' => [
                'label' => 'Good Receipt',
                'icon' => 'ri-inbox-archive-line',
                'search' => ['m.gr_number', 'm.received_date', 'm.status', 'v.nama_vendor', 'w.name', 'm.remarks'],
                'default_order' => ['column' => 'm.received_date', 'dir' => 'desc'],
                'columns' => [
                    ['label' => 'No', 'field' => 'id', 'sort' => 'm.id', 'type' => 'index'],
                    ['label' => 'GR Number', 'field' => 'gr_number', 'sort' => 'm.gr_number'],
                    ['label' => 'Received Date', 'field' => 'received_date', 'sort' => 'm.received_date'],
                    ['label' => 'Vendor', 'field' => 'nama_vendor', 'sort' => 'v.nama_vendor'],
                    ['label' => 'Warehouse', 'field' => 'warehouse_name', 'sort' => 'w.name'],
                    ['label' => 'Status', 'field' => 'status', 'sort' => 'm.status', 'type' => 'status'],
                    ['label' => 'Total Qty', 'field' => 'total_qty', 'sort' => 'm.total_qty', 'type' => 'integer'],
                    ['label' => 'Total Amount', 'field' => 'total_amount', 'sort' => 'm.total_amount', 'type' => 'money'],
                    ['label' => 'Remarks', 'field' => 'remarks', 'sort' => 'm.remarks'],
                ],
            ],
            'pi' => [
                'label' => 'Purchase Invoice',
                'icon' => 'ri-bill-line',
                'search' => ['m.invoice_number', 'm.invoice_date', 'm.status', 'v.nama_vendor', 'm.remarks'],
                'default_order' => ['column' => 'm.invoice_date', 'dir' => 'desc'],
                'columns' => [
                    ['label' => 'No', 'field' => 'id', 'sort' => 'm.id', 'type' => 'index'],
                    ['label' => 'Invoice Number', 'field' => 'invoice_number', 'sort' => 'm.invoice_number'],
                    ['label' => 'Invoice Date', 'field' => 'invoice_date', 'sort' => 'm.invoice_date'],
                    ['label' => 'Vendor', 'field' => 'nama_vendor', 'sort' => 'v.nama_vendor'],
                    ['label' => 'Total Amount', 'field' => 'total_amount', 'sort' => 'm.total_amount', 'type' => 'money'],
                    ['label' => 'Status', 'field' => 'status', 'sort' => 'm.status', 'type' => 'status'],
                    ['label' => 'Remarks', 'field' => 'remarks', 'sort' => 'm.remarks'],
                ],
            ],
            'vb' => [
                'label' => 'Vendor Bill',
                'icon' => 'ri-money-dollar-circle-line',
                'search' => ['m.payment_number', 'm.payment_date', 'm.status', 'v.nama_vendor', 'm.reference_number', 'm.remarks'],
                'default_order' => ['column' => 'm.payment_date', 'dir' => 'desc'],
                'columns' => [
                    ['label' => 'No', 'field' => 'id', 'sort' => 'm.id', 'type' => 'index'],
                    ['label' => 'Payment Number', 'field' => 'payment_number', 'sort' => 'm.payment_number'],
                    ['label' => 'Payment Date', 'field' => 'payment_date', 'sort' => 'm.payment_date'],
                    ['label' => 'Vendor', 'field' => 'nama_vendor', 'sort' => 'v.nama_vendor'],
                    ['label' => 'Method', 'field' => 'payment_method', 'sort' => 'm.payment_method'],
                    ['label' => 'Reference', 'field' => 'reference_number', 'sort' => 'm.reference_number'],
                    ['label' => 'Total Payment', 'field' => 'total_payment', 'sort' => 'm.total_payment', 'type' => 'money'],
                    ['label' => 'Status', 'field' => 'status', 'sort' => 'm.status', 'type' => 'status'],
                    ['label' => 'Remarks', 'field' => 'remarks', 'sort' => 'm.remarks'],
                ],
            ],
            'pr' => [
                'label' => 'Purchase Return',
                'icon' => 'ri-arrow-go-back-line',
                'search' => ['m.code', 'm.return_date', 'm.status', 'v.nama_vendor', 'w.name', 'm.reason'],
                'default_order' => ['column' => 'm.return_date', 'dir' => 'desc'],
                'columns' => [
                    ['label' => 'No', 'field' => 'id', 'sort' => 'm.id', 'type' => 'index'],
                    ['label' => 'Code', 'field' => 'code', 'sort' => 'm.code'],
                    ['label' => 'Return Date', 'field' => 'return_date', 'sort' => 'm.return_date'],
                    ['label' => 'Vendor', 'field' => 'nama_vendor', 'sort' => 'v.nama_vendor'],
                    ['label' => 'Warehouse', 'field' => 'warehouse_name', 'sort' => 'w.name'],
                    ['label' => 'Type', 'field' => 'return_type', 'sort' => 'm.return_type'],
                    ['label' => 'Reference', 'field' => 'reference_id', 'sort' => 'm.reference_id'],
                    ['label' => 'Total Amount', 'field' => 'total_amount', 'sort' => 'm.total_amount', 'type' => 'money'],
                    ['label' => 'Status', 'field' => 'status', 'sort' => 'm.status', 'type' => 'status'],
                    ['label' => 'Reason', 'field' => 'reason', 'sort' => 'm.reason'],
                ],
            ],
            'si' => [
                'label' => 'Sales Invoice',
                'icon' => 'ri-file-text-line',
                'search' => ['m.invoice_number', 'm.invoice_date', 'm.status', 'cc.nama_customer', 'sm.name'],
                'default_order' => ['column' => 'm.invoice_date', 'dir' => 'desc'],
                'columns' => [
                    ['label' => 'No', 'field' => 'id', 'sort' => 'm.id', 'type' => 'index'],
                    ['label' => 'Invoice Number', 'field' => 'invoice_number', 'sort' => 'm.invoice_number'],
                    ['label' => 'Invoice Date', 'field' => 'invoice_date', 'sort' => 'm.invoice_date'],
                    ['label' => 'Customer', 'field' => 'nama_customer', 'sort' => 'cc.nama_customer'],
                    ['label' => 'Salesman', 'field' => 'salesman_name', 'sort' => 'sm.name'],
                    ['label' => 'Warehouse', 'field' => 'warehouse_name', 'sort' => 'w.name'],
                    ['label' => 'Total Amount', 'field' => 'total_amount', 'sort' => 'm.total_amount', 'type' => 'money'],
                    ['label' => 'Amount Paid', 'field' => 'amount_paid', 'sort' => 'm.amount_paid', 'type' => 'money'],
                    ['label' => 'Status', 'field' => 'status', 'sort' => 'm.status', 'type' => 'status'],
                    ['label' => 'Due Date', 'field' => 'due_date', 'sort' => 'm.due_date'],
                ],
            ],
            'do' => [
                'label' => 'Delivery Order',
                'icon' => 'ri-truck-line',
                'search' => ['m.do_number', 'm.do_date', 'm.status', 'cc.nama_customer', 'w.name'],
                'default_order' => ['column' => 'm.do_date', 'dir' => 'desc'],
                'columns' => [
                    ['label' => 'No', 'field' => 'id', 'sort' => 'm.id', 'type' => 'index'],
                    ['label' => 'DO Number', 'field' => 'do_number', 'sort' => 'm.do_number'],
                    ['label' => 'DO Date', 'field' => 'do_date', 'sort' => 'm.do_date'],
                    ['label' => 'Customer', 'field' => 'nama_customer', 'sort' => 'cc.nama_customer'],
                    ['label' => 'Warehouse', 'field' => 'warehouse_name', 'sort' => 'w.name'],
                    ['label' => 'Total Qty', 'field' => 'total_qty', 'sort' => 'm.total_qty', 'type' => 'integer'],
                    ['label' => 'Total Item', 'field' => 'total_item', 'sort' => 'm.total_item', 'type' => 'integer'],
                    ['label' => 'Status', 'field' => 'status', 'sort' => 'm.status', 'type' => 'status'],
                ],
            ],
            'sp' => [
                'label' => 'Sales Payment',
                'icon' => 'ri-wallet-3-line',
                'search' => ['m.payment_code', 'm.payment_date', 'm.status', 'cc.nama_customer', 'm.reference_no'],
                'default_order' => ['column' => 'm.payment_date', 'dir' => 'desc'],
                'columns' => [
                    ['label' => 'No', 'field' => 'id', 'sort' => 'm.id', 'type' => 'index'],
                    ['label' => 'Payment Code', 'field' => 'payment_code', 'sort' => 'm.payment_code'],
                    ['label' => 'Payment Date', 'field' => 'payment_date', 'sort' => 'm.payment_date'],
                    ['label' => 'Customer', 'field' => 'nama_customer', 'sort' => 'cc.nama_customer'],
                    ['label' => 'Method', 'field' => 'payment_method', 'sort' => 'm.payment_method'],
                    ['label' => 'Reference', 'field' => 'reference_no', 'sort' => 'm.reference_no'],
                    ['label' => 'Total Amount', 'field' => 'total_amount', 'sort' => 'm.total_amount', 'type' => 'money'],
                    ['label' => 'Net Amount', 'field' => 'net_amount', 'sort' => 'm.net_amount', 'type' => 'money'],
                    ['label' => 'Status', 'field' => 'status', 'sort' => 'm.status', 'type' => 'status'],
                ],
            ],
            'sr' => [
                'label' => 'Sales Return',
                'icon' => 'ri-arrow-go-back-line',
                'search' => ['m.return_number', 'm.return_date', 'm.status', 'cc.nama_customer', 'sih.invoice_number', 'm.reason'],
                'default_order' => ['column' => 'm.return_date', 'dir' => 'desc'],
                'columns' => [
                    ['label' => 'No', 'field' => 'id', 'sort' => 'm.id', 'type' => 'index'],
                    ['label' => 'Return Number', 'field' => 'return_number', 'sort' => 'm.return_number'],
                    ['label' => 'Return Date', 'field' => 'return_date', 'sort' => 'm.return_date'],
                    ['label' => 'Customer', 'field' => 'nama_customer', 'sort' => 'cc.nama_customer'],
                    ['label' => 'Invoice Number', 'field' => 'invoice_number', 'sort' => 'sih.invoice_number'],
                    ['label' => 'Type', 'field' => 'return_type', 'sort' => 'm.return_type'],
                    ['label' => 'Total Return', 'field' => 'total_return_value', 'sort' => 'm.total_return_value', 'type' => 'money'],
                    ['label' => 'Status', 'field' => 'status', 'sort' => 'm.status', 'type' => 'status'],
                    ['label' => 'Reason', 'field' => 'reason', 'sort' => 'm.reason'],
                ],
            ],
            'sc' => [
                'label' => 'Inventory Closing',
                'icon' => 'ri-archive-2-line',
                'search' => ['m.closing_date', 'm.note', 'u.name'],
                'default_order' => ['column' => 'm.closing_date', 'dir' => 'desc'],
                'columns' => [
                    ['label' => 'No', 'field' => 'id', 'sort' => 'm.id', 'type' => 'index'],
                    ['label' => 'Closing Date', 'field' => 'closing_date', 'sort' => 'm.closing_date'],
                    ['label' => 'Note', 'field' => 'note', 'sort' => 'm.note'],
                    ['label' => 'Created By', 'field' => 'created_by_name', 'sort' => 'u.name'],
                    ['label' => 'Created At', 'field' => 'created_at', 'sort' => 'm.created_at'],
                ],
            ],
        ];

        foreach (self::postActions() as $key => $extra) {
            if (! isset($tabs[$key])) {
                continue;
            }
            $tabs[$key] = $extra + $tabs[$key];
            if (! empty($extra['post'])) {
                $tabs[$key]['columns'][] = ['label' => '', 'field' => null, 'sort' => null, 'type' => 'check'];
            }
        }

        return $tabs;
    }

    /**
     * Aksi posting per tab: mengarah ke function jurnal post milik modul terkait
     * (dipanggil apa adanya dari dashboard, logic modul tidak diubah).
     * Inventory Closing tidak diposting dari sini, hanya diarahkan ke menu Closing Stock.
     */
    public static function postActions(): array
    {
        return [
            'gr' => ['post' => ['module' => 'api/transaksi/good-receipt', 'action' => 'postJurnal']],
            'pi' => ['post' => ['module' => 'api/transaksi/purchase-invoice', 'action' => 'postJurnal']],
            'vb' => ['post' => ['module' => 'api/transaksi/vendor-bill', 'action' => 'postJurnal']],
            'pr' => ['post' => ['module' => 'api/transaksi/purchase-return', 'action' => 'postJurnal']],
            'si' => ['post' => ['module' => 'api/transaksi/sales_invoice', 'action' => 'posted']],
            'do' => ['post' => ['module' => 'api/transaksi/delivery_order', 'action' => 'postJurnal']],
            'sp' => ['post' => ['module' => 'api/transaksi/sales_payment', 'action' => 'posted']],
            'sr' => ['post' => ['module' => 'api/transaksi/sales_return', 'action' => 'postJurnal']],
            'sc' => ['info' => [
                'message' => 'Inventory Closing period berjalan dilakukan melalui menu Closing Stock.',
                'label' => 'Buka Menu Closing Stock',
                'url' => 'transaksi/closing-stock',
            ]],
        ];
    }

    public static function types(): array
    {
        return array_keys(self::tabs());
    }

    public static function periodStart(): string
    {
        return date('Y-m-01');
    }

    public static function periodEnd(): string
    {
        return date('Y-m-t');
    }

    /**
     * Daftar period accounting berstatus OPEN untuk filter periode dashboard.
     */
    public static function openPeriods(): array
    {
        return DB::table('accounting_periods')
            ->where('status', 'OPEN')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($period) => [
                'id' => (int) $period->id,
                'label' => self::periodLabel($period),
            ])
            ->toArray();
    }

    /**
     * Rentang tanggal filter periode: id period OPEN yang dipilih -> tanggal period tsb.
     * Tanpa pilihan / id tidak valid -> bulan berjalan (logic lama tetap jalan).
     */
    public static function resolvePeriod($periodId = null): array
    {
        if (! empty($periodId)) {
            $period = DB::table('accounting_periods')
                ->where('status', 'OPEN')
                ->where('id', $periodId)
                ->first();

            if (! empty($period)) {
                return [
                    'id' => (int) $period->id,
                    'label' => self::periodLabel($period),
                    'start' => $period->start_date,
                    'end' => $period->end_date,
                ];
            }
        }

        return [
            'id' => 0,
            'label' => date('F Y'),
            'start' => self::periodStart(),
            'end' => self::periodEnd(),
        ];
    }

    private static function periodLabel($period): string
    {
        $label = date('F Y', mktime(0, 0, 0, (int) $period->month, 1, (int) $period->year));
        $description = trim((string) ($period->description ?? ''));

        return $description === '' ? $label : $label . ' (' . $description . ')';
    }

    public static function searchColumns(string $type): array
    {
        $tabs = self::tabs();
        return $tabs[$type]['search'] ?? [];
    }

    public static function defaultOrder(string $type): array
    {
        $tabs = self::tabs();
        return $tabs[$type]['default_order'] ?? ['column' => 'm.id', 'dir' => 'desc'];
    }

    public static function orderColumn(string $type, int $index): ?string
    {
        $tabs = self::tabs();
        return $tabs[$type]['columns'][$index]['sort'] ?? null;
    }

    /**
     * Query dasar per tab, sesuai monitoring closing:
     * hanya dokumen yang belum selesai / belum posted,
     * dan hanya untuk period yang dipilih (default: bulan berjalan).
     */
    public static function make(string $type, ?string $dateStart = null, ?string $dateEnd = null)
    {
        $dateStart = $dateStart ?: self::periodStart();
        $dateEnd = $dateEnd ?: self::periodEnd();

        switch ($type) {
            case 'gr':
                return DB::table('goods_receipt_header as m')
                    ->leftJoin('vendor as v', 'v.id', '=', 'm.vendor')
                    ->leftJoin('warehouse as w', 'w.id', '=', 'm.warehouse')
                    ->select([
                        'm.id',
                        'm.gr_number',
                        'm.received_date',
                        'm.status',
                        'm.total_qty',
                        'm.total_amount',
                        'm.remarks',
                        'm.purchase_order',
                        'm.created_at',
                        'v.nama_vendor',
                        'w.name as warehouse_name',
                    ])
                    ->whereNull('m.deleted')
                    ->where('m.status', '!=', 'completed')
                    ->where(function ($query) use ($dateStart, $dateEnd) {
                        $query->whereBetween('m.received_date', [$dateStart, $dateEnd])
                            ->orWhere(function ($sub) use ($dateStart, $dateEnd) {
                                $sub->whereNull('m.received_date')
                                    ->whereBetween('m.created_at', [$dateStart, $dateEnd . ' 23:59:59']);
                            });
                    });

            case 'pi':
                return DB::table('purchase_invoice_header as m')
                    ->leftJoin('vendor as v', 'v.id', '=', 'm.vendor')
                    ->select([
                        'm.id',
                        'm.invoice_number',
                        'm.invoice_date',
                        'm.status',
                        'm.total_amount',
                        'm.remarks',
                        'm.created_at',
                        'v.nama_vendor',
                    ])
                    ->whereNull('m.deleted')
                    ->where('m.status', '!=', 'posted')
                    ->whereBetween('m.invoice_date', [$dateStart, $dateEnd]);

            case 'vb':
                return DB::table('vendor_payment_header as m')
                    ->leftJoin('vendor as v', 'v.id', '=', 'm.vendor')
                    ->select([
                        'm.id',
                        'm.payment_number',
                        'm.payment_date',
                        'm.payment_method',
                        'm.reference_number',
                        'm.total_payment',
                        'm.status',
                        'm.remarks',
                        'm.created_at',
                        'v.nama_vendor',
                    ])
                    ->whereNull('m.deleted')
                    ->where('m.status', '!=', 'posted')
                    ->whereBetween('m.payment_date', [$dateStart, $dateEnd]);

            case 'pr':
                return DB::table('purchase_return as m')
                    ->leftJoin('vendor as v', 'v.id', '=', 'm.vendor')
                    ->leftJoin('warehouse as w', 'w.id', '=', 'm.warehouse')
                    ->select([
                        'm.id',
                        'm.code',
                        'm.return_date',
                        'm.return_type',
                        'm.reference_id',
                        'm.total_amount',
                        'm.status',
                        'm.reason',
                        'm.created_at',
                        'v.nama_vendor',
                        'w.name as warehouse_name',
                    ])
                    ->whereNull('m.deleted')
                    ->where('m.status', '!=', 'POSTED')
                    ->whereBetween('m.return_date', [$dateStart, $dateEnd]);

            case 'si':
                return DB::table('sales_invoice_header as m')
                    ->leftJoin('customer as cc', 'cc.id', '=', 'm.customer_id')
                    ->leftJoin('users as sm', 'sm.id', '=', 'm.salesman_id')
                    ->leftJoin('warehouse as w', 'w.id', '=', 'm.warehouse_id')
                    ->select([
                        'm.id',
                        'm.invoice_number',
                        'm.invoice_date',
                        'm.status',
                        'm.total_amount',
                        'm.amount_paid',
                        'm.due_date',
                        'm.created_at',
                        'cc.nama_customer',
                        'sm.name as salesman_name',
                        'w.name as warehouse_name',
                    ])
                    ->whereNull('m.post_date')
                    ->whereNull('m.deleted')
                    ->whereBetween('m.invoice_date', [$dateStart, $dateEnd]);

            case 'do':
                return DB::table('delivery_order_header as m')
                    ->leftJoin('customer as cc', 'cc.id', '=', 'm.customer_id')
                    ->leftJoin('warehouse as w', 'w.id', '=', 'm.warehouse_id')
                    ->select([
                        'm.id',
                        'm.do_number',
                        'm.do_date',
                        'm.status',
                        'm.total_qty',
                        'm.total_item',
                        'm.created_at',
                        'cc.nama_customer',
                        'w.name as warehouse_name',
                    ])
                    ->whereNull('m.deleted')
                    ->whereNull('m.post_date')
                    ->whereBetween('m.do_date', [$dateStart, $dateEnd]);

            case 'sp':
                return DB::table('sales_payment_header as m')
                    ->leftJoin('customer as cc', 'cc.id', '=', 'm.customer_id')
                    ->select([
                        'm.id',
                        'm.payment_code',
                        'm.payment_date',
                        'm.payment_method',
                        'm.reference_no',
                        'm.total_amount',
                        'm.net_amount',
                        'm.status',
                        'm.created_at',
                        'cc.nama_customer',
                    ])
                    ->whereNull('m.deleted')
                    ->whereNull('m.post_date')
                    ->whereBetween('m.payment_date', [$dateStart, $dateEnd]);

            case 'sr':
                return DB::table('sales_return as m')
                    ->leftJoin('customer as cc', 'cc.id', '=', 'm.customer_id')
                    ->leftJoin('sales_invoice_header as sih', 'sih.id', '=', 'm.invoice_id')
                    ->select([
                        'm.id',
                        'm.return_number',
                        'm.return_date',
                        'm.return_type',
                        'm.total_return_value',
                        'm.status',
                        'm.reason',
                        'm.created_at',
                        'cc.nama_customer',
                        'sih.invoice_number',
                    ])
                    ->whereNull('m.deleted')
                    ->whereNull('m.post_date')
                    ->where('m.return_type', 'RETURN')
                    ->whereBetween('m.return_date', [$dateStart, $dateEnd]);

            case 'sc':
                return DB::table('stock_closing as m')
                    ->leftJoin('users as u', 'u.id', '=', 'm.created_by')
                    ->select([
                        'm.id',
                        'm.closing_date',
                        'm.note',
                        'm.created_at',
                        'u.name as created_by_name',
                    ])
                    ->whereBetween('m.closing_date', [$dateStart, $dateEnd]);

            default:
                return null;
        }
    }
}
