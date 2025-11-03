let elmChoose;
let PurchaseOrder = {
    module: () => {
        return "transaksi/purchase_order";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + PurchaseOrder.module();
    },

    moduleApiProduct: () => {
        return "api/master/product";
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
        window.location.href = url.base_url(PurchaseOrder.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(PurchaseOrder.module()) + "add";
    },

    getPostItem: () => {
        const table = $("table#table-items tbody tr.input");
        let result = [];

        table.each((index, elm) => {
            result.push({
                id: $(elm).attr("data_id"),
                product: $(elm).find("#product").val(),
                qty: $(elm).find("#qty").val(),
                unit: $(elm).find("td#unit").attr("data_id"),
                price: $(elm).find("#price").val(),
                disc_persen:
                    $(elm).find("#disc_persen").val() == ""
                        ? 0
                        : $(elm).find("#disc_persen").val(),
                disc_nominal:
                    $(elm).find("#disc_nominal").val() == ""
                        ? 0
                        : $(elm).find("#disc_nominal").val(),
                subtotal:
                    $(elm).find("#subtotal").val() == ""
                        ? 0
                        : $(elm).find("#subtotal").val(),
                tax:
                    $(elm).find("select#tax").val() == "" ? 0 : $(elm).find("select#tax").val(),
                tax_rate:
                    $(elm).find("select#tax option:selected").data("rate") || 0,
                tax_amount: $(elm).data("tax_amount") || 0,
                remove: $(elm).hasClass("remove") ? 1 : 0,
            });
        });

        return result;
    },

    getPostInput: () => {
        let data = {
            id: $("input#id").val(),
            po_date: $("#po_date").val(),
            vendor: $("#vendor").val(),
            warehouse: $("#warehouse").val(),
            remarks: $("#remarks").val(),
            grand_total: $("#total-harga").text(),
            est_received_date: $("#est_received_date").val(),
            items: PurchaseOrder.getPostItem(),
        };

        return data;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = PurchaseOrder.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(PurchaseOrder.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": PurchaseOrder.csrf_token(),
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
                            PurchaseOrder.back();
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
        window.location.href = url.base_url(PurchaseOrder.module()) + "/";
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
                url: url.base_url(PurchaseOrder.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": PurchaseOrder.csrf_token(),
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
                    data: "po_date",
                },
                {
                    data: "nama_vendor",
                },
                {
                    data: "grand_total",
                },
                {
                    data: "currency_code",
                },
                {
                    data: "created_by_name",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = `<a href='${url.base_url(
                            PurchaseOrder.module()
                        )}cetak?id=${data}' data_id="${
                            row.id
                        }" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-printer"></i></a>&nbsp;`;
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                PurchaseOrder.module()
                            )}ubah?id=${data}' data_id="${
                                row.id
                            }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            html += `<button type="button" data_id="${row.id}" onclick="PurchaseOrder.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
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
            url: url.base_url(PurchaseOrder.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": PurchaseOrder.csrf_token(),
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
            url: url.base_url(PurchaseOrder.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": PurchaseOrder.csrf_token(),
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

    showDataProduct: (elm) => {
        let params = {};

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PurchaseOrder.moduleApi()) + "showDataProduct",
            headers: {
                "X-CSRF-TOKEN": PurchaseOrder.csrf_token(),
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
                PurchaseOrder.getDataProduct();
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
                url:
                    url.base_url(PurchaseOrder.moduleApiProduct()) +
                    `getDataProduct`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": PurchaseOrder.csrf_token(),
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
                        onclick="PurchaseOrder.pilihDataProduct(this, event)"
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
            .val(product_uom_id + "//" + produk_id + "//" + produk_name);
        console.log($(elmChoose).closest("tr").find("td#unit"));
        $(elmChoose).closest("tr").find("td#unit").text(unit_name);
        $(elmChoose).closest("tr").find("td#unit").attr("data_id", unit);
        $("button.btn-close").trigger("click");
    },

    calcRow: (elm) => {
        const tr = $(elm).closest("tr");

        // Ambil value input
        const qty = parseFloat(tr.find("input#qty").val()) || 0;
        const price = parseFloat(tr.find("input#price").val()) || 0;
        const disc_persen = parseFloat(tr.find("input#disc_persen").val()) || 0;
        const disc_nominal =
            parseFloat(tr.find("input#disc_nominal").val()) || 0;

        // Hitung subtotal sebelum pajak
        const subTotal = qty * price;
        const disc = subTotal * (disc_persen / 100) + disc_nominal;
        const dpp = subTotal - disc; // DPP = dasar pengenaan pajak

        // Ambil rate pajak dari option terpilih
        const taxRate =
            parseFloat(tr.find("select#tax option:selected").data("rate")) || 0;
        const taxAmount = dpp * (taxRate / 100);

        // Total per baris = DPP + pajak
        const subtotalResult = dpp + taxAmount;

        // Update input subtotal
        tr.find("input#subtotal").val(subtotalResult.toFixed(2));

        // Simpan data pajak di row untuk reference
        tr.data("dpp", dpp);
        tr.data("tax_amount", taxAmount);
        tr.data("tax_rate", taxRate);

        // Hitung summary total
        PurchaseOrder.hitungSummaryAll();
    },

    hitungSummaryAll: () => {
        const table = $("table#table-items tbody tr.input");
        let totalDpp = 0;
        let totalTax = 0;

        table.each((index, elm) => {
            const subtotal =
                parseFloat($(elm).find("input#subtotal").val()) || 0;
            const tax = parseFloat($(elm).data("tax_amount")) || 0;
            const dpp = parseFloat($(elm).data("dpp")) || 0;

            totalDpp += dpp;
            totalTax += tax;
        });

        const grandTotal = totalDpp + totalTax;

        $("#total-harga").text(grandTotal.toFixed(2));
        $("#total-pajak").text(totalTax.toFixed(2));
    },

    removeRow: (elm) => {
        const data_id = $(elm).closest("tr").attr("data_id");
        if (data_id == "") {
            $(elm).closest("tr").remove();
        } else {
            $(elm).closest("tr").addClass("remove");
            $(elm).closest("tr").addClass("d-none");
        }
    },

    addRow: () => {
        const row = $("table#table-items")
            .find("tbody")
            .find("tr.input:last")
            .clone();
        row.removeClass("remove");
        row.removeClass("d-none");
        row.find("input").val("");
        row.find("td#unit").text("");
        row.find("td#unit").attr("data_id", "");
        row.attr("data_id", "");
        $("table#table-items").find("tbody").append(row);
    },
};

$(function () {
    PurchaseOrder.setSelect2();
    PurchaseOrder.getData();
});
