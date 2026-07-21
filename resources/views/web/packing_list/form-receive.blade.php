<button type="button" id="btn-show-modal" style="display: none;" data-bs-toggle="modal"
    data-bs-target="#data-modal-product"></button>

<div id="content-modal-form"></div>

{{-- <input type="hidden" id="id" value="{{ $data->packing_list_id ?? '' }}"> --}}
<input type="hidden" id="url" value="{{ isset($data) ? route('packing-list-edit') : route('packing-list-add') }}">

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
                    <input type="hidden" id="id" value="{{ $data->id ?? '' }}">
                    <div class="row">

                        <!-- LEFT SIDE -->
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">Packing List No</label>
                                <input disabled type="text" id="packing_list_no" class="form-control"
                                    value="{{ $data->packing_list_no ?? 'AUTO' }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Packing Date</label>
                                <input disabled type="date" id="packing_date" class="form-control required"
                                    value="{{ $data->packing_date ?? date('Y-m-d') }}">
                            </div>

                            <!-- <div class="mb-3">
                                <label class="form-label">Vehicle No</label>
                                <input type="text" id="vehicle_no" class="form-control"
                                       value="{{ $data->vehicle_no ?? '' }}">
                            </div> -->

                            <div class="mb-3">
                                <label class="form-label">Vehicle No</label>
                                <select disabled name="vehicle_no" id="vehicle_no" class="form-control required select2"
                                    error="Vehicle Harus Diisi">
                                    <option value="">Pilih Kendaraan</option>
                                    @foreach ($list_kendaraan as $item)
                                        <option value="{{ $item->nopol }}"
                                            {{ isset($data->vehicle_no) && $data->vehicle_no == $item->nopol ? 'selected' : '' }}>
                                            {{ $item->name }} - {{ $item->nopol }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Driver Name</label>
                                <select disabled name="driver" id="driver" class="form-control required select2"
                                    error="Driver Harus Diisi">
                                    <option value="">Pilih Driver</option>
                                    @foreach ($list_users as $driver)
                                        <option value="{{ $driver->id }}"
                                            {{ isset($data->driver) && $data->driver == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">Expedition Name</label>
                                <input disabled type="text" id="expedition_name" class="form-control"
                                    value="{{ $data->expedition_name ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea disabled id="remarks" class="form-control">{{ $data->remarks ?? '' }}</textarea>
                            </div>

                        </div>

                    </div>

                    <hr>

                    {{-- ==================== DO LIST =========================== --}}
                    <div class="d-flex justify-content-between">
                        <h5>Delivery Orders</h5>
                    </div>

                    <div class="table-responsive mt-2">
                        <table class="table table-bordered" id="table-do">
                            <thead class="table-light">
                                <tr>
                                    <th>DO Number</th>
                                    <th>DO Date</th>
                                    <th>Customer</th>
                                    <th>Received Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody id="do-body">
                                @if (!empty($details))
                                    @foreach ($details as $row)
                                        @php
                                            $statusColor = '';
                                            if ($row->status == 'CONFIRMED') {
                                                $statusColor = 'text-success';
                                            }
                                            if ($row->status == 'NOT DELIVERED') {
                                                $statusColor = 'text-danger';
                                            }
                                            if ($row->status == 'CANCEL') {
                                                $statusColor = 'text-danger';
                                            }

                                            $photo_path = '';
                                            if ($row->photo_path != '') {
                                                $photo_path = $row->photo_path;
                                            }
                                        @endphp
                                        <tr data_id="{{ $row->id }}">
                                            <td id="do_number" data_id="{{ $row->delivery_order_id }}">
                                                {{ $row->do_number }} {!! $row->status == ''
                                                    ? ''
                                                    : '<label class="' .
                                                        $statusColor .
                                                        '" data_id="' .
                                                        $row->id .
                                                        '" status="' .
                                                        $row->status .
                                                        '" onclick="PackingList.cancelPl(this, event)">(' .
                                                        $row->status .
                                                        ') ' .
                                                        $row->remarks .
                                                        '</label>' !!}
                                                @if ($photo_path != '')
                                                    <a href="{{ $photo_path }}" target="_blank">Foto Pengiriman</a>
                                                @endif
                                            </td>
                                            <td id="do_date">{{ $row->do_date }}</td>
                                            <td id="do_customer" data_id="{{ $row->customer_id }}">
                                                {{ $row->customer_code }} - {{ $row->nama_customer }}</td>
                                            <td id="received_wh_date">{{ $row->receive_wh_date }}</td>
                                            <td class="text-center">
                                                <button type="button"
                                                    class="btn {{ $row->receive_wh_date == null ? 'btn-primary' : 'btn-warning' }} btn-sm"
                                                    onclick="PackingList.receive(this)"
                                                    data_id="{{ $row->delivery_order_id }}"
                                                    do_number="{{ $row->do_number }}"
                                                    receive_wh_date="{{ $row->receive_wh_date }}"
                                                    customer_name="{{ $row->customer_code }} - {{ $row->nama_customer }}">
                                                    <i class="bx bx-check-circle"></i>
                                                    {{ $row->receive_wh_date == null ? 'Receive' : 'Edit Receive' }}
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
                                @if (!empty($details))
                                    @php
                                        $totalRows = 1;
                                    @endphp
                                    @foreach ($grouped as $item)
                                        @foreach ($item as $items)
                                            @foreach ($items as $prod)
                                                <tr class="do_detail_{{ $prod->delivery_order_id }}"
                                                    do_id="{{ $prod->delivery_order_id }}"
                                                    data_id="{{ $prod->delivery_detail_id }}">
                                                    <td id="product_id" data_id="{{ $prod->product->id }}">
                                                        {{ $prod->product->code }} - {{ $prod->product->name }}</td>
                                                    <td id="product_qty">{{ $prod->qty_do }}</td>
                                                    <td>{{ $prod->qty_packed }}
                                                    </td>
                                                    <td>{{ $prod->deliveryDetail->units->name ?? '-' }}</td>
                                                    <td>{{ $prod->remark }}
                                                    </td>
                                                </tr>
                                                @php
                                                    $totalRows++;
                                                @endphp
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                    @php
                                        //echo $totalRows;
                                    @endphp
                                @endif
                            </tbody>
                        </table>
                    </div>

                </form>

            </div>
        </div>

        <div class="text-end mt-3">
            <button type="reset" onclick="PackingList.back(this, event)" class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>

    </div>
</div>
