let Permission = {
    module: () => {
        return "master/permission";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + Permission.module();
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
        window.location.href = url.base_url(Permission.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(Permission.module()) + "add";
    },

    getPostInput: () => {
        let data = {
            data: {
                roles: $.trim($("#roles").val()),
            },
            data_menu: Permission.getPostItemMenu(),
        };

        return data;
    },

    getPostItemMenu: () => {
        let menu = $(".checkmenudata");
        let data = [];
        $.each(menu, function () {
            let params = {};
            params.menu_id = $(this).attr("data_id");
            let content_action = $(`.action-menu-${params.menu_id}`);
            params.insert = content_action.find("#insert").is(":checked")
                ? 1
                : 0;
            params.update = content_action.find("#update").is(":checked")
                ? 1
                : 0;
            params.delete = content_action.find("#delete").is(":checked")
                ? 1
                : 0;
            params.view = content_action.find("#view").is(":checked") ? 1 : 0;
            params.print = content_action.find("#print").is(":checked") ? 1 : 0;
            if ($(this).is(":checked")) {
                data.push(params);
            }
        });

        return data;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = Permission.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(Permission.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": Permission.csrf_token(),
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
                            Permission.back();
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
        window.location.href = url.base_url(Permission.module()) + "/";
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
            order: [[0, "desc"]],
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
                url: url.base_url(Permission.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": Permission.csrf_token(),
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
                    data: "group",
                },
                {
                    data: "nama_menu",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        // if (updateAction == 1) {
                        //     html += `<a href='${url.base_url(Permission.module())}ubah?id=${data}' data_id="${row.id}" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        // }
                        if (deleteAction == 1) {
                            html += `<button type="button" data_id="${row.id}" onclick="Permission.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
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
            url: url.base_url(Permission.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": Permission.csrf_token(),
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
            url: url.base_url(Permission.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": Permission.csrf_token(),
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

    showMenu: (elm) => {
        let data = $(elm).val();
        let params = {};
        params.roles = data;

        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(Permission.moduleApi()) + "showMenu",
            headers: {
                "X-CSRF-TOKEN": Permission.csrf_token(),
            },
            beforeSend: () => {
                message.loadingProses("Proses Pengambilan Data");
            },

            error: function () {
                message.closeLoading();
                message.sweetError("Informasi", "Gagal");
            },

            success: function (resp) {
                message.closeLoading();
                let data = resp;
                for (let i = 0; i < data.length; i++) {
                    let menu_id = data[i].menu;
                    $(`.checkmenu-${menu_id}`).prop("checked", true);
                    let content_action = $(`.action-menu-${menu_id}`);
                    data[i].insert == 1
                        ? content_action.find("#insert").prop("checked", true)
                        : content_action.find("#insert").prop("checked", false);
                    data[i].update == 1
                        ? content_action.find("#update").prop("checked", true)
                        : content_action.find("#update").prop("checked", false);
                    data[i].delete == 1
                        ? content_action.find("#delete").prop("checked", true)
                        : content_action.find("#delete").prop("checked", false);
                    data[i].view == 1
                        ? content_action.find("#view").prop("checked", true)
                        : content_action.find("#view").prop("checked", false);
                    data[i].print == 1
                        ? content_action.find("#print").prop("checked", true)
                        : content_action.find("#print").prop("checked", false);
                }
            },
        });
    },
};

$(function () {
    Permission.setSelect2();
    Permission.getData();
});
