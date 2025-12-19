let map = null;
let markers = [];
var group = null;

let Dashboard = {
    module: () => {
        return "dashboard";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    moduleApi: () => {
        return "api/" + Dashboard.module();
    },

    moduleSOApi: () => {
        return "api/transaksi/sales_order";
    },

    moduleAuthApi: () => {
        return "api/auth";
    },

    changeDefaultGroup: (elm, e) => {
        e.preventDefault();
        const params = {
            group: $(elm).attr("data_id"),
            group_name: $(elm).text().trim(),
        };

        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(Dashboard.moduleAuthApi()) + "changeSession",
            beforeSend: () => {
                message.loadingProses("Proses Pengambilan Data...");
            },
            error: function () {
                message.closeLoading();
            },

            success: function (resp) {
                message.closeLoading();
                if (resp.is_valid) {
                    window.location.reload();
                } else {
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },

     getDataSo: async () => {
        let tableData = $("table#table-data-so");

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
                url: url.base_url(Dashboard.moduleSOApi()) + `getData`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": Dashboard.csrf_token(),
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
                    data: "platform",
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
};

$(function () {
    Dashboard.getDataSo();
});
