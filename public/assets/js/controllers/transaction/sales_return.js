let elmChoose;
let discProduct = [];
let SalesReturn = {
    module: () => {
        return "transaksi/sales_return";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + SalesReturn.module();
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
        window.location.href = url.base_url(SalesReturn.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(SalesReturn.module()) + "add";
    },

    getPostItem: () => {
        const rows = $("#table-items tbody tr");
        let result = [];

        rows.each((index, elm) => {
            const $row = $(elm);

            const remove = $row.hasClass("remove") ? 1 : 0;
            const invoice_detail_id = $row.attr("invoice_detail_id") || null;
            const qty_return = parseFloat($row.find("#qty_return").val()) || 0;
            const qty_return_old = parseFloat($row.find("#qty_return").attr("qty_return_old")) || 0;
            const unit_price = parseFloat($row.find("td#unit_price").text()) || 0;
            const discount_amount = parseFloat($row.find("td#discount_amount").text()) || 0;
            const discount_return = parseFloat($row.find("td#discount_amount").attr("discount_return")) || 0;
            const tax_amount = parseFloat($row.find("#tax").text()) || 0;
            const type_tax = $row.find("#tax").attr("type_tax") || null;
            const tax_id = $row.find("#tax").attr("data_id") || null;
            const tax_rate = parseFloat($row.find("#tax").attr("tax_rate")) || 0;
            const tax_amount_return = parseFloat($row.find("#tax").attr("tax_amount")) || 0;

            result.push({
                id: $row.attr("data_id") || null,
                product_id: $row.find("#product_id").attr("data_id") || null,
                qty_return: qty_return,
                unit_price: unit_price,
                discount_amount: discount_amount,
                tax_amount: tax_amount,
                type_tax: type_tax,
                tax_rate: tax_rate,
                tax: tax_id,
                invoice_detail_id: invoice_detail_id,
                discount_return: discount_return,
                tax_amount_return: tax_amount_return,
                qty_return_old: qty_return_old,
                remove: remove,
            });
        });

        return result;
    },

    getPostInput: () => {
        let data = {
            id: $("#id").val() || null,
            return_number: $("#return_number").val() || null,
            return_date: $("#return_date").val() || null,
            customer_id: $("#customer_id").attr("data_id") || null,
            invoice_id: $("#invoice_id").attr("data_id") || null,
            return_type: $("#return_type").val() || null,
            refund_amount: parseFloat($("#refund_amount").val()) || 0,
            deposit_amount: parseFloat($("#deposit_amount").val()) || 0,
            reason: $("#reason").val() || null,
            items: SalesReturn.getPostItem(),
        };

        return data;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = SalesReturn.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(SalesReturn.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": SalesReturn.csrf_token(),
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
                            SalesReturn.back();
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
        window.location.href = url.base_url(SalesReturn.module()) + "/";
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
                url: url.base_url(SalesReturn.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": SalesReturn.csrf_token(),
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
                    data: "invoice_number",
                },
                {
                    data: "nama_customer",
                },
                {
                    data: "return_date",
                },
                {
                    data: "return_type",
                },
                {
                    data: "refund_amount",
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
                        var html = '';
                        // var html = `<a href='${url.base_url(
                        //     SalesReturn.module()
                        // )}cetak?id=${data}' data_id="${
                        //     row.id
                        // }" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-printer"></i></a>&nbsp;`;
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                SalesReturn.module()
                            )}ubah?id=${data}' data_id="${
                                row.id
                            }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            if (row.status == "DRAFT") {
                                html += `<button type="button" data_id="${row.id}" onclick="SalesReturn.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
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
            url: url.base_url(SalesReturn.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": SalesReturn.csrf_token(),
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
            url: url.base_url(SalesReturn.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": SalesReturn.csrf_token(),
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

    posted: (elm) => {
        let params = {};
        params.id = $("#id").val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(SalesReturn.moduleApi()) + "posted",
            headers: {
                "X-CSRF-TOKEN": SalesReturn.csrf_token(),
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

    showModalCustomer: (elm) => {
        let params = {};

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(SalesReturn.moduleApi()) + "showModalCustomer",
            headers: {
                "X-CSRF-TOKEN": SalesReturn.csrf_token(),
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
                SalesReturn.getDataCustomer();
            },
        });
    },

    showModalInvoice: (elm) => {
        let params = {};
        const customer_id = $("#customer_id").val();
        if (customer_id == "") {
            message.sweetError("Informasi", "Pilih Customer Terlebih Dahulu");
            return;
        }

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(SalesReturn.moduleApi()) + "showModalInvoice",
            headers: {
                "X-CSRF-TOKEN": SalesReturn.csrf_token(),
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
                SalesReturn.getDataInvoice();
            },
        });
    },

    getDataCustomer: () => {
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
                url: url.base_url(SalesReturn.moduleApiCustomer()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": SalesReturn.csrf_token(),
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
                    data: "nama_customer",
                },
                {
                    data: "customer_category_name",
                },
                {
                    data: "city_name",
                },
                {
                    data: "kecamatan_name",
                },
                {
                    data: "kelurahan_name",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' code="${row.code}" nama_customer="${row.nama_customer}" onclick="SalesReturn.pilihDataCust(this, event)" data_id="${row.id}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        return html;
                    },
                },
            ],
        });
    },

    getDataInvoice: () => {
        let tableData = $("table#table-data-modal");
        const params = {
            customer: $("#customer_id").attr("data_id"),
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
                url: url.base_url(SalesReturn.moduleApi()) + `getDataInvoice`,
                type: "POST",
                data: params,
                headers: {
                    "X-CSRF-TOKEN": SalesReturn.csrf_token(),
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
                    data: "invoice_number",
                },
                {
                    data: "invoice_date",
                },
                {
                    data: "total_amount",
                },
                {
                    data: "status",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' invoice_number="${row.invoice_number}" onclick="SalesReturn.pilihDataInvoice(this, event)" data_id="${row.id}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        return html;
                    },
                },
            ],
        });
    },

    pilihDataCust: (elm, e) => {
        e.preventDefault();
        let nama_customer = $(elm).attr("nama_customer");
        let code_customer = $(elm).attr("code");
        let customer = $(elm).attr("data_id");
        let data_id = $(elm).attr("data_id");

        $("#customer_id").val(customer + "//" + nama_customer);
        $("#customer_id").attr("data_id", data_id);

        $("button.btn-close").trigger("click");
    },

    pilihDataInvoice: (elm, e) => {
        e.preventDefault();
        let invoice_number = $(elm).attr("invoice_number");
        let data_id = $(elm).attr("data_id");

        $("#invoice_id").val(data_id + "//" + invoice_number);
        $("#invoice_id").attr("data_id", data_id);

        $("button.btn-close").trigger("click");

        SalesReturn.getProductInvoice(data_id);
    },

    getProductInvoice: (invoice) => {
        let params = {
            invoice: invoice,
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(SalesReturn.moduleApi()) + "getProductInvoice",
            headers: {
                "X-CSRF-TOKEN": SalesReturn.csrf_token(),
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
                table_items.find("tbody").html(resp);
                SalesReturn.hitungSummaryAll();
            },
        });
    },

    changeQtyRetur: (elm) => {
        const tr = $(elm).closest("tr");

        // Ambil value input
        // const qty_retur = parseFloat(tr.find("input#qty_return").val()) || 0;
        // const outstanding = parseFloat(tr.find("input#outstanding_amount").val()) || 0;
        // const outstanding_new = outstanding - allocated;

        // Hitung summary total
        SalesReturn.hitungSummaryAll();
    },

    hitungSummaryAll: () => {
        let total_subtotal = 0;
        let total_disc = 0;
        let total_tax = 0;
        let total_net = 0;

        document.querySelectorAll("#table-items tbody tr").forEach((tr) => {
            const qty = parseFloat($(tr).find("input#qty_return").val()) || 0;
            const qty_invoice =
                parseFloat(
                    $(tr).find("input#qty_return").attr("qty_invoice")
                ) || 0;
            const price = parseFloat($(tr).find("td#unit_price").text()) || 0;
            const discount_amount =
                parseFloat($(tr).find("td#discount_amount").text()) || 0;
            const type_tax = $(tr).find("td#tax").attr("type_tax");
            const tax_rate =
                parseFloat($(tr).find("td#tax").attr("tax_rate")) || 0;

            // hitung subtotal dan diskon proporsional
            const subtotal = price * qty;
            const discount_return = (qty / qty_invoice) * discount_amount;
            const net_before_tax = subtotal - discount_return;
            $(tr).find("td#discount_amount").attr('discount_return', discount_return.toFixed(2));            

            let tax_amount = 0;
            let net_total = 0;

            switch (type_tax.toUpperCase()) {
                case "INCLUDE":
                    // subtotal sudah include pajak → pisahkan pajak
                    tax_amount =
                        net_before_tax - net_before_tax / (1 + tax_rate / 100);
                    net_total = net_before_tax; // total sudah termasuk pajak
                    break;
                case "EXCLUDE":
                    // subtotal belum termasuk pajak → tambahkan pajak
                    tax_amount = net_before_tax * (tax_rate / 100);
                    net_total = net_before_tax + tax_amount;
                    break;
                case "NON-TAX":
                default:
                    tax_amount = 0;
                    net_total = net_before_tax;
                    break;
            }

            $(tr).find("td#tax").attr('tax_amount', tax_amount.toFixed(2));

            total_subtotal += subtotal;
            total_disc += discount_return;
            total_tax += tax_amount;
            total_net += net_total;
        });

        document.getElementById("total-return").textContent =
        total_net.toFixed(2);

        const return_type = $("#return_type").val();
        if(return_type == "DEPOSIT"){
            $("input#refund_amount").val(0);
            $("input#deposit_amount").val(total_net.toFixed(2));
        }else{
            $("input#refund_amount").val(total_net.toFixed(2));
            $("input#deposit_amount").val(0);
        }
    },

    removeRow: (elm) => {
        const data_id = $(elm).closest("tr").attr("data_id");
        if (data_id == "") {
            $(elm).closest("tr").remove();
        } else {
            $(elm).closest("tr").addClass("remove");
            $(elm).closest("tr").addClass("d-none");
        }

        SalesReturn.hitungSummaryAll();
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

        const UOM_CONVERSION = SalesReturn.getDataUomConversion();
        const DATA_DISKON = SalesReturn.getDataDiskon();
        const DATA_DISKON_FREE = SalesReturn.getDataDiskonFreeGood();

        // Konversi qty input ke satuan terkecil
        const qtySmallest = SalesReturn.convertToSmallest(
            UOM_CONVERSION,
            productId,
            satuanId,
            qty
        );

        // Cari data diskon yang cocok
        const applicable = DATA_DISKON.find((d) => {
            // konversi range min/max ke satuan terkecil
            const minSmall = SalesReturn.convertToSmallest(
                UOM_CONVERSION,
                d.product_id,
                d.unit_id,
                d.min_qty
            );
            const maxSmall = SalesReturn.convertToSmallest(
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
            const minSmall = SalesReturn.convertToSmallest(
                UOM_CONVERSION,
                d.product_id,
                d.unit_id,
                d.min_qty
            );
            const maxSmall = SalesReturn.convertToSmallest(
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
                                <button class="btn btn-outline-secondary" type="button" disabled onclick="SalesReturn.showDataProduct(this)">Free</button>
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
                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger" disabled onclick="SalesReturn.removeRow(this)"><i class="bx bx-gift"></i></button></td>
                    </tr>
                `;

                tr.after(freeRow);
            }
        } else {
            // Hapus baris freegood lama jika qty tidak lagi memenuhi
            tr.next('tr.freegood[data-free-for="' + productId + '"]').remove();
        }

        // Update total keseluruhans
        SalesReturn.hitungSummaryAll();
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
                SalesReturn.showDiscountProduct(
                    [product_id],
                    [product_name],
                    [unit_id]
                );
                SalesReturn.showDiscountFreeProduct(
                    [product_id],
                    [product_name],
                    [unit_id]
                );
                SalesReturn.showQtySmallestProduct(
                    [product_id],
                    [product_name],
                    [unit_id]
                );
            });
        }
    },
};

$(function () {
    SalesReturn.setSelect2();
    SalesReturn.getData();
    SalesReturn.editReload();
});
