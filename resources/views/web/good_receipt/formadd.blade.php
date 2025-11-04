<button type="button" id="btn-show-modal" class="" style="display: none;" data-bs-toggle="modal"
  data-bs-target="#data-modal-item"></button>
<div id="content-modal-form"></div>
<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Create {{ $title }}</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">{{ $title_parent }}</a></li>
                    <li class="breadcrumb-item active">Create {{ $title }}</li>
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
                <form onsubmit="GoodReceipt.submit(this, event)">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Nomor GR</label>
                                <input type="text" id="gr_number" class="form-control required"
                                    error="Nomor GR" placeholder="Auto Generate" readonly
                                    value="{{ isset($data->gr_number) ? $data->gr_number : 'AUTO' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Purchase Order</label>
                                <select class="form-control select2 required" id="purchase_order" error="Purchase Order" onchange="GoodReceipt.getListItemOutstandingPO(this)">
                                    <option value=""></option>
                                    @foreach ($purchase_orders as $po)
                                        <option value="{{ $po->id }}"
                                            {{ isset($data->purchase_order) && $data->purchase_order == $po->id ? 'selected' : '' }}
                                            vendor="{{ $po->vendor }}"
                                            vendor_name="{{ $po->vendors->nama_vendor }}">
                                            {{ $po->code }} - {{ $po->vendors->nama_vendor }} / {{ $po->status }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vendor</label>
                                <input type="text" id="vendor" class="form-control required"
                                    error="Vendor" placeholder="Vendor" disabled
                                    data_id="{{ isset($data->vendor) ? $data->vendor : '' }}"
                                    value="{{ isset($data->nama_vendor) ? $data->nama_vendor : '' }}">
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Diterima</label>
                                <input type="date" id="received_date" class="form-control required"
                                    error="Tanggal Diterima"
                                    value="{{ isset($data->received_date) ? $data->received_date : date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Diterima Oleh</label>
                                <input type="text" id="received_by" class="form-control"
                                    value="{{ session('nama_lengkap') ?? 'User Aktif' }}" readonly>
                            </div>

                              <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea id="remarks" class="form-control" placeholder="Catatan (opsional)">{{ isset($data->remarks) ? $data->remarks : '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Detail Barang -->
                    <h5 class="mb-3">Detail Barang</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-items">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%">PO Detail</th>
                                    <th style="width: 15%">Qty Received</th>
                                    <th style="width: 10%">Unit</th>
                                    <th style="width: 15%">Lot Number</th>
                                    <th style="width: 15%">Expired Date</th>
                                    <th style="width: 10%">Subtotal</th>
                                    <th style="width: 5%"></th>
                                </tr>
                            </thead>
                            <tbody id="detail-body">
                                <tr class="input" data_id="">
                                    <td>
                                        <div class="input-group">
                                            <button class="btn btn-outline-primary" type="button" id="button-addon1"
                                                onclick="GoodReceipt.showDataPOItem(this)">Pilih</button>
                                            <input disabled id="po_detail" name="po_detail" type="text" class="form-control required"
                                                error="PO Detail" placeholder="Pilih Item dari PO" aria-label="Pilih Item"
                                                aria-describedby="button-addon1" value="">
                                        </div>
                                    </td>
                                    <td><input type="number" class="form-control" id="qty_received" value="1" min="1" onkeyup="GoodReceipt.calcRow(this)"></td>
                                    <td data_id="" id="unit"></td>
                                    <td><input type="text" class="form-control" id="lot_number" placeholder="Nomor Lot"></td>
                                    <td><input type="date" class="form-control required" error="Expired Date" id="expired_date"></td>
                                    <td><input disabled type="text" class="form-control" id="subtotal" value="0"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="GoodReceipt.removeRow(this)"><i class="bx bx-trash-alt"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- <button type="button" class="btn btn-sm btn-primary mt-2" onclick="GoodReceipt.addRow()">+ Tambah Barang</button> --}}

                    <div class="text-end mt-4">
                        <h5>Total Qty: <span id="total-qty">{{ isset($data->total_qty) ? $data->total_qty : 0 }}</span></h5>
                    </div>

                </form>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" onclick="GoodReceipt.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">
                Submit
            </button>
            <button type="reset" onclick="GoodReceipt.back(this, event)" class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>
    </div>
</div>
