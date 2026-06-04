let Customer = {
    module: () => {
        return "master/customer";
    },

    moduleAcc: () => {
        return "approval/customer";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApiProduct: () => {
        return "api/master/product";
    },

    moduleApi: () => {
        return "api/" + Customer.module();
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
        window.location.href = url.base_url(Customer.module()) + "/";
    },

    cancelAcc: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(Customer.moduleAcc()) + "/";
    },

    back: (elm) => {
        window.location.href = url.base_url(Customer.module()) + "/";
    },

    backAcc: (elm) => {
        window.location.href = url.base_url(Customer.moduleAcc()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(Customer.module()) + "add";
    },

    // getPostInput: () => {
    //     let data = {
    //         id: $("input#id").val(),
    //         nama_customer: $("#nama_customer").val(),
    //         pic: $("#pic").val(),
    //         phone: $("#phone").val(),
    //         office_contact: $("#office_contact").val(),
    //         email: $("#email").val(),
    //         address: $("#address").val(),
    //         kota: $("#kota").val(),
    //         provinsi: $("#provinsi").val(),
    //         npwp: $("#npwp").val(),
    //         currency: $("#currency").val(),
    //         price_list: $("#price_list").val(),
    //         payment_terms: $("#payment_terms").val(),
    //         credit_limit: $("#credit_limit").val(),
    //         customer_category: $("#customer_category").val(),
    //         no_ktp: $("#no_ktp").val(),
    //         kecamatan: $("#kecamatan").val(),
    //         kelurahan: $("#kelurahan").val(),
    //         reference_number: $("#reference_number").val(),
    //         max_retur: $("#max_retur").val(),
    //         latitude: $("#latitude").val(),
    //         longitude: $("#longitude").val(),
    //     };

    //     return data;
    // },

    getPostItemPrice: () => {
        const table = $('table#table-price').find('tbody').find('tr.input');
        const result = [];
        $.each(table, function () {
            const id = $(this).attr('data_id');
            const product = $(this).find('#product').val();
            const uom = $(this).find('td#uom').text();
            const min_qty = $(this).find('#min_qty').val();
            const type_price = $(this).find('#type_price').val();
            const max_qty = $(this).find('#max_qty').val();
            const price = $(this).find('#price').val();
            const date_start = $(this).find('#date_start').val();
            const params = {
                id: id,
                product: product,
                uom: uom,
                type_price: type_price,
                min_qty: min_qty,
                max_qty: max_qty,
                price: price,
                date_start: date_start
            };

            result.push(params);
        });

        return result;
    },

    getPostInput: () => {
        let formData = new FormData();

        const items_price = Customer.getPostItemPrice();

        formData.append("id", $("input#id").val());
        formData.append("nama_customer", $("#nama_customer").val());
        formData.append("pic", $("#pic").val());
        formData.append("phone", $("#phone").val());
        formData.append("office_contact", $("#office_contact").val());
        formData.append("email", $("#email").val());
        formData.append("address", $("#address").val());
        formData.append("kota", $("#kota").val());
        formData.append("provinsi", $("#provinsi").val());
        formData.append("npwp", $("#npwp").val());
        formData.append("currency", $("#currency").val());
        formData.append("price_list", $("#price_list").val());
        formData.append("payment_terms", $("#payment_terms").val());
        formData.append("credit_limit", $("#credit_limit").val());
        formData.append("customer_category", $("#customer_category").val());
        formData.append("no_ktp", $("#no_ktp").val());
        formData.append("kecamatan", $("#kecamatan").val());
        formData.append("kelurahan", $("#kelurahan").val());
        formData.append("reference_number", $("#reference_number").val());
        formData.append("max_retur", $("#max_retur").val());
        formData.append("latitude", $("#latitude").val());
        formData.append("longitude", $("#longitude").val());
        formData.append("pasar", $("#pasar").val());
        formData.append("channel_outlet", $("#channel_outlet").val());
        formData.append("sub_channel_outlet", $("#sub_channel_outlet").val());
        formData.append("min_invoice", $("#min_invoice").val());
        formData.append("items_price", JSON.stringify(items_price));

        // FOTO (single upload)
        let photo = $("#photo_path")[0].files[0];
        if (photo) {
            formData.append("photo_path", photo);
        }

        let photo_ktp = $("#foto_ktp_path")[0].files[0];
        if (photo_ktp) {
            formData.append("foto_ktp_path", photo);
        }

        let photo_npwp = $("#foto_npwp_path")[0].files[0];
        if (photo_npwp) {
            formData.append("foto_npwp_path", photo);
        }

        return formData;
    },


    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = Customer.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(Customer.moduleApi()) + "submit",
                processData: false, // 🔥 WAJIB
                contentType: false, // 🔥 WAJIB
                headers: {
                    "X-CSRF-TOKEN": Customer.csrf_token(),
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
                            Customer.back();
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
        //swal alert dialog confirm with input remarks
        Swal.fire({
            title: "Reject Data",
            text: "Masukkan Alasan Penolakan",
            input: "text",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
        }).then((result) => {
            if (result.value) {
                Customer.approve(elm, e, 'rej', result.value);
            } else {
                message.sweetError("Informasi", "Data Belum Lengkap");
                return false;
            }
        })
    },

    showDataProduct: (elm) => {
        let params = {};

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(Customer.moduleApi()) + "showDataProduct",
            headers: {
                "X-CSRF-TOKEN": Customer.csrf_token(),
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
                Customer.getDataProduct();
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
                url: url.base_url(Customer.moduleApi()) + `getDataProduct`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": Customer.csrf_token(),
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
                        onclick="Customer.pilihDataProduct(this, event)"
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
            .val(produk_id + "//" + produk_name);
        $(elmChoose).closest('tr').find('td#uom').html(unit + "-" + unit_name);
        $("button.btn-close").trigger("click");
    },

    approve: (elm, e, status = 'acc', remarks = '') => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = Customer.getPostInput();
            params.status = status;
            params.remarks = remarks;

            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(Customer.moduleApi()) + "approve",
                headers: {
                    "X-CSRF-TOKEN": Customer.csrf_token(),
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
                            Customer.backAcc();
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
                url: url.base_url(Customer.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": Customer.csrf_token(),
                },
            },
            deferRender: true,
            createdRow: function (row, data, dataIndex) {
                // console.log('row', $(row));
            },
            dom: $('#akses_session').val() == 'superadmin' ? "Bftrip" : "-",
            buttons: [
                {
                    extend: "excel",
                    filename: "Customer",
                    action: newexportaction,
                },
            ],
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
                    data: "numbering_code",
                },
                {
                    data: "nama_customer",
                },
                {
                    data: "customer_category_name",
                },
                {
                    data: "pic",
                },
                {
                    data: "email",
                },
                {
                    data: "phone",
                },
                {
                    data: "channel_outlet",
                },
                {
                    data: "sub_channel_outlet",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                Customer.module()
                            )}ubah?id=${data}' data_id="${row.id
                                }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            html += `<button type="button" data_id="${row.id}" onclick="Customer.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
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
                url: url.base_url(Customer.moduleApi()) + `getDataAcc`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": Customer.csrf_token(),
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
                    data: "numbering_code",
                },
                {
                    data: "nama_customer",
                },
                {
                    data: "customer_category_name",
                },
                {
                    data: "pic",
                },
                {
                    data: "email",
                },
                {
                    data: "phone",
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
                                Customer.module()
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
            url: url.base_url(Customer.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": Customer.csrf_token(),
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
            url: url.base_url(Customer.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": Customer.csrf_token(),
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

    getCity: (elm) => {
        const province = $(elm).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                province: province,
            },
            headers: {
                "X-CSRF-TOKEN": Customer.csrf_token(),
            },
            url: url.base_url(Customer.moduleApi()) + "getCity",
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
                    const cityOption = $("select#kota");
                    cityOption.find("option").remove();
                    cityOption.append('<option value=""></option>');
                    $.each(resp.data, function (key, value) {
                        cityOption.append(
                            '<option value="' +
                            value.id +
                            '">' +
                            value.name +
                            "</option>"
                        );
                    });
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    getKecamatan: (elm) => {
        const kota = $(elm).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                kota: kota,
            },
            headers: {
                "X-CSRF-TOKEN": Customer.csrf_token(),
            },
            url: url.base_url(Customer.moduleApi()) + "getKecamatan",
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
                    const cityOption = $("select#kecamatan");
                    cityOption.find("option").remove();
                    cityOption.append('<option value=""></option>');
                    $.each(resp.data, function (key, value) {
                        cityOption.append(
                            '<option value="' +
                            value.id +
                            '">' +
                            value.name +
                            "</option>"
                        );
                    });
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    getKelurahan: (elm) => {
        const kecamatan = $(elm).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                kecamatan: kecamatan,
            },
            headers: {
                "X-CSRF-TOKEN": Customer.csrf_token(),
            },
            url: url.base_url(Customer.moduleApi()) + "getKelurahan",
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
                    const cityOption = $("select#kelurahan");
                    cityOption.find("option").remove();
                    cityOption.append('<option value=""></option>');
                    $.each(resp.data, function (key, value) {
                        cityOption.append(
                            '<option value="' +
                            value.id +
                            '">' +
                            value.name +
                            "</option>"
                        );
                    });
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    changeCreditLimit: (elm) => {
        const top = $(elm).val();
        if (top == '3') {
            $('#credit_limit').val(0);
            $('#credit_limit').attr('disabled', true);
        } else {
            $('#credit_limit').attr('disabled', false);
        }
    },

    addItemPrice: (elm, e) => {
        e.preventDefault();
        let params = {};
        params.id = $("input#id").val();
        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(Customer.moduleApi()) + "addItemPrice",
            headers: {
                "X-CSRF-TOKEN": Customer.csrf_token(),
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
                const tablePrice = $("table#table-price").find("tbody");
                tablePrice.append(resp);
            },
        });
    },

    removeItemPrice: (elm, e) => {
        e.preventDefault();
        const data_id = $(elm).closest("tr").attr("data_id");
        if (data_id == "") {
            $(elm).closest("tr").remove();
        } else {
            Customer.removeUomPrice(data_id);
        }
    },

    removeUomPrice: (id) => {
        let params = {
            id: id,
        };
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(Customer.moduleApiProduct()) + "removeUomPrice",
            headers: {
                "X-CSRF-TOKEN": Customer.csrf_token(),
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


// untuk export all data
function newexportaction(e, dt, button, config) {
    var self = this;
    var oldStart = dt.settings()[0]._iDisplayStart;
    dt.one("preXhr", function (e, s, data) {
        // Just this once, load all data from the server...
        data.start = 0;
        data.length = 2147483647;
        dt.one("preDraw", function (e, settings) {
            // Call the original action function
            if (button[0].className.indexOf("buttons-copy") >= 0) {
                $.fn.dataTable.ext.buttons.copyHtml5.action.call(
                    self,
                    e,
                    dt,
                    button,
                    config,
                );
            } else if (button[0].className.indexOf("buttons-excel") >= 0) {
                $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config)
                    ? $.fn.dataTable.ext.buttons.excelHtml5.action.call(
                        self,
                        e,
                        dt,
                        button,
                        config,
                    )
                    : $.fn.dataTable.ext.buttons.excelFlash.action.call(
                        self,
                        e,
                        dt,
                        button,
                        config,
                    );
            } else if (button[0].className.indexOf("buttons-csv") >= 0) {
                $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config)
                    ? $.fn.dataTable.ext.buttons.csvHtml5.action.call(
                        self,
                        e,
                        dt,
                        button,
                        config,
                    )
                    : $.fn.dataTable.ext.buttons.csvFlash.action.call(
                        self,
                        e,
                        dt,
                        button,
                        config,
                    );
            } else if (button[0].className.indexOf("buttons-pdf") >= 0) {
                $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config)
                    ? $.fn.dataTable.ext.buttons.pdfHtml5.action.call(
                        self,
                        e,
                        dt,
                        button,
                        config,
                    )
                    : $.fn.dataTable.ext.buttons.pdfFlash.action.call(
                        self,
                        e,
                        dt,
                        button,
                        config,
                    );
            } else if (button[0].className.indexOf("buttons-print") >= 0) {
                $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
            }
            dt.one("preXhr", function (e, s, data) {
                // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                // Set the property to what it was before exporting.
                settings._iDisplayStart = oldStart;
                data.start = oldStart;
            });
            // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
            setTimeout(dt.ajax.reload, 0);
            // Prevent rendering of the full data to the DOM
            return false;
        });
    });
    // Requery the server with the new one-time export settings
    dt.ajax.reload();
}

$(function () {
    Customer.setSelect2();
    Customer.getData();
    Customer.getDataAcc();
});
