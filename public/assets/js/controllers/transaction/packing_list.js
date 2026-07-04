let elmChoose;
let discProduct = [];
let PackingList = {
    module: () => {
        return "transaksi/packing_list";
    },

    modulePr: () => {
        return "transaksi/packing_list_pr";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + PackingList.module();
    },

    modulePrApi: () => {
        return "api/" + PackingList.modulePr();
    },

    moduleApiProduct: () => {
        return "api/master/product";
    },

    moduleApiCustomer: () => {
        return "api/master/customer";
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
        window.location.href = url.base_url(PackingList.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(PackingList.module()) + "add";
    },

    addSr: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(PackingList.modulePr()) + "add";
    },

    addAll: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(PackingList.module()) + "addAll";
    },

    getPostItemChecked: () => {
        const checkboxs = $("input.checkbox-so");
        let result = [];

        checkboxs.each((index, elm) => {
            const $check = $(elm);
            if ($check.is(":checked")) {
                const td = $(elm).closest("td");
                const $row = td.closest("tr");

                result.push({
                    id: td.find("a").attr("data_id") || null,
                    so_detail_id: null,
                    product_id: null,
                    qty: 0,
                    uom: null,
                    note: "",
                    remove: 0,
                });
            }
        });

        return result;
    },

    getPostItem: () => {
        const rows = $("#table-items tbody tr");
        let result = [];

        rows.each((index, elm) => {
            const $row = $(elm);

            result.push({
                id: $row.attr("data_id") || null,
                do_id: $row.attr("do_id"),
                product_id: $row.find("#product_id").attr("data_id") || null,
                product_name: $row.find("#product_id").text() || null,
                qty_do: parseFloat($row.find("#product_qty").text()) || 0,
                qty_packed: parseFloat($row.find("#qty_pack").val()) || 0,
                remark: $row.find("input[type='text']").val() || null,
            });
        });

        return result;
    },

    getPostDo: () => {
        const rows = $("#table-do tbody tr");
        let result = [];

        rows.each((index, elm) => {
            const $row = $(elm);

            result.push({
                id: $row.attr("data_id") || null,
                delivery_order_id:
                    $row.find("#do_number").attr("data_id") || null,
                do_number: $row.find("#do_number").text().trim() || null,
                do_date: $row.find("#do_date").text().trim() || null,
                customer_id: $row.find("#do_customer").attr("data_id") || null,
                customer_name: $row.find("#do_customer").text().trim() || null,
                remove: $row.hasClass("remove") ? 1 : 0,
            });
        });

        return result;
    },

    getPostInput: (bulk = false) => {
        let data = {
            id: $("input#id").val() || null,
            packing_list_no: $("#packing_list_no").val() || null,
            packing_date: $("#packing_date").val() || null,
            vehicle_no: $("#vehicle_no").val() || null,
            driver: $("#driver").val() || null,
            driver_name: $("#driver_name").val() || null,
            expedition_name: $("#expedition_name").val() || null,
            remarks: $("#remarks").val() || null,

            // do_list: PackingList.getPostDo(), // 🔥 DO LIST
            // details: PackingList.getPostItem(), // 🔥 ITEM LIST
            // items_checked: PackingList.getPostItemChecked(),
            // 🔥 kirim sebagai JSON string, bukan array nested
            do_list: JSON.stringify(PackingList.getPostDo()),
            details: JSON.stringify(PackingList.getPostItem()),
            items_checked: JSON.stringify(PackingList.getPostItemChecked()),
        };

        return data;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = PackingList.getPostInput(false);
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(PackingList.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                            PackingList.back();
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

    submitSr: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = PackingList.getPostInput(false);
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(PackingList.modulePrApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                            PackingList.backSr();
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

    submitBulk: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = PackingList.getPostInput(true);
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(PackingList.moduleApi()) + "submitBulk",
                headers: {
                    "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                            PackingList.back();
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
        window.location.href = url.base_url(PackingList.module()) + "/";
    },

    backSr: (elm) => {
        window.location.href = url.base_url(PackingList.modulePr()) + "/";
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
                    "pagination-rounded",
                );
            },
            ajax: {
                url: url.base_url(PackingList.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                    data: "packing_list_no",
                },
                {
                    data: "packing_date",
                },
                {
                    data: "vehicle_no",
                },
                {
                    data: "driver_name",
                },
                {
                    data: "expedition_name",
                },
                {
                    data: "created_by_name",
                },
                {
                    data: "status",
                    render: function (data, type, row) {
                        if (data) {
                            return data;
                        }

                        return "Menunggu Konfirmasi Pengiriman";
                    },
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = `<a href='${url.base_url(
                            PackingList.module(),
                        )}cetak?id=${data}' data_id="${row.id
                            }" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-printer"></i></a>&nbsp;`;
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                PackingList.module(),
                            )}ubah?id=${data}' data_id="${row.id
                                }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                            // html += `<a href='${url.base_url(
                            //     PackingList.module(),
                            // )}cetakSj?id=${data}' data_id="${row.id
                            //     }" class="btn btn-secondary btn-sm editable-submit btn-sm waves-effect waves-light">Cetak SJ</a>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            if (row.status != "CONFIRMED") {
                                html += `<button type="button" data_id="${row.id}" onclick="PackingList.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
                            }
                        }
                        return html;
                    },
                },
            ],
        });

        (data
            .buttons()
            .container()
            .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"),
            $(".dataTables_length select").addClass(
                "form-select form-select-sm",
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
                        "pagination-rounded",
                    );
                },
            }));
    },

    delete: (elm, e) => {
        e.preventDefault();
        let params = {};
        params.id = $(elm).attr("data_id");
        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PackingList.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": PackingList.csrf_token(),
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

    getDataSr: async () => {
        let tableData = $("table#table-data-sr");

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
                    "pagination-rounded",
                );
            },
            ajax: {
                url: url.base_url(PackingList.modulePrApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                    data: "packing_list_no",
                },
                {
                    data: "packing_date",
                },
                {
                    data: "vehicle_no",
                },
                {
                    data: "driver_name",
                },
                {
                    data: "expedition_name",
                },
                {
                    data: "created_by_name",
                },
                {
                    data: "status",
                    render: function (data, type, row) {
                        if (data) {
                            return data;
                        }

                        return "Menunggu Konfirmasi Pickup";
                    },
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = `<a href='${url.base_url(
                            PackingList.modulePr(),
                        )}cetak?id=${data}' data_id="${row.id
                            }" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-printer"></i></a>&nbsp;`;
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                PackingList.modulePr(),
                            )}ubah?id=${data}' data_id="${row.id
                                }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            if (row.status == "PENDING") {
                                html += `<button type="button" data_id="${row.id}" onclick="PackingList.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
                            }
                        }
                        return html;
                    },
                },
            ],
        });

        (data
            .buttons()
            .container()
            .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"),
            $(".dataTables_length select").addClass(
                "form-select form-select-sm",
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
                        "pagination-rounded",
                    );
                },
            }));
    },

    delete: (elm, e) => {
        e.preventDefault();
        let params = {};
        params.id = $(elm).attr("data_id");
        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PackingList.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": PackingList.csrf_token(),
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

    cancelPl: (elm, e) => {
        e.preventDefault();
        const status = $(elm).attr("status");
        let allow = false;
        if (status == "NOT DELIVERED") {
            allow = true;
        }

        if (allow == false) {
            message.sweetError(
                "Informasi",
                "Packing List Sudah Tidak Bisa Dibatalkan",
            );
            return;
        }
        let params = {};
        params.id = $(elm).attr("data_id");
        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PackingList.moduleApi()) + "cancelPl",
            headers: {
                "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                $("#content-modal-form").html(resp);
                $("#btn-show-modal").trigger("click");
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
            url: url.base_url(PackingList.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": PackingList.csrf_token(),
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

    confirmCancel: (elm) => {
        let params = {};
        params.id = $(elm).attr("data_id");
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(PackingList.moduleApi()) + "confirmCancel",
            headers: {
                "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                    message.sweetSuccess(
                        "Informasi",
                        "Data Berhasil Dibatalkan",
                    );
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    posted: (elm) => {
        let params = {};
        params.id = $("#id").val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(PackingList.moduleApi()) + "posted",
            headers: {
                "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                    message.sweetSuccess("Informasi", "Data Berhasil Confirm");
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    showModalDO: (elm) => {
        let params = {};
        const payment_method = $("#payment_method").val();
        if (payment_method == "") {
            message.sweetError(
                "Informasi",
                "Pilih Payment Method Terlebih Dahulu",
            );
            return;
        }

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PackingList.moduleApi()) + "showModalDO",
            headers: {
                "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                $("#content-modal-form").html(resp);
                $("#btn-show-modal").trigger("click");
                elmChoose = elm;
                PackingList.getDataDO();
            },
        });
    },

    showModalSR: (elm) => {
        let params = {};
        const payment_method = $("#payment_method").val();
        if (payment_method == "") {
            message.sweetError(
                "Informasi",
                "Pilih Payment Method Terlebih Dahulu",
            );
            return;
        }

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PackingList.modulePrApi()) + "showModalSR",
            headers: {
                "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                $("#content-modal-form").html(resp);
                $("#btn-show-modal").trigger("click");
                elmChoose = elm;
                PackingList.getDataSR();
            },
        });
    },

    getDataDO: () => {
        const data_do_chooce = [];
        const table_do = $("#table-do tbody tr");
        table_do.each(function () {
            let do_ids = $(this).find("td#do_number").attr("data_id");
            data_do_chooce.push(do_ids);
        });

        let tableData = $("table#table-data-modal");
        var data = tableData.DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            autoWidth: false,
            order: [[0, "asc"]],
            aLengthMenu: [
                [200, 500, 700],
                [200, 500, 700],
            ],
            // lengthChange: !1,
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>",
                },
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass(
                    "pagination-rounded",
                );
            },
            ajax: {
                url: url.base_url(PackingList.moduleApi()) + `getDataDO`,
                type: "POST",
                data: {
                    data_do_chooce: data_do_chooce,
                },
                headers: {
                    "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                    data: "do_number",
                },
                {
                    data: "do_date",
                },
                {
                    data: "invoice_number",
                },
                {
                    data: "invoice_date",
                },
                {
                    data: "customer_code",
                },
                {
                    data: "nama_customer",
                },
                {
                    data: "address",
                },
                {
                    data: "so_number",
                },
                {
                    data: "so_date",
                },
                {
                    data: "status",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' code="${row.code}" nama_customer="${row.nama_customer}" onclick="PackingList.pilihDataDO(this, event)" data_id="${row.id}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;
                        <input type="checkbox" class="checkbox-so" id="check-so"/>`;
                        return html;
                    },
                },
            ],
        });
    },

    getDataSR: () => {
        const data_do_chooce = [];
        const table_do = $("#table-do tbody tr");
        table_do.each(function () {
            let do_ids = $(this).find("td#do_number").attr("data_id");
            data_do_chooce.push(do_ids);
        });

        let tableData = $("table#table-data-modal");
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
            // lengthChange: !1,
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>",
                },
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass(
                    "pagination-rounded",
                );
            },
            ajax: {
                url: url.base_url(PackingList.modulePrApi()) + `getDataSR`,
                type: "POST",
                data: {
                    data_do_chooce: data_do_chooce,
                },
                headers: {
                    "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                    data: "return_number",
                },
                {
                    data: "return_date",
                },
                {
                    data: "customer_code",
                },
                {
                    data: "nama_customer",
                },
                {
                    data: "status",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' code="${row.code}" nama_customer="${row.nama_customer}" onclick="PackingList.pilihDataSR(this, event)" data_id="${row.id}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        return html;
                    },
                },
            ],
        });
    },

    // pilihDataDO: (elm, e) => {
    //     e.preventDefault();
    //     const data_id = $(elm).attr("data_id");
    //     $("button.btn-close").trigger("click");

    //     PackingList.getDOConfirmed(data_id);
    //     PackingList.getDODetailConfirmed(data_id);
    // },

    pilihDataDO: (elm, e) => {
        e.preventDefault();
        const data_id = $(elm).attr("data_id");
        $("button.btn-close").trigger("click");

        PackingList.getDOConfirmed(data_id);
        PackingList.getDODetailConfirmed(data_id);
    },

    generateCheckedDO: () => {
        const checkboxes = $("input.checkbox-so:checked");

        if (checkboxes.length === 0) {
            message.sweetError(
                "Informasi",
                "Pilih minimal satu DO terlebih dahulu",
            );
            return;
        }

        // Tutup modal dulu
        $("button.btn-close").trigger("click");

        // Loop setiap checkbox yang dicentang
        checkboxes.each((index, elm) => {
            const td = $(elm).closest("td");
            const data_id = td.find("a").attr("data_id");

            if (data_id) {
                PackingList.getDOConfirmed(data_id);
                PackingList.getDODetailConfirmed(data_id);
            }
        });
    },

    pilihDataSR: (elm, e) => {
        e.preventDefault();
        const data_id = $(elm).attr("data_id");
        $("button.btn-close").trigger("click");

        PackingList.getSRConfirmed(data_id);
        PackingList.getSRDetailConfirmed(data_id);
    },

    getSRConfirmed: (do_id) => {
        let params = {
            do_id: do_id,
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PackingList.modulePrApi()) + "getSRConfirmed",
            headers: {
                "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                const table_items = $("#table-do");
                table_items.find("tbody").append(resp);
                // PackingList.hitungSummaryAll();
            },
        });
    },

    getDOConfirmed: (do_id) => {
        let params = {
            do_id: do_id,
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PackingList.moduleApi()) + "getDOConfirmed",
            headers: {
                "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                const table_items = $("#table-do");
                table_items.find("tbody").append(resp);
                // PackingList.hitungSummaryAll();
            },
        });
    },

    getDODetailConfirmed: (do_id) => {
        let params = {
            do_id: do_id,
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PackingList.moduleApi()) + "getDODetailConfirmed",
            headers: {
                "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                const table_items = $("#table-items");
                table_items.find("tbody").append(resp);
            },
        });
    },

    getSRDetailConfirmed: (do_id) => {
        let params = {
            do_id: do_id,
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url:
                url.base_url(PackingList.modulePrApi()) +
                "getSRDetailConfirmed",
            headers: {
                "X-CSRF-TOKEN": PackingList.csrf_token(),
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
                const table_items = $("#table-items");
                table_items.find("tbody").append(resp);
            },
        });
    },

    changeAllocate: (elm) => {
        const tr = $(elm).closest("tr");

        // Ambil value input
        const allocated =
            parseFloat(tr.find("input#allocated_amount").val()) || 0;
        const outstanding =
            parseFloat(tr.find("input#outstanding_amount").val()) || 0;
        const outstanding_new = outstanding - allocated;

        // Hitung summary total
        PackingList.hitungSummaryAll();
    },

    hitungSummaryAll: () => {
        let total = 0;
        let total_disc = 0;
        let total_subtotal = 0;
        let net_total = 0;

        document.querySelectorAll("#table-items tbody tr").forEach((tr) => {
            const allocated =
                parseFloat($(tr).find("input#allocated_amount").val()) || 0;
            const invoice_id = $(tr).find("td#invoice_id");
            const subtotal = parseFloat(invoice_id.attr("subtotal")) || 0;
            total_subtotal += subtotal;
            const discount_amount =
                parseFloat(invoice_id.attr("discount_amount")) || 0;
            total_disc += discount_amount;
            // const outstanding = parseFloat($(tr).find("#outstanding_amount").val()) || 0;
            const netAmount = subtotal - discount_amount;
            net_total += netAmount;
            total += allocated;
        });

        document.getElementById("grand-total").textContent = total.toFixed(2);
        $("input#total_amount").val(total_subtotal.toFixed(2));
        $("input#discount_amount").val(total_disc.toFixed(2));
        $("input#net_amount").val(net_total.toFixed(2));
    },

    removeRow: (elm) => {
        const data_id = $(elm).closest("tr").attr("data_id");
        const do_id = $(elm).closest("tr").find("td#do_number").attr("data_id");
        if (data_id == "") {
            $(elm).closest("tr").remove();
        } else {
            $(elm).closest("tr").addClass("remove");
            $(elm).closest("tr").addClass("d-none");
        }

        $("tr.do_detail_" + do_id).remove();
    },

    addRow: () => {
        const row = $("table#table-items")
            .find("tbody")
            .find("tr.input:last")
            .clone();
        row.removeClass("remove");
        row.removeClass("d-none");
        row.removeClass("freegood");
        row.find("input").val("");
        row.find("input#product").closest("div").find("button").text("Pilih");
        row.find("input#product")
            .closest("div")
            .find("button")
            .removeAttr("disabled");
        row.find("input#product").removeAttr("disabled");
        row.find("input#qty").removeAttr("disabled");
        row.find("button.btn-danger").removeAttr("disabled");
        row.removeAttr("data-free-for");
        row.find("td#unit").text("");
        row.find("td#unit").attr("data_id", "");
        row.attr("data_id", "");
        $("table#table-items").find("tbody").append(row);
    },

    getDataUomConversion: () => {
        const table_uom = $("table#table-data-uom").find("tbody").find("tr");
        const UOM_CONVERSION = [];
        $.each(table_uom, (index, elm) => {
            const product_id = $(elm).attr("product_id");
            const unit_id = $(elm).attr("unit_id");
            const conversion = $(elm).attr("conversion");
            UOM_CONVERSION.push({
                product_id: product_id,
                unit_id: unit_id,
                conversion: conversion,
            });
        });

        return UOM_CONVERSION;
    },

    getDataDiskon: () => {
        const table_diskon = $("table#table-data-diskon")
            .find("tbody")
            .find("tr");
        const DATA_DISKON = [];
        if (table_diskon.length > 0) {
            $.each(table_diskon, (index, elm) => {
                const product_id = $(elm).attr("product_id");
                const unit_id = $(elm).attr("unit");
                const id = $(elm).attr("data_id");
                const discount_type = $(elm).attr("discount_type");
                const discount_value = isNaN(
                    parseFloat($(elm).attr("discount_value")),
                )
                    ? 0
                    : parseFloat($(elm).attr("discount_value"));
                const customer_category = $(elm).attr("customer_category");
                const min_qty = isNaN(parseFloat($(elm).attr("min_qty")))
                    ? 0
                    : parseFloat($(elm).attr("min_qty"));
                const max_qty = isNaN(parseFloat($(elm).attr("max_qty")))
                    ? 0
                    : parseFloat($(elm).attr("max_qty"));
                const customer = $(elm).attr("customer");
                const berlaku_from = $(elm).attr("berlaku_from");

                DATA_DISKON.push({
                    product_id: product_id,
                    unit_id: unit_id,
                    id: id,
                    discount_type: discount_type,
                    discount_value: discount_value,
                    customer_category: customer_category,
                    min_qty: min_qty,
                    max_qty: max_qty,
                    customer: customer,
                    berlaku_from: berlaku_from,
                });
            });
        }

        return DATA_DISKON;
    },

    getDataDiskonFreeGood: () => {
        const rows = $("table#table-data-diskon-free tbody tr");
        const DATA_DISKON_FREE = [];

        if (rows.length > 0) {
            $.each(rows, (index, elm) => {
                const $tr = $(elm);

                const product_id = $tr.attr("product_id") || null;
                const unit_id = $tr.attr("unit") || null;
                const free_product = $tr.attr("free_product") || null;
                const free_unit_name = $tr.attr("free_unit_name") || null;
                const free_product_name = $tr.attr("free_product_name") || null;
                const free_unit = $tr.attr("free_unit") || null;
                const free_qty = isNaN(parseFloat($tr.attr("free_qty")))
                    ? 0
                    : parseFloat($tr.attr("free_qty"));
                const id = $tr.attr("data_id") || null;
                const customer_category = $tr.attr("customer_category") || null;
                const min_qty = isNaN(parseFloat($tr.attr("min_qty")))
                    ? 0
                    : parseFloat($tr.attr("min_qty"));
                const max_qty = isNaN(parseFloat($tr.attr("max_qty")))
                    ? 0
                    : parseFloat($tr.attr("max_qty"));
                const customer_id = $tr.attr("customer") || null;
                const berlaku_from = $tr.attr("berlaku_from") || null;

                DATA_DISKON_FREE.push({
                    id,
                    product_id,
                    unit_id,
                    min_qty,
                    max_qty,
                    free_product,
                    free_product_name,
                    free_unit_name,
                    free_unit,
                    free_qty,
                    customer_id,
                    customer_category,
                    berlaku_from,
                });
            });
        }

        return DATA_DISKON_FREE;
    },

    calcDiscRow: (elm) => {
        const tr = $(elm).closest("tr");
        const qty = parseFloat(tr.find("#qty").val()) || 0;
        const productId = tr.find("#product").attr("data_id");
        const satuanId = tr.find("td#unit").attr("data_id");
        const price = parseFloat(tr.find("#unit_price").val()) || 0;
        const customerId = $("#customer_id").val();
        const today = new Date().toISOString().slice(0, 10);

        if (!productId) return;

        const UOM_CONVERSION = PackingList.getDataUomConversion();
        const DATA_DISKON = PackingList.getDataDiskon();
        const DATA_DISKON_FREE = PackingList.getDataDiskonFreeGood();

        // Konversi qty input ke satuan terkecil
        const qtySmallest = PackingList.convertToSmallest(
            UOM_CONVERSION,
            productId,
            satuanId,
            qty,
        );

        // Cari data diskon yang cocok
        const applicable = DATA_DISKON.find((d) => {
            // konversi range min/max ke satuan terkecil
            const minSmall = PackingList.convertToSmallest(
                UOM_CONVERSION,
                d.product_id,
                d.unit_id,
                d.min_qty,
            );
            const maxSmall = PackingList.convertToSmallest(
                UOM_CONVERSION,
                d.product_id,
                d.unit_id,
                d.max_qty,
            );

            return (
                d.product_id == productId &&
                qtySmallest >= minSmall &&
                qtySmallest <= maxSmall &&
                (!d.customer_id || d.customer_id == customerId) &&
                today >= d.berlaku_from
            );
        });

        const discPercentInput = tr.find("#disc_percent");
        const discAmountInput = tr.find("#disc_amount");
        const subtotalInput = tr.find("#subtotal");

        if (applicable) {
            if (applicable.discount_type === "percent") {
                discPercentInput.val(applicable.discount_value);
                discAmountInput.val(
                    (price * qty * applicable.discount_value) / 100,
                );
            } else {
                discPercentInput.val(0);
                discAmountInput.val(applicable.discount_value);
            }
        } else {
            discPercentInput.val(0);
            discAmountInput.val(0);
        }

        // Hitung subtotal
        const discAmount = parseFloat(discAmountInput.val()) || 0;
        const subtotal = price * qty - discAmount;
        subtotalInput.val(subtotal.toFixed(2));

        // ========================
        // CARI DISKON FREE GOOD
        // ========================
        const applicableFree = DATA_DISKON_FREE.find((d) => {
            const minSmall = PackingList.convertToSmallest(
                UOM_CONVERSION,
                d.product_id,
                d.unit_id,
                d.min_qty,
            );
            const maxSmall = PackingList.convertToSmallest(
                UOM_CONVERSION,
                d.product_id,
                d.unit_id,
                d.max_qty,
            );

            const isApplicable =
                d.product_id == productId &&
                qtySmallest >= minSmall &&
                qtySmallest <= maxSmall &&
                (!d.customer_id || d.customer_id == customerId) &&
                today >= d.berlaku_from;
            return isApplicable;
        });

        // Jika ada free good
        if (applicableFree) {
            const freeQty = applicableFree.free_qty || 0;

            // Cek apakah baris free good sudah pernah ditambahkan
            const exists =
                tr.next('tr[data-free-for="' + productId + '"]').length > 0;

            if (!exists) {
                const freeRow = `
                    <tr class="input freegood" data-free-for="${productId}">
                        <td>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary" type="button" disabled onclick="PackingList.showDataProduct(this)">Free</button>
                                <input disabled type="text" id="product" class="form-control"
                                    data_id="${applicableFree.free_product}"
                                    value="${applicableFree.free_product_name ||
                    "Free Product"
                    }">
                            </div>
                        </td>
                        <td id="unit" data_id="${applicableFree.free_unit}">
                            ${applicableFree.free_unit_name || ""}
                        </td>
                        <td><input type="number" class="form-control" id="qty" value="${freeQty}" disabled></td>
                        <td><input type="number" class="form-control" id="unit_price" value="0" disabled></td>
                        <td><input type="number" class="form-control" id="disc_percent" value="0" disabled></td>
                        <td><input type="number" class="form-control" id="disc_amount" value="0" disabled></td>
                        <td><input type="text" class="form-control" id="subtotal" value="0" disabled></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger" disabled onclick="PackingList.removeRow(this)"><i class="bx bx-gift"></i></button></td>
                    </tr>
                `;

                tr.after(freeRow);
            }
        } else {
            // Hapus baris freegood lama jika qty tidak lagi memenuhi
            tr.next('tr.freegood[data-free-for="' + productId + '"]').remove();
        }

        // Update total keseluruhans
        PackingList.hitungSummaryAll();
    },

    convertToSmallest: (UOM_CONVERSIONS, productId, satuanId, qty) => {
        const uom = UOM_CONVERSIONS.find(
            (u) => u.product_id == productId && u.unit_id == satuanId,
        );
        if (!uom) return qty; // fallback jika tidak ditemukan
        return qty * uom.conversion;
    },

    changeCustomer: (elm) => {
        const table = $("table#table-items tbody tr.input");
        let result = [];

        table.each((index, elm) => {
            if (index > 0) {
                $(elm).remove();
            }

            $(elm).find("input").val("");
            $(elm).find("td#unit").text("");
            $(elm).find("td#unit").attr("data_id", "");
            $(elm).find("#price").attr("data_id", "");
        });
    },

    getCustomer: (elm) => {
        const url = $("input#url").val();
        const id = $("input#id").val();
        const salesman = $(elm).val();
        if (id == "") {
            window.location.href = url + "?salesman=" + salesman;
        } else {
            window.location.href = url + "&salesman=" + salesman;
        }
    },

    editReload: () => {
        const id = $("#id").val();
        if (id != "") {
            const table = $("table#table-items tbody tr.input");
            let resultProduct = [];

            table.each((index, elm) => {
                const $row = $(elm);
                const isFreeGood = $row.hasClass("freegood");

                if (!isFreeGood) {
                    resultProduct.push({
                        product_id:
                            $row.find("#product").attr("data_id") || null,
                        product_name: $row.find("#product").val() || "",
                        unit_id: $row.find("td#unit").attr("data_id") || null,
                    });
                }
            });

            // 2️⃣ Hapus duplikat berdasarkan product_id
            resultProduct = resultProduct.filter(
                (value, index, self) =>
                    index ===
                    self.findIndex((t) => t.product_id === value.product_id),
            );

            // 3️⃣ Loop per product_id
            resultProduct.forEach((item) => {
                const { product_id, product_name, unit_id } = item;

                // Contoh: panggil fungsi per produk
                PackingList.showDiscountProduct(
                    [product_id],
                    [product_name],
                    [unit_id],
                );
                PackingList.showDiscountFreeProduct(
                    [product_id],
                    [product_name],
                    [unit_id],
                );
                PackingList.showQtySmallestProduct(
                    [product_id],
                    [product_name],
                    [unit_id],
                );
            });
        }
    },
};

$(function () {
    PackingList.setSelect2();
    PackingList.getData();
    PackingList.getDataSr();
    PackingList.editReload();
});
