let objInput = null;
let latitude = 0.0;
let longitude = 0.0;
let ReportVisit = {
    module: () => {
        return "report/report_visit";
    },

    moduleApi: () => {
        return "api/" + ReportVisit.module();
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
        window.location.href = url.base_url(ReportVisit.module()) + "/";
    },

    add: (elm, e) => {
        e.preventDefault();
        window.location.href = url.base_url(ReportVisit.module()) + "add";
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
            let params = ReportVisit.getPostInput();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: params,
                headers: {
                    "X-CSRF-TOKEN": ReportVisit.csrf_token(),
                },
                url: url.base_url(ReportVisit.moduleApi()) + "submit",
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
                            ReportVisit.back();
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
        let params = ReportVisit.getPostInputMom();

        if (params.file == "") {
            message.sweetError("Informasi", "File Mom Harus Diisi");
            return;
        }
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(ReportVisit.moduleApi()) + "submitMom",
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
                        ReportVisit.back();
                    }, 1000);
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

    back: (elm) => {
        window.location.href = url.base_url(ReportVisit.module()) + "/";
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
                url: url.base_url(ReportVisit.moduleApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ReportVisit.csrf_token(),
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
                    filename: "ReportVisit",
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
                    data: "salesman_name",
                    title: "Salesman",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "so_date",
                    title: "Tanggal",
                },
                {
                    data: "customer_code",
                    title: "Kode Pelanggan",
                },
                {
                    data: "nama_customer",
                    title: "Nama customer",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "channel_outlet",
                    title: "Channel Outlet",
                    className: "text-end",
                    render: function (data, type, row) {
                        return data ?? "";
                    },
                },
                {
                    data: "platform",
                    title: "Status Visit",
                    className: "text-end",
                    render: function (data, type, row) {
                        return `<strong>${data == "mobile" ? "Ya" : "Tidak"}</strong>`;
                    },
                },
                {
                    data: "lama_di_jalan",
                    title: "Waktu Lama di Jalan",
                    className: "text-end",
                    render: function (data, type, row) {
                        return `<strong>${data ?? ""}</strong>`;
                    },
                },
                {
                    data: "check_in_time",
                    title: "Jam Masuk di Toko",
                    className: "text-end",
                    render: function (data, type, row) {
                        return `<strong>${data ?? ""}</strong>`;
                    },
                },
                {
                    data: "check_out_time",
                    title: "Jam Keluar dari Toko",
                    className: "text-end",
                    render: function (data, type, row) {
                        return `<strong>${data ?? ""}</strong>`;
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
                "X-CSRF-TOKEN": ReportVisit.csrf_token(),
            },
            url: url.base_url(ReportVisit.moduleApi()) + "delete",
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
                "X-CSRF-TOKEN": ReportVisit.csrf_token(),
            },
            url: url.base_url(ReportVisit.moduleApi()) + "confirmDelete",
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
                "X-CSRF-TOKEN": ReportVisit.csrf_token(),
            },
            url: url.base_url(ReportVisit.moduleApi()) + "showDataKaryawan",

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
                ReportVisit.getDataKaryawan();
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
                "X-CSRF-TOKEN": ReportVisit.csrf_token(),
            },
            url: url.base_url(ReportVisit.moduleApi()) + "showDataCompany",

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
                ReportVisit.getDataCompany();
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
                "X-CSRF-TOKEN": ReportVisit.csrf_token(),
            },
            url: url.base_url(ReportVisit.moduleApi()) + "showDataCustomer",

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
                ReportVisit.getDataCustomer();
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
                "X-CSRF-TOKEN": ReportVisit.csrf_token(),
            },
            url: url.base_url(ReportVisit.moduleApi()) + "showDataVendor",

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
                ReportVisit.getDataVendor();
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
                "X-CSRF-TOKEN": ReportVisit.csrf_token(),
            },
            url: url.base_url(ReportVisit.moduleApi()) + "showDataForecast",

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
                ReportVisit.getDataForecast();
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
                url: url.base_url(ReportVisit.moduleApiKaryawan()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ReportVisit.csrf_token(),
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
                        html += `<a href='' nama_lengkap="${row.nama_lengkap}" onclick="ReportVisit.pilihData(this, event)" data_id="${row.id}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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
                url: url.base_url(ReportVisit.moduleApiProject()) + `getData`,
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
                        html += `<a href='' data_id="${row.id}" code="${row.code_project}" onclick="ReportVisit.pilihDataForecast(this, event)" data_id="${row.nik}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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
                url: url.base_url(ReportVisit.moduleApiVendor()) + `getData`,
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
                        html += `<a href='' data_id="${row.id}" nama_vendor="${row.nama_vendor}" onclick="ReportVisit.pilihDataVendor(this, event)" data_id="${row.nik}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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
                url: url.base_url(ReportVisit.moduleApiCompany()) + `getData`,
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
                        html += `<a href='' data_id="${row.id}" nama_company="${row.nama_company}" onclick="ReportVisit.pilihDataCompany(this, event)" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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
                url: url.base_url(ReportVisit.moduleApiCustomer()) + `getData`,
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
                        html += `<a href='' data_id="${row.id}" nama_customer="${row.nama_customer}" onclick="ReportVisit.pilihDataCustomer(this, event)" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
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

        ReportVisit.getDetailForecast(dataId);
    },

    getDetailForecast: (id) => {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id: id,
            },
            url:
                url.base_url(ReportVisit.moduleApiForecast()) +
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
            url: url.base_url(ReportVisit.moduleApiForecast()) + "getCity",
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
                `<button type="button" onclick="ReportVisit.deleteItem(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`,
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
                url: url.base_url(ReportVisit.moduleApi()) + `getDataSummary`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": ReportVisit.csrf_token(),
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
                    filename: "ReportVisit_Summary",
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
    ReportVisit.setSelect2();
    ReportVisit.getLocation();
    ReportVisit.getData();
    ReportVisit.getDataSummary();
});
