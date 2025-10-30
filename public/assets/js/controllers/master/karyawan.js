let Karyawan = {
    module: () => {
        return "master/karyawan";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + Karyawan.module();
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
        window.location.href = url.base_url(Karyawan.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(Karyawan.module()) + "add";
    },

    getPostInput: () => {
        let data = {
            id: $("input#id").val(),
            company: $("#company").val(),
            nama: $("#nama").val(),
            nik: $("#nik").val(),
            jabatan: $("#jabatan").val(),
            contact: $("#contact").val(),
            email: $("#email").val(),
            branch: $("#branch").val(),
            bank_name: $("#bank_name").val(),
            bank_number: $("#bank_number").val(),
            bank_complete_name: $("#bank_name").find("option:selected").text(),
            group: $("#group").val(),
            items: Karyawan.getPostItem(),
        };

        return data;
    },

    getPostItem: () => {
        let data = $("table#table-group").find("tbody").find("tr.input");
        let result = [];
        data.each((index, elm) => {
            result.push({
                id: $(elm).attr("data_id"),
                group: $(elm).find("#group-item").val(),
                default: $(elm).find("#group-default").is(":checked") ? 1 : 0,
                remove: $(elm).hasClass("remove") ? 1 : 0,
            });
        });

        return result;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = Karyawan.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(Karyawan.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": Karyawan.csrf_token(),
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
                            Karyawan.back();
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
        window.location.href = url.base_url(Karyawan.module()) + "/";
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
                url: url.base_url(Karyawan.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": Karyawan.csrf_token(),
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
                    data: "nama_lengkap",
                },
                {
                    data: "nik",
                },
                {
                    data: "jabatan",
                },
                {
                    data: "group_name",
                },
                {
                    data: "bank_complete_name",
                },
                {
                    data: "bank_number",
                },
                {
                    data: "contact",
                },
                {
                    data: "email",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                Karyawan.module()
                            )}ubah?id=${data}' data_id="${
                                row.id
                            }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            html += `<button type="button" data_id="${row.id}" onclick="Karyawan.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
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
            url: url.base_url(Karyawan.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": Karyawan.csrf_token(),
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
            url: url.base_url(Karyawan.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": Karyawan.csrf_token(),
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

    deleteItem: (elm) => {
        let data_id = $(elm).closest("tr").attr("data_id");
        if (data_id == "") {
            $(elm).closest("tr").remove();
        } else {
            $(elm).closest("tr").addClass("remove");
            $(elm).closest("tr").addClass("hide");
        }
    },

    addItem: (elm, e) => {
        e.preventDefault();
        let table = $(elm)
            .closest("div")
            .find("table#table-group")
            .find("tbody")
            .find("tr.input:last");
        let newTr = table.clone();
        newTr.find("input").val("");
        newTr.attr("data_id", "");
        newTr
            .find("td#action")
            .html(
                `<button type="button" onclick="Karyawan.deleteItem(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`
            );
        table.after(newTr);
    },

    changeDefault: (elm) => {
        const isChecked = $(elm).is(":checked");
        if (isChecked) {
            const group = $(elm).closest("tr").find("#group-item").val();
            $("#group").val(group);
        }
    },
};

$(function () {
    Karyawan.setSelect2();
    Karyawan.getData();
});
