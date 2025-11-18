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
                                <input disabled type="text" id="customer_id" class="form-control required"
                                       error="Customer"
                                       value="{{ isset($data->customer_name) ? $data->customer_id.' // '.$data->customer_name : '' }}"
                                       data_id="{{ $data->customer_id ?? '' }}">
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">Total Amount</label>
                                <input type="number" step="0.01" id="total_amount" class="form-control required"
                                       value="{{ $data->total_amount ?? 0 }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Discount Amount</label>
                                <input type="number" step="0.01" id="discount_amount" class="form-control"
                                       value="{{ $data->discount_amount ?? 0 }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Net Amount</label>
                                <input type="number" step="0.01" id="net_amount" class="form-control" readonly
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
                        <table class="table table-bordered" id="table-details">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50%">Invoice</th>
                                    <th style="width: 30%">Allocated Amount</th>
                                    <th style="width: 20%">Action</th>
                                </tr>
                            </thead>

                            <tbody id="detail-body">
                                @if(!empty($details))
                                    @foreach($details as $i => $item)
                                        <tr data_index="{{ $i }}">
                                            <td>
                                                <input type="text" class="form-control" value="{{ $item->invoice_number }}" readonly>
                                                <input type="hidden" name="details[{{ $i }}][invoice_id]" value="{{ $item->invoice_id }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control"
                                                       name="details[{{ $i }}][allocated_amount]" value="{{ $item->allocated_amount }}">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger btn-remove">
                                                    <i class="bx bx-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr data_index="0">
                                        <td>
                                            <input type="text" class="form-control" readonly>
                                            <input type="hidden" name="details[0][invoice_id]" value="">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="form-control" name="details[0][allocated_amount]">
                                        </td>
                                        <td class="text-center"></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary mt-2" id="add-row">Add Detail</button>
                    </div>

                    <div class="text-end mt-4">
                        <h5>Grand Total: <span id="grand-total">{{ $data->net_amount ?? 0 }}</span></h5>
                    </div>

                </form>

            </div>
        </div>

        <div class="text-end mt-3">
            <button type="submit" onclick="SalesPayment.submit(this, event)"
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

@section('scripts')
<script>
let rowIndex = {{ !empty($details) ? count($details) : 1 }};

// Tambah detail row
document.getElementById('add-row').addEventListener('click', function(){
    let tbody = document.getElementById('detail-body');
    let row = document.createElement('tr');
    row.setAttribute('data_index', rowIndex);
    row.innerHTML = `
        <td>
            <input type="text" class="form-control" readonly>
            <input type="hidden" name="details[${rowIndex}][invoice_id]" value="">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control" name="details[${rowIndex}][allocated_amount]">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger btn-remove">
                <i class="bx bx-trash-alt"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    rowIndex++;
});

// Remove row
document.addEventListener('click', function(e){
    if(e.target.classList.contains('btn-remove') || e.target.closest('.btn-remove')){
        let btn = e.target.closest('.btn-remove');
        btn.closest('tr').remove();
    }
});

// Auto calculate net amount
document.getElementById('total_amount').addEventListener('input', calcNetAmount);
document.getElementById('discount_amount').addEventListener('input', calcNetAmount);

function calcNetAmount() {
    let total = parseFloat(document.getElementById('total_amount').value) || 0;
    let discount = parseFloat(document.getElementById('discount_amount').value) || 0;
    let net = total - discount;
    document.getElementById('net_amount').value = net;
    document.getElementById('grand-total').innerText = net.toFixed(2);
}
</script>
@endsection
