let objInput = null;
let InventoryValuation = {
    module: () => {
        return "report/inventory-valuation";
    },

    moduleApi: () => {
        return "api/" + InventoryValuation.module();
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    getData: async () => {
        let tableData = $("table#table-data");

        let data = tableData.DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            autoWidth: false,
            destroy: true,
            orderCellsTop: false, // urutan memakai baris header kedua (nama kolom)
            order: [[1, "asc"]],
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
                emptyTable: "Tidak ada mutasi stok pada periode ini",
                zeroRecords: "Data tidak ditemukan",
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass(
                    "pagination-rounded",
                );
                InventoryValuation.getSummary();
            },
            ajax: {
                url: url.base_url(InventoryValuation.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": InventoryValuation.csrf_token(),
                },
                data: function (d) {
                    d.date_start = $("#filter-tanggal-awal").val();
                    d.date_end = $("#filter-tanggal").val();
                },
            },
            deferRender: true,
            dom: "Bftrip",
            buttons: [
                {
                    extend: "excel",
                    className: "btn btn-sm btn-soft-success",
                    text: '<i class="ri-file-excel-2-line align-bottom me-1"></i> Export Excel',
                    filename: "InventoryValuation",
                    action: newexportaction,
                },
            ],
            columns: [
                {
                    // No
                    data: "id",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                { data: "item_code", render: (d) => d ?? "" },
                { data: "item_name", render: (d) => d ?? "-" },
                { data: "warehouse_name", render: (d) => d ?? "-" },
                { data: "trans_date" },
                {
                    // Opening Balance
                    data: "opening_balance",
                    className: "text-end g",
                    render: (d) => fmtQty(d),
                },
                {
                    // In = qty_in + qty_transfer_in + qty_return_in
                    data: "qty_in",
                    className: "text-end",
                    render: function (data, type, row) {
                        let total =
                            toFloat(row.qty_in) +
                            toFloat(row.qty_transfer_in) +
                            toFloat(row.qty_return_in);
                        return fmtQty(total, "iv-in");
                    },
                },
                {
                    // Out (termasuk transfer out)
                    data: "qty_out",
                    className: "text-end",
                    render: (d) => fmtQty(d, "iv-out"),
                },
                {
                    // Adjust
                    data: "qty_adjust",
                    className: "text-end",
                    render: (d) => fmtQty(d, "", true),
                },
                {
                    // Ending Balance
                    data: "closing_balance",
                    className: "text-end",
                    render: (d) => `<strong>${formatQty(d)}</strong>`,
                },
                {
                    // Opening Value
                    data: "opening_value",
                    className: "text-end g",
                    render: (d) => fmtVal(d),
                },
                {
                    // Unit Cost
                    data: "unit_cost",
                    className: "text-end",
                    render: (d) => formatCost(d),
                },
                {
                    // Value In
                    data: "value_in",
                    className: "text-end",
                    render: (d) => fmtVal(d, "iv-in"),
                },
                {
                    // Value Out
                    data: "value_out",
                    className: "text-end",
                    render: (d) => fmtVal(d, "iv-out"),
                },
                {
                    // Nominal (negatif = merah, positif = hijau)
                    data: "nominal_value",
                    className: "text-end",
                    render: function (data) {
                        let v = toFloat(data);
                        if (v === 0) return `<span class="iv-zero">-</span>`;
                        return `<span class="${v < 0 ? "iv-out" : "iv-in"}">${formatNumber(v)}</span>`;
                    },
                },
                {
                    // Closing Value
                    data: "closing_value",
                    className: "text-end",
                    render: (d) => `<strong>${formatNumber(d)}</strong>`,
                },
                {
                    // Avg Cost
                    data: "avg_cost",
                    className: "text-end",
                    render: (d) => formatCost(d),
                },
                {
                    // Cost Source
                    data: "cost_source",
                    className: "g",
                    render: function (data) {
                        if (!data) return "-";
                        return `<span class="iv-tag ${data}">${data}</span>`;
                    },
                },
                { data: "note", render: (d) => d ?? "-" },
                {
                    // Reference Type -> nama kelas saja, mis. "Delivery Order Header"
                    data: "reference_type",
                    render: function (data) {
                        if (!data) return "-";
                        let name = data.split("\\").pop();
                        name = name.replace(/([a-z])([A-Z])/g, "$1 $2");
                        return `<span class="iv-tag">${name}</span>`;
                    },
                },
            ],
        });

        data.buttons()
            .container()
            .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)");

        $(".dataTables_length select").addClass("form-select form-select-sm");
    },

    getSummary: async () => {
        try {
            let response = await fetch(
                url.base_url(InventoryValuation.moduleApi()) + `getSummary`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": InventoryValuation.csrf_token(),
                    },
                    body: JSON.stringify({
                        date_start: $("#filter-tanggal-awal").val(),
                        date_end: $("#filter-tanggal").val(),
                        search: {
                            value: $("table#table-data").DataTable()
                                ? $("table#table-data").DataTable().search()
                                : "",
                        },
                    }),
                },
            );

            if (!response.ok) {
                return;
            }

            let total = await response.json();

            // Footer tabel
            $("#total-qty-in").text(formatNumber(total.qty_in));
            $("#total-qty-out").text(formatNumber(total.qty_out));
            $("#total-qty-adjust").text(formatNumber(total.qty_adjust));
            $("#total-opening-value").text(formatNumber(total.opening_value));
            $("#total-value-in").text(formatNumber(total.value_in));
            $("#total-value-out").text(formatNumber(total.value_out));
            $("#total-nominal-value").text(formatNumber(total.nominal_value));
            $("#total-closing-value").text(formatNumber(total.closing_value));

            // Kartu ringkasan
            $("#sum-qty-in").text(formatNumber(total.qty_in));
            $("#sum-qty-out").text(formatNumber(total.qty_out));
            $("#sum-value-in").text(formatNumber(total.value_in));
            $("#sum-value-out").text(formatNumber(total.value_out));
            $("#sum-nominal").text(formatNumber(total.nominal_value));
            $("#sum-closing").text(formatNumber(total.closing_value));
        } catch (e) {
            console.log(e);
        }
    },

    filter: (elm) => {
        const tanggal = $("#filter-tanggal").val();
        const route = $(elm).attr("route");
        const tanggal_awal = $("#filter-tanggal-awal").val();
        window.location.href = route + "?tanggal=" + tanggal + "&date_start=" + tanggal_awal;
    },
};

function newexportaction(e, dt, button, config) {
    var self = this;
    var oldStart = dt.settings()[0]._iDisplayStart;
    dt.one("preXhr", function (e, s, data) {
        data.start = 0;
        data.length = 2147483647;
        dt.one("preDraw", function (e, settings) {
            if (button[0].className.indexOf("buttons-excel") >= 0) {
                $(dt.table.container()).find("input[name=export]").val("Y");
            } else {
                dt.button(button).trigger("export");
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

const toFloat = (value) => {
    let num = parseFloat(value ?? 0);
    return isNaN(num) ? 0 : num;
};

const formatNumber = (num) => {
    return parseFloat(num || 0)
        .toFixed(0)
        .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

// Qty: tampilkan desimal hanya jika ada (maks 2)
const formatQty = (num) => {
    let v = parseFloat(num || 0);
    let s = Number.isInteger(v) ? v.toFixed(0) : v.toFixed(2).replace(/0+$/, "");
    return s.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

const formatCost = (num) => {
    return parseFloat(num || 0)
        .toFixed(2)
        .replace(/\B(?=(\d{3})+(?!\d)\.)/g, ",");
};

// Nol tampil "-" redup; sisanya diberi warna kelas (iv-in / iv-out)
const fmtQty = (num, cls = "", signed = false) => {
    let v = toFloat(num);
    if (v === 0) return `<span class="iv-zero">-</span>`;
    let c = signed ? (v < 0 ? "iv-out" : "iv-in") : cls;
    return `<strong class="${c}">${formatQty(v)}</strong>`;
};

const fmtVal = (num, cls = "") => {
    let v = toFloat(num);
    if (v === 0) return `<span class="iv-zero">-</span>`;
    return `<span class="${cls}">${formatNumber(v)}</span>`;
};

$(function () {
    InventoryValuation.getData();
});