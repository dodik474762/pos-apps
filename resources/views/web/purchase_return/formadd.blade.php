<!-- Modal Placeholder -->
<button type="button" id="btn-show-modal" class="" style="display:none;" data-bs-toggle="modal"
    data-bs-target="#data-modal-purchase-return"></button>
<div id="content-modal-form"></div>
<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Create Purchase Return</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Purchase Return</a></li>
                    <li class="breadcrumb-item active">Create Return</li>
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
                <form onsubmit="PurchaseReturn.submit(this, event)">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Return Number</label>
                                <input type="text" id="return_number" class="form-control"
                                    placeholder="Auto Generate"
                                    value="{{ isset($data->return_number) ? $data->return_number : 'AUTO' }}"
                                    disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Return Date</label>
                                <input type="date" id="return_date" class="form-control required"
                                    value="{{ isset($data->return_date) ? $data->return_date : date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vendor</label>
                                <select class="form-control select2 required" id="vendor"
                                    onchange="PurchaseReturn.loadReference(this)">
                                    <option value=""></option>
                                    @foreach ($vendors as $supplier)
                                        <option value="{{ $supplier->id }}" {{ isset($data->vendor) && $data->vendor == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->nama_vendor }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Warehouse</label>
                                <select class="form-control select2 required" id="warehouse_id">
                                    <option value=""></option>
                                    @foreach ($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ isset($data->warehouse) && $data->warehouse == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Return Type</label>
                                <select id="return_type" class="form-control" onchange="PurchaseReturn.changeType(this)">
                                    <option value="FROM_GR" {{ isset($data->return_type) && $data->return_type == 'FROM_GR' ? 'selected' : '' }}>From Goods Receipt</option>
                                    <option value="FROM_INVOICE" {{ isset($data->return_type) && $data->return_type == 'FROM_INVOICE' ? 'selected' : '' }}>From Invoice</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reference</label>
                                <select id="reference_id" class="form-control select2 required" onchange="PurchaseReturn.loadReferenceDetail(this)">
                                    <option value="">-- Select Reference --</option>
                                    @if (isset($data->reference_id))
                                        <option value="{{ $data->reference_id }}" selected>{{ $reference->gr_number ?? $reference->invoice_number }}</option>
                                    @endif
                                    {{-- Options loaded dynamically via JS --}}
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Total Amount</label>
                                <input type="text" id="total_amount" class="form-control"
                                    value="{{ isset($data->total_amount) ? number_format($data->total_amount, 2) : '0.00' }}"
                                    disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea id="reason" class="form-control" placeholder="Return reason">{{ isset($data->reason) ? $data->reason : '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Detail Retur Barang -->
                    <h5 class="mb-3">Return Items</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-items">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Harga</th>
                                    <th>Total</th>
                                    <th>Reason</th>
                                    <th>Reference Detail</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="detail-body">
                                @if (!empty($details))
                                    @foreach ($details as $det)
                                        <tr class="input" data_id="{{ $det->id }}">
                                            <td>
                                                <input type="text" class="form-control" value="{{ $det->item_name }}" disabled>
                                            </td>
                                            <td><input type="number" class="form-control" id="qty" value="{{ number_format($det->qty, 0) }}" onkeyup="PurchaseReturn.calcRow(this)"></td>
                                            <td><input type="text" disabled class="form-control" id="unit" data_id="{{ $det->unit }}" value="{{ $det->unit_name }}" onkeyup="PurchaseReturn.calcRow(this)"></td>
                                            <td><input type="number" disabled class="form-control" id="unit_price" value="{{ $det->unit_price }}" onkeyup="PurchaseReturn.calcRow(this)"></td>
                                            <td><input type="number" class="form-control" id="total_price" value="{{ $det->qty * $det->unit_price }}" disabled></td>
                                            <td><input type="text" class="form-control" id="reason_detail" value="{{ $det->reason }}"></td>
                                            <td><input type="text" class="form-control" id="reference_detail" value="{{ $det->reference_detail_id }}" disabled></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="PurchaseReturn.removeRow(this)">
                                                    <i class="bx bx-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="input">
                                        <td><input type="text" class="form-control" id="item_name" disabled></td>
                                        <td><input type="number" class="form-control" id="qty" value="0" onkeyup="PurchaseReturn.calcRow(this)"></td>
                                        <td><input type="text" disabled class="form-control" id="unit" value="" onkeyup="PurchaseReturn.calcRow(this)"></td>
                                        <td><input type="number" disabled class="form-control" id="unit_price" value="0" onkeyup="PurchaseReturn.calcRow(this)"></td>
                                        <td><input type="number" class="form-control" id="total_price" value="0" disabled></td>
                                        <td><input type="text" class="form-control" id="reason_detail" value=""></td>
                                        <td><input type="text" class="form-control" id="reference_detail" value="" disabled></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="PurchaseReturn.removeRow(this)">
                                                <i class="bx bx-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-4">
                        <h5>Total Return: <span id="total-return-display">{{ isset($data->total_amount) ? number_format($data->total_amount, 2) : '0.00' }}</span></h5>
                    </div>

                    <div class="text-end">
                        @if (!isset($id))
                            <button type="submit" onclick="PurchaseReturn.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">Submit</button>
                        @endif
                        <button type="reset" onclick="PurchaseReturn.back(this, event)" class="btn btn-secondary waves-effect">Cancel</button>
                    </div>
                </form>

                @include('web.general_ledger.list_general_ledger')
            </div>
        </div>
    </div>
</div>
