 @foreach ($data as $item)
     <tr class="input" data_id="" so_detail_id="{{ $item->so_detail_id }}">
        <td id="product" data_id="{{ $item->product_id }}">{{ $item->product_code }} - {{ $item->product_name }}</td>
        <td id="qty">{{ $item->qty }}</td>
        <td id="price">{{ $item->unit_price }}</td>
        <td id="discount">{{ $item->discount_amount }}</td>
        <td id="tax" data_id="{{ $item->tax_sale }}" type_tax="{{ $item->type_tax }}" rate="{{ $item->tax }}">{{ $item->tax_amount }}</td>
        <td id="subtotal">{{ $item->subtotal + $item->tax_amount }}</td>

        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger"
                    onclick="SalesInvoice.removeRow(this)">
                <i class="bx bx-trash-alt"></i>
            </button>
        </td>
    </tr>
 @endforeach
