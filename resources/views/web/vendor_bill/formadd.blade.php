<!-- Modal Placeholder -->
<button type="button" id="btn-show-modal" class="" style="display:none;" data-bs-toggle="modal"
    data-bs-target="#data-modal-invoice"></button>
<div id="content-modal-form"></div>
<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Create Vendor Payment</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Vendor Payment</a></li>
                    <li class="breadcrumb-item active">Create Payment</li>
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
                <form onsubmit="VendorBill.submit(this, event)">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Payment Number</label>
                                <input type="text" id="payment_number" class="form-control" placeholder="Auto Generate"
                                    value="{{ isset($data->payment_number) ? $data->payment_number : 'AUTO' }}"
                                    disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Date</label>
                                <input type="date" id="payment_date" class="form-control required"
                                    value="{{ isset($data->payment_date) ? $data->payment_date : date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vendor</label>
                                <select class="form-control select2 required" id="vendor"
                                    onchange="VendorBill.loadInvoices(this)">
                                    <option value=""></option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ isset($data->vendor) && $data->vendor == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->nama_vendor }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select class="form-control" id="payment_method">
                                    <option value="cash" {{ isset($data->payment_method) && $data->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ isset($data->payment_method) && $data->payment_method == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer
                                    </option>
                                    <option value="cheque" {{ isset($data->payment_method) && $data->payment_method == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="other" {{ isset($data->payment_method) && $data->payment_method == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Reference Number</label>
                                <input type="text" id="reference_number" class="form-control"
                                    value="{{ isset($data->reference_number) ? $data->reference_number : '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea id="remarks" class="form-control"
                                    placeholder="Optional remarks">{{ isset($data->remarks) ? $data->remarks : '' }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Total Payment</label>
                                <input type="text" id="total_payment" class="form-control"
                                    value="{{ isset($data->total_payment) ? number_format($data->total_payment, 2) : '0.00' }}"
                                    disabled>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Detail Pembayaran -->
                    <h5 class="mb-3">Invoice to Pay</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-items">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Invoice Date</th>
                                    <th>Total Amount</th>
                                    <th>Outstanding</th>
                                    <th>Amount Paid</th>
                                    <th>Remaining</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="detail-body">
                                @if (!empty($data_invoices))
                                    @foreach ($data_invoices as $inv)
                                        <tr class="input" data_id="{{ $inv->id }}">
                                            <td><input type="text" class="form-control" id="invoice_number"
                                                    value="{{ $inv->invoice_number }}" disabled></td>
                                            <td><input type="date" class="form-control" id="invoice_date"
                                                    value="{{ $inv->invoice_date }}" disabled></td>
                                            <td><input type="number" class="form-control" id="total_amount"
                                                    value="{{ $inv->total_amount }}" disabled></td>
                                            <td><input type="number" class="form-control" id="outstanding"
                                                    value="{{ $inv->outstanding }}" disabled></td>
                                            <td><input type="number" class="form-control" id="amount_paid" value="0"
                                                    onkeyup="VendorBill.calcRow(this)"></td>
                                            <td><input type="number" class="form-control" id="remaining"
                                                    value="{{ $inv->outstanding }}" disabled></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="VendorBill.removeRow(this)">
                                                    <i class="bx bx-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="input" data_id="">
                                        <td><input type="text" class="form-control" id="invoice_number"
                                                    value="" disabled></td>
                                        <td><input type="date" class="form-control" id="invoice_date" value="" disabled>
                                        </td>
                                        <td><input type="number" class="form-control" id="total_amount" value="" disabled>
                                        </td>
                                        <td><input type="number" class="form-control" id="outstanding" value="" disabled>
                                        </td>
                                        <td><input type="number" class="form-control" id="amount_paid" value="0"
                                                onkeyup="VendorBil.calcRow(this)"></td>
                                        <td><input type="number" class="form-control" id="remaining" value="" disabled></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="VendorBill.removeRow(this)">
                                                <i class="bx bx-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- <button type="button" class="btn btn-sm btn-primary mt-2" onclick="VendorBill.addRow()">+ Tambah
                        Barang</button> --}}

                    <div class="text-end mt-4">
                        <h5>Total Payment: <span
                                id="total-payment-display">{{ isset($data->total_payment) ? number_format($data->total_payment, 2) : '0.00' }}</span>
                        </h5>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success waves-effect waves-light me-1">Submit</button>
                        <button type="reset" onclick="VendorBill.back(this, event)"
                            class="btn btn-secondary waves-effect">Cancel</button>
                    </div>
                </form>

                @include('web.general_ledger.list_general_ledger')
            </div>
        </div>
    </div>
</div>
