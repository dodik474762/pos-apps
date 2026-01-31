let Pasar = {
    module: () => {
        return "master/pasar";
    },

    moduleAcc: () => {
        return "approval/customer";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + Pasar.module();
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
        window.location.href = url.base_url(Pasar.module()) + "/";
    },

    cancelAcc: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(Pasar.moduleAcc()) + "/";
    },

    back: (elm) => {
        window.location.href = url.base_url(Pasar.module()) + "/";
    },

    backAcc: (elm) => {
        window.location.href = url.base_url(Pasar.moduleAcc()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(Pasar.module()) + "add";
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

    getPostInput: () => {
        let formData = new FormData();

        formData.append("id", $("input#id").val());
        formData.append("nama_pasar", $("#nama_pasar").val());
        formData.append("kota", $("#kota").val());
        formData.append("provinsi", $("#provinsi").val());
        formData.append("kecamatan", $("#kecamatan").val());
        formData.append("latitude", $("#latitude").val());
        formData.append("longitude", $("#longitude").val());
        return formData;
    },


    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = Pasar.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(Pasar.moduleApi()) + "submit",
                processData: false, // 🔥 WAJIB
                contentType: false, // 🔥 WAJIB
                headers: {
                    "X-CSRF-TOKEN": Pasar.csrf_token(),
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
                            Pasar.back();
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


    reject:(elm, e)=>{
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
                Pasar.approve(elm, e, 'rej', result.value);
            }else{
                message.sweetError("Informasi", "Data Belum Lengkap");
                return false;
            }
        })
    },

    approve: (elm, e, status = 'acc', remarks = '') => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = Pasar.getPostInput();
            params.status = status;
            params.remarks = remarks;

            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(Pasar.moduleApi()) + "approve",
                headers: {
                    "X-CSRF-TOKEN": Pasar.csrf_token(),
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
                            Pasar.backAcc();
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
                url: url.base_url(Pasar.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": Pasar.csrf_token(),
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
                    data: "nama_pasar",
                },
                {
                    data: "kecamatan_name",
                },
                {
                    data: "city_name",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                Pasar.module()
                            )}ubah?id=${data}' data_id="${
                                row.id
                            }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            html += `<button type="button" data_id="${row.id}" onclick="Pasar.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
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
            url: url.base_url(Pasar.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": Pasar.csrf_token(),
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
            url: url.base_url(Pasar.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": Pasar.csrf_token(),
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
                "X-CSRF-TOKEN": Pasar.csrf_token(),
            },
            url: url.base_url(Pasar.moduleApi()) + "getCity",
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
                "X-CSRF-TOKEN": Pasar.csrf_token(),
            },
            url: url.base_url(Pasar.moduleApi()) + "getKecamatan",
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
                "X-CSRF-TOKEN": Pasar.csrf_token(),
            },
            url: url.base_url(Pasar.moduleApi()) + "getKelurahan",
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

    changeCreditLimit:(elm)=>{
        const top = $(elm).val();
        if(top == '3'){
            $('#credit_limit').val(0);
            $('#credit_limit').attr('disabled',true);
        }else{
            $('#credit_limit').attr('disabled',false);
        }
    }
};

$(function () {
    Pasar.setSelect2();
    Pasar.getData();
});
