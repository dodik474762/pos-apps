let CustomerCategory = {
    module: () => {
        return "master/customer_category";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + CustomerCategory.module();
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
        window.location.href = url.base_url(CustomerCategory.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(CustomerCategory.module()) + "add";
    },

    getPostInput: () => {
        let data = {
            id: $("input#id").val(),
            category: $("#category").val(),
            remarks: $("#remarks").val(),
        };

        return data;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = CustomerCategory.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(CustomerCategory.moduleApi()) + "submit",
                headers: {
                    'X-CSRF-TOKEN': CustomerCategory.csrf_token(),
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
                            CustomerCategory.back();
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
        window.location.href = url.base_url(CustomerCategory.module()) + "/";
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
                url: url.base_url(CustomerCategory.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': CustomerCategory.csrf_token(),
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
                    data: "category",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = `<ul class="list-inline hstack gap-2 mb-0">`;

                        if (updateAction == 1) {
                            html += `<li class="list-inline-item edit" data-bs-toggle="tooltip"
                            data-bs-trigger="hover" data-bs-placement="top"
                            title="Edit">
                            <a href='${url.base_url(CustomerCategory.module())}ubah?id=${data}' data_id="${row.id}"
                                class="text-primary d-inline-block edit-item-btn">
                                <i class="ri-pencil-fill fs-16"></i>
                            </a>
                        </li>`;
                        }
                        if (deleteAction == 1) {
                            html += `<li class="list-inline-item" data-bs-toggle="tooltip"
                            data-bs-trigger="hover" data-bs-placement="top"
                            title="Remove">
                            <a class="text-danger d-inline-block remove-item-btn"
                                data-bs-toggle="modal" data_id="${row.id}" href="javascript:void(0);" onclick="CustomerCategory.delete(this, event)">
                                <i class="ri-delete-bin-5-fill fs-16"></i>
                            </a>
                        </li>`;
                        }
                        html += "</ul>";
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
            url: url.base_url(CustomerCategory.moduleApi()) + "delete",
            headers: {
                    'X-CSRF-TOKEN': CustomerCategory.csrf_token(),
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
            url: url.base_url(CustomerCategory.moduleApi()) + "confirmDelete",
            headers: {
                    'X-CSRF-TOKEN': CustomerCategory.csrf_token(),
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

    setDate:() =>{
        $('#search-datepicker').flatpickr({});
    }
};

$(function () {
    CustomerCategory.setDate();
    CustomerCategory.setSelect2();
    CustomerCategory.getData();
});
