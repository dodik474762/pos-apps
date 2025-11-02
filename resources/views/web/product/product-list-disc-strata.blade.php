<div class="card">
    <div class="card-body">
        <div class="card-title">
            <label>Product Diskon Strata</label>
        </div>
        <div class="table-responsive">
            <table class="table table-nowrap align-middle table-sm" id="table-disc-strata">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 10%;">Satuan</th>
                        <th style="width: 15%;">Kategori Customer</th>
                        <th style="width: 10%;">Min Qty</th>
                        <th style="width: 10%;">Max Qty</th>
                        <th style="width: 15%;">Disc Tipe</th>
                        <th style="width: 5%;">Disc Nilai</th>
                        <th style="width: 10%;">Tanggal Mulai Berlaku</th>
                        <th style="width: 20%;">Customer</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4">
                            <a href="javascript:;" class="btn btn-primary btn-sm"
                                onclick="Product.addItemDiscStrata(this, event)">Add
                                Item</a>
                        </td>
                    </tr>

                    @foreach ($product_disc_strata as $v)
                        <input type="hidden" id="disc_strata_id" name="disc_strata_id[]" value="{{ $v->id }}">
                        <tr data_id="{{ $v->id }}">
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger" onclick="Product.removeItemDiscStrata(this, event)"><i
                                        class="bx bx-trash-alt"></i></button>
                            </td>
                            <td>
                                <select id="uom_disc_id" name="uom_disc_id[]" class="form-control required" error="Unit">
                                    @foreach ($data_satuan_uom as $item)
                                        <option value="{{ $item['id'] }}" {{ $v->unit == $item['id'] ? 'selected' : '' }}>
                                            {{ $item['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select id="customer_category" name="customer_category[]" class="form-control"
                                    error="Kategori">
                                    <option value=""></option>
                                    @foreach ($data_customer_category as $item)
                                        <option value="{{ $item->id }}" {{ $v->customer_category == $item->id ? 'selected' : '' }}>{{ $item->category }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" id="min_qty" name="min_qty[]" class="form-control required"
                                    error="Min Qty" min="1" value="{{ $v->min_qty }}">
                            </td>
                            <td>
                                <input type="number" id="max_qty" name="max_qty[]" class="form-control required"
                                    error="Max Qty" min="1" value="{{ $v->max_qty }}">
                            </td>
                            <td>
                                <select id="disc_type" name="disc_type[]" class="form-control required" error="Disc Tipe">
                                    @foreach ($data_disc_tipe as $item)
                                        <option value="{{ $item }}" {{ $v->discount_type == $item ? 'selected' : '' }}>
                                            {{ strtoupper($item) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" id="disc_value" name="disc_value[]" class="form-control required"
                                    error="Disc Nilai" min="1" value="{{ $v->discount_value }}">
                            </td>
                            <td>
                                <input type="date" id="date_start" name="date_start_disc[]" class="form-control required"
                                    error="Tanggal Mulai" value="{{ $v->date_start }}">
                            </td>
                            <td>
                                <div class="input-group">
                                    <button class="btn btn-outline-primary" type="button" id="button-addon1"
                                        onclick="Product.showDataCustomer(this)">Pilih</button>
                                    <input id="customer_disc" name="customer_disc[]" type="text" class="form-control" error="Customer"
                                        placeholder="Pilih Data Customer" aria-label="Pilih Data Customer"
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
