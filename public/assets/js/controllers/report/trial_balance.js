let TB = {
    moduleApi: () => {
        return "api/report/trial-balance";
    },

    closeApi: () => {
        return "api/accounting-period"; // sesuaikan dengan endpoint closing period kamu yang sebenarnya
    },

    csrf_token: () => {
        return $('meta[name="csrf-token"]').attr("content");
    },

    formatNumber: (num) => {
        return Number(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    periodBadgeClass: (status) => {
        return status === 'OPEN'
            ? 'bg-success-subtle text-success'
            : 'bg-secondary-subtle text-secondary';
    },

    sourceTag: (status) => {
        return status === 'OPEN'
            ? { text: 'Live — dihitung saat ini, belum ada snapshot', cls: 'bg-warning-subtle text-warning' }
            : { text: 'Snapshot — dari account_period_balances', cls: 'bg-success-subtle text-success' };
    },

    init: () => {
        if ($(".select2").length > 0) {
            $(".select2").select2();
        }

        $('#search-btn').on('click', () => {
            let periodId = $('#accounting_period_id').val();
            if (!periodId) {
                alert('Pilih periode terlebih dahulu');
                return;
            }
            TB.load();
        });

        $('#close-period-btn').on('click', () => {
            let periodId = $('#accounting_period_id').val();
            if (!periodId) return;
            if (!confirm('Tutup periode ini? Tindakan ini tidak bisa dibatalkan.')) return;

            $.ajax({
                url: url.base_url(TB.closeApi()) + '/' + periodId + '/close',
                type: 'POST',
                headers: { "X-CSRF-TOKEN": TB.csrf_token() },
                success: () => {
                    TB.load();
                },
                error: (xhr) => {
                    alert(xhr.responseJSON?.message || 'Gagal menutup periode.');
                }
            });
        });

        TB.table = $('#table-tb').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            searching: false,
            paging: false,
            info: false,
            ajax: {
                url: url.base_url(TB.moduleApi()) + "getData",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": TB.csrf_token(),
                },
                data: function (d) {
                    d.accounting_period_id = $('#accounting_period_id').val();
                }
            },
            columns: [
                { data: "code" },
                { data: "name" },
                { data: "debit", className: 'text-end', render: function (d) { return TB.formatNumber(d); } },
                { data: "credit", className: 'text-end', render: function (d) { return TB.formatNumber(d); } }
            ]
        });

        TB.table.on('xhr', function (e, settings, json) {
            $('#tb-total-debit').text(TB.formatNumber(json.total_debit));
            $('#tb-total-credit').text(TB.formatNumber(json.total_credit));
            if (json.is_balanced) {
                $('#tb-balanced').text('BALANCED').addClass('text-success').removeClass('text-danger');
            } else {
                $('#tb-balanced').text('NOT BALANCED').addClass('text-danger').removeClass('text-success');
            }

            let period = json.period;
            if (!period) {
                $('#tb-period-card-wrapper, #tb-blocker-wrapper').hide();
                return;
            }

            // Kartu status periode
            $('#tb-period-card-wrapper').show();
            $('#tb-period-name').text(period.label || (period.month + '/' + period.year));
            $('#tb-period-badge')
                .removeClass()
                .addClass('badge rounded-pill ' + TB.periodBadgeClass(period.status))
                .text(period.status);
            $('#tb-period-meta').text(period.meta || '');

            // Tombol Tutup Periode: aktif hanya kalau OPEN dan tidak ada blocker
            let hasDraft = (json.draft_journals || []).length > 0;
            $('#close-period-btn').prop('disabled', period.status !== 'OPEN' || hasDraft);

            // Blocker DRAFT
            if (period.status === 'OPEN' && hasDraft) {
                $('#tb-blocker-count').text(json.draft_journals.length);
                let $list = $('#tb-blocker-list').empty();
                json.draft_journals.forEach(function (j) {
                    $list.append(
                        '<li><span class="text-muted">' + j.journal_no + '</span> &middot; ' +
                        j.transaction_label + ' &middot; ' + j.reference_label + ' &middot; Rp ' +
                        TB.formatNumber(j.amount) + '</li>'
                    );
                });
                $('#tb-blocker-wrapper').show();
            } else {
                $('#tb-blocker-wrapper').hide();
            }

            // Tag Live vs Snapshot
            let tag = TB.sourceTag(period.status);
            $('#tb-source-text').text(tag.text);
            $('#tb-source-tag').removeClass().addClass('badge rounded-pill ' + tag.cls);
            $('#tb-source-sub').text(
                period.status === 'OPEN'
                    ? (period.label || '') + ' · akan tersimpan permanen sebagai snapshot begitu periode ini ditutup'
                    : 'Periode sudah closed · angka ini permanen, tidak dihitung ulang tiap dibuka'
            );
        });
    },

    load: () => {
        if (TB.table) {
            TB.table.ajax.reload();
        }
    }
};

$(function () {
    TB.init();
});