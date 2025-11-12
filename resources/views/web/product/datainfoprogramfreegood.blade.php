@foreach ($disc as $item)
    <tr product_id="{{ $item->product }}"
        class="diskon-{{ $item->product }}"
        data_id="{{ $item->id }}"
        unit="{{ $item->unit }}"
        free_product_name="{{ $item->free_product_name }}"
        free_product="{{ $item->free_product }}"
        free_unit_name="{{ $item->free_unit_name }}"
        free_unit="{{ $item->free_unit }}"
        customer_category="{{ $item->customer_category }}"
        min_qty="{{ $item->min_qty }}"
        max_qty="{{ $item->max_qty }}"
        berlaku_from="{{ $item->date_start }}"
        customer="{{ $item->customer }}"
        free_qty="{{ $item->free_qty }}">
        <td>{{ $item->code }} - {{ $item->product_name }}</td>
        <td>{{ $item->unit_name }}</td>
        <td>{{ $item->category }}</td>
        <td>{{ $item->min_qty }}</td>
        <td>{{ $item->max_qty }}</td>
        <td>{{ $item->free_code }} - {{ $item->free_product_name }}</td>
        <td>{{ $item->free_unit_name }}</td>
        <td>{{ $item->free_qty }}</td>
        <td>{{ $item->nama_customer }}</td>
        <td>{{ $item->date_start }}</td>
        <td>&nbsp;</td>
    </tr>
@endforeach
