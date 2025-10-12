let Notifications = {
    module: () => {
        return "dashboard";
    },

    moduleApi: () => {
        return "api/" + Notifications.module();
    },

    showNotification: (elm) => {
        let params = {};
        $.ajax({
            type: "POST",
            dataType: "html",
            data: params,
            url: url.base_url(Notifications.moduleApi()) + "showNotification",
            beforeSend: () => {
                $("div#content-notification-header")
                    .html(`<div class="spinner-border text-primary" role="status">
                                                            <span class="sr-only">Loading...</span>`);
            },
            error: function () {
                $("div#content-notification-header").html("");
            },

            success: function (resp) {
                $("div#content-notification-header").html(resp);
            },
        });
    },

    onReadNotification: (elm, e) => {
        e.preventDefault();
        const href = $(elm).attr("href");
        let params = {
            id : $(elm).attr('primary'),
            primary : $(elm).attr('data_id'),
            menu : $(elm).attr('menu')
        };
        $.ajax({
            type: "POST",
            dataType: "json",
            data: params,
            url: url.base_url(Notifications.moduleApi()) + "updateReadNotification",
            beforeSend: () => {
                message.loadingProses("Proses Saving Data...");
            },
            error: function () {
                message.closeLoading();
                message.sweetError("Informasi", "Gagal");
            },

            success: function (resp) {
                message.closeLoading();
                if(resp.is_valid){
                    // console.log(href);
                    window.location.href = href;
                }else{
                    message.sweetError("Informasi", resp.message);
                }
            },
        });
    },
};

$(function () {
    // Notifications.showNotification();
});
