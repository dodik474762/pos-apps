let objInput = null;
let latitude = 0.0;
let longitude = 0.0;
let ClosingStock = {
    module: () => {
        return "transaksi/closing-stock";
    },

    moduleApi: () => {
        return "api/" + ClosingStock.module();
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
    },

    cancel: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(ClosingStock.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(ClosingStock.module()) + "add";
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

    closing: (elm, e) => {
        e.preventDefault();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                tanggal: $("#filter-tanggal").val(),
            },
            headers: {
                "X-CSRF-TOKEN": ClosingStock.csrf_token(),
            },
            url: url.base_url(ClosingStock.moduleApi()) + "closing",
            beforeSend: () => {
                message.loadingProses("Proses Closing Stock...");
            },
            error: function (err) {
                message.closeLoading();
                message.sweetError(
                    "Informasi",
                    "Gagal Melakukan Closing Stock - " + err.statusText,
                );
            },

            success: function (resp) {
                message.closeLoading();
                if (resp.is_valid) {
                    message.sweetSuccess();
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    submitMom: (elm, e) => {
        e.preventDefault();
        let params = ClosingStock.getPostInputMom();

        if (params.file == "") {
            message.sweetError("Informasi", "File Mom Harus Diisi");
            return;
        }
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(ClosingStock.moduleApi()) + "submitMom",
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
                        ClosingStock.back();
                    }, 1000);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    back: (elm) => {
        window.location.href = url.base_url(ClosingStock.module()) + "/";
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
                url: url.base_url(ClosingStock.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ClosingStock.csrf_token(),
                },
                data: function (d) {
                    d.tanggal = $("#filter-tanggal").val(); // ambil dari input tanggal
                },
            },
            deferRender: true,
            dom: "Bftrip",
            buttons: [
                {
                    extend: "excel",
                    filename: "ClosingStock",
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
                    data: "item_code",
                    title: "Product Code",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "item_name",
                    title: "Product",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "warehouse_name",
                    title: "Warehouse",
                },
                {
                    data: "trans_date",
                    title: "Trans Date",
                },
                {
                    data: "opening_balance",
                    title: "Opening Balance",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "qty_in",
                    title: "In",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? 0;
                    },
                },
                {
                    data: "qty_out",
                    title: "Out",
                    className: "text-end",
                    render: function (data, type, row) {
                        return `<strong>${data ?? 0}</strong>`;
                    },
                },
                {
                    data: "qty_adjust",
                    title: "Adjust",
                    className: "text-end",
                    render: function (data, type, row) {
                        return `<strong>${data ?? 0}</strong>`;
                    },
                },
                {
                    data: "closing_balance",
                    title: "Ending Balance",
                    className: "text-end",
                    render: function (data, type, row) {
                        return `<strong>${data ?? 0}</strong>`;
                    },
                },
                {
                    data: "note",
                    title: "Note",
                    className: "text-end",
                    render: function (data, type, row) {
                        return `<strong>${data ?? 0}</strong>`;
                    },
                },
                {
                    data: "reference_type",
                    title: "Reference Type",
                    className: "text-end",
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

    delete: (elm, e) => {
        e.preventDefault();
        let params = {};
        params.id = $(elm).attr("data_id");
        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            headers: {
                "X-CSRF-TOKEN": ClosingStock.csrf_token(),
            },
            url: url.base_url(ClosingStock.moduleApi()) + "delete",
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
                "X-CSRF-TOKEN": ClosingStock.csrf_token(),
            },
            url: url.base_url(ClosingStock.moduleApi()) + "confirmDelete",
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
                "X-CSRF-TOKEN": ClosingStock.csrf_token(),
            },
            url: url.base_url(ClosingStock.moduleApi()) + "showDataKaryawan",

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
                ClosingStock.getDataKaryawan();
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
                "X-CSRF-TOKEN": ClosingStock.csrf_token(),
            },
            url: url.base_url(ClosingStock.moduleApi()) + "showDataCompany",

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
                ClosingStock.getDataCompany();
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
                "X-CSRF-TOKEN": ClosingStock.csrf_token(),
            },
            url: url.base_url(ClosingStock.moduleApi()) + "showDataCustomer",

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
                ClosingStock.getDataCustomer();
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
                "X-CSRF-TOKEN": ClosingStock.csrf_token(),
            },
            url: url.base_url(ClosingStock.moduleApi()) + "showDataVendor",

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
                ClosingStock.getDataVendor();
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
                "X-CSRF-TOKEN": ClosingStock.csrf_token(),
            },
            url: url.base_url(ClosingStock.moduleApi()) + "showDataForecast",

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
                ClosingStock.getDataForecast();
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
                url: url.base_url(ClosingStock.moduleApiKaryawan()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ClosingStock.csrf_token(),
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
                        html += `<a href='' nama_lengkap="${row.nama_lengkap}" onclick="ClosingStock.pilihData(this, event)" data_id="${row.id}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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
                url: url.base_url(ClosingStock.moduleApiProject()) + `getData`,
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
                        html += `<a href='' data_id="${row.id}" code="${row.code_project}" onclick="ClosingStock.pilihDataForecast(this, event)" data_id="${row.nik}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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
                url: url.base_url(ClosingStock.moduleApiVendor()) + `getData`,
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
                        html += `<a href='' data_id="${row.id}" nama_vendor="${row.nama_vendor}" onclick="ClosingStock.pilihDataVendor(this, event)" data_id="${row.nik}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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
                url: url.base_url(ClosingStock.moduleApiCompany()) + `getData`,
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
                        html += `<a href='' data_id="${row.id}" nama_company="${row.nama_company}" onclick="ClosingStock.pilihDataCompany(this, event)" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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
                url: url.base_url(ClosingStock.moduleApiCustomer()) + `getData`,
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
                        html += `<a href='' data_id="${row.id}" nama_customer="${row.nama_customer}" onclick="ClosingStock.pilihDataCustomer(this, event)" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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

        ClosingStock.getDetailForecast(dataId);
    },

    getDetailForecast: (id) => {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id: id,
            },
            url:
                url.base_url(ClosingStock.moduleApiForecast()) +
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
            url: url.base_url(ClosingStock.moduleApiForecast()) + "getCity",
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
                `<button type="button" onclick="ClosingStock.deleteItem(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`,
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

    getDataStok: async () => {
        let tableData = $("table#table-stock-product");

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
                url: url.base_url(ClosingStock.moduleApi()) + `getDataStock`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ClosingStock.csrf_token(),
                },
                data: function (d) {
                    d.tanggal = $("#filter-tanggal").val(); // ambil dari input tanggal
                },
            },
            deferRender: true,
            dom: "Bftrip",
            buttons: [
                {
                    extend: "excel",
                    filename: "ClosingStock",
                    action: newexportaction,
                },
            ],
            columns: [
                {
                    data: "product_code",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                {
                    data: "product_code",
                    title: "Kode Produk",
                    render: function (data, type, row) {
                        return row.product_code;
                    },
                },
                {
                    data: "product_name",
                    title: "Nama Produk",
                },
                {
                    data: "principal",
                    title: "Nama Principal",
                },
                {
                    data: "uom_product",
                    title: "UoM",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "total_masuk_ctn",
                    title: "CTN",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "total_masuk_pcs",
                    title: "PCS",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "total_keluar_ctn",
                    title: "CTN",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "total_keluar_pcs",
                    title: "PCS",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "total_keluar_pcs",
                    title: "CTN",
                    className: "text-end",
                    render: function (data, type, row) {
                        return "0";
                    },
                },
                {
                    data: "stok_tersedia_pcs",
                    title: "PCS",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "0";
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

    getDataStokDetail: async () => {
        let tableData = $("table#table-stock-product-detail");

        var data = tableData.DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            autoWidth: false,
            destroy: true,
            fixedHeader: true,
            fixedColumns: {
                leftColumns: 5, // No, Kode, Nama, Principal, UoM
            },
            order: [[2, "asc"]], // default order by Nama Produk
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
                    url.base_url(ClosingStock.moduleApi()) +
                    `getDataStockDetail`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ClosingStock.csrf_token(),
                },
                data: function (d) {
                    d.tanggal = $("#filter-tanggal").val();
                },
            },
            deferRender: true,
            dom: "Bftrip",
            buttons: [
                {
                    extend: "excel",
                    filename: "ClosingStockProductDetail",
                    action: newexportaction,
                },
            ],
            columns: [
                {
                    // No
                    data: "product_code",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                // {
                //     // No
                //     data: "product",
                //     render: function (data, type, row, meta) {
                //         return data;
                //     },
                // },
                {
                    // Kode Produk
                    data: "product_code",
                    render: function (data, type, row) {
                        return row.product_code ?? "";
                    },
                },
                {
                    // Nama Produk
                    data: "product_name",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    // Nama Principal
                    data: "principal",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    // UoM
                    data: "uom_product",
                    className: "text-center",
                    render: function (data, type, row) {
                        return data ?? "-";
                    },
                },
                {
                    // CARTON (level 4)
                    data: "qty_ctn",
                    className: "text-end",
                    orderable: false,
                    render: function (data, type, row) {
                        return data ?? "0";
                    },
                },
                {
                    // PACK (level 3)
                    data: "qty_pck",
                    className: "text-end",
                    orderable: false,
                    render: function (data, type, row) {
                        return data ?? "0";
                    },
                },
                {
                    // RENCENG (level 2)
                    data: "qty_rtg",
                    className: "text-end",
                    orderable: false,
                    render: function (data, type, row) {
                        return data ?? "0";
                    },
                },
                {
                    // PCS (level 1)
                    data: "qty_pcs",
                    className: "text-end",
                    orderable: false,
                    render: function (data, type, row) {
                        return data ?? "0";
                    },
                },

                {
                    // Price
                    data: "price",
                    className: "text-end",
                    orderable: false,
                    render: function (data, type, row) {
                        let nominal = parseFloat(data ?? 0);

                        return `${formatNumber(nominal)}`;
                    },
                },

                {
                    // Total Price
                    data: "total_harga",
                    className: "text-end",
                    orderable: false,
                    render: function (data, type, row) {
                        let nominal = parseFloat(data ?? 0);

                        return `${formatNumber(nominal)}`;
                    },
                },
            ],
        });

        // Tombol filter tanggal
        $("#btn-filter").on("click", function () {
            data.ajax.reload();
        });

        data.buttons()
            .container()
            .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)");

        $(".dataTables_length select").addClass("form-select form-select-sm");
    },

    filter: (elm) => {
        const tanggal = $("#filter-tanggal").val();
        const route = $(elm).attr("route");
        window.location.href = route + "?tanggal=" + tanggal;
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

const formatRupiah = new Intl.NumberFormat("id-ID", {
    minimumFractionDigits: 0,
});

const formatNumber = (num) => {
    return parseFloat(num || 0)
        .toFixed(0)
        .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

$(function () {
    ClosingStock.setSelect2();
    ClosingStock.getData();
});
