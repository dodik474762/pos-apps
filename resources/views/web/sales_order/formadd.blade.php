<button type="button" id="btn-show-modal" style="display: none;" data-bs-toggle="modal" data-bs-target="#data-modal-product"></button>
<div id="content-modal-form"></div>
<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">
<input type="hidden" id="url" value="{{ isset($id) ? route('sales-order-edit') : route('sales-order-add') }}">

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Create Sales Order</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Sales</a></li>
                    <li class="breadcrumb-item active">Create Sales Order</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form onsubmit="SalesOrder.submit(this, event)">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">SO Number</label>
                                <input type="text" id="so_number" class="form-control required" error="SO Number"
                                    placeholder="Auto Generate" readonly
                                    value="{{ isset($data->so_number) ? $data->so_number : 'AUTO' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">SO Date</label>
                                <input type="date" id="so_date" class="form-control required" error="SO Date"
                                    value="{{ isset($data->so_date) ? $data->so_date : date('Y-m-d') }}">
                            </div>

                             <div class="mb-3">
                                <label class="form-label">Salesman</label>
                                <select class="form-control select2" id="salesman" error="Salesman" onchange="SalesOrder.getCustomer(this)">
                                    <option value=""></option>
                                    @foreach ($salesmen as $s)
                                        <option value="{{ $s->id }}" {{ (isset($data->salesman) && $data->salesman == $s->id ) || (isset($salesman) && $salesman == $s->id) ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <select class="form-control select2 required" id="customer_id" error="Customer" onchange="SalesOrder.changeCustomer(this)">
                                    <option value=""></option>
                                    @foreach ($customers as $customer)
                                        <option top="{{ $customer->top_value }}" value="{{ $customer->id }}"
                                            {{ isset($data->customer_id) && $data->customer_id == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->nama_customer }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Term</label>
                                <input disabled type="text" id="payment_term" class="form-control" placeholder="e.g. 30 days"
                                    value="{{ isset($data->payment_term) ? $data->payment_term : '0' }}">
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Currency</label>
                                <select class="form-control select2" id="currency">
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}"
                                            {{ isset($data->currency) && $data->currency == $currency->id ? 'selected' : '' }}>
                                            {{ $currency->code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea id="remarks" class="form-control" placeholder="Catatan (opsional)">{{ isset($data->remarks) ? $data->remarks : '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Detail Barang -->

                    <ul class="nav nav-tabs" id="diskonTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-barang" data-bs-toggle="tab" data-bs-target="#tab-pane-barang"
                                type="button" role="tab" aria-controls="tab-pane-barang" aria-selected="true">
                                Barang
                            </button>
                        </li>
                        {{-- <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-diskon-free" data-bs-toggle="tab" data-bs-target="#tab-pane-free"
                                type="button" role="tab" aria-controls="tab-pane-free" aria-selected="false">
                                Program Free Good
                            </button>
                        </li> --}}
                    </ul>

                    <div class="tab-content pt-3" id="barangTabContent">
                        <!-- Tab Diskon -->
                        <div class="tab-pane fade show active" id="tab-pane-barang" role="tabpanel" aria-labelledby="tab-barang">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="table-items">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 20%">Produk</th>
                                            <th style="width: 10%">Satuan</th>
                                            <th style="width: 8%">Qty</th>
                                            <th style="width: 12%">Unit Price</th>
                                            <th style="width: 8%">Disc (%)</th>
                                            <th style="width: 8%">Disc (Rp)</th>
                                            <th style="width: 22%">Subtotal</th>
                                            <th style="width: 7%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail-body">
                                        @if (!empty($data_item))
                                            @foreach ($data_item as $item)
                                                <tr class="input {{ $item->is_free_good ? 'freegood' : '' }}" data_id="{{ $item->id }}" {{ $item->is_free_good ? 'data-free-for='.$item->product_id : '' }}>
                                                    <td>
                                                        <div class="input-group">
                                                            <button {{ $item->is_free_good ? 'disabled' : '' }} class="btn btn-outline-primary" type="button"
                                                                onclick="SalesOrder.showDataProduct(this)">{{ $item->is_free_good ? 'Free' : 'Pilih' }}</button>
                                                            <input disabled type="text" id="product" class="form-control required" error="Product"
                                                                value="{{ $item->product_name }}" data_id="{{ $item->product_id }}">
                                                        </div>
                                                    </td>
                                                    <td data_id="{{ $item->unit }}" id="unit">{{ $item->unit_name }}</td>
                                                    <td><input type="number" class="form-control" {{ $item->is_free_good  ? 'disabled' : '' }} id="qty" value="{{ $item->qty }}" min="1" onkeyup="SalesOrder.calcDiscRow(this)"></td>
                                                    <td><input type="number" class="form-control" id="unit_price" disabled data_id="" value="{{ $item->unit_price }}" onkeyup="SalesOrder.calcRow(this)"></td>
                                                    <td><input type="number" class="form-control" id="disc_percent" disabled value="{{ $item->discount_percent }}" onkeyup="SalesOrder.calcRow(this)"></td>
                                                    <td><input type="number" class="form-control" id="disc_amount" disabled value="{{ $item->discount_amount }}" onkeyup="SalesOrder.calcRow(this)"></td>
                                                    <td><input disabled type="text" class="form-control" id="subtotal" value="{{ $item->subtotal }}"></td>
                                                    <td class="text-center">
                                                        <button {{ $item->is_free_good ? 'disabled' : '' }} type="button" class="btn btn-sm btn-danger" onclick="SalesOrder.removeRow(this)"><i class="bx bx-trash-alt"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="input" data_id="">
                                                <td>
                                                    <div class="input-group">
                                                        <button class="btn btn-outline-primary" type="button" onclick="SalesOrder.showDataProduct(this)">Pilih</button>
                                                        <input disabled type="text" class="form-control required" id="product" error="Product" value="">
                                                    </div>
                                                </td>
                                                <td data_id="" id="unit"></td>
                                                <td><input type="number" class="form-control" id="qty" value="1" min="1" onkeyup="SalesOrder.calcDiscRow(this)"></td>
                                                <td><input type="number" class="form-control" id="unit_price" data_id="" disabled value="0" onkeyup="SalesOrder.calcRow(this)"></td>
                                                <td><input type="number" class="form-control" id="disc_percent" disabled value="0" onkeyup="SalesOrder.calcRow(this)"></td>
                                                <td><input type="number" class="form-control" id="disc_amount" disabled value="0" onkeyup="SalesOrder.calcRow(this)"></td>
                                                <td><input disabled type="text" class="form-control" id="subtotal" value="0"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="SalesOrder.removeRow(this)"><i class="bx bx-trash-alt"></i></button>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                    <button type="button" class="btn btn-sm btn-primary mt-2" onclick="SalesOrder.addRow()">+ Tambah Barang</button>

                    <div class="text-end mt-4">
                        <h5>Total: <span id="total-harga">{{ isset($data->total_amount) ? $data->total_amount : 0 }}</span></h5>
                    </div>

                   <hr>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="diskonTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-diskon-umum" data-bs-toggle="tab" data-bs-target="#tab-pane-diskon"
                                type="button" role="tab" aria-controls="tab-pane-diskon" aria-selected="true">
                                Program Diskon
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-diskon-free" data-bs-toggle="tab" data-bs-target="#tab-pane-free"
                                type="button" role="tab" aria-controls="tab-pane-free" aria-selected="false">
                                Program Free Good
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-uom" data-bs-toggle="tab" data-bs-target="#tab-pane-uom"
                                type="button" role="tab" aria-controls="tab-pane-uom" aria-selected="false">
                                Unit of Measurement
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content pt-3" id="diskonTabContent">
                        <!-- Tab Diskon -->
                        <div class="tab-pane fade show active" id="tab-pane-diskon" role="tabpanel" aria-labelledby="tab-diskon-umum">
                            <div class="table-responsive">
                                <table id="table-data-diskon" class="table table-striped table-bordered dt-responsive nowrap"
                                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Satuan</th>
                                            <th>Kategori Customer</th>
                                            <th>Min Qty</th>
                                            <th>Max Qty</th>
                                            <th>Disc Tipe</th>
                                            <th>Disc Nilai</th>
                                            <th>Tanggal Berlaku</th>
                                            <th>Customer</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- data dinamis diisi via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Free Good -->
                        <div class="tab-pane fade" id="tab-pane-free" role="tabpanel" aria-labelledby="tab-diskon-free">
                            <div class="table-responsive">
                                <table id="table-data-diskon-free" class="table table-striped table-bordered dt-responsive nowrap"
                                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Satuan</th>
                                            <th>Kategori Customer</th>
                                            <th>Min Qty</th>
                                            <th>Max Qty</th>
                                            <th>Free Product</th>
                                            <th>Free Unit</th>
                                            <th>Free Qty</th>
                                            <th>Customer</th>
                                            <th>Tanggal Berlaku</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- data dinamis diisi via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-pane-uom" role="tabpanel" aria-labelledby="tab-uom">
                            <div class="table-responsive">
                                <table id="table-data-uom" class="table table-striped table-bordered dt-responsive nowrap"
                                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Satuan</th>
                                            <th>Qty Terkecil</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- data dinamis diisi via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-end">
            @if (isset($id))
                @if ($data->status == 'draft')
                    <button type="submit" onclick="SalesOrder.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">
                        Submit
                    </button>
                @endif
            @else
                <button type="submit" onclick="SalesOrder.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
            @endif
            <button type="reset" onclick="SalesOrder.back(this, event)" class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>
    </div>
</div>
<!-- end row -->


<style>
    .freegood {
        background-color:#f5f7ff
    }
</style>
