<button type="button" id="btn-show-modal" class="" style="display: none;" data-bs-toggle="modal"
  data-bs-target="#data-modal-karyawan"></button>
<div id="content-modal-form"></div><input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">

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
                                <label class="form-label">Tanggal Estimasi Diterima</label>
                                <input type="date" id="est_received_date" class="form-control required" error="Tanggal PO"
                                    value="{{ isset($data->est_received_date) ? $data->est_received_date : date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vendor</label>
                                <select class="form-control select2 required" id="vendor" error="Vendor">
                                    <option value=""></option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}"
                                            {{ isset($data->vendor) && $data->vendor == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->nama_vendor }}
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
                        <table class="table table-bordered" id="table-items">
                            <thead class="table-light">
                                  <tr>
                                        <th style="width: 20%">Produk</th>
                                        <th style="width: 10%">Satuan</th>
                                        <th style="width: 8%">Qty</th>
                                        <th style="width: 12%">Harga Beli</th>
                                        <th style="width: 8%">Disc (%)</th>
                                        <th style="width: 8%">Disc (Rp)</th>
                                        <th style="width: 12%">Pajak</th>   <!-- ✅ Kolom baru -->
                                        <th style="width: 12%">Subtotal</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                            </thead>
                            <tbody id="detail-body">
                                @if (!empty($data_item))
                                    @foreach ($data_item as $item)
                                        <tr class="input" data_id="{{ $item->id }}">
                                            <td>
                                                <div class="input-group">
                                                    <button class="btn btn-outline-primary" type="button" id="button-addon1"
                                                        onclick="PurchaseOrder.showDataProduct(this)">Pilih</button>
                                                    <input disabled id="product" name="product" type="text" class="form-control required"
                                                        error="Product" placeholder="Pilih Data Product" aria-label="Pilih Data Product"
                                                        aria-describedby="button-addon1"
                                                        value="{{ $item->product_uom.'//'.$item->product.'//'.$item->product_name }}">
                                                </div>
                                            </td>
                                            <td data_id="{{ $item->unit }}" id="unit">{{ $item->unit_name }}</td>
                                            <td><input type="number" class="form-control" id="qty" value="{{ $item->qty }}" min="1" onkeyup="PurchaseOrder.calcRow(this)"></td>
                                            <td><input type="number" class="form-control" id="price" value="{{ $item->purchase_price }}" onkeyup="PurchaseOrder.calcRow(this)"></td>
                                            <td><input type="number" class="form-control" id="disc_persen" value="{{ $item->diskon_persen }}" onkeyup="PurchaseOrder.calcRow(this)"></td>
                                            <td><input type="number" class="form-control" id="disc_nominal" value="{{ $item->diskon_nominal }}" onkeyup="PurchaseOrder.calcRow(this)"></td>
                                            <td>
                                                <select class="form-control" id="tax" onchange="PurchaseOrder.calcRow(this)">
                                                    <option value="" data-rate="0">Tanpa Pajak</option>
                                                    @foreach ($taxes as $tax)
                                                        <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}"
                                                            {{ isset($item->tax) && $item->tax == $tax->id ? 'selected' : '' }}>
                                                            {{ $tax->tax_name }} ({{ $tax->rate }}%)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input disabled type="text" class="form-control" id="subtotal" value="{{ $item->subtotal }}"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="PurchaseOrder.removeRow(this)"><i class="bx bx-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="input" data_id="">
                                        <td>
                                            <div class="input-group">
                                                <button class="btn btn-outline-primary" type="button" id="button-addon1"
                                                    onclick="PurchaseOrder.showDataProduct(this)">Pilih</button>
                                                <input disabled id="product" name="product" type="text" class="form-control required"
                                                    error="Product" placeholder="Pilih Data Product" aria-label="Pilih Data Product"
                                                    aria-describedby="button-addon1"
                                                    value="">
                                            </div>
                                        </td>
                                        <td data_id="" id="unit"></td>
                                        <td><input type="number" class="form-control" id="qty" value="1" min="1" onkeyup="PurchaseOrder.calcRow(this)"></td>
                                        <td><input type="number" class="form-control" id="price" value="0" onkeyup="PurchaseOrder.calcRow(this)"></td>
                                        <td><input type="number" class="form-control" id="disc_persen" value="0" onkeyup="PurchaseOrder.calcRow(this)"></td>
                                        <td><input type="number" class="form-control" id="disc_nominal" value="0" onkeyup="PurchaseOrder.calcRow(this)"></td>
                                        <td>
                                            <select class="form-control" id="tax" onchange="PurchaseOrder.calcRow(this)">
                                                <option value="" data-rate="0">Tanpa Pajak</option>
                                                @foreach ($taxes as $tax)
                                                    <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}">
                                                        {{ $tax->tax_name }} ({{ $tax->rate }}%)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input disabled type="text" class="form-control" id="subtotal" value="0"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="PurchaseOrder.removeRow(this)"><i class="bx bx-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-sm btn-primary mt-2" onclick="PurchaseOrder.addRow()">+ Tambah Barang</button>

                    <div class="text-end mt-4">
                        <h5>Total: <span id="total-harga">{{ isset($data->grand_total) ? $data->grand_total : 0 }}</span></h5>
                    </div>

                </form>
            </div>
        </div>

        <div class="text-end">
            @if (isset($id))
                @if ($data->status == 'draft')
                    <button type="submit" onclick="PurchaseOrder.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">
                        Submit
                    </button>
                @endif
            @else
                <button type="submit" onclick="PurchaseOrder.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
            @endif
            <button type="reset" onclick="PurchaseOrder.back(this, event)" class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>
    </div>
</div>
<!-- end row -->
