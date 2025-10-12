let map = null;
let markers = [];
var group = null;

let Dashboard = {
    module: () => {
        return "dashboard";
    },

    moduleApi: () => {
        return "api/" + Dashboard.module();
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
};

$(function () {
    Dashboard.setSelect2();
});
