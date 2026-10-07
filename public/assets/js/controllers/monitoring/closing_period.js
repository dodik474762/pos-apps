let ClosingPeriod = {
    module: () => {
        return "monitoring/closing-period";
    },

    moduleApi: () => {
        return "api/" + ClosingPeriod.module();
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    tabs: () => {
        return window.CLOSING_PERIOD_TABS || {};
    },

    types: () => {
        return Object.keys(ClosingPeriod.tabs());
    },

    periodId: () => {
        return new URLSearchParams(window.location.search).get("period_id") || "";
    },

    changePeriod: (elm) => {
        let params = new URLSearchParams(window.location.search);
        let value = $(elm).val();
        if (value) {
            params.set("period_id", value);
        } else {
            params.delete("period_id");
        }
        window.location.search = params.toString();
    },

    formatMoney: (num) => {
        return (Number(num) || 0).toLocaleString("id-ID", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    },

    formatInteger: (num) => {
        return (Number(num) || 0).toLocaleString("id-ID");
    },

    formatStatus: (data) => {
        let status = (data || "").toString().toUpperCase();
        let badge = "bg-secondary";
        if (["COMPLETED", "POSTED", "PAID", "PACKED", "CONFIRMED", "APPROVED"].includes(status)) {
            badge = "bg-success";
        } else if (["CANCELLED", "CANCELED"].includes(status)) {
            badge = "bg-danger";
        } else if (["PARTIAL PAID", "PENDING"].includes(status)) {
            badge = "bg-info";
        } else if (["DRAFT", "OPEN", "NOT PAID"].includes(status)) {
            badge = "bg-warning";
        }
        return `<span class="badge ${badge}">${data ?? ""}</span>`;
    },

    buildColumns: (type) => {
        let config = ClosingPeriod.tabs()[type] || {};
        return (config.columns || []).map((column, index) => {
            if (index === 0 || column.type === "index") {
                return {
                    data: column.field,
                    render: (data, display, row, meta) =>
                        meta.row + meta.settings._iDisplayStart + 1,
                };
            }
            if (column.type === "money") {
                return {
                    data: column.field,
                    render: (data) => ClosingPeriod.formatMoney(data),
                };
            }
            if (column.type === "integer") {
                return {
                    data: column.field,
                    render: (data) => ClosingPeriod.formatInteger(data),
                };
            }
            if (column.type === "status") {
                return {
                    data: column.field,
                    render: (data) => ClosingPeriod.formatStatus(data),
                };
            }
            if (column.type === "check") {
                return {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: (data, display, row) =>
                        `<input type="checkbox" class="form-check-input row-check" value="${row.id}" data-type="${type}" onchange="ClosingPeriod.updateSelected('${type}')">`,
                };
            }
            return { data: column.field };
        });
    },

    getData: (type) => {
        let tableData = $("table#table-" + type);
        if (tableData.length === 0) {
            return;
        }

        tableData.DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            autoWidth: false,
            order: [],
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
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                ClosingPeriod.updateSelected(type);
            },
            ajax: {
                url: url.base_url(ClosingPeriod.moduleApi()) + "getData",
                type: "POST",
                data: {
                    type: type,
                    period_id: ClosingPeriod.periodId(),
                },
                headers: {
                    "X-CSRF-TOKEN": ClosingPeriod.csrf_token(),
                },
            },
            deferRender: true,
            columns: ClosingPeriod.buildColumns(type),
        });

        $(".dataTables_length select").addClass("form-select form-select-sm");
    },

    checkAll: (elm, type) => {
        let checked = $(elm).is(":checked");
        $("#list-data-" + type + " input.row-check").prop("checked", checked);
        ClosingPeriod.updateSelected(type);
    },

    updateSelected: (type) => {
        let total = $("#list-data-" + type + " input.row-check").length;
        let selected = $("#list-data-" + type + " input.row-check:checked").length;
        $("#selected-" + type).text(selected + " dokumen dipilih");
        $("#check-all-" + type).prop("checked", total > 0 && total === selected);
    },

    checkedIds: (type) => {
        let ids = [];
        $("#list-data-" + type + " input.row-check:checked").each(function () {
            ids.push($(this).val());
        });
        return ids;
    },

    postOne: (post, id) => {
        return new Promise((resolve, reject) => {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: { id: id },
                url: url.base_url(post.module) + post.action,
                headers: {
                    "X-CSRF-TOKEN": ClosingPeriod.csrf_token(),
                },
                success: (resp) => resolve(resp),
                error: () => reject(new Error("Gagal memproses dokumen")),
            });
        });
    },

    post: (type, e) => {
        if (e) {
            e.preventDefault();
        }
        let config = ClosingPeriod.tabs()[type] || {};
        if (!config.post) {
            return;
        }

        let ids = ClosingPeriod.checkedIds(type);
        if (ids.length === 0) {
            message.sweetError("Informasi", "Pilih dokumen yang akan diposting");
            return;
        }

        Swal.fire({
            title: "Posting Jurnal",
            html: "Posting <b>" + ids.length + "</b> dokumen " + config.label + "?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Posting",
            cancelButtonText: "Batal",
        }).then(async (result) => {
            if (!result.isConfirmed) {
                return;
            }

            message.loadingProses("Proses Posting " + ids.length + " Dokumen...");
            let posted = 0;
            let errors = [];
            for (const id of ids) {
                try {
                    let resp = await ClosingPeriod.postOne(config.post, id);
                    if (resp && resp.is_valid) {
                        posted++;
                    } else {
                        errors.push((resp && resp.message) || "Dokumen tidak valid diposting");
                    }
                } catch (err) {
                    errors.push(err.message || "Gagal memproses dokumen");
                }
            }
            message.closeLoading();

            let text = posted + " dari " + ids.length + " dokumen berhasil diposting";
            if (errors.length > 0) {
                text += ", " + errors.length + " gagal (" + [...new Set(errors)].join("; ") + ")";
            }
            if (posted > 0) {
                message.sweetSuccess("Informasi", text);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                message.sweetError("Informasi", text);
            }
        });
    },

    showTab: (type) => {
        let tab = $("#tab-" + type);
        if (tab.length === 0) {
            return;
        }
        tab.tab("show");
        let target = $("#closingPeriodList");
        if (target.length > 0) {
            $("html, body").animate({ scrollTop: target.offset().top - 80 }, 300);
        }
    },
};

$(function () {
    $.each(ClosingPeriod.types(), function (index, type) {
        ClosingPeriod.getData(type);
    });
});
