let ReportDelivery = {
    module: () => {
        return "report/report_delivery";
    },

    moduleApi: () => {
        return "api/" + ReportDelivery.module();
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

    setSelect2: () => {
        if ($(".select2").length > 0) {
            $.each($(".select2"), function () {
                $(this).select2();
            });
        }
    },

    filter: () => {
        ReportDelivery.getData();
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
                url: url.base_url(ReportDelivery.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ReportDelivery.csrf_token(),
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
                    filename: "ReportDelivery",
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
                    data: "packing_date",
                    title: "Tanggal PL",
                },
                {
                    data: "packing_list_no",
                    title: "No PL",
                },
                {
                    data: "do_date",
                    title: "Tanggal DO",
                },
                {
                    data: "do_number",
                    title: "No DO",
                },
                {
                    data: "confirm_date",
                    title: "Tanggal Konfirmasi",
                    render: function (data, type, row) {
                        return ReportDelivery.formatDateTime(data);
                    },
                },
                {
                    data: "detail_receive_wh_date",
                    title: "Tanggal Terima",
                    render: function (data, type, row) {
                        return ReportDelivery.formatDateTime(data, 7);
                    },
                },
                {
                    data: "nama_customer",
                    title: "Customer",
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
                    data: "qty_do",
                    title: "Qty DO",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "qty_packed",
                    title: "Qty Packed",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "qty_received",
                    title: "Qty Received",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "packing_status",
                    title: "Status",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "driver_name",
                    title: "Driver",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "vehicle_no",
                    title: "No Kendaraan",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "expedition_name",
                    title: "Ekspedisi",
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
    ReportDelivery.setSelect2();
    ReportDelivery.getData();
});
