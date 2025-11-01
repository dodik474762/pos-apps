let filesUpload = null;
let Product = {
    module: () => {
        return "master/product";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + Product.module();
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
        window.location.href = url.base_url(Product.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(Product.module()) + "add";
    },

    getPostInput: () => {
        let data = {
            id: $("input#id").val(),
            name: $("#name").val(),
            model_number: $("#model_number").val(),
            product_type: $("#product_type").val(),
            unit: $("#unit").val(),
            remarks: $("#remarks").val(),
            purchase_price: $("#purchase_price").val(),
            selling_price: $("#selling_price").val(),
            file: $("input#file").attr("src"),
            tipe: $("input#file").attr("tipe"),
            file_name: $("input#file").val(),
        };

        return data;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = Product.getPostInput();
            const formData = new FormData();
            formData.append("data", JSON.stringify(params));
            formData.append("files[]", filesUpload);
            $.ajax({
                type: "POST",
                dataType: "json",
                // data: params,
                data: formData,
                processData: false,
                contentType: false,
                url: url.base_url(Product.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": Product.csrf_token(),
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
                            Product.back();
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
        window.location.href = url.base_url(Product.module()) + "/";
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
                url: url.base_url(Product.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": Product.csrf_token(),
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
                    data: "name",
                },
                {
                    data: "type",
                },
                {
                    data: "model_number",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                Product.module()
                            )}ubah?id=${data}' data_id="${
                                row.id
                            }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            html += `<button type="button" data_id="${row.id}" onclick="Product.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
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
            url: url.base_url(Product.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": Product.csrf_token(),
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
            url: url.base_url(Product.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": Product.csrf_token(),
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

    addFile: (elm) => {
        // Buat uploader secara dinamis
        var uploader = $(
            `<input type="file" id="file" accept="image/*;capture=camera" />`
        );
        var src_foto = $(`input#file`);

        // Tambahkan uploader ke body
        $("body").append(uploader);
        uploader.click();

        // Ketika ada perubahan (file terpilih)
        uploader.on("change", function () {
            var files = uploader.get(0).files[0];
            filesUpload = files;

            if (files) {
                var reader = new FileReader();
                var filename = files.name;
                var data_from_file = filename.split(".");
                var type_file = $.trim(
                    data_from_file[data_from_file.length - 1]
                ).toLowerCase();
                const sizeFiles = files.size / 1024 / 1024; // in MB

                // if(sizeFiles > 1){
                //     message.sweetError(
                //         "Gagal",
                //         "Ukuran file terlalu besar, maksimal 1 MB"
                //     );
                //     return false;
                // }

                // Cek jika format file sesuai
                if (["jpg", "jpeg", "png", "pdf"].includes(type_file)) {
                    reader.onload = function (event) {
                        var data = event.target.result;
                        src_foto.val(filename);
                        src_foto.attr("tipe", type_file);
                        src_foto.attr("src", data);
                    };
                    reader.readAsDataURL(files);
                } else {
                    // Jika format tidak sesuai
                    message.sweetError(
                        "Gagal",
                        "Format file salah, hanya bisa jpg, jpeg, png, dan pdf"
                    );
                }
            }
            // Hapus uploader setelah file dipilih atau proses selesai
            uploader.remove();
        });
    },

    formatRupiah: (num) => {
        return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
    },

    setNumeric: (elm, e) => {
        $(elm).divide({
            delimiter: ".",
            divideThousand: true,
            delimiterRegExp: /[^0-9\,]/g,
        });
    },

    addItemLevel: (elm, e) => {
        e.preventDefault();
        let params = {};
        params.id = $(elm).attr("data_id");
        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(Product.moduleApi()) + "addItemLevel",
            headers: {
                "X-CSRF-TOKEN": Product.csrf_token(),
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
                const tableatuan = $('table#table-satuan').find('tbody');
                tableatuan.append(resp);
            },
        });
    },

    removeItemLevel:(elm)=>{
        $(elm).closest('tr').remove();
    }
};

$(function () {
    Product.setSelect2();
    Product.getData();
});
