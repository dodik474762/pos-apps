<!-- Modal konfirmasi pembuatan jurnal penerimaan barang -->
<div class="modal fade" id="konfirmasi-post-jurnal" aria-hidden="true" aria-labelledby="..." tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Posting Jurnal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin akan membuat dan memposting jurnal penerimaan barang untuk
                    <b>{{ ! empty($gr_number) ? $gr_number : '' }}</b>
                    sebesar <b>Rp {{ ! empty($gr_total) ? number_format($gr_total, 0, ',', '.') : '0' }}</b> ?
                </p>
                <p class="mb-1">Jurnal yang dibuat:</p>
                <ul class="mb-2">
                    <li>Debit <b>INVENTORY</b> dengan nilai barang dari harga di Purchase Order</li>
                    <li>Baris dengan akun debit yang sama digabung menjadi satu baris</li>
                    <li>Credit <b>GRNI</b> sebesar total barang diterima</li>
                </ul>
                <p class="text-muted mb-0">
                    Jurnal ini hanya mengakui barang masuk gudang. Hutang ke supplier
                    <b>belum</b> diakui di sini dan akan diakui saat Purchase Invoice diposting
                    melalui debit ke akun GRNI yang sama. Akun diambil dari menu
                    Master &gt; Account Mapping Rules dengan Transaction Type <b>GOODS_RECEIPT</b>.
                    Bila mapping INVENTORY atau GRNI belum tersedia, proses dibatalkan.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="GoodReceipt.confirmPostJurnal(this)"
                    data_id="{{ ! empty($id) ? $id : '' }}">Ya</button>
                <button class="btn btn-default" data-bs-dismiss="modal">Tidak</button>
            </div>
        </div>
    </div>
</div>
