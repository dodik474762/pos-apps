<button type="button" id="btn-show-modal" style="display: none;"
        data-bs-toggle="modal" data-bs-target="#data-modal-product"></button>

<div id="content-modal-form"></div>

<input type="hidden" id="id" value="{{ $data->packing_list_id ?? '' }}">
<input type="hidden" id="url"
       value="{{ isset($data) ? route('packing-list-edit') : route('packing-list-add') }}">

<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                {{ isset($data) ? 'Edit Packing List' : 'Create Packing List' }}
            </h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="#">Transaction</a></li>
                    <li class="breadcrumb-item active">Packing List</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">

                <form onsubmit="PackingList.submit(this, event)">

                    <div class="row">

                        <!-- LEFT SIDE -->
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">Packing List No</label>
                                <input type="text" id="packing_list_no"
                                       class="form-control"
                                       value="{{ $data->packing_list_no ?? 'AUTO' }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Packing Date</label>
                                <input type="date" id="packing_date" class="form-control required"
                                       value="{{ $data->packing_date ?? date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vehicle No</label>
                                <input type="text" id="vehicle_no" class="form-control"
                                       value="{{ $data->vehicle_no ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Driver Name</label>
                                <input type="text" id="driver_name" class="form-control"
                                       value="{{ $data->driver_name ?? '' }}">
                            </div>

                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">Expedition Name</label>
                                <input type="text" id="expedition_name" class="form-control"
                                       value="{{ $data->expedition_name ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea id="remarks" class="form-control">{{ $data->remarks ?? '' }}</textarea>
                            </div>

                        </div>

                    </div>

                    <hr>

                    {{-- ==================== DO LIST =========================== --}}
                    <div class="d-flex justify-content-between">
                        <h5>Delivery Orders</h5>
                        <button type="button" class="btn btn-primary btn-sm"
                                onclick="PackingList.showModalDO()">Tambah DO</button>
                    </div>

                    <div class="table-responsive mt-2">
                        <table class="table table-bordered" id="table-do">
                            <thead class="table-light">
                                <tr>
                                    <th>DO Number</th>
                                    <th>DO Date</th>
                                    <th>Customer</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody id="do-body">
                                @if(!empty($details))
                                    @foreach($details as $row)
                                        <tr data_id="{{ $row->id }}">
                                            <td id="do_number" data_id="{{ $row->delivery_order_id }}">{{ $row->do_number }}</td>
                                            <td id="do_date">{{ $row->do_date }}</td>
                                            <td id="do_customer" data_id="{{ $row->customer_id }}">{{ $row->customer_code }} - {{ $row->nama_customer }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="PackingList.removeRow(this)">
                                                    <i class="bx bx-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    {{-- ==================== ITEM DETAIL =========================== --}}
                    <h5>Item Details</h5>

                    <div class="table-responsive mt-2">
                        <table class="table table-bordered" id="table-items">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Qty DO</th>
                                    <th>Qty Packed</th>
                                    <th>Satuan</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>

                            <tbody id="detail-body">
                                @if(!empty($details))
                                    @foreach ($grouped as $item)
                                        @foreach ($item as $items)
                                            @foreach ($items as $prod)
                                                <tr class="do_detail_{{ $prod->delivery_order_id }}" do_id="{{ $prod->delivery_order_id }}" data_id="{{ $prod->delivery_detail_id }}">
                                                    <td id="product_id" data_id="{{ $prod->product->id }}">{{ $prod->product->code }} - {{ $prod->product->name }}</td>
                                                    <td id="product_qty">{{ $prod->qty_do }}</td>
                                                    <td>
                                                        <input type="number" step="0.01" class="form-control"
                                                            id="qty_pack"
                                                            value="{{ $prod->qty_packed }}">
                                                    </td>
                                                    <td>{{ $prod->deliveryDetail->units->name }}</td>
                                                    <td>
                                                        <input disabled type="text" class="form-control"
                                                            value="{{ $prod->remark }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                    {{-- @foreach($details as $d)
                                        @foreach ($d->detail as $prod)
                                            <tr class="do_detail_{{ $prod->delivery_order_id }}" do_id="{{ $prod->delivery_order_id }}" data_id="{{ $prod->delivery_detail_id }}">
                                                <td id="product_id" data_id="{{ $prod->product->id }}">{{ $prod->product->code }} - {{ $prod->product->name }}</td>
                                                <td id="product_qty">{{ $prod->qty_do }}</td>
                                                <td>
                                                    <input type="number" step="0.01" class="form-control"
                                                        id="qty_pack"
                                                        value="{{ $prod->qty_packed }}">
                                                </td>
                                                <td>{{ $prod->deliveryDetail->units->name }}</td>
                                                <td>
                                                    <input disabled type="text" class="form-control"
                                                        value="{{ $prod->remark }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach --}}
                                @endif
                            </tbody>
                        </table>
                    </div>

                </form>

            </div>
        </div>

        <div class="text-end mt-3">
            <button type="submit" onclick="PackingList.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                Submit
            </button>

            <button type="reset" onclick="PackingList.back(this, event)"
                    class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>

    </div>
</div>
