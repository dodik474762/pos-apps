<tr data_id="">
    <td class="text-center">
        <button class="btn btn-sm btn-danger" onclick="Product.removeItemPrice(this, event)"><i
                class="bx bx-trash-alt"></i></button>
    </td>
    <td>
        <select id="uom_id" name="uom_id[]" class="form-control required" error="Unit">
            @foreach ($data_satuan as $item)
                <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select id="type_price" name="type_price[]" class="form-control required" error="Type Price">
            @foreach ($tipe_price as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" id="price" name="price[]" class="form-control required" error="Harga" value="">
    </td>
    <td>
        <input type="date" id="date_start" name="date_start[]" class="form-control required" error="Tanggal Mulai"
            value="{{ date('Y-m-d') }}">
    </td>
    <td>
        <div class="input-group">
            <button class="btn btn-outline-primary" type="button" id="button-addon1"
                onclick="Product.showDataCustomer(this)">Pilih</button>
            <input id="customer" name="customer[]" type="text" class="form-control" error="Customer"
                placeholder="Pilih Data Customer" aria-label="Pilih Data Customer" aria-describedby="button-addon1"
                value="">
        </div>
    </td>
</tr>
