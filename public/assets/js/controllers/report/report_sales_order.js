let ReportSalesOrder = {
    module: () => {
        return "report/report_sales_order";
    },

    moduleApi: () => {
        return "api/" + ReportSalesOrder.module();
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    formatDateTime: (value, addHours = 0) => {
        if (!value) {
            return "-";
        }
        let dateTime = String(value).replace("T", " ");
        if (addHours !== 0) {
            let date = new Date(dateTime.replace(" ", "T"));
            if (!isNaN(date.getTime())) {
                date.setHours(date.getHours() + addHours);
                const pad = (number) => String(number).padStart(2, "0");
                return (
                    date.getFullYear() +
                    "-" +
                    pad(date.getMonth() + 1) +
                    "-" +
                    pad(date.getDate()) +
                    " " +
                    pad(date.getHours()) +
                    ":" +
                    pad(date.getMinutes())
                );
            }
        }
        return dateTime.length >= 16 ? dateTime.substring(0, 16) : dateTime;
    },

    evidenceUrl: (data) => {
        if (!data) {
            return "-";
        }
        if (/^https?:\/\//i.test(data)) {
            return data;
        }
        return "https://pos.b3smart.my.id/" + String(data).replace(/^\/+/, "");
    },

    setSelect2: () => {
        if ($(".select2").length > 0) {
            $.each($(".select2"), function () {
                $(this).select2();
            });
        }
    },

    filter: () => {
        ReportSalesOrder.getData();
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
                url: url.base_url(ReportSalesOrder.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ReportSalesOrder.csrf_token(),
                },
                data: function (d) {
                    d.start_date = $("#filter-start-date").val();
                    d.end_date = $("#filter-end-date").val();
                    d.principal = $("#filter-principal").val();
                },
            },
            deferRender: true,
            dom: "Bftrip",
            buttons: [
                {
                    extend: "excel",
                    filename: "ReportSalesOrder",
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
                    data: "so_date",
                    title: "Tanggal SO",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "so_number",
                    title: "No SO",
                    render: function (data, type, row) {
                        return data ?? "-";
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
                    data: "salesman_name",
                    title: "Salesman",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "branch_name",
                    title: "Branch",
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
                    data: "product_code",
                    title: "Kode Produk",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "product_name",
                    title: "Nama Produk",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "principal",
                    title: "Principal",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "qty",
                    title: "Qty",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "unit_name",
                    title: "Satuan",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "unit_price",
                    title: "Harga",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "subtotal",
                    title: "Subtotal",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "so_status",
                    title: "Status",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "platform",
                    title: "Platform",
                    render: function (data, type, row) {
                        return data ?? "-";
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
                    data: "photo_path",
                    title: "Eviden Foto",
                    orderable: false,
                    render: function (data, type, row) {
                        return ReportSalesOrder.evidenceUrl(data);
                    },
                },
                {
                    data: "signature_path",
                    title: "TTD",
                    orderable: false,
                    render: function (data, type, row) {
                        return ReportSalesOrder.evidenceUrl(data);
                    },
                },
                {
                    data: "checkin_path",
                    title: "Check In",
                    orderable: false,
                    render: function (data, type, row) {
                        return ReportSalesOrder.evidenceUrl(data);
                    },
                },
                {
                    data: "owner_path",
                    title: "Foto Owner",
                    orderable: false,
                    render: function (data, type, row) {
                        return ReportSalesOrder.evidenceUrl(data);
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
    ReportSalesOrder.setSelect2();
    ReportSalesOrder.getData();
});
