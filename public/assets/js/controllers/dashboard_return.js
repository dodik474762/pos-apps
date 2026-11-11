const dtReturnLanguage = {
    paginate: {
        previous: "<i class='mdi mdi-chevron-left'>",
        next: "<i class='mdi mdi-chevron-right'>",
    },
    emptyTable: "Belum ada data retur untuk ditampilkan",
    zeroRecords: "Data retur tidak ditemukan",
    info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
    infoEmpty: "Tidak ada data",
    infoFiltered: "(difilter dari _MAX_ data)",
    processing: "Memuat data...",
    search: "Cari:",
};

let DashboardReturn = {
    module: () => {
        return "dashboard/return";
    },

    moduleApi: () => {
        return "api/dashboard/return";
    },

    currentDetailId: null,

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    escape: (value) => {
        if (value === null || value === undefined) return "";
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    },

    formatNumber: (value, decimals = 0) => {
        const number = parseFloat(value);
        if (isNaN(number)) return "0";

        return number.toLocaleString("id-ID", {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    },

    statusBadge: (status) => {
        const key = String(status || "").toUpperCase();
        const map = {
            POSTED: "success",
            PACKED: "info",
            DRAFT: "warning",
            CANCELLED: "danger",
            CANCELED: "danger",
        };
        const color = map[key] || "secondary";
        const label = DashboardReturn.escape(status || "-");

        return `<span class="badge bg-${color}-subtle text-${color} text-capitalize">${label}</span>`;
    },

    getData: () => {
        const tableData = $("table#table-data-return");

        tableData.DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            autoWidth: false,
            order: [[2, "desc"]],
            aLengthMenu: [
                [25, 50, 100],
                [25, 50, 100],
            ],
            lengthChange: false,
            language: dtReturnLanguage,
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            ajax: {
                url: url.base_url(DashboardReturn.moduleApi()) + "getData",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": DashboardReturn.csrf_token(),
                },
                data: function (d) {
                    d.start_date = $("#start_date").val();
                    d.end_date = $("#end_date").val();
                },
            },
            deferRender: true,
            dom: "Bftrip",
            buttons: [
                {
                    extend: "excel",
                    text: '<i class="ri-file-excel-2-line align-bottom me-1"></i> Export Excel',
                    filename: "Daftar Sales Return",
                    action: newexportaction,
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                    },
                },
            ],
            columns: [
                {
                    data: "id",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                {
                    data: "return_number",
                    render: function (data) {
                        return `<span class="fw-semibold">${DashboardReturn.escape(data)}</span>`;
                    },
                },
                {
                    data: "return_date",
                },
                {
                    data: "invoice_number",
                    render: function (data) {
                        return DashboardReturn.escape(data || "-");
                    },
                },
                {
                    data: "nama_customer",
                    render: function (data, type, row) {
                        const code = row.customer_code ? DashboardReturn.escape(row.customer_code) + " - " : "";
                        return code + DashboardReturn.escape(data || "-");
                    },
                },
                {
                    data: "total_qty",
                    className: "text-end",
                    render: function (data) {
                        return DashboardReturn.formatNumber(data, 2);
                    },
                },
                {
                    data: "return_type",
                    render: function (data) {
                        return `<span class="badge bg-secondary-subtle text-secondary">${DashboardReturn.escape(data || "-")}</span>`;
                    },
                },
                {
                    data: "status",
                    render: function (data) {
                        return DashboardReturn.statusBadge(data);
                    },
                },
                {
                    data: "canceled_item_count",
                    className: "text-center",
                    render: function (data, type, row) {
                        const count = parseInt(data) || 0;
                        if (count === 0) return `<span class="text-muted">-</span>`;

                        return `<span class="badge bg-warning-subtle text-warning" title="Qty batal: ${DashboardReturn.formatNumber(row.canceled_qty, 2)}">${count} item</span>`;
                    },
                },
                {
                    data: "received_date",
                    render: function (data) {
                        return DashboardReturn.escape((data || "").toString().slice(0, 10) || "-");
                    },
                },
                {
                    data: "received_by_name",
                    render: function (data) {
                        return DashboardReturn.escape(data || "-");
                    },
                },
                {
                    data: "created_by_name",
                    render: function (data) {
                        return DashboardReturn.escape(data || "-");
                    },
                },
                {
                    data: "id",
                    className: "text-center",
                    orderable: false,
                    render: function (data) {
                        return `<button type="button" class="btn btn-sm btn-soft-info" onclick="DashboardReturn.showDetail(${parseInt(data)})">
                            <i class="ri-eye-line align-bottom me-1"></i> Detail
                        </button>`;
                    },
                },
            ],
        });

        $(".dataTables_length select").addClass("form-select form-select-sm");
    },

    getChartColorsArray: (e) => {
        if (null !== document.getElementById(e)) {
            var t = document.getElementById(e).getAttribute("data-colors");
            if (t)
                return (t = JSON.parse(t)).map(function (e) {
                    var t = e.replace(" ", "");
                    return -1 === t.indexOf(",")
                        ? getComputedStyle(document.documentElement).getPropertyValue(t) || t
                        : 2 == (e = e.split(",")).length
                            ? "rgba(" +
                            getComputedStyle(document.documentElement).getPropertyValue(e[0]) +
                            "," +
                            e[1] +
                            ")"
                            : t;
                });
        }
    },

    getGrafik: () => {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                start_date: $("#start_date").val(),
                end_date: $("#end_date").val(),
            },
            url: url.base_url(DashboardReturn.moduleApi()) + "getGrafik",
            headers: {
                "X-CSRF-TOKEN": DashboardReturn.csrf_token(),
            },
            beforeSend: () => {
                $("div#return_chart").html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div></div>`);
            },
            error: function () {
                $("div#return_chart").html("");
            },
            success: function (resp) {
                $("div#return_chart").html("");
                if (resp.is_valid) {
                    DashboardReturn.setGrafik(resp.data);
                }
            },
        });
    },

    setGrafik: (data) => {
        const colors = DashboardReturn.getChartColorsArray("return_chart");
        const options = {
            series: [
                {
                    name: "Jumlah Retur",
                    type: "bar",
                    data: data.jumlah,
                },
            ],
            chart: {
                height: 350,
                type: "bar",
                toolbar: { show: false },
                fontFamily: "inherit",
            },
            dataLabels: { enabled: false },
            stroke: { width: 0, curve: "smooth" },
            fill: { opacity: 1 },
            xaxis: {
                categories: data.labels,
                axisTicks: { show: false },
                axisBorder: { show: false },
            },
            yaxis: {
                title: { text: "Jumlah Retur" },
                labels: {
                    formatter: function (v) {
                        return Number(v).toLocaleString("id-ID");
                    },
                },
            },
            grid: {
                show: true,
                borderColor: "rgba(135,138,153,.2)",
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { top: 0, right: 10, bottom: 15, left: 10 },
            },
            legend: {
                show: true,
                position: "top",
                horizontalAlign: "right",
                offsetX: 0,
                offsetY: -5,
                markers: { width: 9, height: 9, radius: 6 },
                itemMargin: { horizontal: 10, vertical: 0 },
            },
            plotOptions: {
                bar: { columnWidth: "38%", borderRadius: 4 },
            },
            colors: colors,
            tooltip: {
                y: {
                    formatter: function (e) {
                        return void 0 !== e ? Number(e).toLocaleString("id-ID") + " retur" : e;
                    },
                },
            },
        };

        new ApexCharts(document.querySelector("#return_chart"), options).render();
    },

    showDetail: (id) => {
        DashboardReturn.currentDetailId = id;

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id: id,
            },
            url: url.base_url(DashboardReturn.moduleApi()) + "getDetail",
            headers: {
                "X-CSRF-TOKEN": DashboardReturn.csrf_token(),
            },
            beforeSend: () => {
                $("#return-detail-header").html("");
                $("#return-detail-items").html(`<tr><td colspan="5" class="text-center text-muted">Memuat data...</td></tr>`);
                $("#return-canceled-items").html(`<tr><td colspan="5" class="text-center text-muted">Memuat data...</td></tr>`);
            },
            error: function () {
                DashboardReturn.sweetError("Informasi", "Gagal mengambil detail retur.");
            },
            success: function (resp) {
                if (!resp.is_valid) {
                    DashboardReturn.sweetError("Informasi", resp.message || "Data retur tidak ditemukan.");
                    return;
                }

                DashboardReturn.renderDetail(resp);

                const modalElement = document.getElementById("return-detail-modal");
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.show();
            },
        });
    },

    confirmReceived: () => {
        const id = DashboardReturn.currentDetailId;
        if (!id) {
            DashboardReturn.sweetError("Informasi", "Data retur tidak ditemukan.");
            return;
        }

        Swal.fire({
            title: "Konfirmasi Received",
            text: "Tandai barang retur ini sudah diterima gudang?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Ya, Received",
            cancelButtonText: "Batal",
            confirmButtonColor: "#0ab39c",
        }).then(function (result) {
            if (!result.isConfirmed) return;

            const button = $("#btn-received-return");
            const originalText = button.html();
            button.prop("disabled", true).html(
                '<span class="spinner-border spinner-border-sm align-middle me-1"></span> Memproses...'
            );

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    id: id,
                },
                url: url.base_url(DashboardReturn.moduleApi()) + "confirmReceived",
                headers: {
                    "X-CSRF-TOKEN": DashboardReturn.csrf_token(),
                },
                error: function () {
                    button.prop("disabled", false).html(originalText);
                    DashboardReturn.sweetError("Informasi", "Gagal memproses received.");
                },
                success: function (resp) {
                    button.prop("disabled", false).html(originalText);

                    if (!resp.is_valid) {
                        DashboardReturn.sweetError("Informasi", resp.message || "Gagal memproses received.");
                        return;
                    }

                    const modalElement = document.getElementById("return-detail-modal");
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) modal.hide();

                    $("table#table-data-return").DataTable().ajax.reload(null, false);

                    const successMessage = resp.message || "Barang retur berhasil diterima.";
                    if (window.message && typeof window.message.sweetSuccess === "function") {
                        window.message.sweetSuccess("Informasi", successMessage);
                    } else if (typeof Swal !== "undefined") {
                        Swal.fire({
                            title: "Informasi",
                            text: successMessage,
                            icon: "success",
                            confirmButtonColor: "#5664d2",
                        });
                    }
                },
            });
        });
    },

    renderDetail: (resp) => {
        const header = resp.header || {};
        const fields = [
            ["No. Retur", header.return_number],
            ["Tanggal Retur", header.return_date],
            ["No. Invoice", header.invoice_number || "-"],
            ["Tanggal Invoice", header.invoice_date || "-"],
            ["Customer", (header.customer_code ? header.customer_code + " - " : "") + (header.nama_customer || "-")],
            ["Tipe Retur", header.return_type || "-"],
            ["Status", header.status || "-"],
            ["Platform", header.platform || "-"],
            ["Dibuat Oleh", header.created_by_name || "-"],
            ["Alasan", header.reason || "-"],
        ];

        let headerHtml = "";
        fields.forEach(function (field) {
            headerHtml += `
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <div class="detail-label">${DashboardReturn.escape(field[0])}</div>
                    <div class="detail-value">${DashboardReturn.escape(field[1])}</div>
                </div>
            `;
        });
        $("#return-detail-header").html(headerHtml);
        $("#return-detail-subtitle").text(
            `${header.return_number || ""} · ${header.invoice_number || "-"} · ${header.nama_customer || "-"}`
        );

        let returnItems = "";
        if (resp.return_details && resp.return_details.length > 0) {
            resp.return_details.forEach(function (item) {
                returnItems += `
                    <tr>
                        <td>${DashboardReturn.escape(item.product_code || "-")}</td>
                        <td>${DashboardReturn.escape(item.product_name || "-")}</td>
                        <td class="text-end">${DashboardReturn.formatNumber(item.qty_return, 2)}</td>
                        <td>${DashboardReturn.escape(item.unit_name || "-")}</td>
                        <td>${DashboardReturn.escape(item.invoice_detail_id || "-")}</td>
                    </tr>
                `;
            });
        } else {
            returnItems = `<tr><td colspan="5" class="text-center text-muted">Tidak ada detail barang retur.</td></tr>`;
        }
        $("#return-detail-items").html(returnItems);

        let canceledItems = "";
        if (resp.canceled_items && resp.canceled_items.length > 0) {
            resp.canceled_items.forEach(function (item) {
                canceledItems += `
                    <tr>
                        <td>${DashboardReturn.escape(item.product_code || "-")}</td>
                        <td>${DashboardReturn.escape(item.product_name || "-")}</td>
                        <td class="text-end">${DashboardReturn.formatNumber(item.qty, 2)}</td>
                        <td>${DashboardReturn.escape(item.unit_name || "-")}</td>
                        <td class="text-end">${DashboardReturn.formatNumber(item.return_qty, 2)}</td>
                    </tr>
                `;
            });
        } else {
            canceledItems = `<tr><td colspan="5" class="text-center text-muted">Tidak ada item invoice yang dibatalkan.</td></tr>`;
        }
        $("#return-canceled-items").html(canceledItems);
    },

    sweetError: (title, message) => {
        if (typeof message_ !== "undefined" && message_ && typeof message_.sweetError === "function") {
            message_.sweetError(title, message);
            return;
        }
        if (typeof message !== "undefined" && message && typeof message.sweetError === "function") {
            message.sweetError(title, message);
            return;
        }
        alert(message);
    },
};

// Export seluruh data mengikuti filter aktif (server-side DataTables)
function newexportaction(e, dt, button, config) {
    var self = this;
    var oldStart = dt.settings()[0]._iDisplayStart;
    dt.one("preXhr", function (e, s, data) {
        data.start = 0;
        data.length = 2147483647;
        dt.one("preDraw", function (e, settings) {
            if (button[0].className.indexOf("buttons-excel") >= 0) {
                $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config)
                    ? $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config)
                    : $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
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
    if ($("table#table-data-return").length) DashboardReturn.getData();
    if ($("div#return_chart").length) DashboardReturn.getGrafik();
});
