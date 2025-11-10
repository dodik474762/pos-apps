<button type="button" id="btn-show-modal" style="display: none;" data-bs-toggle="modal" data-bs-target="#data-modal-product"></button>
<div id="content-modal-form"></div>
<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Create Sales Order</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Sales</a></li>
                    <li class="breadcrumb-item active">Create Sales Order</li>
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
                <form onsubmit="SalesOrder.submit(this, event)">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">SO Number</label>
                                <input type="text" id="so_number" class="form-control required" error="SO Number"
                                    placeholder="Auto Generate" readonly
                                    value="{{ isset($data->so_number) ? $data->so_number : 'AUTO' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">SO Date</label>
                                <input type="date" id="so_date" class="form-control required" error="SO Date"
                                    value="{{ isset($data->so_date) ? $data->so_date : date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <select class="form-control select2 required" id="customer_id" error="Customer">
                                    <option value=""></option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                            {{ isset($data->customer_id) && $data->customer_id == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->nama_customer }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Term</label>
                                <input type="text" id="payment_term" class="form-control" placeholder="e.g. 30 days"
                                    value="{{ isset($data->payment_term) ? $data->payment_term : '' }}">
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Currency</label>
                                <select class="form-control select2" id="currency">
                                    <option value=""></option>
                                    {{-- @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}"
                                            {{ isset($data->currency) && $data->currency == $currency->id ? 'selected' : '' }}>
                                            {{ $currency->code }}
                                        </option>
                                    @endforeach --}}
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
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
                                    <th style="width: 12%">Unit Price</th>
                                    <th style="width: 8%">Disc (%)</th>
                                    <th style="width: 8%">Disc (Rp)</th>
                                    <th style="width: 12%">Tax (%)</th>
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
                                                    <button class="btn btn-outline-primary" type="button"
                                                        onclick="SalesOrder.showDataProduct(this)">Pilih</button>
                                                    <input disabled type="text" class="form-control required" error="Product"
                                                        value="{{ $item->product_name }}">
                                                </div>
                                            </td>
                                            <td data_id="{{ $item->unit }}" id="unit">{{ $item->unit_name }}</td>
                                            <td><input type="number" class="form-control" id="qty" value="{{ $item->qty }}" min="1" onkeyup="SalesOrder.calcRow(this)"></td>
                                            <td><input type="number" class="form-control" id="unit_price" value="{{ $item->unit_price }}" onkeyup="SalesOrder.calcRow(this)"></td>
                                            <td><input type="number" class="form-control" id="disc_percent" value="{{ $item->discount_value }}" onkeyup="SalesOrder.calcRow(this)"></td>
                                            <td><input type="number" class="form-control" id="disc_amount" value="{{ $item->discount_amount }}" onkeyup="SalesOrder.calcRow(this)"></td>
                                            <td><input type="number" class="form-control" id="tax_percent" value="{{ $item->tax_percent }}" onkeyup="SalesOrder.calcRow(this)"></td>
                                            <td><input disabled type="text" class="form-control" id="subtotal" value="{{ $item->subtotal }}"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="SalesOrder.removeRow(this)"><i class="bx bx-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="input" data_id="">
                                        <td>
                                            <div class="input-group">
                                                <button class="btn btn-outline-primary" type="button" onclick="SalesOrder.showDataProduct(this)">Pilih</button>
                                                <input disabled type="text" class="form-control required" error="Product" value="">
                                            </div>
                                        </td>
                                        <td data_id="" id="unit"></td>
                                        <td><input type="number" class="form-control" id="qty" value="1" min="1" onkeyup="SalesOrder.calcRow(this)"></td>
                                        <td><input type="number" class="form-control" id="unit_price" value="0" onkeyup="SalesOrder.calcRow(this)"></td>
                                        <td><input type="number" class="form-control" id="disc_percent" value="0" onkeyup="SalesOrder.calcRow(this)"></td>
                                        <td><input type="number" class="form-control" id="disc_amount" value="0" onkeyup="SalesOrder.calcRow(this)"></td>
                                        <td><input type="number" class="form-control" id="tax_percent" value="0" onkeyup="SalesOrder.calcRow(this)"></td>
                                        <td><input disabled type="text" class="form-control" id="subtotal" value="0"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="SalesOrder.removeRow(this)"><i class="bx bx-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-sm btn-primary mt-2" onclick="SalesOrder.addRow()">+ Tambah Barang</button>

                    <div class="text-end mt-4">
                        <h5>Total: <span id="total-harga">{{ isset($data->total_amount) ? $data->total_amount : 0 }}</span></h5>
                    </div>

                </form>
            </div>
        </div>

        <div class="text-end">
            @if (isset($id))
                @if ($data->status == 'draft')
                    <button type="submit" onclick="SalesOrder.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">
                        Submit
                    </button>
                @endif
            @else
                <button type="submit" onclick="SalesOrder.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
            @endif
            <button type="reset" onclick="SalesOrder.back(this, event)" class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>
    </div>
</div>
<!-- end row -->
