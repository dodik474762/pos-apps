let ReportPromo = {
    module: () => {
        return "report_promo";
    },

    moduleApi: () => {
        return "api/report/" + ReportPromo.module();
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    setSelect2: () => {
        if ($(".select2").length > 0) {
            $.each($(".select2"), function () {
                $(this).select2();
            });
        }
    },

    filter: () => {
        ReportPromo.getData();
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
            lengthChange: false,
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
                url: url.base_url(ReportPromo.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ReportPromo.csrf_token(),
                },
                data: function (d) {
                    d.principal = $("#filter-principal").val();
                },
            },
            deferRender: true,
            dom: "Bftrip",
            buttons: [
                {
                    extend: "excel",
                    filename: "ReportPromo",
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
                    data: "promo_name",
                    title: "Promo Name",
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
                    title: "Product Code",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "product_name",
                    title: "Product Name",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "min_qty",
                    title: "Qty Min",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "max_qty",
                    title: "Qty Max",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "discount_type",
                    title: "Discount Type",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "discount_value",
                    title: "Discount Value",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "date_start",
                    title: "Date Start",
                },
                {
                    data: "date_end",
                    title: "Date End",
                },
                {
                    data: "status_aktif",
                    title: "Status",
                    render: function (data, type, row) {
                        return data ? "Aktif" : "Tidak Aktif";
                    },
                },
                {
                    data: "beban",
                    title: "Beban",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "kategori_disc",
                    title: "Kategori Disc",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    data: "syarat_count",
                    title: "Syarat Count",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "free_count",
                    title: "Free Count",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
            ],
        });

        $(".dataTables_length select").addClass("form-select form-select-sm");
    },
};

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
    ReportPromo.setSelect2();
    ReportPromo.getData();
});
