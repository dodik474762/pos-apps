let Journals = {
    module: () => {
        return "transaksi/journals";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + Journals.module();
    },

    setSelect2: () => {
        if ($(".select2").length > 0) {
            $.each($(".select2"), function () {
                $(this).select2();
            });
        }
    },

    cancel: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(Journals.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(Journals.module()) + "add";
    },

    back: (elm) => {
        window.location.href = url.base_url(Journals.module()) + "/";
    },

    addDetail: (rowData, e) => {
        if (e) {
            e.preventDefault();
        }
        let template = $("#template-detail").html();
        let row = $(template);
        $("#tbody-detail").append(row);
        Journals.initSelect2Detail(row);

        if (rowData) {
            row.find('[name="account_id"]').val(rowData.account_id);
            row.find('[name="description"]').val(rowData.description);
            row.find('[name="debit"]').val(rowData.debit);
            row.find('[name="credit"]').val(rowData.credit);
        }

        Journals.refreshLineNo();
        Journals.calculateTotal();
    },

    removeDetail: (elm) => {
        $(elm).closest("tr").remove();
        Journals.refreshLineNo();
        Journals.calculateTotal();
    },

    initSelect2Detail: (row) => {
        if (typeof $.fn.select2 === "undefined") {
            return;
        }
        row.find(".select2-detail").each(function () {
            $(this).select2({ width: "100%" });
        });
    },

    refreshLineNo: () => {
        $("#tbody-detail tr").each(function (index) {
            $(this).find(".td-line").text(index + 1);
        });
    },

    toAmount: (value) => {
        let number = parseFloat(value);
        return isNaN(number) ? 0 : Math.round(number * 100) / 100;
    },

    getDetailInput: () => {
        let details = [];
        $("#tbody-detail tr").each(function () {
            let debit = Journals.toAmount($(this).find('[name="debit"]').val());
            let credit = Journals.toAmount($(this).find('[name="credit"]').val());
            let accountId = $(this).find('[name="account_id"]').val();
            if (accountId == "" && debit <= 0 && credit <= 0) {
                return;
            }
            details.push({
                account_id: accountId == "" ? "" : accountId,
                description: $(this).find('[name="description"]').val(),
                debit: debit,
                credit: credit,
            });
        });
        return details;
    },

    getPostInput: () => {
        let data = {
            id: $("input#id").val(),
            journal_date: $("#journal_date").val(),
            reference_type: $("#reference_type").val(),
            reference_id: $("#reference_id").val(),
            description: $("#description").val(),
            details: JSON.stringify(Journals.getDetailInput()),
        };

        return data;
    },

    isBalance: () => {
        let totalDebit = 0;
        let totalCredit = 0;
        Journals.getDetailInput().forEach(function (row) {
            totalDebit += row.debit;
            totalCredit += row.credit;
        });
        return Math.abs(totalDebit - totalCredit) < 0.001;
    },

    calculateTotal: () => {
        let totalDebit = 0;
        let totalCredit = 0;
        $("#tbody-detail tr").each(function () {
            totalDebit += Journals.toAmount($(this).find('[name="debit"]').val());
            totalCredit += Journals.toAmount($(this).find('[name="credit"]').val());
        });

        $("#total-debit").text(totalDebit.toLocaleString("id-ID", { minimumFractionDigits: 2 }));
        $("#total-credit").text(totalCredit.toLocaleString("id-ID", { minimumFractionDigits: 2 }));

        if (Math.abs(totalDebit - totalCredit) < 0.001) {
            $("#balance-alert").addClass("d-none");
        } else {
            $("#balance-alert").removeClass("d-none");
        }
    },

    submit: (elm, e) => {
        e.preventDefault();
        Journals.doSubmit("submit", elm);
    },

    saveAndPost: (elm, e) => {
        e.preventDefault();
        Journals.doSubmit("posted", elm);
    },

    doSubmit: (action, elm) => {
        let form = $(elm).closest("div.row");
        if (!validation.runWithElement(form)) {
            message.sweetError("Informasi", "Data Belum Lengkap");
            return;
        }
        if (Journals.getDetailInput().length < 2) {
            message.sweetError("Informasi", "Jurnal harus memiliki minimal 2 detail");
            return;
        }
        if (!Journals.isBalance()) {
            message.sweetError("Informasi", "Jurnal tidak balance, total debit harus sama dengan total credit");
            return;
        }

        let params = Journals.getPostInput();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(Journals.moduleApi()) + "submit",
            headers: {
                "X-CSRF-TOKEN": Journals.csrf_token(),
            },
            beforeSend: () => {
                message.loadingProses("Proses Simpan Data...");
            },
            error: function () {
                message.closeLoading();
                message.sweetError("Informasi", "Gagal");
            },
            success: function (resp) {
                message.closeLoading();
                if (!resp.is_valid) {
                    message.sweetError("Informasi", resp.message);
                    return;
                }

                if (action == "posted") {
                    Journals.doPost(resp.id);
                    return;
                }

                message.sweetSuccess();
                setTimeout(function () {
                    Journals.back();
                }, 1000);
            },
        });
    },

    post: (elm, e) => {
        e.preventDefault();
        let params = { id: $(elm).attr("data_id") };
        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(Journals.moduleApi()) + "showModalPost",
            headers: { "X-CSRF-TOKEN": Journals.csrf_token() },
            beforeSend: () => {
                message.loadingProses("Proses Pengambilan Data...");
            },
            error: function () {
                message.closeLoading();
                message.sweetError("Informasi", "Gagal");
            },
            success: function (resp) {
                message.closeLoading();
                $("#content-confirm-post").html(resp);
                $("#confirm-post-btn").trigger("click");
            },
        });
    },

    confirmPost: (elm) => {
        Journals.doPost($(elm).attr("data_id"));
    },

    doPost: (id) => {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: { id: id },
            url: url.base_url(Journals.moduleApi()) + "posted",
            headers: { "X-CSRF-TOKEN": Journals.csrf_token() },
            beforeSend: () => {
                message.loadingProses("Proses Posting Jurnal...");
            },
            error: function () {
                message.closeLoading();
                message.sweetError("Informasi", "Gagal");
            },
            success: function (resp) {
                message.closeLoading();
                if (resp.is_valid) {
                    message.sweetSuccess("Informasi", "Jurnal " + resp.journal_no + " Berhasil Diposting");
                    setTimeout(function () {
                        Journals.back();
                    }, 1000);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    reversal: (elm, e) => {
        e.preventDefault();
        let params = { id: $(elm).attr("data_id") };
        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(Journals.moduleApi()) + "showModalReversal",
            headers: { "X-CSRF-TOKEN": Journals.csrf_token() },
            beforeSend: () => {
                message.loadingProses("Proses Pengambilan Data...");
            },
            error: function () {
                message.closeLoading();
                message.sweetError("Informasi", "Gagal");
            },
            success: function (resp) {
                message.closeLoading();
                $("#content-confirm-reversal").html(resp);
                $("#confirm-reversal-btn").trigger("click");
            },
        });
    },

    confirmReversal: (elm) => {
        let params = {
            id: $(elm).attr("data_id"),
            journal_date: $("#reversal-date").val(),
            description: $("#reversal-description").val(),
        };
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(Journals.moduleApi()) + "reversal",
            headers: { "X-CSRF-TOKEN": Journals.csrf_token() },
            beforeSend: () => {
                message.loadingProses("Proses Reversal Jurnal...");
            },
            error: function () {
                message.closeLoading();
                message.sweetError("Informasi", "Gagal");
            },
            success: function (resp) {
                message.closeLoading();
                if (resp.is_valid) {
                    message.sweetSuccess("Informasi", "Jurnal Reversal " + resp.journal_no + " Berhasil Dibuat");
                    setTimeout(function () {
                        window.location.href = url.base_url(Journals.module()) + "detail?id=" + resp.journal_id;
                    }, 1000);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    getData: async () => {
        let tableData = $("table#table-data");

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
                url: url.base_url(Journals.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": Journals.csrf_token(),
                },
                data: function (d) {
                    SearchData(d);
                },
            },
            deferRender: true,
            createdRow: function (row, data, dataIndex) {},
            buttons: ["copy", "excel", "pdf", "colvis"],
            columns: [
                {
                    data: "id",
                    render: (data, type, row, meta) =>
                        meta.row + meta.settings._iDisplayStart + 1,
                },
                { data: "journal_no" },
                { data: "journal_date" },
                {
                    data: "period_month",
                    render: (data, type, row) =>
                        (data < 10 ? "0" + data : data) + " / " + row.period_year,
                },
                {
                    data: "reference_type",
                    render: (data, type, row) => {
                        if (!data) {
                            return "-";
                        }
                        return data + (row.reference_id ? " #" + row.reference_id : "");
                    },
                },
                { data: "description" },
                {
                    data: "status",
                    render: (data) => {
                        if (data == "POSTED") {
                            return '<span class="badge bg-success">POSTED</span>';
                        } else if (data == "REVERSED") {
                            return '<span class="badge bg-danger">REVERSED</span>';
                        }
                        return '<span class="badge bg-warning">DRAFT</span>';
                    },
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        if (row.status == "DRAFT" && updateAction == 1) {
                            html += `<a href='${url.base_url(Journals.module())}ubah?id=${data}' data_id="${row.id}" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (row.status == "DRAFT") {
                            html += `<button type="button" data_id="${row.id}" onclick="Journals.post(this, event)" class="btn btn-primary editable-submit btn-sm waves-effect waves-light"><i class="bx bx-check-circle"></i></button>&nbsp;`;
                            if (deleteAction == 1) {
                                html += `<button type="button" data_id="${row.id}" onclick="Journals.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>&nbsp;`;
                            }
                        }
                        if (row.status == "POSTED") {
                            html += `<button type="button" data_id="${row.id}" onclick="Journals.reversal(this, event)" class="btn btn-warning editable-submit btn-sm waves-effect waves-light"><i class="bx bx-reply"></i></button>&nbsp;`;
                        }
                        html += `<a href='${url.base_url(Journals.module())}detail?id=${data}' data_id="${row.id}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-search-alt"></i></a>`;
                        return html;
                    },
                },
            ],
        });

        data
            .buttons()
            .container()
            .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"),
            $(".dataTables_length select").addClass("form-select form-select-sm"),
            $("#selection-datatable").DataTable();
    },

    delete: (elm, e) => {
        e.preventDefault();
        let params = {};
        params.id = $(elm).attr("data_id");
        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(Journals.moduleApi()) + "delete",
            headers: { "X-CSRF-TOKEN": Journals.csrf_token() },
            beforeSend: () => {
                message.loadingProses("Proses Pengambilan Data...");
            },
            error: function () {
                message.closeLoading();
                message.sweetError("Informasi", "Gagal");
            },
            success: function (resp) {
                message.closeLoading();
                $("#content-confirm-delete").html(resp);
                $("#confirm-delete-btn").trigger("click");
            },
        });
    },

    confirmDelete: (elm) => {
        let params = {};
        params.id = $(elm).attr("data_id");
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(Journals.moduleApi()) + "confirmDelete",
            headers: { "X-CSRF-TOKEN": Journals.csrf_token() },
            beforeSend: () => {
                message.loadingProses("Proses Simpan Data...");
            },
            error: function () {
                message.closeLoading();
                message.sweetError("Informasi", "Gagal");
            },
            success: function (resp) {
                message.closeLoading();
                if (resp.is_valid) {
                    message.sweetSuccess("Informasi", "Data Berhasil Dihapus");
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },
};

function SearchData(d) {
    if (d && typeof d.search !== "undefined") {
        d.search.status = $("#filter-status").val() || "";
        return;
    }
    if ($("table#table-data").length > 0 && $.fn.DataTable.isDataTable("table#table-data")) {
        $("table#table-data").DataTable().draw();
    }
}

$(function () {
    Journals.setSelect2();

    if ($("table#table-data").length > 0) {
        Journals.getData();
    }

    if ($("#tbody-detail").length > 0) {
        let details = [];
        try {
            details = JSON.parse($("#detail-data").val() || "[]");
        } catch (err) {
            details = [];
        }

        if (details.length > 0) {
            details.forEach(function (row) {
                Journals.addDetail(row);
            });
        } else {
            Journals.addDetail();
            Journals.addDetail();
        }

        $("#tbody-detail").on("input change", "input, select", function () {
            Journals.calculateTotal();
        });
    }
});
