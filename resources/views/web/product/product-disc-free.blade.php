<tr data_id="">
    <td class="text-center">
        <button class="btn btn-sm btn-danger" onclick="Product.removeItemDiscFree(this, event)"><i
                class="bx bx-trash-alt"></i></button>
    </td>
    <td>
        <select id="uom_disc_free_id" name="uom_disc_free_id[]" class="form-control required" error="Unit">
            @foreach ($data_satuan as $item)
                <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select id="customer_category_free" name="customer_category_free[]" class="form-control" error="Kategori">
            <option value=""></option>
            @foreach ($data_customer_category as $item)
                <option value="{{ $item->id }}">{{ $item->category }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" id="min_free_qty" name="min_free_qty[]" class="form-control required" error="Min Qty" min="1"
            value="1">
    </td>
    <td>
        <input type="number" id="max_free_qty" name="max_free_qty[]" class="form-control required" error="Max Qty" min="1"
            value="1">
    </td>
    <td>
        <div class="input-group">
            <button class="btn btn-outline-primary" type="button" id="button-addon1"
                onclick="Product.showDataProduct(this)">Pilih</button>
            <input id="product_free" name="product_free[]" type="text" class="form-control required" error="Product"
                placeholder="Pilih Data Product" aria-label="Pilih Data Product" aria-describedby="button-addon1"
                value="">
        </div>
    </td>
    <td>
        <input type="text" id="product_free_unit" name="product_free_unit[]" class="form-control required" error="Free Unit" value="">
    </td>
    <td>
         <input type="number" id="free_qty" name="free_qty[]" class="form-control required" error="Free Qty" min="1"
            value="1">
    </td>
    <td>
        <input type="date" id="date_start_free" name="date_start_free[]" class="form-control required" error="Tanggal Mulai"
            value="{{ date('Y-m-d') }}">
    </td>
    <td>
        <div class="input-group">
            <button class="btn btn-outline-primary" type="button" id="button-addon1"
                onclick="Product.showDataCustomer(this)">Pilih</button>
            <input id="customer_disc_free" name="customer_disc_free[]" type="text" class="form-control" error="Customer"
                placeholder="Pilih Data Customer" aria-label="Pilih Data Customer" aria-describedby="button-addon1"
                value="">
        </div>
    </td>
</tr>
