<button type="button" id="btn-show-modal" style="display: none;"
        data-bs-toggle="modal" data-bs-target="#data-modal-product"></button>

<div id="content-modal-form"></div>

<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">
<input type="hidden" id="url"
       value="{{ isset($id) ? route('delivery-order-edit') : route('delivery-order-add') }}">

<!-- Start Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                {{ isset($id) ? 'Edit Delivery Order' : 'Create Delivery Order' }}
            </h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Sales</a></li>
                    <li class="breadcrumb-item active">Delivery Order</li>
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

                <form onsubmit="DeliveryOrder.submit(this, event)">

                    <div class="row">

                        <!-- Kiri -->
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">DO Number</label>
                                <input type="text" id="do_number" class="form-control"
                                       value="{{ $data->do_number ?? 'AUTO' }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">DO Date</label>
                                <input type="date" id="do_date" class="form-control required" error="DO Date"
                                       value="{{ $data->do_date ?? date('Y-m-d') }}">
                            </div>

                             <div class="mb-3">
                                <label class="form-label">Sales Order</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-primary"
                                            onclick="DeliveryOrder.showModalSO(this)">
                                        Pilih
                                    </button>
                                    <input disabled type="text" id="so_number" class="form-control required"
                                        value="{{ $data->so_number ?? '' }}"
                                        data_id="{{ $data->so_id ?? '' }}"
                                        error="Sales Order">
                                </div>
                            </div>

                        </div>

                        <!-- Kanan -->
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <input type="text" disabled id="customer_id" class="form-control required" error="Customer"
                                       value="{{ $data->customer_id ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Warehouse</label>
                                <select class="form-control select2 required" id="warehouse_id" error="Warehouse">
                                    <option value=""></option>
                                    @foreach ($warehouses as $w)
                                        <option value="{{ $w->id }}"
                                            {{ isset($data->warehouse_id) && $data->warehouse_id == $w->id ? 'selected' : '' }}>
                                            {{ $w->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>

                    <hr>

                    <!-- Tabel Produk -->
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-items">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%">Product</th>
                                    <th style="width: 15%">UOM</th>
                                    <th style="width: 10%">Qty</th>
                                    <th style="width: 40%">Note</th>
                                    <th style="width: 5%"></th>
                                </tr>
                            </thead>
                            <tbody id="detail-body">

                                @if (!empty($details))
                                    @foreach ($details as $d)
                                        <tr class="input" data_id="{{ $d->id }}" so_detail_id="{{ $d->so_detail_id }}">

                                            <td>
                                                <div class="input-group">
                                                    <button type="button" class="btn btn-outline-primary"
                                                            onclick="DeliveryOrder.showDataProduct(this)">
                                                        Pilih
                                                    </button>
                                                    <input disabled type="text" class="form-control required"
                                                           id="product" error="Product"
                                                           value="{{ $d->product_name }}"
                                                           data_id="{{ $d->product_id }}">
                                                </div>
                                            </td>

                                            <td id="uom" data_id="{{ $d->uom }}">{{ $d->uom }}</td>

                                            <td>
                                                <input type="number" min="1" id="qty" class="form-control"
                                                       value="{{ $d->qty }}"
                                                       onkeyup="DeliveryOrder.calcRow(this)">
                                            </td>

                                            <td>
                                                <input type="text" id="note" class="form-control"
                                                       value="{{ $d->note }}">
                                            </td>

                                            <td class="text-center">
                                                {{-- <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="DeliveryOrder.removeRow(this)">
                                                    <i class="bx bx-trash-alt"></i>
                                                </button> --}}
                                            </td>

                                        </tr>
                                    @endforeach
                                @else

                                    <tr class="input" data_id="" so_detail_id="">
                                        <td id="product" data_id=""></td>
                                        <td id="uom" data_id=""></td>
                                        <td id="qty"></td>
                                        <td>
                                            <input type="text" id="note" class="form-control">
                                        </td>
                                        <td class="text-center">
                                            {{-- <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="DeliveryOrder.removeRow(this)">
                                                <i class="bx bx-trash-alt"></i>
                                            </button> --}}
                                        </td>
                                    </tr>

                                @endif

                            </tbody>
                        </table>
                    </div>

                    {{-- <button type="button" class="btn btn-sm btn-primary mt-2"
                            onclick="DeliveryOrder.addRow()">
                        + Tambah Barang
                    </button> --}}

                    <div class="text-end mt-4">
                        {{-- <h5>Total Qty: <span id="total-qty">{{ $data->total_qty ?? 0 }}</span></h5> --}}
                    </div>

                    <hr>

                </form>

            </div>
        </div>

        <div class="text-end">
            <button type="submit" onclick="DeliveryOrder.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                Submit
            </button>
            <button type="reset" onclick="DeliveryOrder.back(this, event)"
                    class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>

    </div>
</div>

<style>
    .freegood { background-color:#f5f7ff }
</style>
