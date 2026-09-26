<!-- Modal konfirmasi posting -->
<div class="modal fade" id="konfirmasi-post" aria-hidden="true" aria-labelledby="..." tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Posting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin akan memposting jurnal
                    <b>{{ ! empty($journal) ? $journal->journal_no : '' }}</b> ?
                </p>
                <p class="text-muted mb-0">
                    Setelah diposting, jurnal bersifat immutable dan akan menjadi REVERSED hanya melalui jurnal
                    reversal.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="Journals.confirmPost(this)"
                    data_id="{{ ! empty($journal) ? $journal->id : $id }}">Ya</button>
                <button class="btn btn-default" data-bs-dismiss="modal">Tidak</button>
            </div>
        </div>
    </div>
</div>
