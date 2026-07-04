<div class="card">
    <div class="card-body">
        <div class="card-title">
            <label>Product Harga</label>
        </div>
        <div class="table-responsive">
            <table class="table table-nowrap align-middle table-sm" id="table-price">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 20%;">Product</th>
                        <th style="width: 10%;">Satuan</th>
                        <th style="width: 15%;">Tipe</th>
                        <th style="width: 10%;">Min Qty</th> <!-- 🔹 kolom baru -->
                        <th style="width: 10%;">Max Qty</th> <!-- 🔹 kolom baru -->
                        <th style="width: 10%;">Harga</th>
                        <th style="width: 20%;">Tanggal Mulai Berlaku</th>
                    </tr>
                </thead>
                <tbody>
                    @if (strtolower($akses) == 'superadmin')
                        <tr>
                            <td colspan="7">
                                <a href="javascript:;" class="btn btn-primary btn-sm"
                                    onclick="Customer.addItemPrice(this, event)">Add Item</a>
                            </td>
                        </tr>
                    @endif

                    @foreach ($product_prices as $v)
                        <tr class="input" data_id="{{ $v->id }}">
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger" onclick="Customer.removeItemPrice(this, event)">
                                    <i class="bx bx-trash-alt"></i>
                                </button>
                            </td>
                            <td>
                                <div class="input-group">
                                    <button class="btn btn-outline-primary" type="button" id="button-addon1"
                                        onclick="Product.showDataProduct(this)">Pilih</button>
                                    <input id="product" name="product[]" type="text" class="form-control"
                                        error="product" placeholder="Pilih Data product" aria-label="Pilih Data Product"
                                        aria-describedby="button-addon1"
                                        value="{{ $v->product == '' ? '' : $v->product . '//' . $v->product_name }}">
                                </div>
                            </td>
                            <td id="uom">{{ $v->unit }}-{{ $v->unit_name }}</td>
                            <td>
                                <select id="type_price" name="type_price[]" class="form-control required"
                                    error="Type Price">
                                    @foreach ($data_price_list as $item)
                                        <option value="{{ $item->id }}"
                                            {{ $v->price_list == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <!-- 🔹 input baru: Min Qty -->
                                <input type="number" id="min_qty" name="min_qty[]" class="form-control required"
                                    error="Min Qty" min="1" value="{{ $v->min_qty ?? 1 }}">
                            </td>
                            <td>
                                <!-- 🔹 input baru: Min Qty -->
                                <input type="number" id="max_qty" name="max_qty[]" class="form-control required"
                                    error="Max Qty" min="1" value="{{ $v->max_qty ?? 1 }}">
                            </td>
                            <td>
                                <input type="number" id="price" name="price[]" class="form-control required"
                                    error="Harga" value="{{ $v->price }}">
                            </td>
                            <td>
                                <input type="date" id="date_start" name="date_start[]" class="form-control required"
                                    error="Tanggal Mulai" value="{{ $v->date_start }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
