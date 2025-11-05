let elmChoose;
let PurchaseInvoice = {
    module: () => {
        return "transaksi/purchase-invoice";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + PurchaseInvoice.module();
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
        window.location.href = url.base_url(PurchaseInvoice.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(PurchaseInvoice.module()) + "add";
    },

    getPostItem: () => {
        const table = $("table#table-items tbody tr.input");
        let result = [];

        table.each((index, elm) => {
            result.push({
                id: $(elm).attr("data_id"),
                id_detail: $(elm).attr("id_detail"),
                product: $(elm).find("#po_detail").attr('data_id'),
                qty_received: $(elm).find("#qty_received").val(),
                qty_outstanding: $(elm).find("#qty_received").attr('max'),
                unit: $(elm).find("td#unit").attr("data_id"),
                unit_name: $(elm).find("td#unit").text(),
                lot_number: $(elm).find("#lot_number").val(),
                expired_date: $(elm).find("#expired_date").val(),
                subtotal: $(elm).find("#subtotal").val(),
                remove: $(elm).hasClass("remove") ? 1 : 0,
            });
        });

        return result;
    },

    getPostInput: () => {
        let data = {
            id: $("input#id").val(),
            received_date: $("#received_date").val(),
            vendor_name: $("#vendor").val(),
            vendor: $("#vendor").attr('data_id'),
            purchase_order: $("#purchase_order").val(),
            remarks: $("#remarks").val(),
            received_by: $("#received_by").val(),
            total_qty: $("#total-qty").text(),
            items: PurchaseInvoice.getPostItem(),
        };

        return data;
    },

    submit: (elm, e) => {
        e.preventDefault();
        let form = $(elm).closest("div.row");
        if (validation.runWithElement(form)) {
            let params = PurchaseInvoice.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(PurchaseInvoice.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": PurchaseInvoice.csrf_token(),
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
                            PurchaseInvoice.back();
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
        window.location.href = url.base_url(PurchaseInvoice.module()) + "/";
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
                url: url.base_url(PurchaseInvoice.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": PurchaseInvoice.csrf_token(),
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
                    data: "nama_vendor",
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
                            PurchaseInvoice.module()
                        )}cetak?id=${data}' data_id="${
                            row.id
                        }" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-printer"></i></a>&nbsp;`;
                        if (updateAction == 1) {
                            if(row.status == 'open'){
                                html += `<a href='${url.base_url(
                                    PurchaseInvoice.module()
                                )}ubah?id=${data}' data_id="${
                                    row.id
                                }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                            }
                        }
                        if (deleteAction == 1) {
                            if(row.status == 'open'){
                                html += `<button type="button" data_id="${row.id}" onclick="PurchaseInvoice.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
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
            url: url.base_url(PurchaseInvoice.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": PurchaseInvoice.csrf_token(),
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
            url: url.base_url(PurchaseInvoice.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": PurchaseInvoice.csrf_token(),
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

    showDataPOItem: (elm) => {
        let params = {
            po: $('#purchase_order').val()
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PurchaseInvoice.moduleApi()) + "showDataPOItem",
            headers: {
                "X-CSRF-TOKEN": PurchaseInvoice.csrf_token(),
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
                PurchaseInvoice.getDataProduct(po);
            },
        });
    },

    getListItemOutstandingPO: (elm) => {
        let params = {
            po: $('#purchase_order').val()
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(PurchaseInvoice.moduleApi()) + "getListItemOutstandingPO",
            headers: {
                "X-CSRF-TOKEN": PurchaseInvoice.csrf_token(),
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
                $('table#table-items').find('tbody').html(resp);

                const vendor = $('#purchase_order').find('option:selected').attr('vendor');
                const vendor_name = $('#purchase_order').find('option:selected').attr('vendor_name');
                $('input#vendor').val(vendor_name);
                $('input#vendor').attr('data_id', vendor);
                PurchaseInvoice.hitungSummaryAll();
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
                    url.base_url(PurchaseInvoice.moduleApiPo()) +
                    `getDataProduct`,
                type: "POST",
                data: {
                    po: po
                },
                headers: {
                    "X-CSRF-TOKEN": PurchaseInvoice.csrf_token(),
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
                        onclick="PurchaseInvoice.pilihDataProduct(this, event)"
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
        // const tr = $(elm).closest("tr");

        // // Ambil value input
        // const qty = parseFloat(tr.find("input#qty_received").val()) || 0;
        // const price = parseFloat(tr.find("input#po_detail").attr('price')) || 0;

        // // Hitung subtotal sebelum pajak
        // const subTotal = qty * price;
        // const disc = subTotal * (disc_persen / 100) + disc_nominal;
        // const dpp = subTotal - disc; // DPP = dasar pengenaan pajak

        // // Ambil rate pajak dari option terpilih
        // const taxRate =
        //     parseFloat(tr.find("select#tax option:selected").data("rate")) || 0;
        // const taxAmount = dpp * (taxRate / 100);

        // // Total per baris = DPP + pajak
        // const subtotalResult = dpp + taxAmount;

        // // Update input subtotal
        // tr.find("input#subtotal").val(subtotalResult.toFixed(2));

        // // Simpan data pajak di row untuk reference
        // tr.data("dpp", dpp);
        // tr.data("tax_amount", taxAmount);
        // tr.data("tax_rate", taxRate);

        // Hitung summary total
        PurchaseInvoice.hitungSummaryAll();
    },

    hitungSummaryAll: () => {
        const table = $("table#table-items tbody tr.input");
        let subTotalAll = 0;
        let qtyTotal = 0;

        table.each((index, elm) => {
            if(!$(elm).hasClass('remove')){
                const subtotal =
                    parseFloat($(elm).find("input#subtotal").val()) || 0;
                const qty =
                    parseFloat($(elm).find("input#qty_received").val()) || 0;

                subTotalAll += subtotal;
                qtyTotal += qty;
            }
        });

        const grandTotal = subTotalAll;

        console.log('qtyTotal', qtyTotal);

        $("#total-qty").text(qtyTotal.toFixed(2));
    },

    removeRow: (elm) => {
        const data_id = $(elm).closest("tr").attr("data_id");
        if (data_id == "") {
            $(elm).closest("tr").remove();
        } else {
            $(elm).closest("tr").addClass("remove");
            $(elm).closest("tr").addClass("d-none");
        }

        PurchaseInvoice.hitungSummaryAll();
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
    PurchaseInvoice.setSelect2();
    PurchaseInvoice.getData();
});
