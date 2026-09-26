<!-- Modal konfirmasi pembuatan jurnal purchase return -->
<div class="modal fade" id="konfirmasi-post-jurnal" aria-hidden="true" aria-labelledby="..." tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Posting Jurnal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin akan membuat dan mempost jurnal untuk Purchase Return
                    <b>{{ ! empty($return_number) ? $return_number : '' }}</b>
                    dari <b>{{ ! empty($vendor_name) ? $vendor_name : '' }}</b>?
                </p>
                <p class="mb-2">
                    Total retur <b>{{ ! empty($return_total) ? number_format((float) $return_total, 0, ',', '.') : '0' }}</b>
                    untuk <b>{{ ! empty($detail_count) ? $detail_count : 0 }}</b> baris.
                </p>

                @if (! empty($journal_rows))
                    <table class="table table-sm table-bordered mb-2">
                        <thead>
                            <tr>
                                <th>Sumber</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Baris</th>
                                <th>Produk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($journal_rows as $row)
                                <tr>
                                    <td>{{ $row['source'] }}</td>
                                    <td><b>{{ $row['debit_role'] }}</b></td>
                                    <td><b>{{ $row['credit_role'] }}</b></td>
                                    <td>{{ $row['items'] }}</td>
                                    <td>{{ implode(', ', $row['products']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <ul class="mb-2">
                    <li>Baris dari <b>Purchase Invoice</b> memakai debit <b>AP</b>, karena kewajiban ke
                        supplier sudah diakui</li>
                    <li>Baris dari <b>Receiving</b> memakai debit <b>GRNI</b>, karena barang baru diterima
                        dan kewajiban belum diakui</li>
                    <li>Barang <b>non stok</b> dikreditkan ke <b>EXPENSE</b>, membalik beban yang pernah
                        diakui saat pembelian</li>
                    <li>Baris dengan akun yang sama digabung menjadi satu baris</li>
                </ul>

                <p class="text-muted mb-0">
                    Nilai perolehan diambil dari dokumen asal, diprorate terhadap qty yang diretur, bukan
                    dihitung ulang dari harga beli. Nilai invoice sudah termasuk PPN dan tetap dibukukan
                    di sisi persediaan, sama seperti jurnal Purchase Invoice, sehingga PPN tidak dipisah
                    di jurnal ini. Akun diambil dari menu Master &gt; Account Mapping Rules dengan
                    Transaction Type <b>PURCHASE_RETURN</b>. Setelah diposting, jurnal bersifat immutable.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="PurchaseReturn.confirmPostJurnal(this)"
                    data_id="{{ ! empty($id) ? $id : '' }}">Ya</button>
                <button class="btn btn-default" data-bs-dismiss="modal">Tidak</button>
            </div>
        </div>
    </div>
</div>
