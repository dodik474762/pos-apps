let GL = {
    moduleApi: () => {
        return "api/report/general-ledger";
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    formatNumber: (num) => {
        return Number(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    // Badge warna status periode: OPEN = hijau, CLOSED = abu-abu
    periodBadgeClass: (status) => {
        return status === 'OPEN'
            ? 'bg-success-subtle text-success'
            : 'bg-secondary-subtle text-secondary';
    },

    // Tag Live/Snapshot: ikut status periode, sama konsep dengan mockup Period Closing
    sourceTag: (status) => {
        return status === 'OPEN'
            ? { text: 'Live — dihitung saat ini', cls: 'bg-warning-subtle text-warning' }
            : { text: 'Snapshot — dari account_period_balances', cls: 'bg-success-subtle text-success' };
    },

    init: () => {
        if ($(".select2").length > 0) {
            $(".select2").select2();
        }

        $('#search-btn').on('click', () => {
            let accountId = $('#account_id').val();
            let periodId = $('#accounting_period_id').val();
            if (!accountId || !periodId) {
                alert('Pilih periode dan akun terlebih dahulu');
                return;
            }
            GL.load();
        });

        GL.table = $('#table-gl').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            searching: false,
            paging: false,
            info: false,
            ajax: {
                url: url.base_url(GL.moduleApi()) + "getData",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": GL.csrf_token(),
                },
                data: function (d) {
                    d.account_id = $('#account_id').val();
                    d.accounting_period_id = $('#accounting_period_id').val();
                }
            },
            columns: [
                { data: "journal_date" },
                { data: "journal_no" },
                { data: "description" },
                { data: "debit", className: 'text-end', render: function (d) { return d > 0 ? GL.formatNumber(d) : '-'; } },
                { data: "credit", className: 'text-end', render: function (d) { return d > 0 ? GL.formatNumber(d) : '-'; } },
                { data: "running_balance", className: 'text-end', render: function (d) { return GL.formatNumber(d); } }
            ]
        });

        GL.table.on('xhr', function (e, settings, json) {
            $('#gl-account').text(json.account ? json.account.code + ' - ' + json.account.name : '-');

            let period = json.period;
            let periodLabel = period ? period.month + '/' + period.year : '-';
            $('#gl-period').text(periodLabel);
            $('#gl-opening').text(GL.formatNumber(json.opening_balance));
            $('#gl-closing').text(GL.formatNumber(json.closing_balance));
            $('#gl-closing-card').text(GL.formatNumber(json.closing_balance));

            if (period) {
                // Badge status periode (OPEN/CLOSED)
                $('#gl-period-badge')
                    .removeClass()
                    .addClass('badge rounded-pill ' + GL.periodBadgeClass(period.status))
                    .text(period.status)
                    .removeClass('d-none');

                // Tag Live vs Snapshot di header card
                let tag = GL.sourceTag(period.status);
                $('#gl-source-text').text(tag.text);
                $('#gl-source-tag')
                    .removeClass()
                    .addClass('badge rounded-pill ' + tag.cls)
                    .removeClass('d-none');

                // Blocker info kalau periode masih OPEN (opsional, hanya tampil kalau API kirim flag ini)
                if (period.status === 'OPEN' && json.has_draft_journals) {
                    $('#gl-draft-blocker').removeClass('d-none');
                } else {
                    $('#gl-draft-blocker').addClass('d-none');
                }
            }
        });
    },

    load: () => {
        if (GL.table) {
            GL.table.ajax.reload();
        }
    }
};

$(function () {
    GL.init();
});