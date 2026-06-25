<button type="button" id="btn-show-modal" style="display: none;" data-bs-toggle="modal"
    data-bs-target="#data-modal-product"></button>

<div id="content-modal-form"></div>

<input type="hidden" id="id" value="{{ $data->id ?? '' }}">
<input type="hidden" id="url" value="{{ route('sales-payment-add-all') }}">

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
                                    error="Payment Date" value="{{ $data->payment_date ?? date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select id="payment_method" class="form-control select2" required
                                    onchange="SalesPayment.changePaymentMethod(this)">
                                    <option value=""></option>
                                    @foreach (['CASH', 'GIRO', 'TRANSFER'] as $method)
                                        <option value="{{ $method }}"
                                            {{ isset($payment_method) && $payment_method == $method ? 'selected' : '' }}>
                                            {{ $method }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Customer (Biarkan kosong untuk ALL)</label>
                                <select class="form-control select2" multiple="multiple" id="customer_id">
                                    @foreach ($data_customer as $item)
                                        <option value="{{ $item['id'] }}">
                                            {{ $item['customer_code'] }} - {{ $item['nama_customer'] }} -
                                            {{ $item['payment_terms'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Account Kas / Bank</label>
                                <select id="account_id" class="form-control select2 required">
                                    <option value="">-- Pilih Akun --</option>
                                    @foreach ($cashBankAccounts as $acc)
                                        <option value="{{ $acc->id }}"
                                            {{ isset($data->coa_kas) ? ($data->coa_kas == $acc->id ? 'selected' : '') : '' }}>
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
                                <input disabled type="number" step="0.01" id="total_amount"
                                    class="form-control required" value="{{ $data->total_amount ?? 0 }}">
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

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Start Date (Invoice)</label>
                            <input type="date" id="filter_start_date" class="form-control"
                                value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date (Invoice)</label>
                            <input type="date" id="filter_end_date" class="form-control"
                                value="{{ date('Y-m-t') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Packing List No</label>
                            <select id="filter_packing_list_no" class="form-control select2">
                                <option value="">-- Pilih Packing List No --</option>
                                @foreach ($packing_list as $pln)
                                    <option value="{{ $pln->id }}">{{ $pln->packing_list_no }} -
                                        {{ $pln->vehicle_no }} - {{ $pln->driver_name }} / {{ $pln->packing_date }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Rute Sales</label>
                            <input type="date" id="filter_date_rute_sales" class="form-control" value="">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-primary" onclick="SalesPayment.filterBulk()">
                                <i class="bx bx-filter-alt"></i> Filter Outstanding
                            </button>
                        </div>
                    </div>

                    {{-- ================= DETAIL ITEMS ================= --}}
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-items">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 20%">Customer</th>
                                    <th style="width: 15%">Invoice</th>
                                    <th style="width: 15%">Tanggal Invoice</th>
                                    <th style="width: 10%">Jatuh Tempo</th>
                                    <th style="width: 5%">TOP</th>
                                    <th style="width: 10%">Outstanding Amount</th>
                                    <th style="width: 20%">Allocated Amount</th>
                                    <th style="width: 5%">
                                        <input type="checkbox" class="form-check-input" id="select_all"
                                            onchange="SalesPayment.selectAll(this)">
                                    </th>
                                </tr>
                            </thead>

                            <tbody id="detail-body">
                                @if (!empty($details))
                                    @foreach ($details as $i => $item)
                                        <tr data_id="{{ $item->id }}">
                                            <td id="invoice_id" data_id="{{ $item->invoice_id }}"
                                                subtotal="{{ $item->subtotal + $item->discount_amount }}"
                                                discount_amount="{{ $item->discount_amount }}">
                                                {{ $item->invoice_number }}</td>
                                            <td id="date_invoice">{{ $item->invoice_date }}</td>
                                            <td>{{ $item->due_date }}</td>
                                            <td>{{ $item->top_name }}</td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control"
                                                    id="outstanding_amount" disabled
                                                    value="{{ $item->outstanding_amount }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control"
                                                    allocated_amount_old="{{ $item->allocated_amount }}"
                                                    id="allocated_amount" value="{{ $item->allocated_amount }}"
                                                    min="0" max="{{ $item->outstanding_amount }}"
                                                    onkeyup="SalesPayment.changeAllocate(this)">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="SalesPayment.removeRow(this)">
                                                    <i class="bx bx-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr data_id="">
                                        <td></td>
                                        <td id="invoice_id" data_id="" subtotal="" discount_amount=""></td>
                                        <td id="date_invoice"></td>
                                        <td id="due_date"></td>
                                        <td id="top_name"></td>
                                        <td>
                                            <input type="number" step="0.01" class="form-control"
                                                id="outstanding_amount" disabled value="">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="form-control"
                                                id="allocated_amount" value="" min="0" max=""
                                                onkeyup="SalesPayment.changeAllocate(this)">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="SalesPayment.removeRow(this)">
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
                        $disabled = 'disabled';
                    @endphp
                @endif
            @endif
            <button {{ $disabled }} type="submit" onclick="SalesPayment.submitBulk(this, event)"
                class="btn btn-success waves-effect waves-light me-1">
                Submit
            </button>

            <button type="reset" onclick="SalesPayment.back(this, event)" class="btn btn-secondary waves-effect">
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
