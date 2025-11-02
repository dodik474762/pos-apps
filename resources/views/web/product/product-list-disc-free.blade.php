<div class="card">
    <div class="card-body">
        <div class="card-title">
            <label>Product Diskon Free Good</label>
        </div>
        <div class="table-responsive">
            <table class="table table-nowrap align-middle table-sm" id="table-disc-free">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 10%;">Satuan</th>
                        <th style="width: 15%;">Kategori Customer</th>
                        <th style="width: 10%;">Min Qty</th>
                        <th style="width: 10%;">Max Qty</th>
                        <th style="width: 15%;">Free Product</th>
                        <th style="width: 5%;">Free Unit</th>
                        <th style="width: 5%;">Free Qty</th>
                        <th style="width: 10%;">Tanggal Mulai Berlaku</th>
                        <th style="width: 15%;">Customer</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4">
                            <a href="javascript:;" class="btn btn-primary btn-sm"
                                onclick="Product.addItemDiscFreeGood(this, event)">Add
                                Item</a>
                        </td>
                    </tr>

                    @foreach ($product_disc_free as $v)
                        <input type="hidden" id="disc_free_id" name="disc_free_id[]" value="{{ $v->id }}">
                        <tr data_id="{{ $v->id }}">
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger" onclick="Product.removeItemDiscFree(this, event)"><i
                                        class="bx bx-trash-alt"></i></button>
                            </td>
                            <td>
                                <select id="uom_disc_free_id" name="uom_disc_free_id[]" class="form-control required"
                                    error="Unit">
                                    @foreach ($data_satuan_uom as $item)
                                        <option value="{{ $item['id'] }}" {{ $v->unit == $item['id'] ? 'selected' : '' }}>
                                            {{ $item['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select id="customer_category_free" name="customer_category_free[]" class="form-control"
                                    error="Kategori">
                                    <option value=""></option>
                                    @foreach ($data_customer_category as $item)
                                        <option value="{{ $item->id }}" {{ $v->customer_category == $item->id ? 'selected' : '' }}>{{ $item->category }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" id="min_free_qty" name="min_free_qty[]" class="form-control required"
                                    error="Min Qty" min="1" value="{{ $v->min_qty }}">
                            </td>
                            <td>
                                <input type="number" id="max_free_qty" name="max_free_qty[]" class="form-control required"
                                    error="Max Qty" min="1" value="{{ $v->max_qty }}">
                            </td>
                            <td>
                                <div class="input-group">
                                    <button class="btn btn-outline-primary" type="button" id="button-addon1"
                                        onclick="Product.showDataProduct(this)">Pilih</button>
                                    <input id="product_free" name="product_free[]" type="text" class="form-control required"
                                        error="Product" placeholder="Pilih Data Product" aria-label="Pilih Data Product"
                                        aria-describedby="button-addon1"
                                        value="{{ $v->product_uom . '//' . $v->free_product . '//' . $v->product_name }}">
                                </div>
                            </td>
                            <td>
                                <input type="text" id="product_free_unit" name="product_free_unit[]"
                                    class="form-control required" error="Free Unit"
                                    value="{{ $v->free_unit . '//' . $v->unit_name }}">
                            </td>
                            <td>
                                <input type="number" id="free_qty" name="free_qty[]" class="form-control required"
                                    error="Free Qty" min="1" value="{{ $v->free_qty }}">
                            </td>
                            <td>
                                <input type="date" id="date_start_free" name="date_start_free[]"
                                    class="form-control required" error="Tanggal Mulai" value="{{ $v->date_start }}">
                            </td>
                            <td>
                                <div class="input-group">
                                    <button class="btn btn-outline-primary" type="button" id="button-addon1"
                                        onclick="Product.showDataCustomer(this)">Pilih</button>
                                    <input id="customer_disc_free" name="customer_disc_free[]" type="text" class="form-control"
                                        error="Customer" placeholder="Pilih Data Customer" aria-label="Pilih Data Customer"
                                        aria-describedby="button-addon1"
                                        value="{{ $v->customer == '' ? '' : $v->customer . '//' . $v->customer_name }}">
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
