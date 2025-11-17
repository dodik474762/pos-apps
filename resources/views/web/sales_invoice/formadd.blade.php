<button type="button" id="btn-show-modal" style="display: none;"
        data-bs-toggle="modal" data-bs-target="#data-modal-product"></button>

<div id="content-modal-form"></div>

<input type="hidden" id="id" value="{{ $data->id ?? '' }}">
<input type="hidden" id="url"
       value="{{ isset($data) ? route('sales-invoice-edit') : route('sales-invoice-add') }}">

<!-- Start Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                {{ isset($data) ? 'Edit Sales Invoice' : 'Create Sales Invoice' }}
            </h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Sales</a></li>
                    <li class="breadcrumb-item active">Invoice</li>
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

                <form onsubmit="SalesInvoice.submit(this, event)">

                    <div class="row">

                        <!-- LEFT -->
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">Invoice Number</label>
                                <input type="text" id="invoice_number" class="form-control"
                                       value="{{ $data->invoice_number ?? 'AUTO' }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Invoice Date</label>
                                <input type="date" id="invoice_date" class="form-control required"
                                       error="Invoice Date"
                                       value="{{ $data->invoice_date ?? date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Delivery Order</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-primary"
                                            onclick="SalesInvoice.showModalDO(this)">
                                        Pilih
                                    </button>
                                    <input disabled type="text" id="do_number" class="form-control required"
                                           error="DO Number"
                                           value="{{ $data->do_number ?? '' }}"
                                           data_id="{{ $data->do_id ?? '' }}">
                                </div>
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <input disabled type="text" id="customer_id" class="form-control required"
                                       error="Customer"
                                       value="{{ $data->customer_name ?? '' }}"
                                       data_id="{{ $data->customer_id ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tax</label>
                                <select id="tax" class="form-control select2">
                                    <option value=""></option>
                                    @foreach ($taxes as $item)
                                        <option rate="{{ $item->rate }}" value="{{ $item->id }}" {{ isset($data->tax) ? $data->tax == $item->id ? 'selected' : ''  : ''}}>{{ $item->tax_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>

                    <hr>

                    {{-- ================= DETAIL ITEMS ================= --}}
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-items">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%">Product</th>
                                    <th style="width: 10%">Qty</th>
                                    <th style="width: 15%">Price</th>
                                    <th style="width: 15%">Discount</th>
                                    <th style="width: 20%">Subtotal</th>
                                    <th style="width: 5%"></th>
                                </tr>
                            </thead>

                            <tbody id="detail-body">

                                @if (!empty($details))
                                    @foreach ($details as $d)
                                        <tr class="input" data_id="{{ $d->id }}">
                                            <td>
                                                <div class="input-group">
                                                    <button type="button" class="btn btn-outline-primary"
                                                            onclick="SalesInvoice.showDataProduct(this)">
                                                        Pilih
                                                    </button>
                                                    <input disabled type="text" class="form-control required"
                                                           id="product" error="Product"
                                                           value="{{ $d->product_name }}"
                                                           data_id="{{ $d->product_id }}">
                                                </div>
                                            </td>

                                            <td>
                                                <input type="number" class="form-control qty"
                                                       id="qty" min="1"
                                                       value="{{ $d->qty }}"
                                                       onkeyup="SalesInvoice.calcRow(this)">
                                            </td>

                                            <td>
                                                <input type="number" class="form-control price"
                                                       id="price" step="0.01"
                                                       value="{{ $d->price }}"
                                                       onkeyup="SalesInvoice.calcRow(this)">
                                            </td>

                                            <td>
                                                <input type="number" class="form-control discount"
                                                       id="discount" step="0.01"
                                                       value="{{ $d->discount }}"
                                                       onkeyup="SalesInvoice.calcRow(this)">
                                            </td>

                                            <td>
                                                <input type="number" class="form-control subtotal"
                                                       id="subtotal" step="0.01"
                                                       value="{{ $d->subtotal }}" readonly>
                                            </td>

                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="SalesInvoice.removeRow(this)">
                                                    <i class="bx bx-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach

                                @else
                                    {{-- DEFAULT EMPTY ROW --}}
                                    <tr class="input" data_id="">
                                        <td id="product" data_id=""></td>
                                        <td id="qty"></td>
                                        <td id="price"></td>
                                        <td id="discount"></td>
                                        <td id="subtotal"></td>

                                        <td class="text-center">
                                        </td>
                                    </tr>
                                @endif

                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-4">
                        <h5>Grand Total: <span id="grand-total">{{ $data->subtotal ?? 0 }}</span></h5>
                    </div>
                    <hr>

                </form>

            </div>
        </div>

        <div class="text-end">
            <button type="submit" onclick="SalesInvoice.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                Submit
            </button>

            <button type="reset" onclick="SalesInvoice.back(this, event)"
                    class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>

    </div>
</div>

<style>
    .freegood { background-color:#f5f7ff }
</style>
