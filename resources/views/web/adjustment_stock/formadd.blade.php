<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">
<button type="button" id="btn-show-modal" style="display: none;" data-bs-toggle="modal"
    data-bs-target="#data-modal-product"></button>
<div id="content-modal-form"></div>

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Create {{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $title_parent }}</a></li>
                    <li class="breadcrumb-item active">Create {{ $title }}</li>
                </ol>
            </div>

        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form onsubmit="AdjustmentStock.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>Code Adjustment</label>
                                <div>
                                    <input type="text" id="code" class="form-control required" error="Code"
                                        placeholder="Code" value="{{ isset($data->code) ? $data->code : 'AUTO' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Warehouse</label>
                                <select class="form-control select2 required" id="warehouse_id" error="Warehouse">
                                    @foreach ($warehouses as $w)
                                        <option value="{{ $w->id }}"
                                            {{ isset($data->warehouse_id) && $data->warehouse_id == $w->id ? 'selected' : '' }}>
                                            {{ $w->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>Remarks</label>
                                <div>
                                    <input type="text" id="remarks" class="form-control required" error="Remarks"
                                        placeholder="Remarks" value="{{ isset($data->remarks) ? $data->remarks : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-sm table-nowrap" id="table-routing">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Product</th>
                                            <th>Unit</th>
                                            <th>Qty Current Stock</th>
                                            <th>Qty Aktual</th>
                                            <th>Qty Adjustment</th>
                                            <th>Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($data->id))
                                            @foreach ($list_items as $item)
                                                <tr class="input" data_id="{{ $item['id'] }}">
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <div class="input-group">
                                                            <button class="btn btn-outline-primary" type="button"
                                                                id="button-addon1"
                                                                onclick="AdjustmentStock.showDataProduct(this)">Pilih</button>
                                                            <input readonly id="product" type="text"
                                                                class="form-control required" error="Product"
                                                                placeholder="Pilih Data Product"
                                                                aria-label="Pilih Data Product"
                                                                aria-describedby="button-addon1"
                                                                value="{{ $item['product'] . '//' . $item['product_name'] }}">
                                                        </div>
                                                    </td>
                                                    <td id="unit" data_id=""></td>
                                                    <td>
                                                        <input id="qty_current" type="number" readonly
                                                            class="form-control"
                                                            value="{{ isset($item['qty_current']) ? $item['qty_current'] : '' }}">
                                                    </td>
                                                    <td>
                                                        <input id="qty" type="number"
                                                            class="form-control required" error="Qty"
                                                            placeholder="Pilih Data Qty" aria-label="Pilih Data Qty"
                                                            aria-describedby="button-addon1"
                                                            value="{{ $item['qty'] }}"
                                                            onkeyup="AdjustmentStock.calculateQty(this)"
                                                            onchange="AdjustmentStock.calculateQty(this)">
                                                    </td>
                                                    <td>
                                                        <input id="qty_adjustment" type="number" readonly
                                                            class="form-control"
                                                            value="{{ isset($item['qty_adjustment']) ? $item['qty_adjustment'] : '' }}">
                                                    </td>
                                                    <td id="unit_price"></td>
                                                    <td class="text-center" id="action">
                                                        <button type="button"
                                                            onclick="AdjustmentStock.deleteItem(this, event)"
                                                            class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i
                                                                class="bx bx-trash-alt"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        @if (!isset($data->id))
                                            <tr class="input" data_id="">
                                                <td>&nbsp;</td>
                                                <td>
                                                    <div class="input-group">
                                                        <button class="btn btn-outline-primary" type="button"
                                                            id="button-addon1"
                                                            onclick="AdjustmentStock.showDataProduct(this)">Pilih</button>
                                                        <input readonly id="product" type="text"
                                                            class="form-control required" error="Product"
                                                            placeholder="Pilih Data Product"
                                                            aria-label="Pilih Data Product"
                                                            aria-describedby="button-addon1" value="">
                                                    </div>
                                                </td>
                                                <td id="unit" data_id=""></td>
                                                <td>
                                                    <input id="qty_current" type="number" readonly
                                                        class="form-control" value="">
                                                </td>
                                                <td>
                                                    <input id="qty" type="number"
                                                        class="form-control required" error="Qty"
                                                        placeholder="Pilih Data Qty" aria-label="Pilih Data Qty"
                                                        aria-describedby="button-addon1" value=""
                                                        onkeyup="AdjustmentStock.calculateQty(this)"
                                                        onchange="AdjustmentStock.calculateQty(this)">
                                                </td>
                                                <td>
                                                    <input id="qty_adjustment" type="number" readonly
                                                        class="form-control" value="">
                                                </td>
                                                <td id="unit_price"></td>
                                                <td class="text-center" id="action">
                                                    &nbsp;
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="" data_id="">
                                            <td colspan="3">
                                                <a href="javascript:;" class="btn btn-primary btn-sm"
                                                    onclick="AdjustmentStock.addItem(this, event)">Add
                                                    Item</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-end">
            <div>
                <button type="submit" onclick="AdjustmentStock.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="AdjustmentStock.cancel(this, event)" class="btn waves-effect">
                    Cancel
                </button>
            </div>
        </div>
        <!-- end select2 -->

    </div>


</div>
<!-- end row -->
