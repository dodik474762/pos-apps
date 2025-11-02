<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Create {{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">{{ $title_parent }}</a></li>
                    <li class="breadcrumb-item active">Create {{ $title }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form onsubmit="PurchaseOrder.submit(this, event)">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Kode PO</label>
                                <input type="text" id="code" class="form-control required" error="Kode PO"
                                    placeholder="Auto Generate" readonly
                                    value="{{ isset($data->code) ? $data->code : 'AUTO' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal PO</label>
                                <input type="date" id="po_date" class="form-control required" error="Tanggal PO"
                                    value="{{ isset($data->po_date) ? $data->po_date : date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vendor</label>
                                <select class="form-control select2 required" id="vendor" error="Vendor">
                                    <option value=""></option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}"
                                            {{ isset($data->vendor) && $data->vendor == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Gudang</label>
                                <select class="form-control select2 required" id="warehouse" error="Gudang">
                                    <option value=""></option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}"
                                            {{ isset($data->warehouse) && $data->warehouse == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea id="remarks" class="form-control" placeholder="Catatan (opsional)">{{ isset($data->remarks) ? $data->remarks : '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Detail Barang -->
                    <h5 class="mb-3">Detail Barang</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="po-detail-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%">Produk</th>
                                    <th style="width: 10%">Satuan</th>
                                    <th style="width: 10%">Qty</th>
                                    <th style="width: 15%">Harga Beli</th>
                                    <th style="width: 10%">Disc (%)</th>
                                    <th style="width: 10%">Disc (Rp)</th>
                                    <th style="width: 15%">Subtotal</th>
                                    <th style="width: 5%"></th>
                                </tr>
                            </thead>
                            <tbody id="detail-body">
                                <tr>
                                    <td>
                                        <select class="form-control select2 product" onchange="PurchaseOrder.onProductChange(this)">
                                            <option value=""></option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control select2 unit">
                                            <option value=""></option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-control qty" value="1" min="1" onchange="PurchaseOrder.calcRow(this)"></td>
                                    <td><input type="number" class="form-control price" value="0" onchange="PurchaseOrder.calcRow(this)"></td>
                                    <td><input type="number" class="form-control disc_persen" value="0" onchange="PurchaseOrder.calcRow(this)"></td>
                                    <td><input type="number" class="form-control disc_nominal" value="0" onchange="PurchaseOrder.calcRow(this)"></td>
                                    <td><input type="text" class="form-control subtotal" readonly value="0"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="PurchaseOrder.removeRow(this)">x</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-sm btn-primary mt-2" onclick="PurchaseOrder.addRow()">+ Tambah Barang</button>

                    <div class="text-end mt-4">
                        <h5>Total: <span id="total-harga">0</span></h5>
                    </div>

                </form>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" onclick="PurchaseOrder.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">
                Submit
            </button>
            <button type="reset" onclick="PurchaseOrder.back(this, event)" class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>
    </div>
</div>
<!-- end row -->
