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
                        <th style="width: 10%;">Satuan</th>
                        <th style="width: 7.5%;" class="text-center">Channel</th>
                        <th style="width: 7.5%;" class="text-center">Sub Channel</th>
                        <th style="width: 10%;">Min Qty</th> <!-- 🔹 kolom baru -->
                        <th style="width: 10%;">Max Qty</th> <!-- 🔹 kolom baru -->
                        <th style="width: 10%;">Harga</th>
                        <th style="width: 20%;">Tanggal Mulai Berlaku</th>
                        <th style="width: 20%;">Customer</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7">
                            <a href="javascript:;" class="btn btn-primary btn-sm"
                                onclick="Product.addItemPrice(this, event)">Add Item</a>
                        </td>
                    </tr>

                    @foreach ($product_prices as $v)
                        <input type="hidden" id="price_uom" name="price_uom[]" value="{{ $v->id }}">
                        <tr data_id="{{ $v->id }}">
                            <td class="text-center">
                                @if($v->type != 'RETAIL')
                                    <button class="btn btn-sm btn-danger" onclick="Product.removeItemPrice(this, event)">
                                        <i class="bx bx-trash-alt"></i>
                                    </button>
                                @endif                                
                            </td>
                            <td>
                                <select id="uom_id" name="uom_id[]" class="form-control required" error="Unit">
                                    @foreach ($data_satuan_uom as $item)
                                        <option value="{{ $item['id'] }}" {{ $v->unit == $item['id'] ? 'selected' : '' }}>
                                            {{ $item['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-center">
                                <select id="type_price" name="type_price[]" class="form-control required d-none" error="Type Price">
                                    @foreach ($tipe_price as $item)
                                        <option value="{{ $item->id }}" {{ $v->price_list == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @if($v->type == 'RETAIL')
                                    {{ $v->type }}
                                    <select id="channel" name="channel[]" class="form-control required d-none" error="Channel">
                                        @foreach ($channels as $item)
                                            <option value="{{ $item->term_id }}" {{ $v->channel == $item->term_id ? 'selected' : '' }}>
                                                {{ $item->keterangan }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <select id="channel" name="channel[]" class="form-control required" error="Channel">
                                        @foreach ($channels as $item)
                                            <option value="{{ $item->term_id }}" {{ $v->channel == $item->term_id ? 'selected' : '' }}>
                                                {{ $item->keterangan }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif                                
                            </td>
                             <td class="text-center">
                                @if($v->type == 'RETAIL')
                                    {{ $v->type }}
                                    <select id="sub_channel" name="sub_channel[]" class="form-control required d-none" error="Sub Channel">
                                        @foreach ($sub_channels as $item)
                                            <option value="{{ $item->term_id }}" {{ $v->sub_channel == $item->term_id ? 'selected' : '' }}>
                                                {{ $item->keterangan }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <select id="sub_channel" name="sub_channel[]" class="form-control required" error="Sub Channel">
                                        @foreach ($sub_channels as $item)
                                            <option value="{{ $item->term_id }}" {{ $v->sub_channel == $item->term_id ? 'selected' : '' }}>
                                                {{ $item->keterangan }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif                                
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
                                <input type="number" {{ $v->type == 'RETAIL' ? 'readonly' : '' }} id="price" name="price[]" class="form-control required"
                                    error="Harga" value="{{ $v->price }}">
                            </td>
                            <td>
                                <input type="date" id="date_start" name="date_start[]" class="form-control required"
                                    error="Tanggal Mulai" value="{{ $v->date_start }}">
                            </td>
                            <td>
                                <div class="input-group">
                                    <button class="btn btn-outline-primary" type="button" id="button-addon1"
                                        onclick="Product.showDataCustomer(this)">Pilih</button>
                                    <input id="customer" name="customer[]" type="text" class="form-control"
                                        error="Customer" placeholder="Pilih Data Customer"
                                        aria-label="Pilih Data Customer" aria-describedby="button-addon1"
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
