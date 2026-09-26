<!-- Modal konfirmasi reversal -->
<div class="modal fade" id="konfirmasi-reversal" aria-hidden="true" aria-labelledby="..." tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Reversal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Jurnal <b>{{ ! empty($journal) ? $journal->journal_no : '' }}</b> akan dibatalkan dengan membuat
                    jurnal reversal baru, debit dan credit dibalik.</p>
                <p class="text-muted">Jurnal asli tidak dihapus, statusnya menjadi REVERSED.</p>

                <div class="mb-3">
                    <label class="form-label">Tanggal Reversal</label>
                    <input type="date" id="reversal-date" class="form-control" value="{{ date('Y-m-d') }}">
                    <small class="text-muted">Tanggal reversal harus berada pada periode akuntansi OPEN.</small>
                </div>

                <div class="mb-0">
                    <label class="form-label">Alasan</label>
                    <textarea id="reversal-description" class="form-control" rows="2"
                        placeholder="Alasan reversal"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" onclick="Journals.confirmReversal(this)"
                    data_id="{{ ! empty($journal) ? $journal->id : $id }}">Ya</button>
                <button class="btn btn-default" data-bs-dismiss="modal">Tidak</button>
            </div>
        </div>
    </div>
</div>
