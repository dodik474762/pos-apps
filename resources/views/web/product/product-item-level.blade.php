<tr data_id="">
    <td class="text-center">
        <button class="btn btn-sm btn-danger" onclick="Product.removeItemLevel(this, event)"><i class="bx bx-trash-alt"></i></button>
    </td>
    <td>
        <select id="unit_dasar" name="unit_dasar[]" class="form-control required" error="Unit Dasar">
            @foreach ($data_satuan as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select id="unit_tujuan" name="unit_tujuan[]" class="form-control required" error="Unit Tujuan">
            @foreach ($data_satuan as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" id="nilai_konversi" name="nilai_konversi[]" class="form-control required" error="Nilai Konversi">
    </td>
</tr>