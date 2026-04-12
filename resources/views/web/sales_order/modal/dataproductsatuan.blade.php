<!-- Modal Pilih Satuan -->
<div class="modal fade" id="data-modal-product" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Satuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" id="satuan-produk-name">{{ !empty($products) ? $products[0]->name : '-' }}</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="table-satuan-modal">
                        <thead class="table-light">
                            <tr>
                                <th>Satuan</th>
                                <th>Harga</th>
                                <th width="15%">Qty</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="satuan-modal-body">
                            @foreach($products as $item)
                                <tr>
                                    <td>{{ $item->unit_tujuan_name }}</td>
                                    <td>{{ number_format($item->harga,0, ',','.') }}</td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm qty-input" value="1" min="1">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary"
                                            onclick='SalesOrder.pilihSatuan(this, @json($item))'>
                                            Pilih
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>