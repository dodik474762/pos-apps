<button type="button" id="btn-show-modal" style="display: none;" data-bs-toggle="modal"
    data-bs-target="#data-modal-product"></button>

<div id="content-modal-form"></div>

<input type="hidden" id="id" value="{{ $data->id ?? '' }}">
<input type="hidden" id="url" value="{{ isset($data) ? route('credit-note-edit') : route('credit-note-add') }}">

<!-- Start Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                {{ isset($data) ? 'Edit Credit Note' : 'Create Credit Note' }}
            </h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Credit</a></li>
                    <li class="breadcrumb-item active">Note</li>
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

                <form onsubmit="CreditNote.submit(this, event)">

                    <input type="hidden" id="id" value="{{ $data->id ?? '' }}">
                    <input type="hidden" id="url"
                        value="{{ isset($data) ? route('credit-note-edit') : route('credit-note-add') }}">

                    <div class="row">

                        {{-- LEFT --}}
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">Credit No</label>
                                <input type="text" id="credit_note_number" class="form-control"
                                    value="{{ $data->credit_note_number ?? 'AUTO' }}" readonly>
                            </div>

                            {{-- Return Date --}}
                            <div class="mb-3">
                                <label class="form-label">Credit Date</label>
                                <input type="date" id="credit_note_date" class="form-control required"
                                    error="Credit Note Date"
                                    value="{{ $data->credit_note_date ?? date('Y-m-d') }}">
                            </div>

                            {{-- Customer --}}
                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-primary"
                                        onclick="CreditNote.showModalCustomer(this)">Pilih</button>

                                    <input disabled type="text" id="customer_id" class="form-control required"
                                        value="{{ isset($data->customer_id) ? $data->customer_id . ' // ' . $data->nama_customer : '' }}"
                                        data_id="{{ $data->customer_id ?? '' }}">
                                </div>
                            </div>

                            {{-- Invoice (optional) --}}
                            <div class="mb-3">
                                <label class="form-label">Invoice</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-primary"
                                        onclick="CreditNote.showModalInvoice(this)">
                                        Pilih
                                    </button>
                                    <input disabled type="text" id="invoice_id" class="form-control required"
                                        error="Invoice" value="{{ $data->invoice_number ?? '' }}"
                                        data_id="{{ $data->invoice_id ?? '' }}">
                                </div>
                            </div>
                        </div>


                        {{-- RIGHT --}}
                        <div class="col-lg-6">

                            {{-- Return Type --}}
                            <div class="mb-3">
                                <label class="form-label">Note Type</label>
                                <select id="note_type" class="form-control select2 required">
                                    <option value=""></option>
                                    <option value="PRICE_CORRECTION" {{ isset($data->note_type) && $data->note_type == 'PRICE_CORRECTION' ? 'selected' : '' }}>PRICE_CORRECTION</option>
                                    <option value="DISCOUNT" {{ isset($data->note_type) && $data->note_type == 'DISCOUNT' ? 'selected' : '' }}>DISCOUNT</option>
                                    <option value="REBATE" {{ isset($data->note_type) && $data->note_type == 'REBATE' ? 'selected' : '' }}>REBATE</option>
                                    <option value="OTHER" {{ isset($data->note_type) && $data->note_type == 'OTHER' ? 'selected' : '' }}>OTHER</option>
                                </select>
                            </div>

                            {{-- Refund amount --}}
                            <div class="mb-3">
                                <label class="form-label">Total Amount</label>
                                <input disabled type="number" step="0.01" id="total_amount" class="form-control"
                                    value="{{ $data->total_amount ?? 0 }}">
                            </div>

                            {{-- Deposit amount --}}
                            <div class="mb-3">
                                <label class="form-label">Discount Amount</label>
                                <input disabled type="number" step="0.01" id="discount_amount" class="form-control"
                                    value="{{ $data->discount_amount ?? 0 }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tax Amount</label>
                                <input disabled type="number" step="0.01" id="tax_amount" class="form-control"
                                    value="{{ $data->tax_amount ?? 0 }}">
                            </div>

                            {{-- Reason --}}
                            <div class="mb-3">
                                <label class="form-label">Reason</label>
                                <textarea id="credit_reason" class="form-control">{{ $data->credit_reason ?? '' }}</textarea>
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
                                    <th style="width: 15%">Qty Affected</th>
                                    <th style="width: 10%">Unit Price</th>
                                    <th style="width: 15%">Disc</th>
                                    <th style="width: 10%">Tax</th>
                                    <th style="width: 15%">Action</th>
                                </tr>
                            </thead>

                            <tbody id="detail-body">
                                @if(!empty($details))
                                    @foreach($details as $d)
                                        <tr data_id="{{ $d->id }}" invoice_detail_id="{{ $d->invoice_detail_id }}">
                                            <td id="product_id" data_id="{{ $d->product_id }}">
                                                {{ $d->product_code . ' - ' . $d->product_name  }}
                                            </td>

                                            <td>
                                                <input type="number" qty_return_old="{{ $d->qty_affected }}" qty_invoice="{{ $d->qty }}" id="qty_return" step="0.01" class="form-control"
                                                    value="{{ $d->qty_affected }}" max="{{ $d->qty - $d->qty_affected }}"
                                                    onkeyup="CreditNote.changeQtyRetur(this)">
                                            </td>

                                            <td id="unit_price">{{ $d->unit_price }}</td>
                                            <td id="discount_amount" discount_return="{{ $d->discount_return }}">{{ $d->discount }}</td>
                                            <td id="tax" type_tax="{{ $d->type_tax }}" data_id="{{ $d->tax }}"
                                                tax_rate="{{ $d->tax_rate }}" tax_amount="{{ $d->tax_amount }}">{{ $d->tax_amount_invoice }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="CreditNote.removeRow(this)">
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
                                        <td id="tax"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="CreditNote.removeRow(this)">
                                                <i class="bx bx-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-4">
                        <h5>Total Return: <span id="total-return">{{ $data->total_credit ?? 0 }}</span></h5>
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
                @if ($data->status == 'DRAFT')
                    <button type="button" onclick="CreditNote.posted(this, event)"
                        class="btn btn-primary waves-effect waves-light me-1">
                        Confirm
                    </button>
                @else
                    @php
                        $disabled = 'disabled'
                    @endphp
                @endif
            @endif
            <button {{ $disabled }} type="submit" onclick="CreditNote.submit(this, event)"
                class="btn btn-success waves-effect waves-light me-1">
                Submit
            </button>

            <button type="reset" onclick="CreditNote.back(this, event)" class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>
    </div>
</div>

<style>
    .freegood {
        background-color: #f5f7ff
    }
</style>
