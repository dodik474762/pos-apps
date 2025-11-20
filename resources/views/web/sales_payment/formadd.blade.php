<button type="button" id="btn-show-modal" style="display: none;"
        data-bs-toggle="modal" data-bs-target="#data-modal-product"></button>

<div id="content-modal-form"></div>

<input type="hidden" id="id" value="{{ $data->id ?? '' }}">
<input type="hidden" id="url"
       value="{{ isset($data) ? route('sales-payment-edit') : route('sales-payment-add') }}">

<!-- Start Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                {{ isset($data) ? 'Edit Sales Payment' : 'Create Sales Payment' }}
            </h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Sales</a></li>
                    <li class="breadcrumb-item active">Payment</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- End Page Title -->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">

                <form onsubmit="SalesPayment.submit(this, event)">

                    <div class="row">

                        <!-- LEFT -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Payment Code</label>
                                <input type="text" id="payment_code" class="form-control required"
                                       value="{{ $data->payment_code ?? 'AUTO' }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Date</label>
                                <input type="date" id="payment_date" class="form-control required"
                                       error="Payment Date"
                                       value="{{ $data->payment_date ?? date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select id="payment_method" class="form-control select2" required>
                                    <option value=""></option>
                                    @foreach (['CASH','BANK','GIRO','TRANSFER','RETURN','OFFSET'] as $method)
                                        <option value="{{ $method }}" {{ isset($data->payment_method) && $data->payment_method == $method ? 'selected' : '' }}>{{ $method }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-primary"
                                            onclick="SalesPayment.showModalCustomer(this)">
                                        Pilih
                                    </button>
                                    <input disabled type="text" id="customer_id" class="form-control required"
                                           error="Customer"
                                           value="{{ isset($data->customer_id) ? $data->customer_id . '//' . $data->nama_customer : '' }}"
                                           data_id="{{ $data->customer_id ?? '' }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Account Kas / Bank</label>
                                <select id="account_id" class="form-control select2 required">
                                    <option value="">-- Pilih Akun --</option>
                                    @foreach ($cashBankAccounts as $acc)
                                        <option value="{{ $acc->id }}" {{ isset($data->coa_kas) ? ($data->coa_kas == $acc->id ? 'selected' : '')  : ''}}>
                                            {{ $acc->account_code }} - {{ $acc->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">Total Amount</label>
                                <input disabled type="number" step="0.01" id="total_amount" class="form-control required"
                                       value="{{ $data->total_amount ?? 0 }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Discount Amount</label>
                                <input disabled type="number" step="0.01" id="discount_amount" class="form-control"
                                       value="{{ $data->discount_amount ?? 0 }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Net Amount</label>
                                <input disabled type="number" step="0.01" id="net_amount" class="form-control"
                                       value="{{ $data->net_amount ?? 0 }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reference No</label>
                                <input type="text" id="reference_no" class="form-control"
                                       value="{{ $data->reference_no ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea id="remarks" class="form-control">{{ $data->remarks ?? '' }}</textarea>
                            </div>

                        </div>
                    </div>

                    <hr>

                    {{-- ================= DETAIL ITEMS ================= --}}
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-items">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%">Invoice</th>
                                    <th style="width: 20%">Tanggal Invoice</th>
                                    <th style="width: 20%">Outstanding Amount</th>
                                    <th style="width: 20%">Allocated Amount</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>

                            <tbody id="detail-body">
                                @if(!empty($details))
                                    @foreach($details as $i => $item)
                                        <tr data_id="{{ $item->id }}">
                                            <td id="invoice_id" data_id="{{ $item->invoice_id }}" subtotal="{{ $item->subtotal + $item->discount_amount }}" discount_amount="{{ $item->discount_amount }}">{{ $item->invoice_number }}</td>
                                            <td id="date_invoice">{{ $item->invoice_date }}</td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control" id="outstanding_amount" disabled value="{{ $item->outstanding_amount }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control" id="allocated_amount" value="{{ $item->allocated_amount }}" min="0" max="{{ $item->outstanding_amount }}" onkeyup="SalesPayment.changeAllocate(this)">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="SalesPayment.removeRow(this)">
                                                    <i class="bx bx-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr data_id="">
                                        <td id="invoice_id"  data_id="" subtotal="" discount_amount=""></td>
                                        <td id="date_invoice"></td>
                                        <td>
                                            <input type="number" step="0.01" class="form-control" id="outstanding_amount" disabled value="">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="form-control" id="allocated_amount" value="" min="0" max="" onkeyup="SalesPayment.changeAllocate(this)">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="SalesPayment.removeRow(this)">
                                                <i class="bx bx-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                    </div>

                    <div class="text-end mt-4">
                        <h5>Grand Total Pembayaran: <span id="grand-total">{{ $data->total_amount ?? 0 }}</span></h5>
                    </div>

                </form>

                @include('web.general_ledger.list_general_ledger')

            </div>
        </div>

        <div class="text-end mt-3">
            @php
                $disabled = '';
            @endphp
            @if (isset($id))
                @if ($data->status == 'PENDING')
                    <button type="button" onclick="SalesPayment.posted(this, event)"
                            class="btn btn-primary waves-effect waves-light me-1">
                        Confirm
                    </button>
                @else
                    @php
                        $disabled = 'disabled'
                    @endphp
                @endif
            @endif
            <button {{ $disabled }} type="submit" onclick="SalesPayment.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                Submit
            </button>

            <button type="reset" onclick="SalesPayment.back(this, event)"
                    class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>
    </div>
</div>

<style>
    .freegood { background-color:#f5f7ff }
</style>

