let elmChoose;
let discProduct = [];
let SalesOrder = {
    module: () => {
        return "transaksi/sales_order";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + SalesOrder.module();
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
        window.location.href = url.base_url(SalesOrder.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(SalesOrder.module()) + "add";
    },

    getPostItem: () => {
        const table = $("table#table-items tbody tr.input");
        let result = [];

        table.each((index, elm) => {
            const $row = $(elm);
            const isFreeGood = $row.hasClass("freegood");

            result.push({
                id: $row.attr("data_id") || null,
                product_id: $row.find("#product").attr("data_id") || null,
                product_name: $row.find("#product").val() || "",
                qty: parseFloat($row.find("#qty").val()) || 0,
                unit_id: $row.find("td#unit").attr("data_id") || null,
                price: isFreeGood
                    ? 0
                    : parseFloat($row.find("#unit_price").val()) || 0,
                disc_percent: isFreeGood
                    ? 0
                    : parseFloat($row.find("#disc_percent").val()) || 0,
                disc_amount: isFreeGood
                    ? 0
                    : parseFloat($row.find("#disc_amount").val()) || 0,
                subtotal: isFreeGood
                    ? 0
                    : parseFloat($row.find("#subtotal").val()) || 0,
                is_freegood: isFreeGood ? 1 : 0,
                free_for: isFreeGood ? $row.data("free-for") || null : null, // referensi produk asal
                remove: $row.hasClass("remove") ? 1 : 0,
            });
        });

        return result;
    },

    getPostInput: () => {
        let data = {
            id: $("#id").val() || null,
            so_number: $("#so_number").val() || null,
            so_date: $("#so_date").val() || null,
            salesman: $("#salesman").val() || null,
            customer_id: $("#customer_id").val() || null,
            payment_term: $("#payment_term").val() || null,
            currency: $("#currency").val() || null,
            remarks: $("#remarks").val() || "",
            total_amount: parseFloat($("#total-harga").text()) || 0,
            items: SalesOrder.getPostItem(),
        };

        return data;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = SalesOrder.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(SalesOrder.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": SalesOrder.csrf_token(),
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
                            SalesOrder.back();
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
        window.location.href = url.base_url(SalesOrder.module()) + "/";
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
                url: url.base_url(SalesOrder.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": SalesOrder.csrf_token(),
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
                    data: "so_number",
                },
                {
                    data: "so_date",
                },
                {
                    data: "nama_customer",
                },
                {
                    data: "total_amount",
                },
                {
                    data: "currency_code",
                },
                {
                    data: "created_by_name",
                },
                {
                    data: "status",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = `<a href='${url.base_url(
                            SalesOrder.module()
                        )}cetak?id=${data}' data_id="${
                            row.id
                        }" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-printer"></i></a>&nbsp;`;
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                SalesOrder.module()
                            )}ubah?id=${data}' data_id="${
                                row.id
                            }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            if (row.status == "draft") {
                                html += `<button type="button" data_id="${row.id}" onclick="SalesOrder.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
                            }
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
            url: url.base_url(SalesOrder.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": SalesOrder.csrf_token(),
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
            url: url.base_url(SalesOrder.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": SalesOrder.csrf_token(),
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
        const customer = $("#customer_id").val();
        if (customer == "") {
            message.sweetError("Informasi", "Pilih Customer");
            return false;
        }

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(SalesOrder.moduleApi()) + "showDataProduct",
            headers: {
                "X-CSRF-TOKEN": SalesOrder.csrf_token(),
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
                SalesOrder.getDataProduct();
            },
        });
    },

    getDataProduct: () => {
        let tableData = $("table#table-data-modal");
        const params = {
            customer: $("#customer_id").val(),
        };
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
                url: url.base_url(SalesOrder.moduleApi()) + `getDataProduct`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": SalesOrder.csrf_token(),
                },
                data: params,
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
                    data: "min_qty",
                },
                {
                    data: "max_qty",
                },
                {
                    data: "customer_name",
                },
                {
                    data: "harga",
                },
                {
                    data: "date_start",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' produk_id="${row.id}" unit="${row.unit_tujuan_id}" unit_name="${row.unit_tujuan_name}"
                        code="${row.code}" produk_name="${row.name}"
                        price="${row.harga}"
                        price_id="${row.price_id}"
                        onclick="SalesOrder.pilihDataProduct(this, event)"
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
        let price = $(elm).attr("price");
        let price_id = $(elm).attr("price_id");
        $(elmChoose)
            .closest("div")
            .find("input")
            .val(product_uom_id + "//" + produk_id + "//" + produk_name);
        $(elmChoose).closest("div").find("input").attr("data_id", produk_id);

        $(elmChoose).closest("tr").find("td#unit").text(unit_name);
        $(elmChoose).closest("tr").find("td#unit").attr("data_id", unit);
        $(elmChoose).closest("tr").find("#unit_price").val(price);
        $(elmChoose)
            .closest("tr")
            .find("#unit_price")
            .attr("data_id", price_id);
        $("button.btn-close").trigger("click");

        SalesOrder.showDiscountProduct(produk_id, produk_name, unit);
        SalesOrder.showDiscountFreeProduct(produk_id, produk_name, unit);
        SalesOrder.showQtySmallestProduct(produk_id, produk_name, unit);
    },

    showDiscountProduct: (produk_id, produk_name, unit) => {
        let params = {
            customer: $("#customer_id").val(),
            produk_id: produk_id,
            unit: unit,
            produk_name: produk_name,
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(SalesOrder.moduleApi()) + "showDiscountProduct",
            headers: {
                "X-CSRF-TOKEN": SalesOrder.csrf_token(),
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
                const table_items = $("#table-data-diskon");
                table_items.find("tbody").append(resp);
            },
        });
    },

    showDiscountFreeProduct: (produk_id, produk_name, unit) => {
        let params = {
            customer: $("#customer_id").val(),
            produk_id: produk_id,
            unit: unit,
            produk_name: produk_name,
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url:
                url.base_url(SalesOrder.moduleApi()) +
                "showDiscountFreeProduct",
            headers: {
                "X-CSRF-TOKEN": SalesOrder.csrf_token(),
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
                const table_items = $("#table-data-diskon-free");
                table_items.find("tbody").append(resp);
            },
        });
    },

    showQtySmallestProduct: (produk_id, produk_name, unit) => {
        let params = {
            customer: $("#customer_id").val(),
            produk_id: produk_id,
            unit: unit,
            produk_name: produk_name,
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url:
                url.base_url(SalesOrder.moduleApi()) + "showQtySmallestProduct",
            headers: {
                "X-CSRF-TOKEN": SalesOrder.csrf_token(),
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
                const table_items = $("#table-data-uom");
                table_items.find("tbody").append(resp);
            },
        });
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
        SalesOrder.hitungSummaryAll();
    },

    hitungSummaryAll: () => {
        let total = 0;
        document.querySelectorAll("#table-items tbody tr").forEach((tr) => {
            const subtotal =
                parseFloat(tr.querySelector("#subtotal").value) || 0;
            total += subtotal;
        });
        document.getElementById("total-harga").textContent = total.toFixed(2);
    },

    removeRow: (elm) => {
        const data_id = $(elm).closest("tr").attr("data_id");
        if (data_id == "") {
            $(elm).closest("tr").remove();
        } else {
            $(elm).closest("tr").addClass("remove");
            $(elm).closest("tr").addClass("d-none");
        }

        const product_id = $(elm).closest("tr").find("input#product").val();
        const splitProductId = product_id.split("//");
        const programDiskon = $(`.diskon-` + splitProductId[1]);
        programDiskon.remove();
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
                    parseFloat($(elm).attr("discount_value"))
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

        const UOM_CONVERSION = SalesOrder.getDataUomConversion();
        const DATA_DISKON = SalesOrder.getDataDiskon();
        const DATA_DISKON_FREE = SalesOrder.getDataDiskonFreeGood();

        // Konversi qty input ke satuan terkecil
        const qtySmallest = SalesOrder.convertToSmallest(
            UOM_CONVERSION,
            productId,
            satuanId,
            qty
        );

        // Cari data diskon yang cocok
        const applicable = DATA_DISKON.find((d) => {
            // konversi range min/max ke satuan terkecil
            const minSmall = SalesOrder.convertToSmallest(
                UOM_CONVERSION,
                d.product_id,
                d.unit_id,
                d.min_qty
            );
            const maxSmall = SalesOrder.convertToSmallest(
                UOM_CONVERSION,
                d.product_id,
                d.unit_id,
                d.max_qty
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
                    (price * qty * applicable.discount_value) / 100
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
            const minSmall = SalesOrder.convertToSmallest(
                UOM_CONVERSION,
                d.product_id,
                d.unit_id,
                d.min_qty
            );
            const maxSmall = SalesOrder.convertToSmallest(
                UOM_CONVERSION,
                d.product_id,
                d.unit_id,
                d.max_qty
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
                                <button class="btn btn-outline-secondary" type="button" disabled onclick="SalesOrder.showDataProduct(this)">Free</button>
                                <input disabled type="text" id="product" class="form-control"
                                    data_id="${applicableFree.free_product}"
                                    value="${
                                        applicableFree.free_product_name ||
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
                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger" disabled onclick="SalesOrder.removeRow(this)"><i class="bx bx-gift"></i></button></td>
                    </tr>
                `;

                tr.after(freeRow);
            }
        } else {
            // Hapus baris freegood lama jika qty tidak lagi memenuhi
            tr.next('tr.freegood[data-free-for="' + productId + '"]').remove();
        }

        // Update total keseluruhans
        SalesOrder.hitungSummaryAll();
    },

    convertToSmallest: (UOM_CONVERSIONS, productId, satuanId, qty) => {
        const uom = UOM_CONVERSIONS.find(
            (u) => u.product_id == productId && u.unit_id == satuanId
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

        const top = $(elm).find("option:selected").attr("top");
        $('#payment_term').val(top);
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
                    self.findIndex((t) => t.product_id === value.product_id)
            );

            // 3️⃣ Loop per product_id
            resultProduct.forEach((item) => {
                const { product_id, product_name, unit_id } = item;

                // Contoh: panggil fungsi per produk
                SalesOrder.showDiscountProduct(
                    [product_id],
                    [product_name],
                    [unit_id]
                );
                SalesOrder.showDiscountFreeProduct(
                    [product_id],
                    [product_name],
                    [unit_id]
                );
                SalesOrder.showQtySmallestProduct(
                    [product_id],
                    [product_name],
                    [unit_id]
                );
            });
        }
    },
};

$(function () {
    SalesOrder.setSelect2();
    SalesOrder.getData();
    SalesOrder.editReload();
});
