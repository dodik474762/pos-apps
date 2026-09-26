<!-- First modal dialog -->
<div class="modal fade" id="konfirmasi-delete" aria-hidden="true" aria-labelledby="..." tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                    $isOpen = ! empty($is_open);
                    $hasActiveJournal = ! empty($has_active_journal);
                    $journalNo = ! empty($journal_no) ? $journal_no : '';
                    $journalStatus = ! empty($journal_status) ? $journal_status : '';
                @endphp

                @if ($hasActiveJournal)
                    <div class="alert alert-warning mb-3">
                        <h6 class="alert-heading">Jurnal harus direversal terlebih dahulu</h6>
                        <p class="mb-0">
                            Penerimaan barang <b>{{ $gr_number }}</b> memiliki jurnal
                            <b>{{ $journal_no }}</b> yang masih berstatus <b>{{ $journal_status }}</b>.
                            Penghapusan ditolak selama jurnal tersebut belum direversal.
                        </p>
                    </div>
                    <p class="mb-0">
                        Lakukan reversal jurnal pada menu <b>Transaksi &gt; Jurnal</b>, dengan memilih jurnal
                        <b>{{ $journal_no }}</b> dan menekan tombol <b>Reversal</b>. Setelah jurnal berstatus
                        <b>REVERSED</b>, penerimaan barang ini dapat dihapus.
                    </p>
                @else
                    <p>Apakah anda yakin akan menghapus data ini ?</p>

                    @if (! $isOpen)
                        <div class="alert alert-warning mb-3">
                            <h6 class="alert-heading">Status bukan open</h6>
                            <p class="mb-0">
                                Penerimaan barang <b>{{ $gr_number }}</b> berstatus <b>{{ $gr_status }}</b>
                                sehingga dampaknya sudah pernah berjalan ke modul lain. Hapus data hanya
                                diperbolehkan bila jurnal penerimaan telah direversal.
                            </p>
                        </div>

                        @if ($journalNo != '' && $journalStatus == 'REVERSED')
                            <p class="text-muted mb-0">
                                Jurnal <b>{{ $journal_no }}</b> sudah berstatus <b>REVERSED</b>, penghapusan
                                dapat dilanjutkan.
                            </p>
                        @else
                            <p class="mb-0">
                                Bila jurnal untuk penerimaan barang ini sudah pernah dibuat, jurnal tersebut
                                harus direversal lebih dulu melalui menu <b>Transaksi &gt; Jurnal</b>.
                            </p>
                        @endif
                    @endif
                @endif
            </div>
            <div class="modal-footer">
                <!-- Toogle to second dialog -->
                @if ($hasActiveJournal)
                    <button class="btn btn-warning" data-bs-dismiss="modal">Tutup</button>
                @else
                    <button class="btn btn-primary" onclick="GoodReceipt.confirmDelete(this)"
                        data_id="{{ $id }}">Ya</button>
                    <button class="btn btn-default" data-bs-dismiss="modal">Tidak</button>
                @endif
            </div>
        </div>
    </div>
</div>
