<div class="card">
    <div class="card-body">
        <div class="card-title">
            <label>Product Harga</label>
        </div>
        <div class="table-responsive">
            <table class="table table-nowrap align-middle table-sm" id="table-price">
                <thead class="table-light">
                    <tr>
                        <th style="width: 10%;">#</th>
                        <th style="width: 10%;">Satuan</th>
                        <th style="width: 20%;">Tipe</th>
                        <th style="width: 10%;">Harga</th>
                        <th style="width: 20%;">Tanggal Mulai Berlaku</th>
                        <th style="width: 30%;">Customer</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4">
                            <a href="javascript:;" class="btn btn-primary btn-sm"
                                onclick="Product.addItemPrice(this, event)">Add
                                Item</a>
                        </td>
                    </tr>

                    @foreach ($product_prices as $v)
                        <input type="hidden" id="price_uom" name="price_uom[]" value="{{ $v->id }}">
                        <tr data_id="{{ $v->id }}">
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger" onclick="Product.removeItemPrice(this)"><i
                                        class="bx bx-trash-alt"></i></button>
                            </td>
                            <td>
                                <select id="uom_id" name="uom_id[]" class="form-control required" error="Unit">
                                    @foreach ($data_satuan_uom as $item)
                                        <option value="{{ $item['id'] }}" {{ $v->unit == $item['id'] ? 'selected' : '' }}>{{ $item['name'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select id="type_price" name="type_price[]" class="form-control required"
                                    error="Type Price">
                                    @foreach ($tipe_price as $item)
                                        <option value="{{ $item->id }}" {{ $v->price_list == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" id="price" name="price[]" class="form-control required" error="Harga"
                                    value="{{ $v->price }}">
                            </td>
                            <td>
                                <input type="date" id="date_start" name="date_start[]" class="form-control required"
                                    error="Tanggal Mulai" value="{{ $v->date_start }}">
                            </td>
                            <td>
                                <div class="input-group">
                                    <button class="btn btn-outline-primary" type="button" id="button-addon1"
                                        onclick="Product.showDataCustomer(this)">Pilih</button>
                                    <input readonly id="customer" name="customer[]" type="text" class="form-control"
                                        error="Customer" placeholder="Pilih Data Customer" aria-label="Pilih Data Customer"
                                        aria-describedby="button-addon1" value="{{ $v->customer == '' ? '' : $v->customer.'//'.$v->customer_name }}">
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>