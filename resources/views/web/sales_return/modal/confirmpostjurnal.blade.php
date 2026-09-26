<!-- Modal konfirmasi pembuatan jurnal sales return -->
<div class="modal fade" id="konfirmasi-post-jurnal" aria-hidden="true" aria-labelledby="..." tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Posting Jurnal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin akan membuat dan mempost jurnal untuk Sales Return
                    <b>{{ ! empty($return_number) ? $return_number : '' }}</b>
                    dari <b>{{ ! empty($customer_name) ? $customer_name : '' }}</b>?
                </p>
                <p class="mb-1">Jurnal yang dibuat, untuk
                    <b>{{ ! empty($detail_count) ? $detail_count : 0 }}</b> baris retur:</p>
                <ul class="mb-2">
                    <li>Debit <b>SALES_RETURN</b> (kontra pendapatan) sebesar nilai jual barang yang dikembalikan</li>
                    <li>Credit <b>AR</b> sebesar nilai jual barang yang dikembalikan</li>
                    @if (! empty($is_damaged))
                        <li>Debit <b>LOSS</b> sebesar HPP, <b>tidak</b> melalui Inventory</li>
                        <li>Credit <b>COGS</b> sebesar HPP barang yang dikembalikan</li>
                    @else
                        <li>Debit <b>INVENTORY</b> sebesar HPP, karena barang kembali ke stok</li>
                        <li>Credit <b>COGS</b> sebesar HPP barang yang dikembalikan</li>
                    @endif
                    <li>Baris dengan akun yang sama digabung menjadi satu baris</li>
                </ul>

                @if (! empty($is_damaged))
                    <div class="alert alert-warning mb-2">
                        <h6 class="alert-heading">Kondisi barang: tidak layak jual</h6>
                        <p class="mb-0">
                            Sales Return ini berstatus
                            <b>{{ ! empty($good_condition) ? $good_condition : '-' }}</b>, sehingga barang
                            dianggap rusak dan tidak dikembalikan ke stok. Sisi persediaan memakai akun
                            kerugian <b>LOSS</b>, bukan <b>INVENTORY</b>.
                        </p>
                    </div>
                @endif

                <p class="text-muted mb-0">
                    Nilai jual diambil dari Sales Invoice asal, diprorate terhadap qty yang diretur.
                    Nilai HPP ditelusuri ke Delivery Order asal memakai basis
                    <b>product_uom_cost</b> yang sama dengan jurnal Delivery Order, sehingga tidak
                    dihitung ulang. PPN tetap diproses oleh posting GL legacy sehingga tidak termasuk
                    jurnal ini. Akun diambil dari menu Master &gt; Account Mapping Rules dengan
                    Transaction Type <b>SALES_RETURN</b>. Setelah diposting, jurnal bersifat immutable.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="SalesReturn.confirmPostJurnal(this)"
                    data_id="{{ ! empty($id) ? $id : '' }}">Ya</button>
                <button class="btn btn-default" data-bs-dismiss="modal">Tidak</button>
            </div>
        </div>
    </div>
</div>
