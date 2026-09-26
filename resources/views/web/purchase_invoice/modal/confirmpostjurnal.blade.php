<!-- Modal konfirmasi pembuatan jurnal pembelian purchase invoice -->
<div class="modal fade" id="konfirmasi-post-jurnal" aria-hidden="true" aria-labelledby="..." tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Posting Jurnal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin akan membuat dan memposting jurnal pembelian untuk Purchase Invoice
                    <b>{{ ! empty($invoice_number) ? $invoice_number : '' }}</b>
                    sebesar <b>Rp {{ ! empty($invoice_total) ? number_format($invoice_total, 0, ',', '.') : '0' }}</b> ?
                </p>
                <p class="mb-1">Jurnal yang dibuat:</p>
                <ul class="mb-2">
                    <li>Debit <b>INVENTORY</b> untuk baris produk yang <b>is_stock</b> bukan 0</li>
                    <li>Debit <b>EXPENSE</b> untuk baris produk yang <b>is_stock</b> bernilai 0</li>
                    <li>Baris dengan akun debit yang sama digabung menjadi satu baris</li>
                    <li>Credit <b>AP</b> sebesar total invoice</li>
                </ul>
                <p class="text-muted mb-0">
                    Akun diambil dari menu Master &gt; Account Mapping Rules dengan Transaction Type
                    <b>PURCHASE_INVOICE</b>. Bila mapping AP, INVENTORY atau EXPENSE belum tersedia, proses
                    dibatalkan. PPN Masukan tetap diproses oleh posting GL legacy sehingga tidak termasuk jurnal ini.
                    Setelah diposting, jurnal bersifat immutable.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="PurchaseInvoice.confirmPostJurnal(this)"
                    data_id="{{ ! empty($id) ? $id : '' }}">Ya</button>
                <button class="btn btn-default" data-bs-dismiss="modal">Tidak</button>
            </div>
        </div>
    </div>
</div>
