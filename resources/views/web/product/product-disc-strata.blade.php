<tr data_id="">
    <td class="text-center">
        <button class="btn btn-sm btn-danger" onclick="Product.removeItemDiscStrata(this, event)"><i
                class="bx bx-trash-alt"></i></button>
    </td>
     <td>
        <select id="uom_disc_id" name="uom_disc_id[]" class="form-control required" error="Unit">
            @foreach ($data_satuan as $item)
                <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select id="customer_category" name="customer_category[]" class="form-control" error="Kategori">
            <option value=""></option>
            @foreach ($data_customer_category as $item)
                <option value="{{ $item->id }}">{{ $item->category }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" id="min_qty" name="min_qty[]" class="form-control required" error="Min Qty"
            min="1"
            value="1">
    </td>
    <td>
        <input type="number" id="max_qty" name="max_qty[]" class="form-control required" error="Max Qty"
            min="1"
            value="1">
    </td>
    <td>
        <select id="disc_type" name="disc_type[]" class="form-control required" error="Disc Tipe">
            @foreach ($data_disc_tipe as $item)
                <option value="{{ $item }}">{{ strtoupper($item) }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" id="disc_value" name="disc_value[]" class="form-control required" error="Disc Nilai"
            min="1"
            value="1">
    </td>
    <td>
        <input type="date" id="date_start" name="date_start_disc[]" class="form-control required" error="Tanggal Mulai"
            value="{{ date('Y-m-d') }}">
    </td>
    <td>
        <div class="input-group">
            <button class="btn btn-outline-primary" type="button" id="button-addon1"
                onclick="Product.showDataCustomer(this)">Pilih</button>
            <input id="customer" name="customer_disc[]" type="text" class="form-control" error="Customer"
                placeholder="Pilih Data Customer" aria-label="Pilih Data Customer" aria-describedby="button-addon1"
                value="">
        </div>
    </td>
</tr>
