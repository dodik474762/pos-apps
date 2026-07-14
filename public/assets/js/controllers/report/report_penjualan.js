let objInput = null;
let latitude = 0.0;
let longitude = 0.0;
let ReportPenjualan = {
    module: () => {
        return "report/report_penjualan";
    },

    moduleApi: () => {
        return "api/" + ReportPenjualan.module();
    },

    modulePiutangApi: () => {
        return "api/report/report_piutang";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleUserApi: () => {
        return "api/master/users";
    },

    moduleApiKaryawan: () => {
        return "api/master/karyawan";
    },

    moduleApiCustomer: () => {
        return "api/master/customer";
    },

    moduleApiCompany: () => {
        return "api/master/company";
    },

    moduleApiVendor: () => {
        return "api/master/vendor";
    },

    moduleApiProject: () => {
        return "api/transaksi/generate_project";
    },

    moduleApiForecast: () => {
        return "api/transaksi/forecast";
    },

    setSelect2: () => {
        if ($(".select2").length > 0) {
            $.each($(".select2"), function () {
                $(this).select2();
            });
        }


        // Tambah di bagian bawah $(function() { ... })
        $('a[href="#report-per-barang"]').on('shown.bs.tab', function () {
            $('#filter-satuan-wrapper').show();
        });
        $('a[href="#report-per-barang"]').on('hidden.bs.tab', function () {
            $('#filter-satuan-wrapper').hide();
        });
    },

    cancel: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(ReportPenjualan.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(ReportPenjualan.module()) + "add";
    },

    getPostItem: () => {
        let data = $("table#table-travel-item").find("tbody").find("tr.input");
        let result = [];
        data.each((index, elm) => {
            result.push({
                id: $(elm).attr("data_id"),
                cost: $(elm).find("input#cost").val(),
                remarks_travel: $(elm).find("#remarks_travel").val(),
                travel_item: $(elm).find("#travel_item").val(),
                remove: $(elm).hasClass("remove") ? 1 : 0,
            });
        });

        return result;
    },

    getPostInput: () => {
        let data = {
            id: $("input#id").val(),
            nik: $("#nik").val(),
            presence_date: $("#presence_date").val(),
            remarks: $("#remarks").val(),
            latitude: $("#latitude").val(),
            longitude: $("#longitude").val(),
            file: $("input#file").attr("src"),
            tipe: $("input#file").attr("tipe"),
            file_name: $("input#file").val(),
        };

        return data;
    },

    getPostInputMom: () => {
        let data = {
            id: $("input#id").val(),
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
            let params = ReportPenjualan.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                headers: {
                    "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
                },
                url: url.base_url(ReportPenjualan.moduleApi()) + "submit",
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
                            ReportPenjualan.back();
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

    submitMom: (elm, e) => {
        e.preventDefault();
        let params = ReportPenjualan.getPostInputMom();

        if (params.file == "") {
            message.sweetError("Informasi", "File Mom Harus Diisi");
            return;
        }
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(ReportPenjualan.moduleApi()) + "submitMom",
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
                        ReportPenjualan.back();
                    }, 1000);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    back: (elm) => {
        window.location.href = url.base_url(ReportPenjualan.module()) + "/";
    },

    getData: async () => {
        let tableData = $("table#table-data");

        let deleteAction = $("#delete").val();

        var data = tableData.DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            autoWidth: false,
            destroy: true,
            fixedHeader: true,
            fixedColumns: {
                leftColumns: 4,
            },
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
                url: url.base_url(ReportPenjualan.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
                },
                data: function (d) {
                    d.date_start = $("#filter-start-date").val();
                    d.date_end = $("#filter-end-date").val();
                },
            },
            deferRender: true,
            dom: "Bftrip",
            buttons: [
                {
                    extend: "excel",
                    filename: "ReportPenjualan",
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
                    data: "principal",
                    title: "PRINCIPAL",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "nama_vendor",
                    title: "VENDOR",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "product_code",
                    title: "KODE PRODUK",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "product_name",
                    title: "NAMA PRODUK",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "category",
                    title: "KATEGORI",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "brand",
                    title: "BRAND",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "sub_brand",
                    title: "SUB BRAND",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "product_name",
                    title: "PACKAGING",
                    render: function (data, type, row) {
                        return "";
                    },
                },
                {
                    data: "customer_code",
                    title: "KODE CUSTOMER",
                },
                {
                    data: "nama_customer",
                    title: "NAMA CUSTOMER",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "kecamatan",
                    title: "KECAMATAN",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "kabupaten",
                    title: "KABUPATEN",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "kelurahan",
                    title: "KELURAHAN",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "alamat",
                    title: "ALAMAT",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "channel_outlet",
                    title: "CHANNEL",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "salesman_nik",
                    title: "KODE SALESMAN",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "salesman_name",
                    title: "NAMA SALESMAN",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "cicle_type",
                    title: "CYCLE KUNJUNGAN",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "day_name",
                    title: "DAY KUNJUNGAN",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "day",
                    title: "TANGGAL",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "month",
                    title: "BULAN",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "year",
                    title: "TAHUN",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "invoice_number",
                    title: "NOMOR INVOICE",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "qty_sold",
                    title: "QTYSOLD",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "qty_sold",
                    title: "KARTON",
                    render: function (data, type, row) {
                        if (!data) return 0;
                        let parts = data.split(".");
                        let total = parts.length;
                        if (total === 4) return parts[0]; // karton.renteng.pack.pcs
                        if (total === 3) return parts[0]; // karton.pack.pcs
                        if (total === 2) return parts[0]; // karton.pcs
                        return 0; // 1 satuan, tidak ada karton
                    },
                },
                {
                    data: "qty_sold",
                    title: "RENTENG",
                    render: function (data, type, row) {
                        if (!data) return 0;
                        let parts = data.split(".");
                        let total = parts.length;
                        if (total === 4) return parts[1]; // hanya ada di 4 level
                        return 0;
                    },
                },
                {
                    data: "qty_sold",
                    title: "PACK",
                    render: function (data, type, row) {
                        if (!data) return 0;
                        let parts = data.split(".");
                        let total = parts.length;
                        if (total === 4) return parts[2]; // karton.renteng.pack.pcs
                        if (total === 3) return parts[1]; // karton.pack.pcs
                        return 0;
                    },
                },
                {
                    data: "qty_sold",
                    title: "PCS",
                    render: function (data, type, row) {
                        if (!data) return 0;
                        let parts = data.split(".");
                        let total = parts.length;
                        if (total === 4) return parts[3];
                        if (total === 3) return parts[2];
                        if (total === 2) return parts[1];
                        return parts[0]; // 1 satuan = semua pcs
                    },
                },
                {
                    data: "so_date",
                    title: "TIPE TRANS",
                    render: function (data, type, row) {
                        return "sales";
                    },
                },
                {
                    data: "gross_amount",
                    title: "GROSSAMOUNT",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "prorate_discount",
                    title: "LINE DISCOUNT 1",
                    render: function (data, type, row) {
                        // beban PRINCIPAL → masuk line discount 1
                        return row.beban === "PRINCIPAL" ? (data ?? 0) : 0;
                    },
                },
                {
                    data: "prorate_discount",
                    title: "LINE DISCOUNT 2",
                    render: function (data, type, row) {
                        // beban DISTRIBUTOR → masuk line discount 2
                        return row.beban === "DISTRIBUTOR" ? (data ?? 0) : 0;
                    },
                },
                {
                    data: "discount_per_product",
                    title: "LINE DISCOUNT 3",
                    render: function (data, type, row) {
                        // selalu 0
                        return row.discount_per_product ?? 0;
                    },
                },
                {
                    data: "prorate_discount",
                    title: "TOTAL DISCOUNT",
                    render: function (data, type, row) {
                        let line1 = row.beban === "PRINCIPAL" ? (data ?? 0) : 0;
                        let line2 =
                            row.beban === "DISTRIBUTOR" ? (data ?? 0) : 0;
                        let line3 = row.discount_per_product ?? 0;
                        line3 = isNaN(parseFloat(line3))
                            ? 0
                            : parseFloat(line3);
                        line1 = isNaN(parseFloat(line1))
                            ? 0
                            : parseFloat(line1);
                        line2 = isNaN(parseFloat(line2))
                            ? 0
                            : parseFloat(line2);
                        return line1 + line2 + line3;
                    },
                },
                {
                    data: "prorate_discount",
                    title: "NET AMOUNT",
                    render: function (data, type, row) {
                        let line1 = row.beban === "PRINCIPAL" ? (data ?? 0) : 0;
                        let line2 =
                            row.beban === "DISTRIBUTOR" ? (data ?? 0) : 0;
                        let line3 = row.discount_per_product ?? 0;
                        line3 = isNaN(parseFloat(line3))
                            ? 0
                            : parseFloat(line3);
                        line1 = isNaN(parseFloat(line1))
                            ? 0
                            : parseFloat(line1);
                        line2 = isNaN(parseFloat(line2))
                            ? 0
                            : parseFloat(line2);
                        let totalDiscount = line1 + line2 + line3;
                        let grossAmount = row.gross_amount ?? 0;
                        grossAmount = isNaN(parseFloat(grossAmount))
                            ? 0
                            : parseFloat(grossAmount);
                        return grossAmount - totalDiscount;
                    },
                },
                {
                    data: "so_date",
                    title: "NO FAKTUR PAJAK",
                    render: function (data, type, row) {
                        return "";
                    },
                },
                {
                    data: "so_date",
                    title: "TGL FAKTUR PAJAK",
                    render: function (data, type, row) {
                        return "";
                    },
                },
                {
                    data: "so_date",
                    title: "COGS",
                    render: function (data, type, row) {
                        return "";
                    },
                },
            ],
        });

        // Tombol filter tanggal
        $("#btn-filter").on("click", function () {
            data.ajax.reload();
        });

        (data
            .buttons()
            .container()
            .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"),
            $(".dataTables_length select").addClass(
                "form-select form-select-sm",
            ));
    },

    delete: (elm, e) => {
        e.preventDefault();
        let params = {};
        params.id = $(elm).attr("data_id");
        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            headers: {
                "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
            },
            url: url.base_url(ReportPenjualan.moduleApi()) + "delete",
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
            headers: {
                "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
            },
            url: url.base_url(ReportPenjualan.moduleApi()) + "confirmDelete",
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

    showDataKaryawan: (elm) => {
        let params = {};

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            headers: {
                "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
            },
            url: url.base_url(ReportPenjualan.moduleApi()) + "showDataKaryawan",

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
                ReportPenjualan.getDataKaryawan();
            },
        });
    },

    showDataCompany: (elm) => {
        let params = {};

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            headers: {
                "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
            },
            url: url.base_url(ReportPenjualan.moduleApi()) + "showDataCompany",

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
                ReportPenjualan.getDataCompany();
            },
        });
    },

    showDataCustomer: (elm) => {
        let params = {};

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            headers: {
                "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
            },
            url: url.base_url(ReportPenjualan.moduleApi()) + "showDataCustomer",

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
                ReportPenjualan.getDataCustomer();
            },
        });
    },

    showDataVendor: (elm) => {
        let params = {};

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            headers: {
                "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
            },
            url: url.base_url(ReportPenjualan.moduleApi()) + "showDataVendor",

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
                objInput = elm;
                ReportPenjualan.getDataVendor();
            },
        });
    },

    showDataForecast: (elm) => {
        let params = {};

        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            headers: {
                "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
            },
            url: url.base_url(ReportPenjualan.moduleApi()) + "showDataForecast",

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
                ReportPenjualan.getDataForecast();
            },
        });
    },

    getDataKaryawan: () => {
        let tableData = $("table#table-data-karyawan");
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
                    "pagination-rounded",
                );
            },
            ajax: {
                url:
                    url.base_url(ReportPenjualan.moduleApiKaryawan()) +
                    `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
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
                    data: "nik",
                },
                {
                    data: "nama_lengkap",
                },
                {
                    data: "contact",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' nama_lengkap="${row.nama_lengkap}" onclick="ReportPenjualan.pilihData(this, event)" data_id="${row.id}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        return html;
                    },
                },
            ],
        });
    },

    getDataForecast: () => {
        let tableData = $("table#table-data-forecast");
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
                    "pagination-rounded",
                );
            },
            ajax: {
                url:
                    url.base_url(ReportPenjualan.moduleApiProject()) +
                    `getData`,
                type: "POST",
                data: {
                    type: "project",
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
                    data: "code_project",
                },
                {
                    data: "plan_date",
                },
                {
                    data: "remarks",
                },
                {
                    data: "periode_start",
                },
                {
                    data: "periode_end",
                },
                {
                    data: "estimated_cost",
                },
                {
                    data: "status",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' data_id="${row.id}" code="${row.code_project}" onclick="ReportPenjualan.pilihDataForecast(this, event)" data_id="${row.nik}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        return html;
                    },
                },
            ],
        });
    },

    getDataVendor: () => {
        let tableData = $("table#table-data-vendor");
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
                    "pagination-rounded",
                );
            },
            ajax: {
                url:
                    url.base_url(ReportPenjualan.moduleApiVendor()) + `getData`,
                type: "POST",
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
                    data: "address",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' data_id="${row.id}" nama_vendor="${row.nama_vendor}" onclick="ReportPenjualan.pilihDataVendor(this, event)" data_id="${row.nik}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        return html;
                    },
                },
            ],
        });
    },

    getDataCompany: () => {
        let tableData = $("table#table-data-company");
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
                    "pagination-rounded",
                );
            },
            ajax: {
                url:
                    url.base_url(ReportPenjualan.moduleApiCompany()) +
                    `getData`,
                type: "POST",
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
                    data: "nama_company",
                },
                {
                    data: "alamat",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' data_id="${row.id}" nama_company="${row.nama_company}" onclick="ReportPenjualan.pilihDataCompany(this, event)" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        return html;
                    },
                },
            ],
        });
    },

    getDataCustomer: () => {
        let tableData = $("table#table-data-customer");
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
                    "pagination-rounded",
                );
            },
            ajax: {
                url:
                    url.base_url(ReportPenjualan.moduleApiCustomer()) +
                    `getData`,
                type: "POST",
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
                    data: "numbering_code",
                },
                {
                    data: "nama_customer",
                },
                {
                    data: "kota",
                },
                {
                    data: "address",
                },
                {
                    data: "id",
                    render: function (data, type, row) {
                        var html = "";
                        html += `<a href='' data_id="${row.id}" nama_customer="${row.nama_customer}" onclick="ReportPenjualan.pilihDataCustomer(this, event)" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
                        return html;
                    },
                },
            ],
        });
    },

    pilihData: (elm, e) => {
        e.preventDefault();
        let nama_lengkap = $(elm).attr("nama_lengkap");
        let nik = $(elm).attr("data_id");
        console.log("DATA ", nama_lengkap, nik);
        $("#nik").val(nik + "//" + nama_lengkap);
        $("button.btn-close").trigger("click");
    },

    pilihDataCompany: (elm, e) => {
        e.preventDefault();
        let nama_company = $(elm).attr("nama_company");
        let dataId = $(elm).attr("data_id");
        $("#company").val(dataId + "//" + nama_company);
        $("button.btn-close").trigger("click");
    },

    pilihDataCustomer: (elm, e) => {
        e.preventDefault();
        let nama_customer = $(elm).attr("nama_customer");
        let dataId = $(elm).attr("data_id");
        $("#customer").val(dataId + "//" + nama_customer);
        $("button.btn-close").trigger("click");
    },

    pilihDataForecast: (elm, e) => {
        e.preventDefault();
        let code = $(elm).attr("code");
        let dataId = $(elm).attr("data_id");
        $("#forecast").val(dataId + "//" + code);
        $("button.btn-close").trigger("click");

        ReportPenjualan.getDetailForecast(dataId);
    },

    getDetailForecast: (id) => {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id: id,
            },
            url:
                url.base_url(ReportPenjualan.moduleApiForecast()) +
                "getDetailForecast",
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
                    let data = resp.data;
                    $("#nik").val(data.karyawan);
                    $("#nik").trigger("change");
                    $("#customer").val(
                        data.customer + "//" + data.nama_customer,
                    );
                    $("#estimated_cost").val(data.estimated_cost);
                    $("#region").val(data.region);
                    $("select#region").trigger("change");
                    setTimeout(function () {
                        $("#city").val(data.city);
                        $("#city").trigger("change");
                    }, 1500);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    pilihDataVendor: (elm, e) => {
        e.preventDefault();
        let nama_vendor = $(elm).attr("nama_vendor");
        let dataId = $(elm).attr("data_id");
        if (objInput != null) {
            $(objInput)
                .closest("div")
                .find("input#vendor")
                .val(dataId + "//" + nama_vendor);
        }
        $("button.btn-close").trigger("click");
    },

    getCity: (elm) => {
        const province = $(elm).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                province: province,
            },
            url: url.base_url(ReportPenjualan.moduleApiForecast()) + "getCity",
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
                    const cityOption = $("select#city");
                    cityOption.find("option").remove();
                    $.each(resp.data, function (key, value) {
                        cityOption.append(
                            '<option value="' +
                            value.id +
                            '">' +
                            value.name +
                            "</option>",
                        );
                    });
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    addItem: (elm, e) => {
        e.preventDefault();
        let table = $("table#table-travel-item")
            .find("tbody")
            .find("tr.input:last");
        let newTr = table.clone();
        newTr.find("input").val("");
        newTr.find("select").val("");
        newTr.attr("data_id", "");
        newTr
            .find("td#action")
            .html(
                `<button type="button" onclick="ReportPenjualan.deleteItem(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`,
            );
        table.after(newTr);
    },

    deleteItem: (elm) => {
        let data_id = $(elm).closest("tr").attr("data_id");
        if (data_id == "") {
            $(elm).closest("tr").remove();
        } else {
            $(elm).closest("tr").addClass("remove");
            $(elm).closest("tr").addClass("hide");
        }
    },

    addFile: (elm) => {
        // Buat uploader secara dinamis
        var uploader = $(
            `<input type="file" id="file" accept="image/*;capture=camera" />`,
        );
        var src_foto = $(`input#file`);

        // Tambahkan uploader ke body
        $("body").append(uploader);
        uploader.click();

        // Ketika ada perubahan (file terpilih)
        uploader.on("change", function () {
            var files = uploader.get(0).files[0];

            if (files) {
                var reader = new FileReader();
                var filename = files.name;
                var data_from_file = filename.split(".");
                var type_file = $.trim(
                    data_from_file[data_from_file.length - 1],
                ).toLowerCase();

                // Cek jika format file sesuai
                if (
                    ["jpg", "jpeg", "png", "pdf", "heic", "HEIC"].includes(
                        type_file,
                    )
                ) {
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
                        "Format file salah, hanya bisa jpg, jpeg, png, heic dan pdf",
                    );
                }
            }
            // Hapus uploader setelah file dipilih atau proses selesai
            uploader.remove();
        });
    },

    getLocation: () => {
        if (navigator.geolocation) {
            console.log("grolocation active", navigator.geolocation);
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    latitude = position.coords.latitude;
                    longitude = position.coords.longitude;
                    console.log("Latitude:", latitude);
                    console.log("Longitude:", longitude);
                    if ($("#latitude").length > 0) {
                        $("#latitude").val(latitude);
                        $("#longitude").val(longitude);
                    }
                },
                function (error) {
                    console.error("Error getting location:", error);
                },
            );
        } else {
            console.error("Geolocation is not supported by this browser.");
        }
    },

    getDataSummary: async () => {
        let tableData = $("table#table-data-summary");

        var data = tableData.DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            autoWidth: false,
            destroy: true,
            fixedHeader: true,
            fixedColumns: {
                leftColumns: 3,
            },
            order: [[1, "asc"]],
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
                url:
                    url.base_url(ReportPenjualan.moduleApi()) +
                    `getDataSummary`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
                },
                data: function (d) {
                    d.date_start = $("#filter-start-date").val();
                    d.date_end = $("#filter-end-date").val();
                },
            },
            deferRender: true,
            dom: "Bftrip",
            buttons: [
                {
                    extend: "excel",
                    filename: "ReportPenjualan_Summary",
                    action: newexportaction,
                },
            ],
            columns: [
                {
                    // No
                    data: null,
                    title: "No",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                {
                    // Salesman
                    data: "salesman_name",
                    title: "Salesman",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    // Tanggal
                    data: "so_date",
                    title: "Tanggal",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    // Jam Berangkat (dari presence)
                    data: "jam_berangkat",
                    title: "Jam Berangkat",
                    className: "text-center",
                    render: function (data, type, row) {
                        return `<strong>${data ?? "-"}</strong>`;
                    },
                },
                {
                    // Jam Kembali (check_out_time terakhir)
                    data: "jam_kembali",
                    title: "Jam Kembali",
                    className: "text-center",
                    render: function (data, type, row) {
                        return `<strong>${data ?? "-"}</strong>`;
                    },
                },
                {
                    // Waktu Jual = total SUM check_in - check_out semua outlet
                    data: "waktu_jual",
                    title: "Waktu Jual",
                    className: "text-center",
                    render: function (data, type, row) {
                        return `<strong>${data ?? "-"}</strong>`;
                    },
                },
                {
                    // Rata-rata lama transaksi per outlet
                    data: "avg_lama_transaksi",
                    title: "Rata-Rata Lama Transaksi per Outlet",
                    className: "text-center",
                    render: function (data, type, row) {
                        return `<strong>${data ?? "-"}</strong>`;
                    },
                },
                {
                    // Lama di Jalan = Total Keluar Kantor - Waktu Jual
                    data: "lama_di_jalan",
                    title: "Lama di Jalan",
                    className: "text-center",
                    render: function (data, type, row) {
                        return `<strong>${data ?? "-"}</strong>`;
                    },
                },
                {
                    // Total Keluar Kantor = Jam Kembali - Jam Berangkat
                    data: "total_keluar_kantor",
                    title: "Total Keluar Kantor",
                    className: "text-center",
                    render: function (data, type, row) {
                        return `<strong>${data ?? "-"}</strong>`;
                    },
                },
                {
                    // Total Call Keseluruhan
                    data: "total_call",
                    title: "Total Call Keseluruhan",
                    className: "text-center",
                    render: function (data, type, row) {
                        return `<strong>${data ?? 0}</strong>`;
                    },
                },
                {
                    // Total Call Sesuai PJP
                    data: "total_call_pjp",
                    title: "Total Call Sesuai PJP",
                    className: "text-center",
                    render: function (data, type, row) {
                        return `<strong>${data ?? 0}</strong>`;
                    },
                },
                {
                    // Total Call Extra Call
                    data: "total_call_extra",
                    title: "Total Call Extra Call",
                    className: "text-center",
                    render: function (data, type, row) {
                        return `<strong>${data ?? 0}</strong>`;
                    },
                },
            ],
        });

        // Tombol filter tanggal
        $("#btn-filter").on("click", function () {
            data.ajax.reload();
        });

        (data
            .buttons()
            .container()
            .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"),
            $(".dataTables_length select").addClass(
                "form-select form-select-sm",
            ));
    },

    getDataPerPenjual: async () => {
        let tableData = $("table#table-data-per-penjual");

        let deleteAction = $("#delete").val();

        var data = tableData.DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            autoWidth: false,
            destroy: true,
            fixedHeader: true,
            fixedColumns: {
                leftColumns: 4,
            },
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
                url:
                    url.base_url(ReportPenjualan.modulePiutangApi()) +
                    `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
                },
                data: function (d) {
                    d.date_start = $("#filter-start-date").val();
                    d.date_end = $("#filter-end-date").val();
                    d.types = "per-penjual";
                },
            },
            deferRender: true,
            dom: "Bftrip",
            buttons: [
                {
                    text: "Excel",
                    className: "btn btn-success btn-sm",
                    action: function (e, dt, button, config) {
                        // Ambil semua data langsung dari server via AJAX
                        $.ajax({
                            url:
                                url.base_url(
                                    ReportPenjualan.modulePiutangApi(),
                                ) + "getData",
                            type: "POST",
                            headers: {
                                "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
                            },
                            dataType: "JSON",
                            data: {
                                date_start: $("#filter-start-date").val(),
                                date_end: $("#filter-end-date").val(),
                                types: "per-penjual",
                                start: 0,
                                length: 99999, // ambil semua
                            },
                            success: function (resp) {
                                var tableRows = resp.data;
                                console.log("resp", tableRows);

                                if (!tableRows || tableRows.length === 0) {
                                    alert("Tidak ada data untuk di-export.");
                                    return;
                                }

                                // Group by salesman_name
                                var grouped = {};
                                var order = [];
                                tableRows.forEach(function (row) {
                                    var key =
                                        row.salesman_name ||
                                        "(Tidak Ada Salesman)";
                                    if (!grouped[key]) {
                                        grouped[key] = [];
                                        order.push(key);
                                    }
                                    grouped[key].push(row);
                                });

                                // Susun rows untuk Excel
                                var excelRows = [];

                                // Header kolom
                                excelRows.push([
                                    "NO",
                                    "NO FAKTUR",
                                    "TANGGAL FAKTUR",
                                    "KODE CUSTOMER",
                                    "NAMA CUSTOMER",
                                    "KECAMATAN",
                                    "KABUPATEN",
                                    "TOTAL PIUTANG",
                                    "TOTAL DIBAYAR",
                                    "SISA HUTANG",
                                ]);

                                order.forEach(function (salesman) {
                                    var rows = grouped[salesman];
                                    var subtotal = rows.reduce(function (
                                        sum,
                                        r,
                                    ) {
                                        return (
                                            sum +
                                            parseFloat(
                                                r.outstanding_amount || 0,
                                            )
                                        );
                                    }, 0);

                                    var total_piutang = rows.reduce(function (sum, r) {
                                        return sum + parseFloat(r.total_amount || 0);
                                    }, 0);

                                    var total_bayar = rows.reduce(function (sum, r) {
                                        return sum + parseFloat(r.amount_paid || 0);
                                    }, 0);

                                    // Baris header group salesman
                                    excelRows.push([
                                        salesman,
                                        "",
                                        "",
                                        "",
                                        "",
                                        "",
                                        "",
                                        total_piutang,
                                        total_bayar,
                                        subtotal,
                                    ]);

                                    // Baris data
                                    rows.forEach(function (r, i) {
                                        excelRows.push([
                                            i + 1,
                                            r.invoice_number ?? "",
                                            r.invoice_date ?? "",
                                            r.customer_code ?? "",
                                            r.nama_customer ?? "",
                                            r.kecamatan ?? "",
                                            r.kabupaten ?? "",
                                            parseFloat(r.total_amount || 0),
                                            parseFloat(r.amount_paid || 0),
                                            parseFloat(
                                                r.outstanding_amount || 0,
                                            ),
                                        ]);
                                    });
                                });

                                // ✅ Cek apakah XLSX tersedia, fallback ke $.fn.dataTable.ext jika tidak
                                if (typeof XLSX !== "undefined") {
                                    var wb = XLSX.utils.book_new();
                                    var ws = XLSX.utils.aoa_to_sheet(excelRows);
                                    ws["!cols"] = [
                                        { wch: 5 },
                                        { wch: 15 },
                                        { wch: 15 },
                                        { wch: 18 },
                                        { wch: 25 },
                                        { wch: 15 },
                                        { wch: 20 },
                                        { wch: 15 },
                                        { wch: 15 },
                                        { wch: 15 },
                                    ];
                                    XLSX.utils.book_append_sheet(
                                        wb,
                                        ws,
                                        "Report Per Penjual",
                                    );
                                    XLSX.writeFile(
                                        wb,
                                        "ReportPenjualanPerPenjual.xlsx",
                                    );
                                } else {
                                    // Fallback: pakai DataTables internal XLSX
                                    var _XLSX = $.fn.dataTable.ext.buttons
                                        .excelHtml5
                                        ? window.JSZip
                                        : null;

                                    // Buat CSV sebagai fallback terakhir
                                    var csvContent = excelRows
                                        .map(function (row) {
                                            return row
                                                .map(function (cell) {
                                                    var val = (cell ?? "")
                                                        .toString()
                                                        .replace(/"/g, '""');
                                                    return '"' + val + '"';
                                                })
                                                .join(",");
                                        })
                                        .join("\n");

                                    var blob = new Blob(
                                        ["\uFEFF" + csvContent],
                                        {
                                            type: "text/csv;charset=utf-8;",
                                        },
                                    );
                                    var link = document.createElement("a");
                                    link.href = URL.createObjectURL(blob);
                                    link.download =
                                        "ReportPenjualanPerPenjual.csv";
                                    link.click();
                                }
                            },
                            error: function () {
                                alert("Gagal mengambil data untuk export.");
                            },
                        });
                    },
                },
            ],
            // ✅ Tambah rowGroup
            rowGroup: {
                dataSrc: "salesman_name",
                startRender: function (rows, group) {
                    var totalPiutang = rows
                        .data()
                        .pluck("total_amount")
                        .reduce(function (a, b) {
                            return parseFloat(a || 0) + parseFloat(b || 0);
                        }, 0);

                    var totalBayar = rows
                        .data()
                        .pluck("amount_paid")
                        .reduce(function (a, b) {
                            return parseFloat(a || 0) + parseFloat(b || 0);
                        }, 0);

                    var subtotal = rows
                        .data()
                        .pluck("outstanding_amount")
                        .reduce(function (a, b) {
                            return parseFloat(a || 0) + parseFloat(b || 0);
                        }, 0);

                    var fmt = function (v) {
                        return new Intl.NumberFormat("id-ID", {
                            minimumFractionDigits: 2,
                        }).format(v);
                    };

                    const salesman_nik = rows.data().pluck("salesman_nik").toArray()[0];
                    if (salesman_nik != group) {
                        group = salesman_nik + " " + group;
                    }

                    return $("<tr/>")
                        .append(
                            `<td colspan="7" class="group-salesman-header">
                    <i class="mdi mdi-account me-1"></i>
                    <strong>${group}</strong>
                </td>`,
                        )
                        .append(
                            `<td class="text-end group-salesman-header">
                    <strong>${fmt(totalPiutang)}</strong>
                </td>`,
                        )
                        .append(
                            `<td class="text-end group-salesman-header">
                    <strong>${fmt(totalBayar)}</strong>
                </td>`,
                        )
                        .append(
                            `<td class="text-end group-salesman-header">
                    <strong>${fmt(subtotal)}</strong>
                </td>`,
                        );
                },
            },
            columns: [
                {
                    data: "id",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                {
                    data: "invoice_number",
                    title: "NO FAKTUR",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "invoice_date",
                    title: "TANGGAL FAKTUR",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "customer_code",
                    title: "KODE CUSTOMER",
                },
                {
                    data: "nama_customer",
                    title: "NAMA CUSTOMER",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "kecamatan",
                    title: "KECAMATAN",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "kabupaten",
                    title: "KABUPATEN",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "total_amount",
                    title: "TOTAL PIUTANG",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "amount_paid",
                    title: "TOTAL DIBAYAR",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "outstanding_amount",
                    title: "SISA HUTANG",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                // ✅ Kolom salesman_name disembunyikan, hanya untuk grouping
                {
                    data: "salesman_name",
                    title: "SALESMAN",
                    visible: false,
                },
            ],
        });

        // Tombol filter tanggal
        $("#btn-filter").on("click", function () {
            data.ajax.reload();
        });

        (data
            .buttons()
            .container()
            .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"),
            $(".dataTables_length select").addClass(
                "form-select form-select-sm",
            ));
    },

    filter: () => {
        const type = $("#select-option-report").val();
        if (type == "PENJUALAN") {
            ReportPenjualan.getData();
        }
        if (type == "PENJUALAN PER PENJUAL") {
            ReportPenjualan.getDataPerPenjual();
        }
        if (type == "PENJUALAN PER BARANG") {
            ReportPenjualan.getDataPerProduct();
        }
    },

    getDataPerProduct: async () => {
        let tableData = $("table#table-data-per-barang");

        let deleteAction = $("#delete").val();

        // variable untuk tracking invoice terakhir yang sudah tampil discount-nya
        let lastInvoiceDiscount = null;
        // tracking invoice terakhir untuk kolom TOTAL HARGA
        let lastInvoiceTotal = null;

        var data = tableData.DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            autoWidth: false,
            destroy: true,
            fixedHeader: true,
            fixedColumns: {
                leftColumns: 4,
            },
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
            // reset tracking tiap kali sebelum data digambar ulang (ganti page/filter/dll)
            preDrawCallback: function () {
                lastInvoiceDiscount = null;
                lastInvoiceTotal = null;
            },
            ajax: {
                url:
                    url.base_url(ReportPenjualan.moduleApi()) +
                    `getDataPenjualanPerProduct`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ReportPenjualan.csrf_token(),
                },
                data: function (d) {
                    d.date_start = $("#filter-start-date").val();
                    d.date_end = $("#filter-end-date").val();
                    d.filter_satuan = $("#filter-satuan").val() || "default";
                },
            },
            deferRender: true,
            dom: "Bftrip",
            buttons: [
                {
                    extend: "excel",
                    filename: "ReportPenjualanPerProduct",
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
                    data: "invoice_number",
                    title: "NO FAKTUR",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "invoice_date",
                    title: "TANGGAL FAKTUR",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "salesman_nik",
                    title: "KODE SALESMAN",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "salesman_name",
                    title: "NAMA SALESMAN",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "customer_code",
                    title: "KODE CUSTOMER",
                },
                {
                    data: "nama_customer",
                    title: "NAMA CUSTOMER",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "kecamatan",
                    title: "KECAMATAN",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "kabupaten",
                    title: "KABUPATEN",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "kelurahan",
                    title: "KELURAHAN",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "alamat",
                    title: "ALAMAT",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "product_code",
                    title: "KODE PRODUK",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "product_name",
                    title: "NAMA PRODUK",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "category_product",
                    title: "KATEGORI BARANG",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    // Kolom KUANTITAS — render sesuai filter_satuan
                    data: "qty",
                    title: "KUANTITAS",
                    render: function (data, type, row) {
                        let satuan = $("#filter-satuan").val() || "default";
                        if (satuan === "terkecil") return row.qty_terkecil ?? 0;
                        if (satuan === "terbesar") return row.qty_terbesar ?? 0;
                        return data ?? 0; // default = qty dari invoice detail
                    },
                },

                {
                    // Kolom SATUAN — label menyesuaikan pilihan
                    data: "unit_jual",
                    title: "SATUAN",
                    render: function (data, type, row) {
                        let satuan = $("#filter-satuan").val() || "default";
                        if (satuan === "terkecil") return row.unit_terkecil ?? data;
                        if (satuan === "terbesar") return row.unit_terbesar ?? data;
                        return data ?? "";
                    },
                },
                {
                    data: "price",
                    title: "HARGA",
                    render: function (data, type, row) {
                        let satuan = $("#filter-satuan").val() || "default";
                        if (satuan === "terkecil") return row.price_terkecil ?? data;
                        if (satuan === "terbesar") return row.price_terbesar ?? data;
                        return data ?? "";
                    },
                },
                {
                    data: "discount_amount",
                    title: "DISCOUNT",
                    render: function (data, type, row) {
                        // hanya proses logika grouping saat render tampilan (bukan sort/filter)
                        if (type !== "display") return data;

                        if (row.invoice_number === lastInvoiceDiscount) {
                            return "0"; // kosongkan kalau invoice masih sama dengan baris sebelumnya
                        }

                        lastInvoiceDiscount = row.invoice_number;
                        return data;
                    },
                },
                // {
                //     data: "subtotal",
                //     title: "TOTAL HARGA",
                //     render: function (data, type, row) {
                //         let satuan = $("#filter-satuan").val() || "default";
                //         console.log('satuan', satuan);
                //         console.log("price_terkecil:", row.price_terkecil, "qty_terkecil:", row.qty_terkecil);
                //         console.log("price_terbesar:", row.price_terbesar, "qty_terbesar:", row.qty_terbesar);
                //         if (satuan === "terkecil") return row.price_terkecil * row.qty_terkecil;
                //         if (satuan === "terbesar") return row.price_terbesar * row.qty_terbesar;
                //         return data ?? 0;
                //     },
                // },
                {
                    data: "subtotal",
                    title: "TOTAL HARGA",
                    render: function (data, type, row) {
                        let satuan = $("#filter-satuan").val() || "default";

                        // hitung subtotal dasar sesuai satuan
                        let baseTotal;
                        if (satuan === "terkecil") {
                            console.log('price_terkecil', row.price_terkecil);
                            console.log('qty_terkecil', row.qty_terkecil);
                            baseTotal = row.price_terkecil * row.qty_terkecil;
                        } else if (satuan === "terbesar") {
                            baseTotal = row.price_terbesar * row.qty_terbesar;
                        } else {
                            baseTotal = data ?? 0;
                            console.log('data', data);
                        }

                        if (type !== "display") return baseTotal;

                        // discount hanya dikurangi di baris pertama tiap invoice
                        let isFirstRow = row.invoice_number !== lastInvoiceTotal;
                        lastInvoiceTotal = row.invoice_number;

                        let discount = isFirstRow ? (row.discount_amount ?? 0) : 0;

                        return baseTotal - discount;
                    },
                },
                {
                    data: "sku_name",
                    title: "NAMA KATEGORI PELANGGAN",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "channel_outlet",
                    title: "CHANNEL",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
            ],
        });

        // Tombol filter tanggal
        $("#btn-filter").on("click", function () {
            data.ajax.reload();
        });

        (data
            .buttons()
            .container()
            .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"),
            $(".dataTables_length select").addClass(
                "form-select form-select-sm",
            ));
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
    ReportPenjualan.setSelect2();
    // ReportPenjualan.getLocation();
    ReportPenjualan.getData();
    ReportPenjualan.getDataPerPenjual();
    ReportPenjualan.getDataPerProduct();
});
