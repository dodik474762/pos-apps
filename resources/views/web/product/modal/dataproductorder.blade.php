<!-- First modal dialog -->
<div class="modal bs-example-modal-lg fade" id="data-modal-product" aria-hidden="true" aria-labelledby="..."
    tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label>Principal</label>
                            <div>
                                <select class="form-control select2" error='Principal' id="principal" name="principal" onchange="SalesOrder.filterPrincipal(this)">
                                    <option value="">-- Principal --</option>
                                    @foreach ($vendors as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->nama_vendor }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="table-data-modal" class="table table-striped table-bordered dt-responsive nowrap"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Principal</th>
                                <th>Kode Produk</th>
                                <th>Produk</th>
                                <th>Satuan</th>
                                <th>Min Qty</th>
                                <th>Max Qty</th>
                                <th>Customer</th>
                                <th>Harga</th>
                                <th>Tanggal Berlaku</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center" colspan="10">Tidak ada data ditemukan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <!-- Toogle to second dialog -->
            </div>
        </div>
    </div>
</div>
