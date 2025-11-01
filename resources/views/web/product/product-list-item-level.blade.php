<div class="card">
    <div class="card-body">
        <div class="card-title">
            <label>Product Satuan Level</label>
        </div>
        <div class="table-responsive">
            <table class="table table-nowrap align-middle table-sm" id="table-satuan">
                <thead class="table-light">
                    <tr>
                        <th style="width: 10%;">#</th>
                        <th style="width: 40%;">Satuan Dasar</th>
                        <th style="width: 40%;">Satuan Tujuan</th>
                        <th style="width: 10%;">Konversi</th>
                    </tr>
                </thead>
                <tbody> 
                    <tr>
                        <td colspan="4">
                            <a href="javascript:;" class="btn btn-primary btn-sm"
                                onclick="Product.addItemLevel(this, event)">Add
                                Item</a>
                        </td>
                    </tr>    
                    
                    @foreach ($product_uoms as $v)
                        <input type="hidden" id="level_id" name="level_id[]" value="{{ $v->id }}">
                        <tr data_id="{{ $v->id }}">
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger" onclick="Product.removeItemLevel(this)"><i class="bx bx-trash-alt"></i></button>
                            </td>
                            <td>
                                <select id="unit_dasar" name="unit_dasar[]" class="form-control required" error="Unit Dasar">
                                    @foreach ($data_satuan as $item)
                                        <option value="{{ $item->id }}" {{ $v->unit_dasar == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select id="unit_tujuan" name="unit_tujuan[]" class="form-control required" error="Unit Tujuan">
                                    @foreach ($data_satuan as $item)
                                        <option value="{{ $item->id }}" {{ $v->unit_tujuan == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" id="nilai_konversi" name="nilai_konversi[]" class="form-control required" error="Nilai Konversi" value="{{ $v->nilai_konversi }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>