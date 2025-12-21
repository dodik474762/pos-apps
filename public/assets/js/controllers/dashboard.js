let map = null;
let markers = [];
var group = null;

let Dashboard = {
    module: () => {
        return "dashboard";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + Dashboard.module();
    },

    moduleSOApi: () => {
        return "api/transaksi/sales_order";
    },

    moduleAuthApi: () => {
        return "api/auth";
    },

    changeDefaultGroup: (elm, e) => {
        e.preventDefault();
        const params = {
            group: $(elm).attr("data_id"),
            group_name: $(elm).text().trim(),
        };

        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(Dashboard.moduleAuthApi()) + "changeSession",
            beforeSend: () => {
                message.loadingProses("Proses Pengambilan Data...");
            },
            error: function () {
                message.closeLoading();
            },

            success: function (resp) {
                message.closeLoading();
                if (resp.is_valid) {
                    window.location.reload();
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

     getDataSo: async () => {
        let tableData = $("table#table-data-so");

        let updateAction = $("#update").val();
        let deleteAction = $("#delete").val();

        var data = tableData.DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            autoWidth: false,
            order: [[0, "asc"]],
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
                url: url.base_url(Dashboard.moduleSOApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": Dashboard.csrf_token(),
                },
            },
            deferRender: true,
            createdRow: function (row, data, dataIndex) {
                // console.log('row', $(row));
            },
            buttons: ["copy", "excel", "pdf", "colvis"],
            columns: [
                {
                    data: "id",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                {
                    data: "so_number",
                },
                {
                    data: "so_date",
                },
                {
                    data: "nama_customer",
                },
                {
                    data: "total_amount",
                },
                {
                    data: "currency_code",
                },
                {
                    data: "created_by_name",
                },
                {
                    data: "status",
                },
                {
                    data: "platform",
                },
            ],
        });

        data
            .buttons()
            .container()
            .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"),
            $(".dataTables_length select").addClass(
                "form-select form-select-sm"
            ),
            $("#selection-datatable").DataTable({
                select: {
                    style: "multi",
                },
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
            });
    },

     getGrafikPenjualan: (elm) => {
        let params = {};
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(Dashboard.moduleApi()) + "getGrafikPenjualan",
            headers: {
                "X-CSRF-TOKEN": Dashboard.csrf_token(),
            },
            beforeSend: () => {
                $("div#penjualan_chart")
                    .html(`<div class="text-center mb-3"><div class="spinner-border text-primary" role="status">
                                                            <span class="sr-only">Loading...</span>
                                                        </div></div>`);
            },
            error: function () {
                $("div#penjualan_chart").html(``);
            },

            success: function (resp) {
                $("div#penjualan_chart").html(``);
                if (resp.is_valid) {
                    Dashboard.setGrafikPenjualan(resp);
                }
            },
        });
    },

     getChartColorsArray: (e) => {
        if (null !== document.getElementById(e)) {
            var t = document.getElementById(e).getAttribute("data-colors");
            if (t)
                return (t = JSON.parse(t)).map(function (e) {
                    var t = e.replace(" ", "");
                    return -1 === t.indexOf(",")
                        ? getComputedStyle(
                              document.documentElement
                          ).getPropertyValue(t) || t
                        : 2 == (e = e.split(",")).length
                        ? "rgba(" +
                          getComputedStyle(
                              document.documentElement
                          ).getPropertyValue(e[0]) +
                          "," +
                          e[1] +
                          ")"
                        : t;
                });
        }
    },

    setGrafikPenjualan: (data) => {
        const linechartcustomerColors =
            Dashboard.getChartColorsArray("penjualan_chart");
        const options = {
            series: [
                {
                    name: "Penjualan OK",
                    type: "bar",
                    data: data.so_ok,
                },
                {
                    name: "Penjualan Cancel",
                    type: "bar",
                    data: data.so_cancel,
                },
            ],
            chart: { height: 370, type: "line", toolbar: { show: !1 } },
            stroke: {
                curve: "straight",
                dashArray: [0, 0, 8],
                width: [2, 0, 2.2],
            },
            fill: { opacity: [0.1, 0.9, 1] },
            markers: {
                size: [0, 0, 0],
                strokeWidth: 2,
                hover: { size: 4 },
            },
            xaxis: {
                categories: [
                    "Jan",
                    "Feb",
                    "Mar",
                    "Apr",
                    "May",
                    "Jun",
                    "Jul",
                    "Aug",
                    "Sep",
                    "Oct",
                    "Nov",
                    "Dec",
                ],
                axisTicks: { show: !1 },
                axisBorder: { show: !1 },
            },
            grid: {
                show: !0,
                xaxis: { lines: { show: !0 } },
                yaxis: { lines: { show: !1 } },
                padding: { top: 0, right: -2, bottom: 15, left: 10 },
            },
            legend: {
                show: !0,
                horizontalAlign: "center",
                offsetX: 0,
                offsetY: -5,
                markers: { width: 9, height: 9, radius: 6 },
                itemMargin: { horizontal: 10, vertical: 0 },
            },
            plotOptions: { bar: { columnWidth: "30%", barHeight: "70%" } },
            colors: linechartcustomerColors,
            tooltip: {
                shared: !0,
                y: [
                    {
                        formatter: function (e) {
                            return void 0 !== e ? e.toFixed(0) : e;
                        },
                    },
                    {
                        formatter: function (e) {
                            return void 0 !== e ? "" + e.toFixed(2) + "" : e;
                        },
                    },
                    {
                        formatter: function (e) {
                            return void 0 !== e ? e.toFixed(0) + " " : e;
                        },
                    },
                ],
            },
        };

        const chart = new ApexCharts(
            document.querySelector("#penjualan_chart"),
            options
        ).render();
    },
};

$(function () {
    Dashboard.getDataSo();
    Dashboard.getGrafikPenjualan();
});
