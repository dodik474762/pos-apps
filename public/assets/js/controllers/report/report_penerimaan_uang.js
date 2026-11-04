let ReportPenerimaanUang = {
    module: () => {
        return "report/report_penerimaan_uang";
    },

    moduleApi: () => {
        return "api/" + ReportPenerimaanUang.module();
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    formatNumber: (value) => {
        let number = parseFloat(value ?? 0);
        if (isNaN(number)) {
            number = 0;
        }
        return new Intl.NumberFormat("id-ID", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }).format(number);
    },

    setSelect2: () => {
        if ($(".select2").length > 0) {
            $.each($(".select2"), function () {
                $(this).select2();
            });
        }
    },

    filter: () => {
        ReportPenerimaanUang.getData();
    },

    getData: async () => {
        let tableData = $("table#table-data");

        tableData.DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            autoWidth: false,
            destroy: true,
            fixedHeader: true,
            order: [[1, "desc"]],
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
                    "pagination-rounded",
                );
            },
            ajax: {
                url: url.base_url(ReportPenerimaanUang.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ReportPenerimaanUang.csrf_token(),
                },
                data: function (d) {
                    d.start_date = $("#filter-start-date").val();
                    d.end_date = $("#filter-end-date").val();
                    d.salesman = $("#filter-salesman").val();
                },
            },
            deferRender: true,
            dom: "Bftrip",
            buttons: [
                {
                    extend: "excel",
                    filename: "ReportPenerimaanUang",
                    action: newexportaction,
                },
            ],
            columns: [
                {
                    data: "id",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                {
                    data: "receive_date",
                    title: "Tanggal Terima",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "code",
                    title: "No Penerimaan",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "visit_date",
                    title: "Tanggal Kunjungan",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "salesman_full_name",
                    title: "Salesman",
                    render: function (data, type, row) {
                        return data || row.salesman_name || row.salesman_nik || "-";
                    },
                },
                {
                    data: "nama_customer",
                    title: "Customer",
                    render: function (data, type, row) {
                        if (!data) {
                            return "-";
                        }
                        return row.customer_code
                            ? row.customer_code + " - " + data
                            : data;
                    },
                },
                {
                    data: "invoice_number",
                    title: "No Invoice",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "invoice_date",
                    title: "Tanggal Invoice",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "due_date",
                    title: "Jatuh Tempo",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "do_number",
                    title: "No DO",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "warehouse_name",
                    title: "Warehouse",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "amount_paid",
                    title: "Nilai Terima",
                    className: "text-end",
                    render: function (data, type, row) {
                        if (type === "display" || type === "filter") {
                            return ReportPenerimaanUang.formatNumber(data);
                        }
                        return data;
                    },
                },
                {
                    data: "header_total",
                    title: "Total Penerimaan",
                    className: "text-end",
                    render: function (data, type, row) {
                        if (type === "display" || type === "filter") {
                            return ReportPenerimaanUang.formatNumber(data);
                        }
                        return data;
                    },
                },
                {
                    data: "status",
                    title: "Status",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "type_trans",
                    title: "Tipe",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "remarks",
                    title: "Keterangan",
                    orderable: false,
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
            ],
        });

        $(".dataTables_length select").addClass("form-select form-select-sm");
    },
};

// untuk export all data
function newexportaction(e, dt, button, config) {
    var self = this;
    var oldStart = dt.settings()[0]._iDisplayStart;
    dt.one("preXhr", function (e, s, data) {
        data.start = 0;
        data.length = 2147483647;
        dt.one("preDraw", function (e, settings) {
            if (button[0].className.indexOf("buttons-copy") >= 0) {
                $.fn.dataTable.ext.buttons.copyHtml5.action.call(
                    self,
                    e,
                    dt,
                    button,
                    config,
                );
            } else if (button[0].className.indexOf("buttons-excel") >= 0) {
                $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config)
                    ? $.fn.dataTable.ext.buttons.excelHtml5.action.call(
                        self,
                        e,
                        dt,
                        button,
                        config,
                    )
                    : $.fn.dataTable.ext.buttons.excelFlash.action.call(
                        self,
                        e,
                        dt,
                        button,
                        config,
                    );
            } else if (button[0].className.indexOf("buttons-csv") >= 0) {
                $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config)
                    ? $.fn.dataTable.ext.buttons.csvHtml5.action.call(
                        self,
                        e,
                        dt,
                        button,
                        config,
                    )
                    : $.fn.dataTable.ext.buttons.csvFlash.action.call(
                        self,
                        e,
                        dt,
                        button,
                        config,
                    );
            } else if (button[0].className.indexOf("buttons-pdf") >= 0) {
                $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config)
                    ? $.fn.dataTable.ext.buttons.pdfHtml5.action.call(
                        self,
                        e,
                        dt,
                        button,
                        config,
                    )
                    : $.fn.dataTable.ext.buttons.pdfFlash.action.call(
                        self,
                        e,
                        dt,
                        button,
                        config,
                    );
            } else if (button[0].className.indexOf("buttons-print") >= 0) {
                $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
            }
            dt.one("preXhr", function (e, s, data) {
                settings._iDisplayStart = oldStart;
                data.start = oldStart;
            });
            setTimeout(dt.ajax.reload, 0);
            return false;
        });
    });
    dt.ajax.reload();
}

$(function () {
    ReportPenerimaanUang.setSelect2();
    ReportPenerimaanUang.getData();
});
