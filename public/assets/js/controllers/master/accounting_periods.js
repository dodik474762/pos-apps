let AccountingPeriods = {
    module: () => {
        return "master/accounting_periods";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + AccountingPeriods.module();
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
        window.location.href = url.base_url(AccountingPeriods.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(AccountingPeriods.module()) + "add";
    },

    getPostInput: () => {
        let data = {
            id: $("input#id").val(),
            year: $("#year").val(),
            month: $("#month").val(),
            start_date: $("#start_date").val(),
            end_date: $("#end_date").val(),
            status: $("#status").val(),
            description: $("#description").val(),
        };

        return data;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = AccountingPeriods.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(AccountingPeriods.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": AccountingPeriods.csrf_token(),
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
                    if (resp.is_valid) {
                        message.sweetSuccess();
                        setTimeout(function () {
                            AccountingPeriods.back();
                        }, 1000);
                    } else {
                        message.sweetError("Informasi", resp.message);
                    }
                },
            });
        } else {
            message.sweetError("Informasi", "Data Belum Lengkap");
        }
    },

    generateYear: (elm, e) => {
        e.preventDefault();
        let params = { year: new Date().getFullYear() };
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(AccountingPeriods.moduleApi()) + "generateYear",
            headers: {
                "X-CSRF-TOKEN": AccountingPeriods.csrf_token(),
            },
            beforeSend: () => {
                message.loadingProses("Proses Generate Periode...");
            },
            error: function () {
                message.closeLoading();
                message.sweetError("Informasi", "Gagal");
            },
            success: function (resp) {
                message.closeLoading();
                if (resp.is_valid) {
                    message.sweetSuccess("Informasi", resp.message);
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    changeStatus: (elm, e) => {
        e.preventDefault();
        let params = { id: $(elm).attr("data_id") };
        let action = $(elm).attr("data_action");
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(AccountingPeriods.moduleApi()) + action,
            headers: {
                "X-CSRF-TOKEN": AccountingPeriods.csrf_token(),
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
                if (resp.is_valid) {
                    message.sweetSuccess();
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    back: (elm) => {
        window.location.href = url.base_url(AccountingPeriods.module()) + "/";
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
                url: url.base_url(AccountingPeriods.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": AccountingPeriods.csrf_token(),
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
                {
                    data: "month",
                    render: (data, type, row) =>
                        (data < 10 ? "0" + data : data) + " / " + row.year,
                },
                { data: "start_date" },
                { data: "end_date" },
                {
                    data: "status",
                    render: (data) => {
                        if (data == "OPEN") {
                            return '<span class="badge bg-success">OPEN</span>';
                        } else if (data == "CLOSED") {
                            return '<span class="badge bg-danger">CLOSED</span>';
                        }
                        return '<span class="badge bg-secondary">' + data + "</span>";
                    },
                },
                { data: "description" },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(AccountingPeriods.module())}ubah?id=${data}' data_id="${row.id}" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (row.status == "OPEN") {
                            html += `<button type="button" data_id="${row.id}" data_action="close" onclick="AccountingPeriods.changeStatus(this, event)" class="btn btn-warning editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-lock"></i></button>&nbsp;`;
                        } else {
                            html += `<button type="button" data_id="${row.id}" data_action="reopen" onclick="AccountingPeriods.changeStatus(this, event)" class="btn btn-info editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-lock-open"></i></button>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            html += `<button type="button" data_id="${row.id}" onclick="AccountingPeriods.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
                        }
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
            url: url.base_url(AccountingPeriods.moduleApi()) + "delete",
            headers: { "X-CSRF-TOKEN": AccountingPeriods.csrf_token() },
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
            url: url.base_url(AccountingPeriods.moduleApi()) + "confirmDelete",
            headers: { "X-CSRF-TOKEN": AccountingPeriods.csrf_token() },
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

function SearchDataPeriod() {
    if ($("table#table-data").length > 0 && $.fn.DataTable.isDataTable("table#table-data")) {
        $("table#table-data").DataTable().draw();
    }
}

$(function () {
    AccountingPeriods.setSelect2();
    AccountingPeriods.getData();
});
