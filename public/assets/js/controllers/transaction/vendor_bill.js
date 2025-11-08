let elmChoose;
let itemsChoose = [];
let VendorBill = {
    module: () => {
        return "transaksi/vendor-bill";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + VendorBill.module();
    },

    moduleApiPo: () => {
        return "api/transaksi/purchase_order";
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
        window.location.href = url.base_url(VendorBill.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(VendorBill.module()) + "add";
    },

    getPostInput: () => {
        let items = VendorBill.getPostItem();

        let data = {
            id: $("#id").val(),
            payment_number: $("#payment_number").val(),
            payment_date: $("#payment_date").val(),
            vendor: $("#vendor").val(),
            payment_method: $("#payment_method").val(),
            reference_number: $("#reference_number").val(),
            remarks: $("#remarks").val(),
            total_payment: $("#total_payment").val().replace(/,/g, ""), // hilangkan koma
            account_id: $("#account_id").val(),
            items: items,
        };

        return data;
    },

    // Ambil data dari setiap baris invoice
    getPostItem: () => {
        let items = [];

        $("#detail-body tr.input").each(function () {
            let tr = $(this);
            let data_id = tr.attr("data_id");
            let id_detail = tr.attr("id_detail");
            let invoice_number = tr.find("#invoice_number").val();
            let invoice_date = tr.find("#invoice_date").val();
            let total_amount = tr.find("#total_amount").val();
            let outstanding = tr.find("#outstanding").val();
            let amount_paid = tr.find("#amount_paid").val();
            let remaining = tr.find("#remaining").val();

            // hanya push jika ada invoice_number atau amount_paid > 0
            if (invoice_number && parseFloat(amount_paid) > 0) {
                items.push({
                    id: data_id,
                    id_detail: id_detail,
                    invoice_number: invoice_number,
                    invoice_date: invoice_date,
                    total_amount: total_amount,
                    outstanding: outstanding,
                    amount_paid: amount_paid,
                    remaining: remaining,
                    remove: tr.hasClass("remove") ? 1 : 0,
                });
            }
        });

        return items;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = VendorBill.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(VendorBill.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": VendorBill.csrf_token(),
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
                            VendorBill.back();
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
        window.location.href = url.base_url(VendorBill.module()) + "/";
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
                url: url.base_url(VendorBill.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": VendorBill.csrf_token(),
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
                    data: "payment_number",
                },
                {
                    data: "payment_date",
                },
                {
                    data: "nama_vendor",
                },
                {
                    data: "total_payment",
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
                        // var html = `<a href='${url.base_url(
                        //     VendorBill.module()
                        // )}cetak?id=${data}' data_id="${
                        //     row.id
                        // }" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-printer"></i></a>&nbsp;`;
                        let html = '';
                        if (updateAction == 1) {
                            if (row.status == "draft") {
                                html += `<a href='${url.base_url(
                                    VendorBill.module()
                                )}ubah?id=${data}' data_id="${
                                    row.id
                                }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                            }
                        }
                        if (deleteAction == 1) {
                            if (row.status == "draft") {
                                html += `<button type="button" data_id="${row.id}" onclick="VendorBill.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
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
            url: url.base_url(VendorBill.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": VendorBill.csrf_token(),
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
            url: url.base_url(VendorBill.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": VendorBill.csrf_token(),
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

    showDataInvoice: (elm) => {
        let params = {
            vendor: $("#vendor").val(),
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(VendorBill.moduleApi()) + "showDataInvoice",
            headers: {
                "X-CSRF-TOKEN": VendorBill.csrf_token(),
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
                VendorBill.getDataProduct();
            },
        });
    },

    loadInvoices: (elm) => {
        let params = {
            vendor: $("#vendor").val(),
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(VendorBill.moduleApi()) + "loadInvoices",
            headers: {
                "X-CSRF-TOKEN": VendorBill.csrf_token(),
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
                $("table#table-items").find("tbody").html(resp);

                // const vendor = $('#purchase_order').find('option:selected').attr('vendor');
                // const vendor_name = $('#purchase_order').find('option:selected').attr('vendor_name');
                // $('input#vendor').val(vendor_name);
                // $('input#vendor').attr('data_id', vendor);
                GoodReceipt.hitungSummaryAll();
            },
        });
    },

    getListItemOutstandingPO: (elm) => {
        let params = {
            po: $("#purchase_order").val(),
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url:
                url.base_url(VendorBill.moduleApi()) +
                "getListItemOutstandingPO",
            headers: {
                "X-CSRF-TOKEN": VendorBill.csrf_token(),
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
                $("table#table-items").find("tbody").html(resp);

                const vendor = $("#purchase_order")
                    .find("option:selected")
                    .attr("vendor");
                const vendor_name = $("#purchase_order")
                    .find("option:selected")
                    .attr("vendor_name");
                $("input#vendor").val(vendor_name);
                $("input#vendor").attr("data_id", vendor);
                VendorBill.hitungSummaryAll();
            },
        });
    },

    getDataProduct: (po) => {
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
                    url.base_url(VendorBill.moduleApiPo()) +
                    `getDataProductPoDetail`,
                type: "POST",
                data: {
                    vendor: $("#vendor").val(),
                    itemsChoose: itemsChoose,
                },
                headers: {
                    "X-CSRF-TOKEN": VendorBill.csrf_token(),
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
                    data: "po_code",
                },
                {
                    data: "product_code",
                },
                {
                    data: "product_name",
                },
                {
                    data: "unit_name",
                },
                {
                    data: "qty",
                },
                {
                    data: "purchase_price",
                },
                {
                    data: "diskon_persen",
                },
                {
                    data: "diskon_nominal",
                },
                {
                    data: "tax_rate",
                },
                {
                    data: "tax_amount",
                },
                {
                    data: "subtotal",
                },
                {
                    data: "status",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' produk_id="${row.product}" unit="${row.unit}"
                        unit_name="${row.unit_name}"
                        po_number="${row.po_code}"
                        purchase_price="${row.purchase_price}"
                        qty="${row.qty}"
                        produk_name="${row.product_name}"
                        data_id="${row.id}"
                        diskon_persen="${row.diskon_persen}"
                        diskon_nominal="${row.diskon_nominal}"
                        tax_rate="${row.tax_rate}"
                        tax_amount="${row.tax_amount}"
                        subtotal="${row.subtotal}"
                        onclick="VendorBill.pilihDataProduct(this, event)"
                        class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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
        let qty = $(elm).attr("qty");
        let unit = $(elm).attr("unit");
        let unit_name = $(elm).attr("unit_name");
        let purchase_price = $(elm).attr("purchase_price");
        let po_number = $(elm).attr("po_number");
        let purchase_order_detail_id = $(elm).attr("data_id");
        let tax_amount = $(elm).attr("tax_amount");
        let tax_rate = $(elm).attr("tax_rate");
        let diskon_persen = $(elm).attr("diskon_persen");
        let diskon_nominal = $(elm).attr("diskon_nominal");
        let subtotal = $(elm).attr("subtotal");

        qty = isNaN(parseFloat(qty)) ? 0 : parseFloat(qty);
        purchase_price = isNaN(parseFloat(purchase_price))
            ? 0
            : parseFloat(purchase_price);
        diskon_persen = isNaN(parseFloat(diskon_persen))
            ? 0
            : parseFloat(diskon_persen);
        diskon_nominal = isNaN(parseFloat(diskon_nominal))
            ? 0
            : parseFloat(diskon_nominal);
        const grandTotal = qty * purchase_price;
        const totalDiskonPersen = grandTotal * (diskon_persen / 100);
        const diskonTotal = totalDiskonPersen + diskon_nominal;
        $(elmChoose)
            .closest("div")
            .find("input")
            .val(
                po_number +
                    "//" +
                    purchase_order_detail_id +
                    "//" +
                    produk_id +
                    "//" +
                    produk_name
            );
        console.log($(elmChoose).closest("tr").find("td#unit"));
        $(elmChoose).closest("tr").find("input#qty").val(qty);
        $(elmChoose).closest("tr").find("input#unit").val(unit_name);
        $(elmChoose).closest("tr").find("input#unit").attr("data_id", unit);
        $(elmChoose).closest("tr").find("input#price").val(purchase_price);
        $(elmChoose).closest("tr").find("input#discount").val(diskonTotal);
        $(elmChoose).closest("tr").find("input#tax").val(tax_amount);
        $(elmChoose).closest("tr").find("input#subtotal").val(subtotal);
        itemsChoose.push({
            purchase_order_detail_id: purchase_order_detail_id,
        });
        $("button.btn-close").trigger("click");

        VendorBill.hitungSummaryAll();
    },

    calcRow: (elm) => {
        VendorBill.hitungSummaryAll();
    },

    hitungSummaryAll: () => {
        const table = $("table#table-items tbody tr.input");
        let subTotalAll = 0;

        table.each((index, elm) => {
            if (!$(elm).hasClass("remove")) {
                const amount =
                    parseFloat($(elm).find("input#amount_paid").val()) || 0;

                subTotalAll += amount;
            }
        });

        $("#total-payment-display").text(subTotalAll.toFixed(2));
        $("input#total_payment").val(subTotalAll.toFixed(2));
    },

    removeRow: (elm) => {
        let data_id = $(elm).closest("tr").attr("data_id");
        if (data_id == "") {
            // const po_detail = $(elm).closest("tr").find("input#po-detail").val();
            // if(po_detail != '') {
            //     const splitPoDetail = po_detail.split("//");
            //     data_id = splitPoDetail[1];
            // }
            $(elm).closest("tr").remove();
        } else {
            $(elm).closest("tr").addClass("remove");
            $(elm).closest("tr").addClass("d-none");
        }

        if (itemsChoose.length > 0) {
            for (let i = 0; i < itemsChoose.length; i++) {
                if (itemsChoose[i].purchase_order_detail_id == data_id) {
                    itemsChoose.splice(i, 1);
                    break;
                }
            }
        }
        VendorBill.hitungSummaryAll();
    },

    addRow: () => {
        const row = $("table#table-items")
            .find("tbody")
            .find("tr.input:last")
            .clone();
        row.removeClass("remove");
        row.removeClass("d-none");
        row.find("input").val("");
        row.find("input#unit").attr("data_id", "");
        row.attr("data_id", "");
        $("table#table-items").find("tbody").append(row);
    },
};

$(function () {
    VendorBill.setSelect2();
    VendorBill.getData();
});
