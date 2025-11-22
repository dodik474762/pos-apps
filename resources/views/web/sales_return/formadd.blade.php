<button type="button" id="btn-show-modal" style="display: none;"
        data-bs-toggle="modal" data-bs-target="#data-modal-product"></button>

<div id="content-modal-form"></div>

<input type="hidden" id="id" value="{{ $data->id ?? '' }}">
<input type="hidden" id="url"
       value="{{ isset($data) ? route('sales-return-edit') : route('sales-return-add') }}">

<!-- Start Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                {{ isset($data) ? 'Edit Sales Return' : 'Create Sales Return' }}
            </h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Sales</a></li>
                    <li class="breadcrumb-item active">Return</li>
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

            <form onsubmit="SalesReturn.submit(this, event)">

                <input type="hidden" id="id" value="{{ $data->id ?? '' }}">
                <input type="hidden" id="url"
                    value="{{ isset($data) ? route('sales-return-edit') : route('sales-return-add') }}">

                <div class="row">

                    {{-- LEFT --}}
                    <div class="col-lg-6">

                        {{-- Return Number --}}
                        <div class="mb-3">
                            <label class="form-label">Return No</label>
                            <input type="text" id="return_number" class="form-control"
                                value="{{ $data->return_number ?? 'AUTO' }}" readonly>
                        </div>

                        {{-- Return Date --}}
                        <div class="mb-3">
                            <label class="form-label">Return Date</label>
                            <input type="date" id="return_date" class="form-control required"
                                value="{{ $data->return_date ?? date('Y-m-d') }}">
                        </div>

                        {{-- Customer --}}
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-primary"
                                        onclick="SalesReturn.showModalCustomer(this)">Pilih</button>

                                <input disabled type="text" id="customer_id" class="form-control required"
                                    value="{{ isset($data->customer_id) ? $data->customer_id.' // '.$data->nama_customer : '' }}"
                                    data_id="{{ $data->customer_id ?? '' }}">
                            </div>
                        </div>

                        {{-- Invoice (optional) --}}
                        <div class="mb-3">
                            <label class="form-label">Invoice</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-primary"
                                        onclick="SalesReturn.showModalInvoice(this)">
                                    Pilih
                                </button>
                                <input disabled type="text" id="invoice_id" class="form-control required" error="Invoice"
                                    value="{{ $data->invoice_number ?? '' }}"
                                    data_id="{{ $data->invoice_id ?? '' }}">
                            </div>
                        </div>
                    </div>


                    {{-- RIGHT --}}
                    <div class="col-lg-6">

                        {{-- Return Type --}}
                        <div class="mb-3">
                            <label class="form-label">Return Type</label>
                            <select id="return_type" class="form-control select2 required">
                                <option value=""></option>
                                <option value="REFUND"     {{ isset($data->return_type) && $data->return_type=='REFUND' ? 'selected' : '' }}>REFUND</option>
                                <option value="DEPOSIT"    {{ isset($data->return_type) && $data->return_type=='DEPOSIT' ? 'selected' : '' }}>DEPOSIT</option>
                                <option value="CORRECTION" {{ isset($data->return_type) && $data->return_type=='CORRECTION' ? 'selected' : '' }}>CORRECTION</option>
                            </select>
                        </div>

                        {{-- Refund amount --}}
                        <div class="mb-3">
                            <label class="form-label">Refund Amount</label>
                            <input disabled type="number" step="0.01" id="refund_amount" class="form-control"
                                value="{{ $data->refund_amount ?? 0 }}">
                        </div>

                        {{-- Deposit amount --}}
                        <div class="mb-3">
                            <label class="form-label">Deposit Amount</label>
                            <input disabled type="number" step="0.01" id="deposit_amount" class="form-control"
                                value="{{ $data->deposit_amount ?? 0 }}">
                        </div>

                        {{-- Reason --}}
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea id="reason" class="form-control">{{ $data->reason ?? '' }}</textarea>
                        </div>

                    </div>
                </div>


                <hr>

                {{-- ================= ITEMS ================= --}}
                <div class="table-responsive">
                    <table class="table table-bordered" id="table-items">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30%">Product</th>
                                <th style="width: 15%">Qty Return</th>
                                <th style="width: 20%">Unit Price</th>
                                <th style="width: 15%">Disc</th>
                                <th style="width: 15%">Action</th>
                            </tr>
                        </thead>

                        <tbody id="detail-body">
                            @if(!empty($details))
                                @foreach($details as $d)
                                    <tr data_id="{{ $d->id }}">
                                        <td id="product_id" data_id="{{ $d->product_id }}">
                                            {{ $d->product_name ?? '' }}
                                        </td>

                                        <td>
                                            <input type="number" id="qty_return" step="0.01" class="form-control"
                                                value="{{ $d->qty_return }}"
                                                onkeyup="SalesReturn.updateLine(this)">
                                        </td>

                                        <td>
                                            <input type="number" id="unit_price" step="0.01" class="form-control"
                                                value="{{ $d->unit_price }}"
                                                onkeyup="SalesReturn.updateLine(this)">
                                        </td>

                                        <td>
                                            <input type="number" id="discount_amount" step="0.01" class="form-control"
                                                value="{{ $d->discount_amount }}"
                                                onkeyup="SalesReturn.updateLine(this)">
                                        </td>

                                        <td>
                                            <input type="number" id="tax_amount" step="0.01" class="form-control"
                                                value="{{ $d->tax_amount }}"
                                                onkeyup="SalesReturn.updateLine(this)">
                                        </td>

                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="SalesReturn.removeRow(this)">
                                                <i class="bx bx-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                {{-- Row kosong pertama --}}
                                <tr data_id="">
                                    <td id="product_id" data_id=""></td>
                                    <td><input type="number" id="qty_return" class="form-control"></td>
                                    <td id="unit_price"></td>
                                    <td id="discount_amount"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="SalesReturn.removeRow(this)">
                                            <i class="bx bx-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <h5>Total Return: <span id="total-return">{{ $data->total_return_value ?? 0 }}</span></h5>
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
                    <button type="button" onclick="SalesReturn.posted(this, event)"
                            class="btn btn-primary waves-effect waves-light me-1">
                        Confirm
                    </button>
                @else
                    @php
                        $disabled = 'disabled'
                    @endphp
                @endif
            @endif
            <button {{ $disabled }} type="submit" onclick="SalesReturn.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                Submit
            </button>

            <button type="reset" onclick="SalesReturn.back(this, event)"
                    class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>
    </div>
</div>

<style>
    .freegood { background-color:#f5f7ff }
</style>

