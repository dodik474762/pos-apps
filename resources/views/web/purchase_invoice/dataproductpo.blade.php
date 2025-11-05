@if(isset($po_details_outstanding) && count($po_details_outstanding) > 0)
    @foreach($po_details_outstanding as $item)
        <tr class="input" data_id="{{ $item->id }}" id_detail="">
            <td>
                <input disabled id="po_detail" data_id="{{ $item->product }}" name="po_detail" type="text" class="form-control required" error="PO Detail"
                        placeholder="Pilih Item dari PO" aria-label="Pilih Item" aria-describedby="button-addon1"
                        price="{{ $item->purchase_price }}"
                        value="{{ $item->product_code }} / {{ $item->product_name }}">
            </td>
            <td>
                <input type="number" class="form-control" id="qty_received"
                    value="{{ number_format($item->outstanding_qty, 2, '.', '') }}" min="1" max="{{ $item->outstanding_qty }}"
                    onkeyup="GoodReceipt.calcRow(this)">
            </td>
            <td data_id="{{ $item->unit }}" id="unit">{{ $item->unit_name }}</td>
            <td><input type="text" class="form-control" id="lot_number" placeholder="Nomor Lot"></td>
            <td><input type="date" class="form-control required" error="Expired Date" id="expired_date"></td>
            <td><input disabled type="text" class="form-control" id="subtotal"
                    value="{{ $item->purchase_price * $item->qty }}"></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="GoodReceipt.removeRow(this)">
                    <i class="bx bx-trash-alt"></i>
                </button>
            </td>
        </tr>
    @endforeach
@else
    {{-- Default baris kosong jika tidak ada item --}}
    <tr class="input" data_id="" id_detail="">
        <td>
            <input disabled id="po_detail" name="po_detail" type="text" class="form-control required" error="PO Detail"
                    placeholder="Pilih Item dari PO" aria-label="Pilih Item" aria-describedby="button-addon1" value="" data_id="" price="0">
        </td>
        <td><input type="number" class="form-control" id="qty_received" value="1" min="1"
                onkeyup="GoodReceipt.calcRow(this)"></td>
        <td data_id="" id="unit"></td>
        <td><input type="text" class="form-control" id="lot_number" placeholder="Nomor Lot"></td>
        <td><input type="date" class="form-control required" error="Expired Date" id="expired_date"></td>
        <td><input disabled type="text" class="form-control" id="subtotal" value="0"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="GoodReceipt.removeRow(this)">
                <i class="bx bx-trash-alt"></i>
            </button>
        </td>
    </tr>
@endif
