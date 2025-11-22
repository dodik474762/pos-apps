@foreach ($data as $item)
     <tr data_id="">
        <td id="product_id" data_id="{{ $item->product_id }}">{{ $item->product_code }} - {{ $item->product_name }}</td>
        <td><input type="number" qty_invoice="{{ $item->qty }}" onkeyup="SalesReturn.changeQtyRetur(this)" id="qty_return" class="form-control" min="1" max="{{ $item->outstanding_can_return }}" value="{{ $item->outstanding_can_return }}"></td>
        <td id="unit_price">{{ $item->price }}</td>
        <td id="discount_amount">{{ $item->discount }}</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="SalesReturn.removeRow(this)">
                <i class="bx bx-trash-alt"></i>
            </button>
        </td>
    </tr>
@endforeach
