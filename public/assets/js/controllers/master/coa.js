let Coa = {
    module: () => {
        return "master/coa";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + Coa.module();
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
        window.location.href = url.base_url(Coa.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(Coa.module()) + "add";
    },

    getPostInput: () => {
        let data = {
            id: $("input#id").val(),
            account_code: $("#account_code").val(),
            account_name: $("#account_name").val(),
            parent_code: $("#parent_code").val(),
            account_type: $("#account_type").val(),
            category: $("#category").val(),
            normal_balance: $("#normal_balance").val(),
            description: $("#description").val(),
            is_active: $("#is_active").val(),
        };

        return data;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = Coa.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(Coa.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": Coa.csrf_token(),
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
                            // window.location.reload();
                            Coa.back();
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

    back: (elm) => {
        window.location.href = url.base_url(Coa.module()) + "/";
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
                url: url.base_url(Coa.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": Coa.csrf_token(),
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
                    data: "account_code",
                },
                {
                    data: "account_type",
                },
                {
                    data: "account_name",
                },
                {
                    data: "category",
                },
                {
                    data: "normal_balance",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                Coa.module()
                            )}ubah?id=${data}' data_id="${
                                row.id
                            }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            html += `<button type="button" data_id="${row.id}" onclick="Coa.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
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

    delete: (elm, e) => {
        e.preventDefault();
        let params = {};
        params.id = $(elm).attr("data_id");
        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(Coa.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": Coa.csrf_token(),
            },
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
            url: url.base_url(Coa.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": Coa.csrf_token(),
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

$(function () {
    Coa.setSelect2();
    Coa.getData();
});
