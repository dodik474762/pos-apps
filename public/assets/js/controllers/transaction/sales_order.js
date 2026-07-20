let elmChoose;
let discProduct = [];
let productCheckPromo = [];
let channel_outlet = null;
let sub_channel_outlet = null;
// Simpan sementara semua data harga+satuan dari baris produk yang dipilih
let _selectedProductRows = [];
let lastProductSearchKeyword = "";
let _suppressSalesmanChange = false;
let currentTab = 'all';

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
                tax: $row.find("#product").attr("tax") || null,
                tax_type: $row.find("#product").attr("tax_type") || null,
                tax_rate: $row.find("#product").attr("tax_rate") || null,
                product_name: $row.find("#product").val() || "",
                qty: parseFloat($row.find("#qty").val()) || 0,
                unit_id: $row.find("td#unit").attr("data_id") || null,
                price: isFreeGood
                    ? 0
                    : parseFloat($row.find("#unit_price").attr("price")) || 0,
                disc_percent: isFreeGood
                    ? 0
                    : parseFloat($row.find("#disc_percent").val()) || 0,
                disc_amount: isFreeGood
                    ? 0
                    : parseFloat($row.find("#disc_amount").attr("amount")) || 0,
                subtotal: isFreeGood
                    ? 0
                    : parseFloat($row.find("#subtotal").attr("subtotal")) || 0,
                tax_amount: isFreeGood
                    ? 0
                    : parseFloat($row.find("#tax_amount").attr("amount")) || 0,
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
            branch: $("#branch").val() || null,
            warehouse: $("#warehouse").val() || 1,
            discount_percent_header: $("#discount_percent_header").val() || 0,
            discount_amount_header:
                $("#discount_amount_header").attr("amount") || 0,
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
                    "pagination-rounded",
                );
            },
            ajax: {
                url: url.base_url(SalesOrder.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": SalesOrder.csrf_token(),
                },
                data: function (d) {
                    d.start_date = $("#start_date").val();
                    d.end_date = $("#end_date").val();
                    d.tab = currentTab;
                }
            },
            deferRender: true,
            createdRow: function (row, data, dataIndex) {
                // console.log('row', $(row));
            },
            dom: "Bftrip",
            buttons: [
                {
                    extend: "excel",
                    filename: "ReportSalesOrder",
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
                    data: "so_number",
                },
                {
                    data: "branch_name",
                },
                {
                    data: "warehouse_name",
                },
                {
                    data: "so_date",
                },
                {
                    data: "customer_code",
                },
                {
                    data: "nama_customer",
                },
                {
                    data: "net_total",
                    render: function (data, type, row) {
                        let value = parseFloat(data ?? 0);

                        if (isNaN(value)) {
                            value = 0;
                        }

                        if (type === "display" || type === "filter") {
                            return new Intl.NumberFormat("id-ID", {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }).format(value);
                        }

                        return value;
                    },
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
                            SalesOrder.module(),
                        )}cetak?id=${data}' data_id="${row.id
                            }" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-printer"></i></a>&nbsp;`;
                        html += `<a href='${url.base_url(
                            SalesOrder.module(),
                        )}detail?id=${data}' data_id="${row.id
                            }" class="btn btn-warning editable-submit btn-sm waves-effect waves-light"><i class="bx bx-show"></i></a>&nbsp;`;
                        if (updateAction == 1) {
                            html += `<a href='${url.base_url(
                                SalesOrder.module(),
                            )}ubah?id=${data}' data_id="${row.id
                                }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        }
                        if (deleteAction == 1) {
                            if (
                                row.status == "draft" ||
                                row.status == "submited"
                            ) {
                                html += `<button type="button" data_id="${row.id}" onclick="SalesOrder.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
                            }
                        }
                        return html;
                    },
                },
            ],
        });

        (data
            .buttons()
            .container()
            .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"),
            $(".dataTables_length select").addClass(
                "form-select form-select-sm",
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
                        "pagination-rounded",
                    );
                },
            }));
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

    confirmAlHandheld: (elm) => {
        let params = {};
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(SalesOrder.moduleApi()) + "confirmAlHandheld",
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
                    message.sweetSuccess("Informasi", "Data Berhasil Diproses");
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

    generateAll: (elm, e) => {
        e.preventDefault();
        let params = {};
        const start_date = $("#start_date").val();
        const end_date = $("#end_date").val();
        if (start_date == "" || end_date == "") {
            message.sweetError("Informasi", "Pilih Tanggal start dan end terlebih dahulu");
            return false;
        }

        params.start_date = start_date;
        params.end_date = end_date;

        const itemsChecked = $('input.check-item:checked');
        if (itemsChecked.length == 0) {
            message.sweetError("Informasi", "Pilih Data Terlebih Dahulu");
            return false;
        }

        params.items_checked = [];
        itemsChecked.each(function (index) {
            params.items_checked.push($(this).attr("data_id"));
        });


        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(SalesOrder.moduleApi()) + "generateAll",
            headers: {
                "X-CSRF-TOKEN": SalesOrder.csrf_token(),
            },

            beforeSend: () => {
                message.loadingProses("Proses Generate Data");
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
    },

    filterTab: (tab) => {
        currentTab = tab;
        $("#table-data").DataTable().ajax.reload();
    },

    filterData: () => {
        $("#table-data").DataTable().ajax.reload();
    },

    search: (elm, state = '') => {
        const url = $(elm).attr("url");
        const start_date = $("#start_date").val();
        const end_date = $("#end_date").val();
        if (start_date == "" || end_date == "") {
            message.sweetError("Informasi", "Pilih tanggal start dan end terlebih dahulu");
            return;
        }
        window.location.href = url + "?start_date=" + start_date + "&end_date=" + end_date + "&state=" + state;
    },

    getDataProduct: () => {
        let tableData = $("table#table-data-modal");
        const params = {
            customer: $("#customer_id").val(),
            principal: $("#principal").val(),
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
            search: {
                search: lastProductSearchKeyword,
            },
            // lengthChange: !1,
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>",
                },
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass(
                    "pagination-rounded",
                );
            },
            ajax: {
                url: url.base_url(SalesOrder.moduleApi()) + `getDataProduct`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": SalesOrder.csrf_token(),
                },
                data: function (d) {
                    d.principal = $("#principal").val(); // ambil nilai status dari elemen input/select
                    d.customer = $("#customer_id").val();
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
                    data: "nama_vendor",
                },
                {
                    data: "code",
                },
                {
                    data: "name",
                },
                // {
                //     data: "unit_tujuan_name",
                // },
                // {
                //     data: "min_qty",
                // },
                // {
                //     data: "max_qty",
                // },
                {
                    data: "customer_name",
                },
                // {
                //     data: "harga",
                // },
                // {
                //     data: "date_start",
                // },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' produk_id="${row.id}" unit="${row.unit_tujuan_id}" unit_name="${row.unit_tujuan_name}"
                        code="${row.code}" produk_name="${row.name}"
                        price="${row.harga}"
                        price_id="${row.price_id}"
                        tax="${row.tax_sale}"
                        tax_rate="${row.tax_rate}"
                        type_tax="${row.type_tax}"
                        onclick="SalesOrder.pilihProdukDulu(this, event)"
                        data_id="${row.id_uom}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        return html;
                    },
                },
            ],
        });

        data.on("search.dt", function () {
            lastProductSearchKeyword = data.search();
        });
    },

    pilihSatuan_fromRow: (row) => {
        // contoh isi
        console.log("Selected:", row);

        // lanjutkan logic yang sama seperti pilihSatuan()
        SalesOrder.pilihSatuan(null, row);
    },

    pilihProdukDulu: (elm, e) => {
        e.preventDefault();
        $("button.btn-close").trigger("click");
        const produk_id = $(elm).attr("produk_id");
        const params = {
            product_id: produk_id,
            customer: $("#customer_id").val(),
            salesman: $("#salesman").val()
        };

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(SalesOrder.moduleApi()) + "pilihProdukDulu",
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
            },
        });
    },

    pilihSatuan: (elm, row) => {
        if (!row) {
            row = elm;
            elm = null;
        }

        let qty = 1;
        if (elm) {
            qty =
                parseFloat($(elm).closest("tr").find(".qty-input").val()) || 1;
        }

        console.log("row chooce", row);
        // const row = _selectedProductRows[index];
        // if (!row) return;

        // Tutup modal satuan
        $("button.btn-close").trigger("click");

        // Jalankan logic yang sama dengan pilihDataProduct
        let produk_name = row.name;
        let produk_id = row.id;
        let unit = row.unit_tujuan_id;
        let unit_name = row.unit_tujuan_name;
        let product_uom_id = row.id_uom;
        let price = row.harga;
        let price_id = row.price_id;
        let tax = row.tax_sale;
        let tax_rate = row.tax_rate;
        let tax_type = row.type_tax;

        $(elmChoose)
            .closest("div")
            .find("input")
            .val(product_uom_id + "//" + produk_id + "//" + produk_name)
            .attr("data_id", produk_id)
            .attr("tax", tax)
            .attr("tax_type", tax_type)
            .attr("tax_rate", tax_rate);

        $(elmChoose)
            .closest("tr")
            .find("td#unit")
            .text(unit_name)
            .attr("data_id", unit);

        $(elmChoose)
            .closest("tr")
            .find("#unit_price")
            .attr("price", price)
            .attr("data_id", price_id)
            .val(
                new Intl.NumberFormat("id-ID", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(price),
            );

        $(elmChoose).closest("tr").find("#qty").val(qty);

        SalesOrder.showDiscountProduct(produk_id, produk_name, unit);
        SalesOrder.showDiscountFreeProduct(produk_id, produk_name, unit);
        SalesOrder.showPromoItem(produk_id, produk_name, unit, qty);
        SalesOrder.showQtySmallestProduct(produk_id, produk_name, unit);

        SalesOrder.calcRow(elmChoose);

        // Callback ke pilih product lagi di baris baru
        setTimeout(() => {
            SalesOrder.addRow();
            let newElm = $("table#table-items")
                .find("tbody")
                .find("tr.input:last")
                .find("input#product")
                .closest("div")
                .find("button");
            SalesOrder.showDataProduct(newElm[0] || newElm);
        }, 800);
    },

    pilihDataProduct_fromRow: (row) => {
        // Untuk kasus satuan cuma 1, langsung apply tanpa modal
        document.activeElement.blur(); // 🔥 penting
        $("button.btn-close").trigger("click");
        SalesOrder.pilihSatuan_fromRow(row);
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
        let tax = $(elm).attr("tax");
        let tax_rate = $(elm).attr("tax_rate");
        let tax_type = $(elm).attr("type_tax");
        $(elmChoose)
            .closest("div")
            .find("input")
            .val(product_uom_id + "//" + produk_id + "//" + produk_name);
        $(elmChoose).closest("div").find("input").attr("data_id", produk_id);
        $(elmChoose).closest("div").find("input").attr("tax", tax);
        $(elmChoose).closest("div").find("input").attr("tax_type", tax_type);
        $(elmChoose).closest("div").find("input").attr("tax_rate", tax_rate);

        $(elmChoose).closest("tr").find("td#unit").text(unit_name);
        $(elmChoose).closest("tr").find("td#unit").attr("data_id", unit);
        $(elmChoose).closest("tr").find("#unit_price").attr("price", price);
        $(elmChoose)
            .closest("tr")
            .find("#unit_price")
            .val(
                new Intl.NumberFormat("id-ID", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(price),
            );
        $(elmChoose)
            .closest("tr")
            .find("#unit_price")
            .attr("data_id", price_id);
        $("button.btn-close").trigger("click");

        SalesOrder.showDiscountProduct(produk_id, produk_name, unit);
        SalesOrder.showDiscountFreeProduct(produk_id, produk_name, unit);
        SalesOrder.showPromoItem(produk_id, produk_name, unit, 0);
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

    showPromoItem: (produk_id, produk_name, unit, qty) => {
        productCheckPromo.push({
            produk_id: produk_id,
            produk_name: produk_name,
            unit: unit,
            qty: qty,
        });

        let params = {
            customer: $("#customer_id").val(),
            items: productCheckPromo,
        };

        console.log("params promo", params);

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(SalesOrder.moduleApi()) + "showPromoItem",
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
                $("div#tab-pane-promo").html(resp);
                // table_items.find("tbody").append(resp);
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
        const price = parseFloat(tr.find("input#price").attr("price")) || 0;
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

    // hitungSummaryAll: () => {
    //   let total = 0;
    //   document.querySelectorAll("#table-items tbody tr").forEach((tr) => {
    //     const subtotal =
    //       parseFloat(tr.querySelector("#subtotal").getAttribute("subtotal")) || 0;
    //     console.log("subtotal", subtotal);
    //     const taxAmount =
    //       parseFloat(tr.querySelector("#tax_amount").getAttribute("amount")) || 0;
    //     total += subtotal + taxAmount;
    //   });

    //   document.getElementById("total-harga").textContent = total.toFixed(2);
    //   document.getElementById("total-harga-show").textContent =
    //     new Intl.NumberFormat("id-ID", {
    //       minimumFractionDigits: 2,
    //       maximumFractionDigits: 2,
    //     }).format(total);
    // },

    hitungSummaryAll: () => {
        let total = 0;
        const discAmountHeader =
            parseFloat($("#discount_amount_header").attr("amount")) || 0;

        // Hitung total DPP dulu untuk proporsi
        let totalDPP = 0;
        document.querySelectorAll("#table-items tbody tr").forEach((tr) => {
            const subtotal =
                parseFloat(
                    tr.querySelector("#subtotal")?.getAttribute("subtotal"),
                ) || 0;
            totalDPP += subtotal;
        });

        document.querySelectorAll("#table-items tbody tr").forEach((tr) => {
            const subtotal =
                parseFloat(
                    tr.querySelector("#subtotal")?.getAttribute("subtotal"),
                ) || 0;
            const taxAmount =
                parseFloat(
                    tr.querySelector("#tax_amount")?.getAttribute("amount"),
                ) || 0;

            if (subtotal == 0 && taxAmount == 0) return;

            if (discAmountHeader > 0 && totalDPP > 0) {
                // Kurangi DPP proporsional per baris
                const proporsi = subtotal / totalDPP;
                const discPorsi = discAmountHeader * proporsi;
                const dppAfterDisc = subtotal - discPorsi;

                // Recalc tax proporsional
                const taxRate =
                    parseFloat(
                        tr.querySelector("#product")?.getAttribute("tax_rate"),
                    ) || 0;
                const typeTax = tr
                    .querySelector("#product")
                    ?.getAttribute("tax_type");

                let taxAfterDisc = 0;
                if (typeTax == "include") {
                    taxAfterDisc =
                        dppAfterDisc - dppAfterDisc / (1 + taxRate / 100);
                } else {
                    taxAfterDisc = dppAfterDisc * (taxRate / 100);
                }

                total +=
                    dppAfterDisc + (typeTax == "include" ? 0 : taxAfterDisc);
            } else {
                // Tidak ada disc header, pakai existing
                total += subtotal + taxAmount;
            }
        });

        document.getElementById("total-harga").textContent = total.toFixed(2);
        document.getElementById("total-harga-show").textContent =
            new Intl.NumberFormat("id-ID", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(total);
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

        SalesOrder.hitungSummaryAll();
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
                    parseFloat($(elm).attr("discount_value")),
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

    recalculateAllRows: () => {
        SalesOrder._promoAppliedMap = {}; // reset map promo mix
        $("tr.input[data-promo-applied]").removeAttr("data-promo-applied");

        $("table#table-items tbody tr.input")
            .not(".freegood")
            .each(function () {
                SalesOrder.calcDiscRow(this);
            });
    },

    _promoAppliedMap: {},

    calcDiscRow: (elm) => {
        return;
        const tr = $(elm).closest("tr");
        const qty = parseFloat(tr.find("#qty").val()) || 0;
        const productId = tr.find("#product").attr("data_id");
        const satuanId = tr.find("td#unit").attr("data_id");
        let price = parseFloat(tr.find("#unit_price").attr("price")) || 0;
        const type_tax = tr.find("#product").attr("tax_type");
        const tax_rate = tr.find("#product").attr("tax_rate");
        const customerId = $("#customer_id").val();
        const today = new Date().toISOString().slice(0, 10);

        if (!productId) return;

        const UOM_CONVERSION = SalesOrder.getDataUomConversion();

        const discPercentInput = tr.find("#disc_percent");
        const discAmountInput = tr.find("#disc_amount");
        const subtotalInput = tr.find("#subtotal");
        const taxAmountInpute = tr.find("#tax_amount");
        const unitPriceInput = tr.find("#unit_price");
        taxAmountInpute.val("0");
        taxAmountInpute.attr("amount", "0");
        discPercentInput.val("0");
        discAmountInput.attr("amount", "0");
        discAmountInput.val("0");

        const promoHeaders = SalesOrder.getPromoHeader();
        if (promoHeaders.length > 0) {
            // semua product_id yang ada di SO
            const soProductIds = SalesOrder.getAllProductIdsInTable();
            const products = SalesOrder.getAllProductsInTable(); //promo item ini bisa digunakan jika satuan konversi produknya sama
            let qtySmallestAllProduct = 0;
            products.forEach((p) => {
                qtySmallestAllProduct += SalesOrder.convertToSmallest(
                    UOM_CONVERSION,
                    p.product_id,
                    p.unit_id,
                    p.qty,
                );
            });

            // ========================
            // LOOP 1: PROMO POTONG GRAND TOTAL
            // ========================
            for (let index = 0; index < promoHeaders.length; index++) {
                const promoHeader = promoHeaders[index];
                if (promoHeader.potong_grand_total != 1) continue; // skip yang bukan grand total

                const parent_id = promoHeader.id;
                const kelipatan = promoHeader.kelipatan;
                const discount_kategori = promoHeader.discount_kategori;

                const channelMatch =
                    !promoHeader.channel_outlet ||
                    promoHeader.channel_outlet == channel_outlet;
                const subChannelMatch =
                    !promoHeader.sub_channel_outlet ||
                    promoHeader.sub_channel_outlet == sub_channel_outlet;
                if (!channelMatch || !subChannelMatch) continue;

                const class_promo_item = "promo-item-" + parent_id;
                const promoProducts =
                    SalesOrder.getPromoProducts(class_promo_item);

                const productMatch = promoProducts.some(
                    (p) => p.product == tr.find("#product").attr("data_id"),
                );
                if (!productMatch) continue;

                const matchedPromoProducts = promoProducts.filter((p) =>
                    soProductIds.includes(String(p.product)),
                );
                const mixCount = matchedPromoProducts.length;
                const mixOk =
                    !promoHeader.min_mix ||
                    (mixCount >= promoHeader.min_mix &&
                        mixCount <= promoHeader.max_mix);

                if (!mixOk || today < promoHeader.date_start) continue;

                const promoMinSmall = SalesOrder.convertToSmallest(
                    UOM_CONVERSION,
                    productId,
                    promoHeader.unit_id,
                    promoHeader.min_qty,
                );
                const kelipatanSmall = SalesOrder.convertToSmallest(
                    UOM_CONVERSION,
                    productId,
                    promoHeader.unit_id,
                    kelipatan,
                );
                const rawSubtotal = price * qty;

                let promoApplicable = false;
                let pengaliFix = 1;

                if (discount_kategori == "nominal") {
                    const minNominal = promoHeader.min_qty || 0;
                    const maxNominal = promoHeader.max_qty || Infinity;
                    if (kelipatan == "1") {
                        const pengali = Math.floor(rawSubtotal / kelipatan);
                        if (rawSubtotal >= minNominal && pengali > 0)
                            promoApplicable = true;
                        pengaliFix =
                            pengali == 1
                                ? 1
                                : Math.floor(pengali / promoHeader.min_qty);
                    } else {
                        if (
                            rawSubtotal >= minNominal &&
                            rawSubtotal <= maxNominal
                        )
                            promoApplicable = true;
                    }
                } else {
                    if (kelipatan == "1") {
                        const pengali = Math.floor(
                            qtySmallestAllProduct / kelipatanSmall,
                        );
                        if (
                            qtySmallestAllProduct >= promoMinSmall &&
                            pengali > 0
                        )
                            promoApplicable = true;
                        pengaliFix =
                            pengali == 1
                                ? 1
                                : Math.floor(pengali / promoHeader.min_qty);
                    } else {
                        const promoMaxSmall = promoHeader.max_qty
                            ? SalesOrder.convertToSmallest(
                                UOM_CONVERSION,
                                productId,
                                promoHeader.unit_id,
                                promoHeader.max_qty,
                            )
                            : Infinity;
                        if (
                            qtySmallestAllProduct >= promoMinSmall &&
                            qtySmallestAllProduct <= promoMaxSmall
                        )
                            promoApplicable = true;
                    }
                }

                if (!promoApplicable) continue;

                // Hitung grand total semua baris
                let grandTotal = 0;
                $("table#table-items tbody tr.input")
                    .not(".freegood")
                    .each(function () {
                        const rowPrice =
                            parseFloat(
                                $(this).find("#unit_price").attr("price"),
                            ) || 0;
                        const rowQty =
                            parseFloat($(this).find("#qty").val()) || 0;
                        grandTotal += rowPrice * rowQty;
                    });

                let discAmountHeader = 0;
                let discPercentHeader = 0;

                if (promoHeader.discount_type === "percent") {
                    discPercentHeader = promoHeader.discount_value;
                    discAmountHeader =
                        grandTotal * (promoHeader.discount_value / 100);
                }
                if (promoHeader.discount_type === "nominal") {
                    discAmountHeader = promoHeader.discount_value * pengaliFix;
                }

                // Tambah additional disc
                if (promoHeader.additional_disc > 0) {
                    let additionalDiscAmount = 0;
                    if (promoHeader.additional_disc_type === "percent") {
                        additionalDiscAmount =
                            grandTotal * (promoHeader.additional_disc / 100);
                    } else if (promoHeader.additional_disc_type === "nominal") {
                        additionalDiscAmount = promoHeader.additional_disc;
                    }
                    discAmountHeader += additionalDiscAmount;
                }

                $("#discount_percent_header").val(discPercentHeader);
                $("#discount_amount_header").val(
                    new Intl.NumberFormat("id-ID", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }).format(discAmountHeader),
                );
                $("#discount_amount_header").attr("amount", discAmountHeader);

                // Hitung subtotal
                const discAmount =
                    parseFloat(discAmountInput.attr("amount")) || 0;
                const subtotal = price * qty - discAmount;
                let taxAmount = 0;
                if (type_tax == "include") {
                    taxAmount = subtotal - subtotal / (1 + tax_rate / 100);
                } else {
                    taxAmount = subtotal * (tax_rate / 100);
                }
                taxAmountInpute.attr("amount", taxAmount);
                taxAmountInpute.val(
                    new Intl.NumberFormat("id-ID", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }).format(taxAmount),
                );
                subtotalInput.attr("subtotal", subtotal.toFixed(2));
                subtotalInput.val(
                    new Intl.NumberFormat("id-ID", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }).format(subtotal),
                );

                break; // promo grand total pertama yang applicable langsung break
            }

            for (let index = 0; index < promoHeaders.length; index++) {
                const promoHeader = promoHeaders[index];
                const parent_id = promoHeader.id;
                const kelipatan = promoHeader.kelipatan;
                const discount_type = promoHeader.discount_type;
                const discount_kategori = promoHeader.discount_kategori;
                const potong_grand_total = promoHeader.potong_grand_total;
                if (promoHeader.potong_grand_total != 0) continue; // skip yang bukan grand total

                // console.log("class_promo_item", parent_id);

                // ========================
                // FILTER CHANNEL OUTLET
                // ========================
                const channelMatch =
                    !promoHeader.channel_outlet ||
                    promoHeader.channel_outlet == channel_outlet;
                const subChannelMatch =
                    !promoHeader.sub_channel_outlet ||
                    promoHeader.sub_channel_outlet == sub_channel_outlet;

                console.log(
                    "channel_outlet",
                    channel_outlet,
                    "sub_channel_outlet",
                    sub_channel_outlet,
                );
                if (!channelMatch || !subChannelMatch) {
                    continue; // skip promo ini, lanjut ke promo berikutnya
                }

                const class_promo_item = "promo-item-" + parent_id;
                const class_promo_free = "promo-free-" + parent_id;

                const promoProducts =
                    SalesOrder.getPromoProducts(class_promo_item);
                const promoFree =
                    SalesOrder.getPromoFreeProducts(class_promo_free);

                let promoApplicable = false;
                let pengaliKelipatanFreegood = 1;

                const today = new Date().toISOString().slice(0, 10);

                const productMatch = promoProducts.some(
                    (p) => p.product == tr.find("#product").attr("data_id"),
                );

                // product promo yang benar-benar ada di SO
                const matchedPromoProducts = promoProducts.filter((p) =>
                    soProductIds.includes(String(p.product)),
                );

                const mixCount = matchedPromoProducts.length;
                // console.log('mixCount', mixCount);

                // cek min_mix dan max mix
                const mixOk =
                    !promoHeader.min_mix ||
                    (mixCount >= promoHeader.min_mix &&
                        mixCount <= promoHeader.max_mix);

                // DEBUG - hapus setelah fix
                // console.log("=== DEBUG PROMO MIX ===");
                // console.log("parent_id:", parent_id);
                // console.log("max_mix:", promoHeader.max_mix);
                // console.log("min_mix:", promoHeader.min_mix);
                // console.log("mixCount:", mixCount);
                // console.log("mixOk:", mixOk);
                // console.log("productId baris ini:", productId);
                // console.log(
                //   "data-promo-applied sudah ada?",
                //   $("tr.input")
                //     .not(tr)
                //     .filter(function () {
                //       return $(this).attr("data-promo-applied") == parent_id;
                //     }).length > 0,
                // );

                const promoMinSmall = SalesOrder.convertToSmallest(
                    UOM_CONVERSION,
                    productId,
                    promoHeader.unit_id,
                    promoHeader.min_qty,
                );

                //jika tidak ada diskon campuran mix
                if (promoHeader.min_mix == 1 && promoHeader.max_mix == 1) {
                    const productMatch = products.find(
                        (p) =>
                            p.product_id == tr.find("#product").attr("data_id"),
                    );
                    //   console.log('productMatch min mix 1', productMatch);

                    qtySmallestAllProduct = SalesOrder.convertToSmallest(
                        UOM_CONVERSION,
                        productMatch.product_id,
                        productMatch.unit_id,
                        productMatch.qty,
                    );
                }
                //jika tidak ada diskon campuran mix

                const kelipatanSmall = SalesOrder.convertToSmallest(
                    UOM_CONVERSION,
                    productId,
                    promoHeader.unit_id,
                    kelipatan,
                );

                const rawSubtotal = price * qty;

                // console.log("promoMinSmall", promoMinSmall);
                if (mixOk && today >= promoHeader.date_start) {
                    // cek qty untuk ROW INI

                    if (discount_kategori == "nominal") {
                        // ========================
                        // KATEGORI NOMINAL: min/max qty = subtotal
                        // ========================
                        const minNominal = promoHeader.min_qty || 0;
                        const maxNominal = promoHeader.max_qty || Infinity;
                        console.log(
                            "diskon kategori nominal",
                            rawSubtotal,
                            minNominal,
                            maxNominal,
                        );

                        if (kelipatan == "1") {
                            const kelipatanNominal = kelipatan; // kelipatan dalam rupiah
                            pengaliKelipatanFreegood = Math.floor(
                                rawSubtotal / kelipatanNominal,
                            );
                            if (
                                rawSubtotal >= minNominal &&
                                pengaliKelipatanFreegood > 0
                            ) {
                                promoApplicable = true;
                            }
                        } else {
                            if (
                                rawSubtotal >= minNominal &&
                                rawSubtotal <= maxNominal
                            ) {
                                promoApplicable = true;
                            }
                        }
                    } else {
                        // ========================
                        // KATEGORI QTY (existing logic)
                        // ========================
                        if (qtySmallestAllProduct < promoMinSmall) {
                            promoApplicable = false;
                        }

                        // console.log("kelipatan", kelipatan);
                        if (kelipatan == "1") {
                            // hitung berapa kali kelipatan terpenuhi
                            pengaliKelipatanFreegood = Math.floor(
                                qtySmallestAllProduct / kelipatanSmall,
                            );
                            // cek promo applicable
                            if (
                                qtySmallestAllProduct >= promoMinSmall &&
                                pengaliKelipatanFreegood > 0
                            ) {
                                promoApplicable = true;
                            }
                        } else {
                            const promoMaxSmall = promoHeader.max_qty
                                ? SalesOrder.convertToSmallest(
                                    UOM_CONVERSION,
                                    productId,
                                    promoHeader.unit_id,
                                    promoHeader.max_qty,
                                )
                                : Infinity;

                            if (
                                qtySmallestAllProduct >= promoMinSmall &&
                                qtySmallestAllProduct <= promoMaxSmall
                            ) {
                                promoApplicable = true;
                            }
                        }
                    }
                }

                // const pengali =
                //   kelipatanSmall == 0
                //     ? 1
                //     : Math.floor(qtySmallestAllProduct / kelipatanSmall);
                // let pengaliFix =
                //   pengali == 1 ? 1 : Math.floor(pengali / promoHeader.min_qty);

                let pengali, pengaliFix;

                if (discount_kategori === "nominal") {
                    // pengali berdasarkan subtotal
                    const kelipatanNominal = parseFloat(kelipatan) || 1;
                    pengali =
                        kelipatanNominal == 0
                            ? 1
                            : Math.floor(rawSubtotal / kelipatanNominal);
                    pengaliFix =
                        pengali == 1
                            ? 1
                            : Math.floor(pengali / promoHeader.min_qty);
                } else {
                    // pengali berdasarkan qty terkecil (existing)
                    pengali =
                        kelipatanSmall == 0
                            ? 1
                            : Math.floor(
                                qtySmallestAllProduct / kelipatanSmall,
                            );
                    pengaliFix =
                        pengali == 1
                            ? 1
                            : Math.floor(pengali / promoHeader.min_qty);
                }

                // ========================
                // POTONG GRAND TOTAL - handle duluan sebelum cek max_mix
                // ========================
                if (promoApplicable && promoHeader && potong_grand_total == 1) {
                    let grandTotal = 0;
                    $("table#table-items tbody tr.input")
                        .not(".freegood")
                        .each(function () {
                            const rowPrice =
                                parseFloat(
                                    $(this).find("#unit_price").attr("price"),
                                ) || 0;
                            const rowQty =
                                parseFloat($(this).find("#qty").val()) || 0;
                            grandTotal += rowPrice * rowQty;
                        });

                    let discAmountHeader = 0;
                    let discPercentHeader = 0;

                    if (promoHeader.discount_type === "percent") {
                        discPercentHeader = promoHeader.discount_value;
                        discAmountHeader =
                            grandTotal * (promoHeader.discount_value / 100);
                    }
                    if (promoHeader.discount_type === "nominal") {
                        discPercentHeader = 0;
                        discAmountHeader =
                            promoHeader.discount_value * pengaliFix;
                    }

                    $("#discount_percent_header").val(discPercentHeader);
                    $("#discount_amount_header").val(
                        new Intl.NumberFormat("id-ID", {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        }).format(discAmountHeader),
                    );
                    $("#discount_amount_header").attr(
                        "amount",
                        discAmountHeader,
                    );

                    break; // ← langsung break, tidak perlu apply ke baris
                }

                // Jika promo mix (max_mix > 1), diskon hanya 1x
                if (promoApplicable && promoHeader && promoHeader.max_mix > 1) {
                    const mapKey = String(parent_id);
                    const alreadyApplied =
                        SalesOrder._promoAppliedMap[mapKey] !== undefined &&
                        SalesOrder._promoAppliedMap[mapKey] !== productId;

                    if (alreadyApplied) {
                        promoApplicable = false;
                    }
                }

                if (promoApplicable && promoHeader) {
                    // Tandai baris ini sudah dapat diskon untuk promo ini
                    // Tandai baris ini sudah dapat diskon untuk promo mix ini
                    if (promoHeader.max_mix > 1) {
                        SalesOrder._promoAppliedMap[String(parent_id)] =
                            productId;
                    }
                    if (promoHeader.discount_type === "percent") {
                        discPercentInput.val(promoHeader.discount_value);
                        const amountDisc =
                            (price * qty * promoHeader.discount_value) / 100;
                        discAmountInput.attr("amount", amountDisc);
                        discAmountInput.val(
                            new Intl.NumberFormat("id-ID", {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }).format(amountDisc),
                        );
                    }
                    if (promoHeader.discount_type === "nominal") {
                        discPercentInput.val(0);
                        const amountDisc =
                            promoHeader.discount_value * pengaliFix;
                        discAmountInput.attr("amount", amountDisc);
                        discAmountInput.val(
                            new Intl.NumberFormat("id-ID", {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }).format(amountDisc),
                        );
                    }
                    if (promoHeader.discount_type === "price") {
                        discPercentInput.val(0);
                        const amountDisc = 0;
                        discAmountInput.attr("amount", amountDisc);
                        discAmountInput.val(
                            new Intl.NumberFormat("id-ID", {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }).format(amountDisc),
                        );
                        unitPriceInput.val(promoHeader.discount_value);
                        price = promoHeader.discount_value;
                        tr.find("#unit_price").attr("price", price);
                    }
                }

                // Additional disc ke grand total jika promoApplicable
                if (promoApplicable && promoHeader.additional_disc > 0) {
                    let grandTotal = 0;
                    $("table#table-items tbody tr.input")
                        .not(".freegood")
                        .each(function () {
                            const rowPrice =
                                parseFloat(
                                    $(this).find("#unit_price").attr("price"),
                                ) || 0;
                            const rowQty =
                                parseFloat($(this).find("#qty").val()) || 0;
                            const rowDiscAmount =
                                parseFloat(
                                    $(this).find("#disc_amount").attr("amount"),
                                ) || 0;
                            grandTotal += rowPrice * rowQty - rowDiscAmount;
                        });

                    let additionalDiscAmount = 0;
                    if (promoHeader.additional_disc_type === "percent") {
                        additionalDiscAmount =
                            grandTotal * (promoHeader.additional_disc / 100);
                    } else if (promoHeader.additional_disc_type === "nominal") {
                        additionalDiscAmount = promoHeader.additional_disc;
                    }

                    // Akumulasi ke discount_amount_header
                    const existingDiscHeader =
                        parseFloat(
                            $("#discount_amount_header").attr("amount"),
                        ) || 0;
                    const totalDiscHeader =
                        existingDiscHeader + additionalDiscAmount;

                    $("#discount_amount_header").attr(
                        "amount",
                        totalDiscHeader,
                    );
                    $("#discount_amount_header").val(
                        new Intl.NumberFormat("id-ID", {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        }).format(totalDiscHeader),
                    );
                }

                // ========================
                // PROMO FREE GOOD
                // ========================
                if (promoApplicable && promoFree.length) {
                    promoFree.forEach((free) => {
                        console.log(
                            "free",
                            free,
                            "pengaliKelipatanFreegood",
                            pengaliKelipatanFreegood,
                        );
                        const freeQty = free.qty * pengaliFix;

                        // cek apakah free good sudah ada
                        const exists =
                            tr.next(
                                'tr.freegood[data-free-for="' +
                                productId +
                                '"]',
                            ).length > 0;

                        if (!exists) {
                            const freeRow = `
                        <tr class="input freegood" data-free-for="${productId}">
                            <td>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary" type="button" disabled
                                        onclick="SalesOrder.showDataProduct(this)">Free</button>
                                    <input disabled type="text" id="product" class="form-control"
                                        tax="0"
                                        tax_amount="0"
                                        tax_type=""
                                        data_id="${free.product}"
                                        value="${free.name || "Free Product"}">
                                </div>
                            </td>
                            <td id="unit" data_id="${free.unit_id}">
                                ${free.unit || ""}
                            </td>
                            <td>
                                <input type="number" class="form-control" id="qty"
                                    value="${freeQty}" onkeyup="SalesOrder.calcDiscRow(this)" disabled>
                            </td>
                            <td>
                                <input type="text" class="form-control" price="0" id="unit_price"
                                    value="0" disabled>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="disc_percent"
                                    value="0" disabled>
                            </td>
                            <td>
                                <input type="text" class="form-control" amount="0" id="disc_amount"
                                    value="0" disabled>
                            </td>
                            <td>
                                <input type="text" class="form-control" subtotal="0" id="subtotal"
                                    value="0" disabled>
                            </td>
                            <td>
                                <input type="text" class="form-control" amount="0" id="tax_amount"
                                    value="0" disabled>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger" disabled
                                    onclick="SalesOrder.removeRow(this)">
                                    <i class="bx bx-gift"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                            tr.after(freeRow);
                        }
                    });
                } else {
                    // hapus free good promo jika sudah tidak memenuhi syarat
                    tr.next(
                        'tr.freegood[data-free-for="' + productId + '"]',
                    ).remove();
                }

                // Hitung subtotal
                const discAmount =
                    parseFloat(discAmountInput.attr("amount")) || 0;
                const subtotal = price * qty - discAmount;
                let taxAmount = 0;
                if (type_tax == "include") {
                    taxAmount = subtotal - subtotal / (1 + tax_rate / 100);
                } else {
                    taxAmount = subtotal * (tax_rate / 100);
                }
                taxAmountInpute.attr("amount", taxAmount);
                taxAmountInpute.val(
                    new Intl.NumberFormat("id-ID", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }).format(taxAmount),
                );
                subtotalInput.attr("subtotal", subtotal.toFixed(2));
                subtotalInput.val(
                    new Intl.NumberFormat("id-ID", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }).format(subtotal),
                );

                if (promoApplicable) {
                    break;
                }
            }
        } else {
            // Hitung subtotal jika tidak ada promo item
            const discAmount = parseFloat(discAmountInput.val()) || 0;
            discAmountInput.attr("amount", discAmount);
            discAmountInput.val(
                new Intl.NumberFormat("id-ID", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(discAmount),
            );
            const subtotal = price * qty - discAmount;
            let taxAmount = 0;
            if (type_tax == "include") {
                taxAmount = subtotal - subtotal / (1 + tax_rate / 100);
            } else {
                taxAmount = subtotal * (tax_rate / 100);
            }
            taxAmountInpute.attr("amount", taxAmount);
            taxAmountInpute.val(
                new Intl.NumberFormat("id-ID", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(taxAmount),
            );
            subtotalInput.attr("subtotal", subtotal);
            subtotalInput.val(
                new Intl.NumberFormat("id-ID", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(subtotal),
            );
        }

        // Update total keseluruhans

        SalesOrder.hitungSummaryAll();
    },

    getAllProductIdsInTable: () => {
        const ids = new Set();

        // ⛔ skip baris freegood
        $("#table-items tbody tr")
            .not(".freegood")
            .each(function () {
                const pid = $(this).find("#product").attr("data_id");
                // console.log("pid", pid);
                if (pid) ids.add(String(pid));
            });

        return Array.from(ids);
    },

    getAllProductsInTable: () => {
        let result = [];
        $("#table-items tbody tr")
            .not(".freegood")
            .each(function () {
                const pid = $(this).find("#product").attr("data_id");
                const qty = parseFloat($(this).find("#qty").val()) || 0;
                const productId = $(this).find("#product").attr("data_id");
                const satuanId = $(this).find("td#unit").attr("data_id");
                const unit_price =
                    parseFloat($(this).find("#unit_price").attr("price")) || 0;

                if (pid) {
                    result.push({
                        product_id: productId,
                        product_name: $(this).find("#product").val(),
                        qty: qty,
                        unit_id: satuanId,
                        unit_price: unit_price,
                    });
                }
            });

        return result;
    },

    getPromoHeader: () => {
        const row = $("#table-data-promo-header tbody tr");
        if (!row.length) return [];

        const result = [];
        $.each(row, function () {
            const data = {
                promo_name: $(this).find("#promo-name").text().trim(),
                min_qty: parseFloat($(this).find("#promo-min-qty").text()) || 0,
                max_qty: parseFloat($(this).find("#promo-max-qty").text()) || 0,
                unit_name: $(this).find("#promo-unit").text().trim(),
                unit_id: $(this).find("#promo-unit").attr("unit_id"),
                min_mix: parseInt($(this).find("#promo-min-mix").text()) || 0,
                max_mix: parseInt($(this).find("#promo-max-mix").text()) || 0,
                discount_type: $(this)
                    .find("#promo-discount-type")
                    .text()
                    .trim(),
                discount_kategori: $(this)
                    .find("#promo-discount-kategori")
                    .text()
                    .trim(),
                discount_value:
                    parseFloat($(this).find("#promo-discount-value").text()) ||
                    0,
                date_start: $(this).find("#promo-date-start").text().trim(),
                channel_outlet: $(this)
                    .find("#promo-channel-outlet")
                    .text()
                    .trim(),
                sub_channel_outlet: $(this)
                    .find("#promo-sub-channel-outlet")
                    .text()
                    .trim(),
                kelipatan: $(this).attr("kelipatan"),
                potong_grand_total: $(this).attr("potong_grand_total"),
                additional_disc: $(this)
                    .find("#promo-additional-disc")
                    .text()
                    .trim(),
                additional_disc_type: $(this)
                    .find("#promo-additional-disc-type")
                    .text()
                    .trim(),
                id: $(this).attr("data_id"),
            };

            result.push(data);
        });

        return result;
    },

    getPromoProducts: (className) => {
        const products = [];
        $("#table-data-promo-product tbody tr." + className).each(function () {
            products.push({
                product: $(this).find("#promo-product-code").attr("product_id"),
                code: $(this).find("#promo-product-code").text().trim(),
                name: $(this).find("#promo-product-name").text().trim(),
                unit: $(this).find("#promo-unit-name").text().trim(),
                parent_id: $(this).attr("parent_id"),
            });
        });
        return products;
    },

    getPromoFreeProducts: (className) => {
        const free = [];
        $("#table-data-promo-product-free tbody tr." + className).each(
            function () {
                free.push({
                    product: $(this)
                        .find("#promo-free-product-code")
                        .attr("product_id"),
                    code: $(this)
                        .find("#promo-free-product-code")
                        .text()
                        .trim(),
                    name: $(this)
                        .find("#promo-free-product-name")
                        .text()
                        .trim(),
                    unit: $(this).find("#promo-free-unit-name").text().trim(),
                    unit_id: $(this)
                        .find("#promo-free-unit-name")
                        .attr("unit_id"),
                    qty:
                        parseFloat($(this).find("#promo-free-qty").text()) || 0,
                    parent_id: $(this).attr("parent_id"),
                });
            },
        );
        return free;
    },

    convertToSmallest: (UOM_CONVERSIONS, productId, satuanId, qty) => {
        const uom = UOM_CONVERSIONS.find(
            (u) => u.product_id == productId && u.unit_id == satuanId,
        );
        if (!uom) return qty; // fallback jika tidak ditemukan
        return qty * uom.conversion;
    },

    // changeCustomer: (elm) => {
    //     const table = $("table#table-items tbody tr.input");
    //     let result = [];

    //     table.each((index, elm) => {
    //         if (index > 0) {
    //             $(elm).remove();
    //         }

    //         $(elm).find("input").val("");
    //         $(elm).find("td#unit").text("");
    //         $(elm).find("td#unit").attr("data_id", "");
    //         $(elm).find("#price").attr("data_id", "");
    //     });

    //     const top = $(elm).find("option:selected").attr("top");
    //     channel_outlet = $(elm).find("option:selected").attr("channel_outlet");
    //     sub_channel_outlet = $(elm)
    //         .find("option:selected")
    //         .attr("sub_channel_outlet");
    //     $("#payment_term").val(top);
    // },

    changeCustomer: (elm) => {
        const table = $("table#table-items tbody tr.input");

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
        channel_outlet = $(elm).find("option:selected").attr("channel_outlet");
        sub_channel_outlet = $(elm)
            .find("option:selected")
            .attr("sub_channel_outlet");
        $("#payment_term").val(top);

        const customerId = $(elm).val();
        if (customerId) {
            SalesOrder.cekSalesmanByCustomer(customerId);
        }
    },

    cekSalesmanByCustomer: (customerId) => {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: { customer_id: customerId },
            url: url.base_url(SalesOrder.moduleApi()) + "cekSalesman",
            headers: {
                "X-CSRF-TOKEN": SalesOrder.csrf_token(),
            },
            success: function (resp) {
                if (resp.is_valid && resp.salesman_id) {
                    // 🔥 set flag supaya onchange tidak jalan
                    _suppressSalesmanChange = true;

                    $("#salesman").val(resp.salesman_id).trigger("change"); // trigger change untuk select2 re-render

                    // 🔥 reset flag setelah select2 selesai
                    // setTimeout(() => {
                    //     _suppressSalesmanChange = false;
                    // }, 100);
                }
            },
            error: function () {
                // silent fail, tidak perlu alert
            },
        });
    },

    getCustomer: (elm) => {
        if (_suppressSalesmanChange) return;

        const url = $("input#url").val();
        const id = $("input#id").val();
        const salesman = $(elm).val() == "1" ? "" : $(elm).val();

        if (id == "") {
            window.location.href = url + "?salesman=" + salesman;
        } else {
            window.location.href = url + "&salesman=" + salesman;
        }
    },

    editReload: () => {
        const id = $("#id").val();
        const platform = $("#platform").val();
        const status = $("#status").val();
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
                    self.findIndex((t) => t.product_id === value.product_id),
            );

            // 3️⃣ Loop per product_id
            resultProduct.forEach((item) => {
                const { product_id, product_name, unit_id } = item;

                // Contoh: panggil fungsi per produk
                SalesOrder.showDiscountProduct(
                    [product_id],
                    [product_name],
                    [unit_id],
                );
                SalesOrder.showDiscountFreeProduct(
                    [product_id],
                    [product_name],
                    [unit_id],
                );
                SalesOrder.showQtySmallestProduct(
                    [product_id],
                    [product_name],
                    [unit_id],
                );

                SalesOrder.showPromoItem(
                    [product_id],
                    [product_name],
                    [unit_id],
                    0,
                );
            });

            // Jalankan ulang kalkulasi setelah semua promo & diskon di-load

            // if (platform == "mobile" && status == "draft") {
            //   setTimeout(() => {
            //     SalesOrder.recalculateAllRows();
            //   }, 1000);
            // }
        }
    },

    recalculateAllRows: () => {
        SalesOrder._promoAppliedMap = {};
        $("tr.input[data-promo-applied]").removeAttr("data-promo-applied");

        // Reset semua diskon header
        $("#discount_percent_header").val("0");
        $("#discount_amount_header").val("0").attr("amount", "0");

        $("table#table-items tbody tr.input")
            .not(".freegood")
            .each(function () {
                SalesOrder.calcDiscRow(this);
            });
    },

    filterPrincipal: (elm) => {
        const principal = $("#principal").val();
        $("#principal").val(principal);
        $("#table-data-modal").DataTable().ajax.reload();
    },

    search: (elm, state = "") => {
        const url = $(elm).attr("url");
        const start_date = $("#start_date").val();
        const end_date = $("#end_date").val();
        if (start_date == "" || end_date == "") {
            message.sweetError("Informasi", "Pilih tanggal start dan end terlebih dahulu");
            return;
        }
        window.location.href = url + "?start_date=" + start_date + "&end_date=" + end_date + "&state=" + state;
    },

    checkAll: (elm) => {
        let checked = $(elm).is(":checked");
        document.querySelectorAll(".check-item").forEach((el) => {
            el.checked = checked;
        });
    },

    checkDiscount: (elm, e, state = "check") => {
        e.preventDefault();
        const customerId = $("#customer_id").val();
        if (!customerId) {
            message.sweetError("Informasi", "Pilih Customer terlebih dahulu");
            return;
        }

        const products = SalesOrder.getAllProductsInTable();
        if (products.length == 0) {
            message.sweetError("Informasi", "Tambahkan produk terlebih dahulu");
            return;
        }

        const params = {
            customer_id: customerId,
            details: products,
            salesman: $('#salesman').val()
        };

        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(SalesOrder.moduleApi()) + "checkDiscount",
            headers: {
                "X-CSRF-TOKEN": SalesOrder.csrf_token(),
            },
            beforeSend: () => {
                message.loadingProses("Proses Checking Discount Data...");
            },
            error: function () {
                message.closeLoading();
                message.sweetError("Informasi", "Gagal");
            },

            success: function (resp) {
                message.closeLoading();
                if (!resp.is_valid) {
                    message.sweetError("Informasi", resp.message);
                    return;
                }

                const data = resp.data;

                // ========================
                // SIMPAN data_id FREEGOOD LAMA SEBELUM DIHAPUS
                // ========================
                const savedFreeGoodIds = {};
                console.log("data", $("table#table-items tbody tr.freegood"), data.free_goods);
                $("table#table-items tbody tr.freegood").each(function () {
                    const freeFor = $(this).data("free-for"); // product_id induk
                    const freeProductId = $(this)
                        .find("#product")
                        .attr("data_id"); // product_id free good
                    const dataId = $(this).attr("data_id") || null;
                    if (freeFor && freeProductId) {
                        const key = freeFor + "_" + freeProductId;
                        savedFreeGoodIds[key] = dataId;
                        console.log("saved key:", key, "=>", dataId);
                    }
                });

                // ========================
                // RESET DULU SEMUA BARIS
                // ========================
                $("table#table-items tbody tr.freegood").remove();
                $("table#table-items tbody tr.input")
                    .not(".freegood")
                    .each(function () {
                        $(this).find("#disc_percent").val("0");
                        $(this)
                            .find("#disc_amount")
                            .val("0")
                            .attr("amount", "0");
                    });

                // Reset discount header
                $("#discount_percent_header").val("0");
                $("#discount_amount_header").val("0").attr("amount", "0");

                const productDiscounts = {};

                data.result_items.forEach((promo) => {
                    promo.items.forEach((item) => {
                        const productId = item.product_id;

                        if (!productDiscounts[productId]) {
                            productDiscounts[productId] = {
                                amount: 0,
                                percent: 0
                            };
                        }

                        // diskon item
                        productDiscounts[productId].amount +=
                            Number(item.discountAmount || 0);

                        productDiscounts[productId].percent +=
                            Number(item.discountPercent || 0);

                        // // diskon promo level
                        // if (promo.potong_grand_total == 1) {
                        //     productDiscounts[productId].amount +=
                        //         Number(promo.discount_amount || 0);
                        // }
                    });
                });
                console.log(productDiscounts);

                // ========================
                // APPLY PROMO PER ITEM
                // ========================
                if (data.result_items && data.result_items.length > 0) {
                    data.result_items.forEach((promo) => {
                        promo.items.forEach((item) => {
                            $("table#table-items tbody tr.input")
                                .not(".freegood")
                                .each(function () {
                                    const rowProductId = $(this)
                                        .find("#product")
                                        .attr("data_id");
                                    if (rowProductId != item.product_id) return;

                                    const tr = $(this);
                                    const price = item.price;
                                    const qty = item.qty;

                                    const disc = productDiscounts[rowProductId];

                                    if (!disc) return;

                                    tr.find("#disc_percent").val(disc.percent);

                                    tr.find("#disc_amount")
                                        .val(
                                            new Intl.NumberFormat("id-ID", {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2,
                                            }).format(disc.amount)
                                        )
                                        .attr("amount", disc.amount);

                                    // const discAmount = item.discountAmount ?? 0;
                                    const discAmount = disc.amount;

                                    // const discPercent =
                                    //     item.discountPercent ?? 0;
                                    const discPercent = disc.percent;

                                    const subtotal = price * qty - discAmount;
                                    const taxRate =
                                        parseFloat(
                                            tr
                                                .find("#product")
                                                .attr("tax_rate"),
                                        ) || 0;

                                    const taxAmount =
                                        subtotal -
                                        subtotal / (1 + taxRate / 100);

                                    if (promo.discount_type === "price") {
                                        tr.find("#unit_price").val(
                                            new Intl.NumberFormat("id-ID", {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2,
                                            }).format(price),
                                        );
                                        tr.find("#unit_price").attr(
                                            "price",
                                            price,
                                        );
                                    }

                                    if (discPercent !== undefined && discPercent !== null && discPercent !== 0) {
                                        tr.find("#disc_percent").val(discPercent);
                                    }

                                    if (discAmount !== undefined && discAmount !== null && discAmount !== 0) {
                                        tr.find("#disc_amount")
                                            .val(
                                                new Intl.NumberFormat("id-ID", {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                }).format(discAmount)
                                            )
                                            .attr("amount", discAmount);
                                    }

                                    tr.find("#subtotal")
                                        .val(
                                            new Intl.NumberFormat("id-ID", {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2,
                                            }).format(subtotal),
                                        )
                                        .attr("subtotal", subtotal.toFixed(2));

                                    tr.find("#tax_amount")
                                        .val(
                                            new Intl.NumberFormat("id-ID", {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2,
                                            }).format(taxAmount),
                                        )
                                        .attr("amount", taxAmount);
                                });
                        });

                        // ========================
                        // FREE GOODS
                        // ========================
                        if (
                            promo.discount_free &&
                            promo.discount_free.length > 0
                        ) {
                            console.log("free", promo.discount_free);
                            promo.discount_free.forEach((free) => {
                                $("table#table-items tbody tr.input")
                                    .not(".freegood")
                                    .each(function () {
                                        const rowProductId = $(this)
                                            .find("#product")
                                            .attr("data_id");
                                        const freeFor =
                                            promo.items[0]?.product_id;
                                        if (rowProductId != freeFor) return;

                                        const tr = $(this);
                                        const exists =
                                            tr.next(
                                                'tr.freegood[data-free-for="' +
                                                freeFor +
                                                '"]',
                                            ).length > 0;
                                        if (!exists) {
                                            // Ambil data_id lama jika mode edit
                                            const savedKey =
                                                free.product_id +
                                                "_" +
                                                free.product_id;
                                            console.log("savedKey", savedKey);
                                            const existingDataId =
                                                savedFreeGoodIds[savedKey] ||
                                                "";

                                            const freeRow = `
                                    <tr class="input freegood" data-free-for="${freeFor}" data_id="${existingDataId}">
                                    <td>
                                        <div class="input-group">
                                        <button class="btn btn-outline-secondary" type="button" readonly>Free</button>
                                        <input readonly type="text" id="product" class="form-control"
                                            tax="0" tax_amount="0" tax_type="" tax_rate="0"
                                            data_id="${free.product_id}"
                                            value="${free.product_name || "Free Product"}">
                                        </div>
                                    </td>
                                    <td id="unit" data_id="${free.unit}">${free.unit_name || ""}</td>
                                    <td><input type="number" class="form-control" id="qty" value="${free.qty}" readonly></td>
                                    <td><input type="text" class="form-control" price="0" id="unit_price" value="0" readonly></td>
                                    <td><input type="text" class="form-control" id="disc_percent" value="0" readonly></td>
                                    <td><input type="text" class="form-control" amount="0" id="disc_amount" value="0" readonly></td>
                                    <td><input readonly type="text" class="form-control" id="subtotal" subtotal="0" value="0"></td>
                                    <td><input readonly type="text" class="form-control" amount="0" id="tax_amount" value="0"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger" readonly>
                                        <i class="bx bx-gift"></i>
                                        </button>
                                    </td>
                                    </tr>
                                    `;
                                            tr.after(freeRow);
                                        }
                                    });
                            });
                        }
                    });
                }

                // ========================
                // KALKULASI MANUAL UNTUK BARIS YANG TIDAK KENA PROMO
                // ========================
                $("table#table-items tbody tr.input")
                    .not(".freegood")
                    .each(function () {
                        const tr = $(this);
                        const productId = tr.find("#product").attr("data_id");
                        if (!productId) return;

                        let sudahKenaPromo = false;
                        if (data.result_items && data.result_items.length > 0) {
                            data.result_items.forEach((promo) => {
                                promo.items.forEach((item) => {
                                    if (item.product_id == productId) {
                                        sudahKenaPromo = true;
                                    }
                                });
                            });
                        }

                        if (!sudahKenaPromo) {
                            const price =
                                parseFloat(
                                    tr.find("#unit_price").attr("price"),
                                ) || 0;
                            const qty = parseFloat(tr.find("#qty").val()) || 0;
                            const discAmount =
                                parseFloat(
                                    tr.find("#disc_amount").attr("amount"),
                                ) || 0;
                            const subtotal = price * qty - discAmount;
                            const taxRate =
                                parseFloat(
                                    tr.find("#product").attr("tax_rate"),
                                ) || 0;
                            const typeTax = tr
                                .find("#product")
                                .attr("tax_type");

                            let taxAmount = 0;
                            if (typeTax == "include") {
                                taxAmount =
                                    subtotal - subtotal / (1 + taxRate / 100);
                            } else {
                                taxAmount = subtotal * (taxRate / 100);
                            }

                            tr.find("#subtotal")
                                .val(
                                    new Intl.NumberFormat("id-ID", {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    }).format(subtotal),
                                )
                                .attr("subtotal", subtotal.toFixed(2));

                            tr.find("#tax_amount")
                                .val(
                                    new Intl.NumberFormat("id-ID", {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    }).format(taxAmount),
                                )
                                .attr("amount", taxAmount);
                        }
                    });

                // ========================
                // APPLY DISCOUNT HEADER
                // ========================
                if (data.discount_header && data.discount_header.length > 0) {
                    let totalDiscPercent = 0;
                    let totalDiscAmount = 0;
                    data.discount_header.forEach((dh) => {
                        totalDiscPercent += dh.discount_percent ?? 0;
                        totalDiscAmount += dh.discount_amount ?? 0;
                    });

                    $("#discount_percent_header").val(totalDiscPercent);
                    $("#discount_amount_header")
                        .val(
                            new Intl.NumberFormat("id-ID", {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }).format(totalDiscAmount),
                        )
                        .attr("amount", totalDiscAmount);
                }

                // ========================
                // UPDATE GRAND TOTAL
                // ========================
                SalesOrder.hitungSummaryAll();

                if (state == "save") {
                    SalesOrder.submit(elm, e);
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
    SalesOrder.setSelect2();
    SalesOrder.getData();
    // SalesOrder.editReload();
});
