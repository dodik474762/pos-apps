let objInput = null;
let PromoItem = {
    module: () => {
        return "master/promo_item";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + PromoItem.module();
    },

    moduleProductApi: () => {
        return "api/master/product";
    },

    moduleApiUsers: () => {
        return "api/master/users";
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
        window.location.href = url.base_url(PromoItem.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(PromoItem.module()) + "add";
    },

    addItem: (elm, e) => {
        e.preventDefault();
        let table = $(elm)
            .closest("div")
            .find("table#table-routing")
            .find("tbody")
            .find("tr.input:last");
        let newTr = table.clone();
        newTr.find("input").val("");
        newTr.attr("data_id", "");
        newTr
            .find("td#action")
            .html(
                `<button type="button" onclick="PromoItem.deleteItem(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`
            );
        table.after(newTr);
    },

    addReminderItem: (elm, e) => {
        e.preventDefault();
        let table = $(elm)
            .closest("div")
            .find("table#table-routing-reminder")
            .find("tbody")
            .find("tr.input:last");
        let newTr = table.clone();
        newTr.find("input").val("");
        newTr.attr("data_id", "");
        newTr
            .find("td#action")
            .html(
                `<button type="button" onclick="PromoItem.deleteItem(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`
            );
        table.after(newTr);
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

    getPostItem: () => {
        let data = $("table#table-routing").find("tbody").find("tr.input");
        let result = [];
        data.each((index, elm) => {
            result.push({
                id: $(elm).attr("data_id"),
                product: $(elm).find("input#product").val(),
                remove: $(elm).hasClass("remove") ? 1 : 0,
            });
        });

        return result;
    },

    getPostFreeItem: () => {
        let data = $("table#table-routing-reminder")
            .find("tbody")
            .find("tr.input");
        let result = [];
        data.each((index, elm) => {
            result.push({
                id: $(elm).attr("data_id"),
                product: $(elm).find("input#product").val(),
                qty: $(elm).find("input#qty").val(),
                remove: $(elm).hasClass("remove") ? 1 : 0,
            });
        });

        return result;
    },

    getPostInput: () => {
        let data = {
            id: $("input#id").val(),
            promo_name: $("#promo_name").val(),
            max_qty: $("#max_qty").val(),
            disc_value: $("#disc_value").val(),
            min_qty: $("#min_qty").val(),
            disc_type: $("#disc_type").val(),
            date_start: $("#date_start").val(),
            min_mix: $("#min_mix").val(),
            promo_item: PromoItem.getPostItem(),
            free_product: PromoItem.getPostFreeItem(),
        };

        return data;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = PromoItem.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(PromoItem.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": PromoItem.csrf_token(),
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
                            PromoItem.back();
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
        window.location.href = url.base_url(PromoItem.module()) + "/";
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
                url: url.base_url(PromoItem.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": PromoItem.csrf_token(),
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
                    data: "promo_name",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                PromoItem.module()
                            )}ubah?id=${data}' data_id="${
                                row.id
                            }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            html += `<button type="button" data_id="${row.id}" onclick="PromoItem.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
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
            url: url.base_url(PromoItem.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": PromoItem.csrf_token(),
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
            url: url.base_url(PromoItem.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": PromoItem.csrf_token(),
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

    setDate: () => {
        $("#search-datepicker").flatpickr({});
    },

    showDataUsers: (elm) => {
        let params = {};

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PromoItem.moduleApi()) + "showDataUsers",
            headers: {
                "X-CSRF-TOKEN": PromoItem.csrf_token(),
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
                objInput = elm;
                PromoItem.getDataUsers();
            },
        });
    },

    getDataUsers: () => {
        let tableData = $("table#table-data-users");
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
                    "pagination-rounded"
                );
            },
            ajax: {
                url: url.base_url(PromoItem.moduleApiUsers()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": PromoItem.csrf_token(),
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
                    data: "nik",
                },
                {
                    data: "username",
                },
                {
                    data: "nama_lengkap",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' nama_lengkap="${row.nama_lengkap}" onclick="PromoItem.pilihData(this, event)" data_id="${row.id}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        return html;
                    },
                },
            ],
        });
    },

    pilihData: (elm, e) => {
        e.preventDefault();
        let nama_lengkap = $(elm).attr("nama_lengkap");
        let dataId = $(elm).attr("data_id");
        if (objInput != null) {
            $(objInput)
                .closest("tr")
                .find("input#users")
                .val(dataId + "//" + nama_lengkap);
        }
        $("button.btn-close").trigger("click");
    },

    showDataProduct: (elm) => {
        let params = {};

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PromoItem.moduleProductApi()) + "showDataProduct",
            headers: {
                "X-CSRF-TOKEN": PromoItem.csrf_token(),
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
                PromoItem.getDataProduct();
            },
        });
    },

     getDataProduct: () => {
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
                    "pagination-rounded"
                );
            },
            ajax: {
                url: url.base_url(PromoItem.moduleProductApi()) + `getDataProduct`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": PromoItem.csrf_token(),
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
                    data: "name",
                },
                {
                    data: "unit_tujuan_name",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' produk_id="${row.id}" unit="${row.unit_tujuan_id}" unit_name="${row.unit_tujuan_name}" code="${row.code}" produk_name="${row.name}"
                        onclick="PromoItem.pilihDataProduct(this, event)"
                        data_id="${row.id_uom}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        return html;
                    },
                },
            ],
        });
    },

     pilihDataProduct: (elm, e) => {
        e.preventDefault();
        let produk_name = $(elm).attr("produk_name");
        let produk_id = $(elm).attr("produk_id");
        let unit = $(elm).attr("unit");
        let unit_name = $(elm).attr("unit_name");
        let product_uom_id = $(elm).attr("data_id");
        $(elmChoose)
            .closest("div")
            .find("input")
            .val(product_uom_id+"//"+produk_id+ "//" + produk_name+"//"+unit_name);
        $("button.btn-close").trigger("click");
    },
};

$(function () {
    PromoItem.setDate();
    PromoItem.setSelect2();
    PromoItem.getData();
});
