let elmChoose;
let itemsChoose = [];
let PurchaseReturn = {
    module: () => {
        return "transaksi/purchase-return";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + PurchaseReturn.module();
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
        window.location.href = url.base_url(PurchaseReturn.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(PurchaseReturn.module()) + "add";
    },

    getPostInput: () => {
        let items = PurchaseReturn.getPostItem();

        let data = {
            id: $("#id").val(),
            return_type: $("#return_type").val(),
            reference_id: $("#reference_id").val(),
            return_date: $("#return_date").val(),
            reason: $("#reason").val(),
            status: $("#status").val(),
            total_amount: $("#total_amount").val().replace(/,/g, ""), // hilangkan koma ribuan
            warehouse_id: $("#warehouse_id").val(),
            vendor: $("#vendor").val(),
            items: items,
        };

        return data;
    },

    // Ambil data dari setiap baris invoice
    // Ambil data dari tabel detail barang
    getPostItem: () => {
        let items = [];

        $("#detail-body tr.input").each(function () {
            let tr = $(this);

            let item_id = tr.attr("data_id");
            let id_detail = tr.attr("id_detail");
            let qty = tr.find("#qty").val();
            let unit = tr.find("#unit").attr("data_id");
            let unit_price = tr.find("#unit_price").val();
            let total_price = tr.find("#total_price").val();
            let reason_detail = tr.find("#reason_detail").val();
            let reference_detail = tr.find("#reference_detail").val();

            // hanya push jika ada item dan qty > 0
            if (item_id && parseFloat(qty) > 0) {
                items.push({
                    item_id: item_id,
                    qty: qty,
                    unit: unit,
                    unit_price: unit_price,
                    total_price: total_price,
                    reason_detail: reason_detail,
                    reference_detail: reference_detail,
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
            let params = PurchaseReturn.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                url: url.base_url(PurchaseReturn.moduleApi()) + "submit",
                headers: {
                    "X-CSRF-TOKEN": PurchaseReturn.csrf_token(),
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
                            PurchaseReturn.back();
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
        window.location.href = url.base_url(PurchaseReturn.module()) + "/";
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
                url: url.base_url(PurchaseReturn.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": PurchaseReturn.csrf_token(),
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
                    data: "return_type",
                },
                {
                    data: "return_date",
                },
                {
                    data: "reason",
                },
                {
                    data: "currency_code",
                },
                {
                    data: "total_amount",
                },
                {
                    data: "created_by_name",
                },
                {
                    data: "status",
                },
                {
                    data: "nama_vendor",
                },
                {
                    data: "warehouse_name",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        // var html = `<a href='${url.base_url(
                        //     PurchaseReturn.module()
                        // )}cetak?id=${data}' data_id="${
                        //     row.id
                        // }" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-printer"></i></a>&nbsp;`;
                        let html = "";
                        if (updateAction == 1) {
                            if (row.status.toLowerCase() == "draft") {
                                html += `<a href='${url.base_url(
                                    PurchaseReturn.module()
                                )}ubah?id=${data}' data_id="${
                                    row.id
                                }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                            }
                        }
                        if (deleteAction == 1) {
                            if (row.status.toLowerCase() == "draft") {
                                html += `<button type="button" data_id="${row.id}" onclick="PurchaseReturn.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
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
            url: url.base_url(PurchaseReturn.moduleApi()) + "delete",
            headers: {
                "X-CSRF-TOKEN": PurchaseReturn.csrf_token(),
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
            url: url.base_url(PurchaseReturn.moduleApi()) + "confirmDelete",
            headers: {
                "X-CSRF-TOKEN": PurchaseReturn.csrf_token(),
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
            url: url.base_url(PurchaseReturn.moduleApi()) + "showDataInvoice",
            headers: {
                "X-CSRF-TOKEN": PurchaseReturn.csrf_token(),
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
                PurchaseReturn.getDataProduct();
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
            url: url.base_url(PurchaseReturn.moduleApi()) + "loadInvoices",
            headers: {
                "X-CSRF-TOKEN": PurchaseReturn.csrf_token(),
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
                url.base_url(PurchaseReturn.moduleApi()) +
                "getListItemOutstandingPO",
            headers: {
                "X-CSRF-TOKEN": PurchaseReturn.csrf_token(),
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
                PurchaseReturn.hitungSummaryAll();
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
                    url.base_url(PurchaseReturn.moduleApiPo()) +
                    `getDataProductPoDetail`,
                type: "POST",
                data: {
                    vendor: $("#vendor").val(),
                    itemsChoose: itemsChoose,
                },
                headers: {
                    "X-CSRF-TOKEN": PurchaseReturn.csrf_token(),
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
                        onclick="PurchaseReturn.pilihDataProduct(this, event)"
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

        PurchaseReturn.hitungSummaryAll();
    },

    calcRow: (elm) => {
        PurchaseReturn.hitungSummaryAll();
    },

    hitungSummaryAll: () => {
        const table = $("table#table-items tbody tr.input");
        let subTotalAll = 0;

        const type = $("#return_type").val();

        table.each((index, elm) => {
            if (!$(elm).hasClass("remove")) {
                const unit_price =
                    parseFloat($(elm).find("input#unit_price").val()) || 0;
                const qty = parseFloat($(elm).find("input#qty").val()) || 0;

                const amount = unit_price * qty;

                subTotalAll += amount;
            }
        });

        $("#total-return-display").text(subTotalAll.toFixed(2));
        $("input#total_amount").val(subTotalAll.toFixed(2));
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
        PurchaseReturn.hitungSummaryAll();
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

    changeType: function (el) {
        const newType = $(el).val();

        // Kosongkan reference dropdown
        $("#reference_id").html(
            '<option value="">-- Select Reference --</option>'
        );

        // Kosongkan tabel detail
        $("#detail-body").html(`
            <tr class="input">
                <td><input type="text" class="form-control" id="item_name" disabled></td>
                <td><input type="number" class="form-control" id="qty" value="0" onkeyup="PurchaseReturn.calcRow(this)"></td>
                <td><input type="number" class="form-control" id="unit_price" value="0" onkeyup="PurchaseReturn.calcRow(this)"></td>
                <td><input type="number" class="form-control" id="total_price" value="0" disabled></td>
                <td><input type="text" class="form-control" id="reason_detail" value=""></td>
                <td><input type="text" class="form-control" id="reference_detail" value="" disabled></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" onclick="PurchaseReturn.removeRow(this)">
                        <i class="bx bx-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `);

        // Reset total return display
        $("#total-return-display").text("0.00");

        // Jika supplier sudah dipilih, langsung load referensi sesuai tipe baru
        const supplierId = $("#vendor").val();
        if (supplierId) {
            PurchaseReturn.loadReference(el);
        }
    },

    loadReference: function (el) {
        const vendor = $("#vendor").val();
        const returnType = $("#return_type").val();

        if (!vendor || !returnType) {
            $("#reference_id").html(
                '<option value="">-- Select Reference --</option>'
            );
            return;
        }

        $.ajax({
            url: url.base_url(PurchaseReturn.moduleApi()) + `references`,
            method: "GET",
            data: {
                vendor: vendor,
                return_type: returnType,
            },
            headers: {
                "X-CSRF-TOKEN": PurchaseReturn.csrf_token(),
            },
            beforeSend: function () {
                message.loadingProses("Proses Pengambilan Data...");
            },
            success: function (res) {
                message.closeLoading();
                let options =
                    '<option value="">-- Select Reference --</option>';
                res.forEach((ref) => {
                    options += `<option value="${ref.id}">
                        ${ref.reference_number} | ${
                        ref.reference_date
                    } | ${parseFloat(ref.total_amount).toLocaleString()}
                    </option>`;
                });
                $("#reference_id").html(options);
            },
            error: function (xhr) {
                message.closeLoading();
                console.error("Failed to load references:", xhr.responseText);
                alert("Error loading references");
            },
        });
    },

    loadReferenceDetail: (el) => {
        const referenceId = $(el).val();
        const returnType = $("#return_type").val();
        if (!referenceId) {
            $("#detail-body").html("");
            $("#total-return-display").text("0.00");
            return;
        }

        $.ajax({
            url: url.base_url(PurchaseReturn.moduleApi()) + `reference-detail`,
            method: "GET",
            data: {
                id: referenceId,
                type: returnType,
            },
            beforeSend: () => {
                message.loadingProses("Proses Pengambilan Data...");
            },
            success: function (res) {
                message.closeLoading();
                let html = "";
                let total = 0;

                res.forEach((row, i) => {
                    const lineTotal =
                        parseFloat(row.qty) * parseFloat(row.unit_price);
                    total += lineTotal;

                    const qty_returned = isNaN(parseFloat(row.qty_returned)) ? 0 : parseFloat(row.qty_returned);
                    console.log('qty_returned', qty_returned);
                    const qty = parseFloat(row.qty) - qty_returned;

                    html += `
                    <tr class="input" data_id="${row.item_id}" id_detail="">
                        <td><input type="text" class="form-control" value="${
                            row.item_name
                        }" disabled></td>
                        <td><input type="number" class="form-control" id="qty" value="${
                            qty
                        }" onkeyup="PurchaseReturn.calcRow(this)"></td>
                        <td><input type="text" data_id="${
                            row.unit
                        }" id="unit" class="form-control" value="${
                        row.unit_name
                    }" disabled></td>
                        <td><input disabled type="number" class="form-control" id="unit_price" value="${parseFloat(
                            row.unit_price
                        ).toFixed(
                            2
                        )}" onkeyup="PurchaseReturn.calcRow(this)"></td>
                        <td><input type="number" class="form-control" id="total_price" value="${lineTotal.toFixed(
                            2
                        )}" disabled></td>
                        <td><input type="text" class="form-control" id="reason_detail" placeholder="Reason"></td>
                        <td><input type="text" class="form-control" id="reference_detail" value="${
                            returnType === "FROM_GR"
                                ? row.gr_detail_id
                                : row.invoice_detail_id
                        }" disabled></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger" onclick="PurchaseReturn.removeRow(this)">
                                <i class="bx bx-trash-alt"></i>
                            </button>
                        </td>
                    </tr>`;
                });

                $("#detail-body").html(html);
                $("#total-return-display").text(
                    total.toLocaleString("en-US", { minimumFractionDigits: 2 })
                );
                $("#total_amount").val(total);
            },
            error: function (xhr) {
                message.closeLoading();
                console.error("Error loading detail:", xhr.responseText);
                alert("Failed to load reference details");
            },
        });
    },
};

$(function () {
    PurchaseReturn.setSelect2();
    PurchaseReturn.getData();
});
