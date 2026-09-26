<!-- Modal konfirmasi pembuatan jurnal HPP delivery order -->
<div class="modal fade" id="konfirmasi-post-jurnal" aria-hidden="true" aria-labelledby="..." tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Posting Jurnal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin akan membuat dan memposting jurnal HPP untuk Delivery Order
                    <b>{{ ! empty($do_number) ? $do_number : '' }}</b> ?
                </p>
                <p class="mb-1">Jurnal yang dibuat:</p>
                <ul class="mb-2">
                    <li>Debit <b>COGS</b> sebesar total HPP</li>
                    <li>Credit <b>INVENTORY</b> sebesar total HPP yang sama</li>
                </ul>
                <p class="text-muted mb-0">
                    Akun diambil dari menu Master &gt; Account Mapping Rules dengan Transaction Type
                    <b>DELIVERY_ORDER</b>. Bila mapping COGS atau INVENTORY belum tersedia, proses dibatalkan.
                    Setelah diposting, jurnal bersifat immutable.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="DeliveryOrder.confirmPostJurnal(this)"
                    data_id="{{ ! empty($id) ? $id : '' }}">Ya</button>
                <button class="btn btn-default" data-bs-dismiss="modal">Tidak</button>
            </div>
        </div>
    </div>
</div>
