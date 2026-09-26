let ApSubledger = {
    module: () => {
        return "transaksi/ap_subledger";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + ApSubledger.module();
    },

    setSelect2: () => {
        if ($(".select2").length > 0) {
            $.each($(".select2"), function () {
                $(this).select2();
            });
        }
    },

    getTableColumns: () => {
        let updateAction = $("#update").val();
        let deleteAction = $("#delete").val();

        return [
            {
                data: "id",
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
            },
            {
                data: "invoice_no",
            },
            {
                data: "nama_vendor",
                render: function (data, type, row) {
                    return row.supplier_code + ' - ' + data;
                }
            },
            {
                data: "invoice_date",
            },
            {
                data: "due_date",
            },
            {
                data: "invoice_amount",
                className: 'text-end',
                render: $.fn.dataTable.render.number('.', ',', 2)
            },
            {
                data: "paid_amount",
                className: 'text-end',
                render: $.fn.dataTable.render.number('.', ',', 2)
            },
            {
                data: "outstanding_amount",
                className: 'text-end',
                render: $.fn.dataTable.render.number('.', ',', 2)
            },
            {
                data: "status",
                render: function (data) {
                    let badge = 'bg-secondary';
                    if (data === 'OPEN') badge = 'bg-danger';
                    else if (data === 'PARTIALLY_PAID') badge = 'bg-warning text-dark';
                    else if (data === 'PAID') badge = 'bg-success';
                    else if (data === 'REVERSED') badge = 'bg-secondary';
                    return '<span class="badge ' + badge + '">' + data + '</span>';
                }
            },
            {
                data: "id",
                render: function (data, type, row) {
                    return '<a href="' + row.detail_url + '&start_date=' + $('#start_date').val() + '&end_date=' + $('#end_date').val() +
                        '" class="btn btn-sm btn-info" title="Lihat Ledger"><i class="ri-eye-line"></i></a>';
                },
                orderable: false,
                searchable: false,
            },
        ];
    },

    getTableConfig: () => {
        return {
            processing: true,
            serverSide: true,
            responsive: true,
            ordering: true,
            autoWidth: false,
            order: [[0, "desc"]],
            aLengthMenu: [
                [25, 50, 100],
                [25, 50, 100],
            ],
            lengthChange: !1,
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>",
                },
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass(
                    "pagination-rounded"
                );
            },
            ajax: {
                url: url.base_url(ApSubledger.moduleApi()) + "getData",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ApSubledger.csrf_token(),
                },
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.supplier_id = $('#supplier_id').val();
                    d.status = $('#status').val();
                }
            },
            deferRender: true,
            columns: ApSubledger.getTableColumns(),
            drawCallback: function (settings) {
                ApSubledger.updateSummary(settings.json);
                $(".dataTables_paginate > .pagination").addClass(
                    "pagination-rounded"
                );
            },
        };
    },

    getLedgerTableConfig: () => {
        return {
            processing: true,
            serverSide: true,
            responsive: true,
            ordering: true,
            autoWidth: false,
            order: [[1, "asc"]],
            aLengthMenu: [
                [25, 50, 100, -1],
                [25, 50, 100, "All"],
            ],
            lengthChange: !1,
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>",
                },
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass(
                    "pagination-rounded"
                );
            },
            ajax: {
                url: url.base_url(ApSubledger.moduleApi()) + "getLedgerData",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ApSubledger.csrf_token(),
                },
                data: function (d) {
                    const params = new URLSearchParams(window.location.search);
                    d.supplier_id = params.get('supplier_id');
                    d.start_date = params.get('start_date');
                    d.end_date = params.get('end_date');
                }
            },
            deferRender: true,
            columns: [
                {
                    data: "id",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                {
                    data: "date",
                },
                {
                    data: "type",
                    render: function (data) {
                        let badge = 'bg-secondary';
                        if (data === 'INVOICE') badge = 'bg-primary';
                        else if (data === 'PAYMENT') badge = 'bg-success';
                        else if (data === 'CREDIT_NOTE') badge = 'bg-info';
                        return '<span class="badge ' + badge + '">' + data + '</span>';
                    }
                },
                {
                    data: "ref_no",
                },
                {
                    data: "debit",
                    className: 'text-end',
                    render: function (data) {
                        return data > 0 ? ApSubledger.formatNumber(data) : '-';
                    }
                },
                {
                    data: "credit",
                    className: 'text-end',
                    render: function (data) {
                        return data > 0 ? ApSubledger.formatNumber(data) : '-';
                    }
                },
                {
                    data: "balance",
                    className: 'text-end',
                    render: function (data) {
                        return ApSubledger.formatNumber(data);
                    }
                },
                {
                    data: "status",
                    render: function (data) {
                        let badge = 'bg-secondary';
                        if (data === 'OPEN') badge = 'bg-danger';
                        else if (data === 'PARTIALLY_PAID') badge = 'bg-warning text-dark';
                        else if (data === 'PAID') badge = 'bg-success';
                        else if (data === 'REVERSED') badge = 'bg-secondary';
                        else if (data === 'UNALLOCATED') badge = 'bg-danger';
                        else if (data === 'PARTIALLY_ALLOCATED') badge = 'bg-warning text-dark';
                        else if (data === 'ALLOCATED') badge = 'bg-success';
                        else if (data === 'ACTIVE') badge = 'bg-success';
                        else if (data === 'ALLOCATION_REVERSED') badge = 'bg-secondary';
                        return '<span class="badge ' + badge + '">' + data + '</span>';
                    }
                },
            ],
            order: [[1, 'asc']],
            paging: false,
            searching: false,
            info: false,
            footerCallback: function (row, data, start, end, display) {
                let api = this.api();
                let totalDebit = api.column(4).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
                let totalCredit = api.column(5).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
                let lastBalance = api.column(6).data().toArray().pop() || 0;

                $(api.column(4).footer()).html(ApSubledger.formatNumber(totalDebit));
                $(api.column(5).footer()).html(ApSubledger.formatNumber(totalCredit));
                $(api.column(6).footer()).html(ApSubledger.formatNumber(lastBalance));
            }
        };
    },

    formatNumber: (num) => {
        return num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    updateSummary: function (json) {
        if (!json || !json.data) return;

        let sumInvoice = 0, sumPayment = 0, sumCreditNote = 0, sumOutstanding = 0;

        $.each(json.data, function (i, row) {
            sumInvoice += parseFloat(row.invoice_amount) || 0;
            sumPayment += parseFloat(row.paid_amount) || 0;
            sumOutstanding += parseFloat(row.outstanding_amount) || 0;
        });

        $('#sum-invoice').text(ApSubledger.formatNumber(sumInvoice));
        $('#sum-payment').text(ApSubledger.formatNumber(sumPayment));
        $('#sum-credit-note').text(ApSubledger.formatNumber(sumCreditNote));
        $('#sum-outstanding').text(ApSubledger.formatNumber(sumOutstanding));
    },

    initTable: () => {
        let table = $('#table-ap');
        if (!table.length) return;

        ApSubledger.table = table.DataTable(ApSubledger.getTableConfig());

        ApSubledger.dataTableButtons('#table-ap');
    },

    initLedgerTable: () => {
        let table = $('#table-ledger');
        if (!table.length) return;

        ApSubledger.ledgerTable = table.DataTable(ApSubledger.getLedgerTableConfig());

        ApSubledger.dataTableButtons('#table-ledger');
    },

    dataTableButtons: (tableSelector) => {
        let table = $(tableSelector).DataTable();
        if (!table) return;

        new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: 'excel',
                    filename: 'AP_Subledger',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'pdf',
                    filename: 'AP_Subledger',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'print',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ]
        });

        table.buttons().container()
            .appendTo($(tableSelector + '_wrapper .col-md-6:eq(0)'));
    },

    init: () => {
        ApSubledger.setSelect2();
        ApSubledger.initTable();
        ApSubledger.initLedgerTable();

        $('#search-btn').on('click', () => {
            if (ApSubledger.table) ApSubledger.table.ajax.reload();
        });

        $('#reset-btn').on('click', () => {
            $('#start_date').val('');
            $('#end_date').val('');
            $('#supplier_id').val('').trigger('change');
            $('#status').val('').trigger('change');
            if (ApSubledger.table) ApSubledger.table.ajax.reload();
        });

        $('#filter-form').on('submit', function (e) {
            e.preventDefault();
            window.location.href = url.base_url(ApSubledger.module()) + '/supplier-ledger?' +
                $(this).serialize();
        });
    }
};

$(function () {
    ApSubledger.setSelect2();
    ApSubledger.init();
});