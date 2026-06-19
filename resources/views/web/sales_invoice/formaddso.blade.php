<button type="button" id="btn-show-modal" style="display: none;" data-bs-toggle="modal"
    data-bs-target="#data-modal-product"></button>

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
                                    error="Invoice Date" value="{{ $data->invoice_date ?? date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Sales Order</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-primary"
                                        onclick="SalesInvoice.showModalSO(this)">
                                        Pilih
                                    </button>
                                    <input disabled type="text" id="so_number" class="form-control required"
                                        error="SO Number" value="{{ $data->so_number ?? '' }}"
                                        data_id="{{ $data->sales_order ?? '' }}">
                                </div>
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <input disabled type="text" id="customer_id" class="form-control required"
                                    error="Customer"
                                    value="{{ isset($data->nama_customer) ? $data->customer_id . '//' . $data->nama_customer : '' }}"
                                    data_id="{{ $data->customer_id ?? '' }}">
                            </div>

                            <div class="mb-3 d-none">
                                <label class="form-label">Tax</label>
                                <select id="tax" class="form-control select2">
                                    <option value=""></option>
                                    @foreach ($taxes as $item)
                                        <option rate="{{ $item->rate }}" value="{{ $item->id }}"
                                            {{ isset($data->tax_id) ? ($data->tax_id == $item->id ? 'selected' : '') : '' }}>
                                            {{ $item->tax_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Packing List</label>
                                <input type="checkbox" id="is_packing" class=""
                                    {{ isset($data->is_packing) ? ($data->is_packing == 1 ? 'checked' : '') : 'checked' }}>
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
                                    <th style="width: 10%">Discount</th>
                                    <th style="width: 10%">Tax</th>
                                    <th style="width: 15%">Subtotal</th>
                                    <th style="width: 5%"></th>
                                </tr>
                            </thead>

                            <tbody id="detail-body">

                                @if (!empty($details))
                                    @foreach ($details as $item)
                                        <tr class="input" data_id="{{ $item->id }}"
                                            so_detail_id="{{ $item->so_detail_id }}" data-price="{{ $item->price }}"
                                            data-disc="{{ $item->discount }}" data-tax-rate="{{ $item->tax_rate }}">
                                            <td id="product" data_id="{{ $item->product_id }}">
                                                {{ $item->product_code }} - {{ $item->product_name }}</td>
                                            <td id="qty" style="min-width:90px">
                                                <input type="number" id="qty"
                                                    class="form-control form-control-sm" step="any" min="0"
                                                    value="{{ $item->qty }}"
                                                    data-original-qty="{{ $item->qty }}"
                                                    oninput="SalesInvoice.recalcExistingRow(this)">
                                            </td>
                                            <td id="price">{{ $item->price }}</td>
                                            <td id="discount">{{ $item->discount }}</td>
                                            <td id="tax" data_id="{{ $item->tax }}"
                                                type_tax="{{ $item->type_tax }}" rate="{{ $item->tax_rate }}">
                                                {{ $item->tax_amount }}</td>
                                            <td id="subtotal">{{ $item->subtotal }}</td>

                                            <td class="text-center">
                                                @if ($item->flag_cancel == '1')
                                                    <i>Dibatalkan oleh Driver, Saat Kirim</i>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="SalesInvoice.removeRow(this)">
                                                        <i class="bx bx-trash-alt"></i>
                                                    </button>
                                                @endif
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
                                        <td id="tax"></td>
                                        <td id="subtotal"></td>
                                        <td class="text-center">
                                        </td>
                                    </tr>
                                @endif

                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-4">
                        <h5>Grand Total: <span
                                id="grand-total">{{ isset($data->subtotal) ? $data->subtotal : 0 }}</span></h5>
                    </div>

                </form>

                @include('web.general_ledger.list_general_ledger')

            </div>
        </div>

        <div class="text-end">
            @if (isset($id))
                @if ($data->status == 'DRAFT')
                    <button type="button" onclick="SalesInvoice.posted(this, event)"
                        class="btn btn-primary waves-effect waves-light me-1">
                        Posted
                    </button>
                    &nbsp;
                    <button type="submit" onclick="SalesInvoice.submit(this, event)"
                        class="btn btn-success waves-effect waves-light me-1">
                        Submit
                    </button>
                @endif
                @if ($data->status != 'PAID' && $data->status != 'PARTIAL-PAID' && $data->status != 'CANCELED')
                    <button type="submit" onclick="SalesInvoice.submit(this, event)"
                        class="btn btn-success waves-effect waves-light me-1">
                        Submit
                    </button>
                @endif
            @endif
            @if (!isset($id))
                <button type="submit" onclick="SalesInvoice.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
            @endif

            <button type="reset" onclick="SalesInvoice.back(this, event)" class="btn btn-secondary waves-effect">
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
