<!-- Modal for Detail -->
<button type="button" id="btn-show-modal" class="" style="display: none;" data-bs-toggle="modal"
    data-bs-target="#data-modal-item"></button>
<div id="content-modal-form"></div>
<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Create Purchase Invoice</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Purchase Invoice</a></li>
                    <li class="breadcrumb-item active">Create Purchase Invoice</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<!-- Main Form -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form onsubmit="PurchaseInvoice.submit(this, event)">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Invoice Number</label>
                                <input type="text" id="invoice_number" class="form-control required"
                                    placeholder="Auto Generate" disabled
                                    value="{{ isset($data->invoice_number) ? $data->invoice_number : 'AUTO' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Invoice Date</label>
                                <input type="date" id="invoice_date" class="form-control required"
                                    value="{{ isset($data->invoice_date) ? $data->invoice_date : date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vendor</label>
                                <select class="form-control select2 required" id="vendor"
                                    onchange="PurchaseInvoice.getVendorData(this)">
                                    <option value=""></option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ isset($data->vendor) && $data->vendor == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Total Amount</label>
                                <input type="text" id="total_amount" class="form-control" value="0.00" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-control select2" id="status">
                                    <option value="draft" {{ isset($data->status) && $data->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="posted" {{ isset($data->status) && $data->status == 'posted' ? 'selected' : '' }}>Posted</option>
                                    <option value="paid" {{ isset($data->status) && $data->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="cancelled" {{ isset($data->status) && $data->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea id="remarks" class="form-control"
                                    placeholder="Optional remarks">{{ isset($data->remarks) ? $data->remarks : '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Detail Barang -->
                    <h5 class="mb-3">Invoice Details</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-items">
                            <thead class="table-light">
                                <tr>
                                    <th>PO Detail</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Tax</th>
                                    <th>Subtotal</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="detail-body">
                                @if (!empty($data_item))
                                    @foreach ($data_item as $item)
                                        <tr class="input" data_id="{{ $item->purchase_order_detail }}"
                                            id_detail="{{ $item->id }}">
                                            <td>
                                                <input type="text" class="form-control" id="po_detail" disabled
                                                    value="{{ $item->product_code }} / {{ $item->product_name }}">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" id="qty" value="{{ $item->qty }}"
                                                    min="1" onkeyup="PurchaseInvoice.calcRow(this)">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" id="unit" value="{{ $item->unit }}"
                                                    disabled>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" id="price"
                                                    value="{{ $item->purchase_price }}" readonly>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" id="discount"
                                                    value="{{ $item->discount_percent }}"
                                                    onkeyup="PurchaseInvoice.calcRow(this)">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" id="tax" value="{{ $item->tax }}"
                                                    onkeyup="PurchaseInvoice.calcRow(this)">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" id="subtotal"
                                                    value="{{ $item->subtotal }}" readonly>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="PurchaseInvoice.removeRow(this)"><i
                                                        class="bx bx-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="input" data_id="" id_detail="">
                                        <td><input type="text" class="form-control" id="po_detail"
                                                placeholder="Select PO Item" disabled></td>
                                        <td><input type="number" class="form-control" id="qty" value="1" min="1"
                                                onkeyup="PurchaseInvoice.calcRow(this)"></td>
                                        <td><input type="text" class="form-control" id="unit" disabled></td>
                                        <td><input type="number" class="form-control" id="price" value="0" readonly></td>
                                        <td><input type="number" class="form-control" id="discount" value="0"
                                                onkeyup="PurchaseInvoice.calcRow(this)"></td>
                                        <td><input type="number" class="form-control" id="tax" value="0"
                                                onkeyup="PurchaseInvoice.calcRow(this)"></td>
                                        <td><input type="number" class="form-control" id="subtotal" value="0" readonly></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="PurchaseInvoice.removeRow(this)"><i
                                                    class="bx bx-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-4">
                        <h5>Total Amount: <span id="total-amount">0.00</span></h5>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success waves-effect waves-light me-1">Submit</button>
                        <button type="reset" onclick="PurchaseInvoice.back(this, event)"
                            class="btn btn-secondary waves-effect">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
