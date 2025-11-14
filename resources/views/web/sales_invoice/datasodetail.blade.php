 @foreach ($data as $item)
    <tr class="input" data_id="" so_detail_id="{{ $item->id }}">
        <td id="product" data_id="{{ $item->product_id }}">{{ $item->product_code }} - {{ $item->product_name }}</td>
        <td id="uom" data_id="{{ $item->unit }}">{{ $item->unit_name }}</td>
        <td id="qty">{{ $item->qty }}</td>
        <td>
            <input {{ $item->free_for != '' ? 'disabled' : '' }} type="text" id="note" class="form-control" value="{{ $item->free_for == '' ? '' : 'FREE GOOD' }}">
        </td>
        <td class="text-center">
            {{-- <button type="button" class="btn btn-sm btn-danger"
                    onclick="DeliveryOrder.removeRow(this)">
                <i class="bx bx-trash-alt"></i>
            </button> --}}
        </td>
    </tr>
 @endforeach
