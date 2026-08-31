let CustomerLimitTop = {
    module: () => {
        return "master/customer_limit_top";
    },

    moduleAcc: () => {
        return "approval/customer_limit_top";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + CustomerLimitTop.module();
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
        window.location.href = url.base_url(CustomerLimitTop.module()) + "/";
    },

    cancelAcc: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(CustomerLimitTop.moduleAcc()) + "/";
    },

    back: (elm) => {
        window.location.href = url.base_url(CustomerLimitTop.module()) + "/";
    },

    backAcc: (elm) => {
        window.location.href = url.base_url(CustomerLimitTop.moduleAcc()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(CustomerLimitTop.module()) + "add";
    },

    getPostInput: () => {
        let data = {
            id: $("input#id").val(),
            customer: $("#customer").val(),
            type_pengajuan: $("#type_pengajuan").val(),
            new_credit_limit: $("#new_credit_limit").val(),
            new_payment_terms: $("#new_payment_terms").val(),
            reason: $("#reason").val(),
        };

        return data;
    },

    changeTypePengajuan: (elm) => {
        const type = $(elm).val();

        if (type == 'CREDIT_LIMIT') {
            $('.field-credit-limit').show();
            $('#new_credit_limit').addClass('required').removeAttr('disabled');
            $('.field-top').hide();
            $('#new_payment_terms').removeClass('required').val('').attr('disabled', true);
        } else if (type == 'TERM_OF_PAYMENT') {
            $('.field-credit-limit').hide();
            $('#new_credit_limit').removeClass('required').val('').attr('disabled', true);
            $('.field-top').show();
            $('#new_payment_terms').addClass('required').removeAttr('disabled');
        } else if (type == 'CREDIT_LIMIT_DAN_TOP') {
            $('.field-credit-limit').show();
            $('#new_credit_limit').addClass('required').removeAttr('disabled');
            $('.field-top').show();
            $('#new_payment_terms').addClass('required').removeAttr('disabled');
        } else {
            $('.field-credit-limit').hide();
            $('#new_credit_limit').removeClass('required').val('').attr('disabled', true);
            $('.field-top').hide();
            $('#new_payment_terms').removeClass('required').val('').attr('disabled', true);
        }
    },

    getDetailCustomer: (elm) => {
        const customer = $(elm).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                customer: customer,
            },
            headers: {
                "X-CSRF-TOKEN": CustomerLimitTop.csrf_token(),
            },
            url: url.base_url(CustomerLimitTop.moduleApi()) + "getDetailCustomer",
            beforeSend: () => {
                message.loadingProses("Proses Pengambilan Data...");
            },
            error: function () {
                message.closeLoading();
                message.sweetError("Informasi", "Gagal");
            },

            success: function (resp) {
                message.closeLoading();
                if (resp.is_valid) {
                    $("input#info_credit_limit").val(resp.data.credit_limit);
                    $("input#info_top_name").val(resp.data.current_top_name ?? '');
                    $("input#current_credit_limit").val(resp.data.credit_limit);
                    $("input#current_payment_terms").val(resp.data.payment_terms);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = CustomerLimitTop.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(CustomerLimitTop.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": CustomerLimitTop.csrf_token(),
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
                            CustomerLimitTop.back();
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

    reject: (elm, e) => {
        e.preventDefault();
        Swal.fire({
            title: "Reject Data",
            text: "Masukkan Alasan Penolakan",
            input: "text",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya",
            cancelButtonText: "-",
        }).then((result) => {
            if (result.value) {
                CustomerLimitTop.approve(elm, e, 'rej', result.value);
            } else {
                message.sweetError("Informasi", "Data Belum Lengkap");
                return false;
            }
        })
    },

    approve: (elm, e, status = 'acc', remarks = '') => {
        e.preventDefault();
        let params = {};
        params.id = $("input#id").val();
        params.status = status;
        params.remarks = remarks;

        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(CustomerLimitTop.moduleApi()) + "approve",
            headers: {
                "X-CSRF-TOKEN": CustomerLimitTop.csrf_token(),
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
                        CustomerLimitTop.backAcc();
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
                url: url.base_url(CustomerLimitTop.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": CustomerLimitTop.csrf_token(),
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
                    data: "code",
                },
                {
                    data: "customer_code",
                },
                {
                    data: "customer_name",
                },
                {
                    data: "type_pengajuan",
                    render: function (data, type, row) {
                        if (data == 'CREDIT_LIMIT') {
                            return "Credit Limit";
                        } else if (data == 'TERM_OF_PAYMENT') {
                            return "Term Of Payment";
                        }

                        return "Credit Limit & Term Of Payment";
                    },
                },
                {
                    data: "current_credit_limit",
                    render: function (data, type, row) {
                        return data != null ? data : "-";
                    },
                },
                {
                    data: "new_credit_limit",
                    render: function (data, type, row) {
                        return data != null ? data : "-";
                    },
                },
                {
                    data: "current_top_name",
                    render: function (data, type, row) {
                        return data != null ? data : "-";
                    },
                },
                {
                    data: "new_top_name",
                    render: function (data, type, row) {
                        return data != null ? data : "-";
                    },
                },
                {
                    "data": "status",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        if (updateAction == 1 && row.status != 'APPROVED' && row.status != 'REJECTED') {
                            html += `<a href='${url.base_url(
                                CustomerLimitTop.module()
                            )}ubah?id=${data}' data_id="${row.id
                                }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (deleteAction == 1 && row.status != 'APPROVED' && row.status != 'REJECTED') {
                            html += `<a href='' onclick="CustomerLimitTop.delete(this, event)" data_id="${row.id}" class="btn btn-danger editable-submit btn-sm waves-effect waves-light"><i class="bx bx-trash"></i></a>&nbsp;`;
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

    getDataAcc: async () => {
        let tableData = $("table#table-data-acc");

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
                url: url.base_url(CustomerLimitTop.moduleApi()) + `getDataAcc`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": CustomerLimitTop.csrf_token(),
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
                    data: "code",
                },
                {
                    data: "customer_code",
                },
                {
                    data: "customer_name",
                },
                {
                    data: "type_pengajuan",
                    render: function (data, type, row) {
                        if (data == 'CREDIT_LIMIT') {
                            return "Credit Limit";
                        } else if (data == 'TERM_OF_PAYMENT') {
                            return "Term Of Payment";
                        }

                        return "Credit Limit & Term Of Payment";
                    },
                },
                {
                    data: "current_credit_limit",
                    render: function (data, type, row) {
                        return data != null ? data : "-";
                    },
                },
                {
                    data: "new_credit_limit",
                    render: function (data, type, row) {
                        return data != null ? data : "-";
                    },
                },
                {
                    data: "current_top_name",
                    render: function (data, type, row) {
                        return data != null ? data : "-";
                    },
                },
                {
                    data: "new_top_name",
                    render: function (data, type, row) {
                        return data != null ? data : "-";
                    },
                },
                {
                    "data": "status",
                },
                {
                    "data": "spv_sales_date",
                    render: function (data, type, row) {
                        return data != null ? "Sudah" : "Belum";
                    },
                },
                {
                    "data": "admin_sales_date",
                    render: function (data, type, row) {
                        return data != null ? "Sudah" : "Belum";
                    },
                },
                {
                    "data": "om_date",
                    render: function (data, type, row) {
                        return data != null ? "Sudah" : "Belum";
                    },
                },
                {
                    "data": "superadmin_date",
                    render: function (data, type, row) {
                        return data != null ? "Sudah" : "Belum";
                    },
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                CustomerLimitTop.module()
                            )}detail?id=${data}' data_id="${row.id
                                }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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

    getDataAccHistory: async () => {
        let tableData = $("table#table-data-history");

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
                url: url.base_url(CustomerLimitTop.moduleApi()) + `getDataAccHistory`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": CustomerLimitTop.csrf_token(),
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
                    data: "code",
                },
                {
                    data: "customer_code",
                },
                {
                    data: "customer_name",
                },
                {
                    data: "type_pengajuan",
                    render: function (data, type, row) {
                        if (data == 'CREDIT_LIMIT') {
                            return "Credit Limit";
                        } else if (data == 'TERM_OF_PAYMENT') {
                            return "Term Of Payment";
                        }

                        return "Credit Limit & Term Of Payment";
                    },
                },
                {
                    data: "current_credit_limit",
                    render: function (data, type, row) {
                        return data != null ? data : "-";
                    },
                },
                {
                    data: "new_credit_limit",
                    render: function (data, type, row) {
                        return data != null ? data : "-";
                    },
                },
                {
                    data: "current_top_name",
                    render: function (data, type, row) {
                        return data != null ? data : "-";
                    },
                },
                {
                    data: "new_top_name",
                    render: function (data, type, row) {
                        return data != null ? data : "-";
                    },
                },
                {
                    "data": "status",
                },
                {
                    "data": "remarks",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                CustomerLimitTop.module()
                            )}detail?id=${data}' data_id="${row.id
                                }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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
            url: url.base_url(CustomerLimitTop.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": CustomerLimitTop.csrf_token(),
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
            url: url.base_url(CustomerLimitTop.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": CustomerLimitTop.csrf_token(),
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
    CustomerLimitTop.setSelect2();
    if ($("table#table-data").length > 0) {
        CustomerLimitTop.getData();
    }
    if ($("table#table-data-acc").length > 0) {
        CustomerLimitTop.getDataAcc();
    }
    if ($("table#table-data-history").length > 0) {
        CustomerLimitTop.getDataAccHistory();
    }
    if ($("select#type_pengajuan").length > 0) {
        CustomerLimitTop.changeTypePengajuan($("select#type_pengajuan"));
    }
});
